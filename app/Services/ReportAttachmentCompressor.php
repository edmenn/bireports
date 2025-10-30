<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AttachmentTooLargeException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ReportAttachmentCompressor
{
    public function ensureUnderLimit(string $path, string $format, int $limitMb): string
    {
        if (! is_file($path)) {
            throw new AttachmentTooLargeException('Attachment file missing.');
        }

        if ($this->sizeInMb($path) <= $limitMb) {
            return $path;
        }

        $compressed = match (strtolower($format)) {
            'pdf' => $this->compressPdf($path),
            'ppt', 'pptx' => $this->compressPpt($path),
            default => null,
        };

        if ($compressed && is_file($compressed) && $this->sizeInMb($compressed) <= $limitMb) {
            return $compressed;
        }

        throw new AttachmentTooLargeException(
            sprintf('Attachment exceeds the allowed size of %d MB after compression.', $limitMb)
        );
    }

    protected function sizeInMb(string $path): float
    {
        return round(filesize($path) / 1024 / 1024, 2);
    }

    protected function compressPdf(string $path): ?string
    {
        $binary = $this->findExecutable(['gs', 'gswin64c', 'gswin32c']);
        if (! $binary) {
            Log::warning('Ghostscript executable not found. Skipping PDF compression.', ['path' => $path]);
            return null;
        }

        $outputPath = $path . '.compressed.pdf';
        $process = new Process([
            $binary,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/ebook',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-sOutputFile=' . $outputPath,
            $path,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::warning('Ghostscript compression failed.', [
                'path' => $path,
                'error' => $process->getErrorOutput(),
            ]);
            return null;
        }

        return $outputPath;
    }

    protected function compressPpt(string $path): ?string
    {
        $binary = $this->findExecutable(['soffice', 'libreoffice']);
        if (! $binary) {
            Log::warning('LibreOffice executable not found. Skipping PPT compression.', ['path' => $path]);
            return null;
        }

        $outputDir = dirname($path);
        $process = new Process([
            $binary,
            '--headless',
            '--convert-to',
            'pptx',
            '--outdir',
            $outputDir,
            $path,
        ]);
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::warning('LibreOffice compression failed.', [
                'path' => $path,
                'error' => $process->getErrorOutput(),
            ]);
            return null;
        }

        $converted = $this->convertedPath($path, 'pptx');
        return is_file($converted) ? $converted : null;
    }

    protected function convertedPath(string $original, string $extension): string
    {
        $info = pathinfo($original);
        return $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . ltrim($extension, '.');
    }

    protected function findExecutable(array $candidates): ?string
    {
        $finder = new ExecutableFinder();
        foreach ($candidates as $candidate) {
            $path = $finder->find($candidate);
            if ($path) {
                return $path;
            }
        }

        return null;
    }
}

