<?php
declare(strict_types=1);

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Generador sencillo de archivos XLSX solo con datos tabulares.
 */
final class SimpleXlsxWriter
{
    /**
     * @var array<int, array{name: string, headers: array<int, string>, rows: array<int, array<int, int|float|string|bool|null>>}>
     */
    private array $sheets = [];

    /**
     * Agrega una hoja al libro.
     *
     * @param array<int, string> $headers
     * @param array<int, array<int, int|float|string|bool|null>> $rows
     */
    public function addSheet(string $name, array $headers, array $rows): void
    {
        if (empty($headers)) {
            throw new RuntimeException('Sheet headers cannot be empty.');
        }

        $cleanName = $this->uniqueSheetName($this->sanitizeSheetName($name));
        $columnCount = count($headers);

        $normalizedHeaders = array_map(static fn (mixed $header): string => (string) $header, $headers);
        $normalizedRows = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                $row = [$row];
            }
            $values = array_values($row);
            if (count($values) < $columnCount) {
                $values = array_pad($values, $columnCount, null);
            } elseif (count($values) > $columnCount) {
                $values = array_slice($values, 0, $columnCount);
            }
            $normalizedRows[] = $values;
        }

        $this->sheets[] = [
            'name'    => $cleanName,
            'headers' => $normalizedHeaders,
            'rows'    => $normalizedRows,
        ];
    }

    /**
     * Emite el contenido XLSX en la salida estándar.
     */
    public function output(): void
    {
        if (empty($this->sheets)) {
            throw new RuntimeException('No sheets available for export.');
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extension ZipArchive de PHP es requerida para generar archivos XLSX.');
        }

        $path = tempnam(sys_get_temp_dir(), 'xlsx_');
        if ($path === false) {
            throw new RuntimeException('Unable to create temporary file for XLSX export.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            throw new RuntimeException('Unable to initialise XLSX archive.');
        }

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypes());
        $zip->addFromString('_rels/.rels', $this->buildRootRelationships());
        $zip->addFromString('docProps/app.xml', $this->buildAppProperties());
        $zip->addFromString('docProps/core.xml', $this->buildCoreProperties());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->buildStyles());

        foreach ($this->sheets as $index => $sheet) {
            $zip->addFromString(
                sprintf('xl/worksheets/sheet%d.xml', $index + 1),
                $this->buildWorksheet($sheet)
            );
        }

        $zip->close();

        $stream = fopen($path, 'rb');
        if ($stream === false) {
            @unlink($path);
            throw new RuntimeException('Unable to read generated XLSX data.');
        }

        while (!feof($stream)) {
            $chunk = fread($stream, 8192);
            if ($chunk === false) {
                break;
            }
            echo $chunk;
        }
        fclose($stream);
        @unlink($path);
    }
    private function sanitizeSheetName(string $name): string
    {
        $name = trim($this->filterXmlString($name));
        $name = preg_replace('/[\\\\*\[\]\?:\/]/', ' ', $name) ?? 'Sheet';
        $name = $name === '' ? 'Sheet' : $name;
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        return $this->limitLength($name, 31);
    }

    private function uniqueSheetName(string $base): string
    {
        $existing = array_column($this->sheets, 'name');
        if (!in_array($base, $existing, true)) {
            return $base;
        }

        $suffix = 2;
        $cleanBase = $base;
        do {
            $suffixLabel = ' (' . $suffix . ')';
            $maxBaseLength = 31 - $this->stringLength($suffixLabel);
            $candidate = $this->limitLength($cleanBase, $maxBaseLength);
            $candidate = rtrim($candidate);
            if ($candidate === '') {
                $candidate = 'Sheet';
            }
            $candidate .= $suffixLabel;
            $suffix++;
        } while (in_array($candidate, $existing, true));

        return $candidate;
    }

    private function buildContentTypes(): string
    {
        $sheetOverrides = '';
        foreach ($this->sheets as $index => $sheet) {
            $sheetOverrides .= sprintf(
                '<Override PartName="/xl/worksheets/sheet%d.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>',
                $index + 1
            );
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . $sheetOverrides
            . '</Types>';
    }

    private function buildRootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function buildAppProperties(): string
    {
        $sheetCount = count($this->sheets);
        $titles = '';
        foreach ($this->sheets as $sheet) {
            $titles .= '<vt:lpstr>' . $this->escapeXml($sheet['name']) . '</vt:lpstr>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Laravel Export</Application>'
            . '<DocSecurity>0</DocSecurity>'
            . '<ScaleCrop>false</ScaleCrop>'
            . '<HeadingPairs><vt:vector size="2" baseType="variant">'
            . '<vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant>'
            . '<vt:variant><vt:i4>' . $sheetCount . '</vt:i4></vt:variant>'
            . '</vt:vector></HeadingPairs>'
            . '<TitlesOfParts><vt:vector size="' . $sheetCount . '" baseType="lpstr">'
            . $titles
            . '</vt:vector></TitlesOfParts>'
            . '<Company></Company>'
            . '<LinksUpToDate>false</LinksUpToDate>'
            . '<SharedDoc>false</SharedDoc>'
            . '<HyperlinksChanged>false</HyperlinksChanged>'
            . '<AppVersion>16.0300</AppVersion>'
            . '</Properties>';
    }

    private function buildCoreProperties(): string
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>BI Reports</dc:creator>'
            . '<cp:lastModifiedBy>BI Reports</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function buildWorkbook(): string
    {
        $sheetsXml = '';
        foreach ($this->sheets as $index => $sheet) {
            $sheetsXml .= sprintf(
                '<sheet name="%s" sheetId="%d" r:id="rId%d"/>',
                $this->escapeXml($sheet['name']),
                $index + 1,
                $index + 1
            );
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetsXml . '</sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRelationships(): string
    {
        $relationships = '';
        foreach ($this->sheets as $index => $sheet) {
            $relationships .= sprintf(
                '<Relationship Id="rId%d" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet%d.xml"/>',
                $index + 1,
                $index + 1
            );
        }

        $relationships .= sprintf(
            '<Relationship Id="rId%d" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>',
            count($this->sheets) + 1
        );

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $relationships
            . '</Relationships>';
    }

    private function buildStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    /**
     * @param array{name: string, headers: array<int, string>, rows: array<int, array<int, int|float|string|bool|null>>} $sheet
     */
    private function buildWorksheet(array $sheet): string
    {
        $rowsXml = '';
        $rows = array_merge([$sheet['headers']], $sheet['rows']);

        foreach ($rows as $rowIndex => $row) {
            $cellsXml = '';
            foreach ($row as $columnIndex => $value) {
                $cellsXml .= $this->buildCell($columnIndex, $rowIndex + 1, $value);
            }
            $rowsXml .= sprintf('<row r="%d">%s</row>', $rowIndex + 1, $cellsXml);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . '</worksheet>';
    }

    private function buildCell(int $columnIndex, int $rowNumber, mixed $value): string
    {
        $cellRef = $this->columnName($columnIndex) . $rowNumber;

        if ($value === null || $value === '') {
            return '<c r="' . $cellRef . '"/>';
        }

        if (is_bool($value)) {
            return '<c r="' . $cellRef . '" t="b"><v>' . ($value ? '1' : '0') . '</v></c>';
        }

        if (is_int($value) || is_float($value)) {
            return '<c r="' . $cellRef . '"><v>' . $value . '</v></c>';
        }

        $escaped = $this->escapeXml((string) $value);
        return '<c r="' . $cellRef . '" t="inlineStr"><is><t xml:space="preserve">' . $escaped . '</t></is></c>';
    }

    private function columnName(int $index): string
    {
        $name = '';
        $column = $index;
        do {
            $remainder = $column % 26;
            $name = chr(65 + $remainder) . $name;
            $column = intdiv($column, 26) - 1;
        } while ($column >= 0);

        return $name;
    }

    private function escapeXml(string $value): string
    {
        $filtered = $this->filterXmlString($value);
        return htmlspecialchars($filtered, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function filterXmlString(string $value): string
    {
        $filtered = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value);
        if ($filtered === null) {
            return '';
        }
        return $filtered;
    }
    private function limitLength(string $value, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max, 'UTF-8');
        }

        return substr($value, 0, $max);
    }

    private function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        return strlen($value);
    }
}







