Param(
    [string]$Root = "."
)

$ErrorActionPreference = 'Stop'

# Encodings
$utf8Strict = [System.Text.UTF8Encoding]::new($false, $true)  # no BOM, throw on invalid
$utf8NoBom = [System.Text.UTF8Encoding]::new($false)          # no BOM
$latin1 = [System.Text.Encoding]::GetEncoding(28591)

# Directories to skip (by folder name)
$skipDirs = @(
    '.git','vendor','node_modules','storage','.idea','.vscode','dist','build','bin','obj','.svn','.hg'
)

# File patterns to include (text-like files)
$patterns = @(
    '*.php','*.blade.php','*.js','*.ts','*.json','*.css','*.scss','*.html','*.htm',
    '*.md','*.yml','*.yaml','*.env','*.ini','*.txt','*.vue','*.xml','*.twig','*.conf',
    '*.csv','*.less','*.lock','*.stub','*.php.dist','.htaccess','*.ps1','*.psm1','*.bat','*.cmd'
)

function Test-ShouldSkipDir([IO.DirectoryInfo]$dir) {
    while ($null -ne $dir) {
        if ($skipDirs -contains $dir.Name) { return $true }
        $dir = $dir.Parent
    }
    return $false
}

function Test-MatchPattern([IO.FileInfo]$fi) {
    foreach ($p in $patterns) {
        if ($fi.Name -like $p) { return $true }
    }
    return $false
}

function Get-SuspiciousScore([string]$s) {
    $badSet = @('?','?','?')
    $goodSet = @('?','?','?','?','?','?','?','?','?','?','?','?','?','?','?','?')
    $bad = 0
    foreach ($ch in $badSet) { $bad += ([regex]::Matches($s, [regex]::Escape($ch))).Count }
    $good = 0
    foreach ($ch in $goodSet) { $good += ([regex]::Matches($s, [regex]::Escape($ch))).Count }
    return [pscustomobject]@{ Bad=$bad; Good=$good }
}

function Fix-File([IO.FileInfo]$fi) {
    $bytes = [IO.File]::ReadAllBytes($fi.FullName)

    # Strip UTF-8 BOM if present
    $removedBom = $false
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        if ($bytes.Length -gt 3) {
            $bytes = $bytes[3..($bytes.Length-1)]
        } else {
            $bytes = @()
        }
        $removedBom = $true
    }

    # Decode as UTF-8 strictly, fallback to Latin1 if needed
    $decodedAs = 'utf8'
    try {
        $text = $utf8Strict.GetString($bytes)
    } catch {
        $decodedAs = 'latin1'
        $text = $latin1.GetString($bytes)
    }

    $originalText = $text
    $score = Get-SuspiciousScore $text

    # Attempt up to 2 passes of mojibake repair: interpret current text as Latin1 bytes then decode as UTF-8
    for ($i = 0; $i -lt 2; $i++) {
        $candidate = $utf8NoBom.GetString($latin1.GetBytes($text))
        $candScore = Get-SuspiciousScore $candidate
        if ($candScore.Bad -lt $score.Bad -or $candScore.Good -gt $score.Good) {
            $text = $candidate
            $score = $candScore
        } else {
            break
        }
    }

    $textChanged = ($text -ne $originalText)
    $reencoded = ($decodedAs -ne 'utf8')
    $changed = $removedBom -or $textChanged -or $reencoded

    if ($changed) {
        $out = $utf8NoBom.GetBytes($text)
        [IO.File]::WriteAllBytes($fi.FullName, $out)
    }

    return [pscustomobject]@{
        Path = $fi.FullName
        RemovedBOM = $removedBom
        Reencoded = $reencoded
        TextChanged = $textChanged
        Changed = $changed
    }
}

$rootPath = Resolve-Path -LiteralPath $Root

$allFiles = Get-ChildItem -LiteralPath $rootPath -Recurse -File -ErrorAction SilentlyContinue | Where-Object {
    -not (Test-ShouldSkipDir $_.Directory) -and (Test-MatchPattern $_)
}

$results = @()
foreach ($f in $allFiles) {
    try {
        $res = Fix-File $f
        if ($res.Changed) { $results += $res }
    } catch {
        Write-Warning "Failed processing: $($f.FullName) - $($_.Exception.Message)"
    }
}

# Summary
$total = $allFiles.Count
$changedCount = ($results | Where-Object {$_.Changed}).Count
$bomRemoved = ($results | Where-Object {$_.RemovedBOM}).Count
$reencodedCount = ($results | Where-Object {$_.Reencoded}).Count
$textChangedCount = ($results | Where-Object {$_.TextChanged}).Count

Write-Host "Processed files: $total" -ForegroundColor Cyan
Write-Host "Modified files:  $changedCount" -ForegroundColor Cyan
Write-Host "- BOM removed:   $bomRemoved"
Write-Host "- Reencoded:     $reencodedCount"
Write-Host "- Text fixed:    $textChangedCount"

# List modified files (brief)
foreach ($r in $results) {
    Write-Host ("* {0} | BOM:{1} Reenc:{2} Text:{3}" -f $r.Path, $r.RemovedBOM, $r.Reencoded, $r.TextChanged)
}
