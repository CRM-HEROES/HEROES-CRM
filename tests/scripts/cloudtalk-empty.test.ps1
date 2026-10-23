#Requires -Version 5.1
# Standalone fixture: no credentials or network requests are used.
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0
$templatePath = Join-Path $PSScriptRoot '../../resources/scripts/cloudtalk-export.ps1'
$template = [IO.File]::ReadAllText([IO.Path]::GetFullPath($templatePath))
$utf8 = New-Object Text.UTF8Encoding($false)

function Assert-True([bool]$Condition, [string]$Message) {
    if (-not $Condition) { throw $Message }
}

$temporary = Join-Path ([IO.Path]::GetTempPath()) ('cloudtalk-empty-test-' + [Guid]::NewGuid().ToString('N'))
[void][IO.Directory]::CreateDirectory($temporary)
$config = @{
    api_key_id = 'fixture-key'; api_key_secret = 'fixture-secret'
    date_from = '2026-01-01 00:00:00'; date_to = '2026-01-01 23:59:59'; download_audio = $true
} | ConvertTo-Json -Compress
$encoded = [Convert]::ToBase64String($utf8.GetBytes($config))
$fixture = @'

function Get-CallsPage([int]$Page) {
    [IO.File]::AppendAllText((Join-Path $PSScriptRoot 'requests.log'), "$Page`n")
    return [PSCustomObject]@{ page = $Page; itemsCount = 0; pageCount = $script:emptyPageCount; items = @() }
}
function Invoke-CloudTalkRequest([string]$Url, [bool]$Audio = $false) {
    throw 'An empty export must not request any recording.'
}
$script:realManifest = ${function:Get-VerifiedCallManifest}
function Get-VerifiedCallManifest([string]$CacheDirectory) {
    $manifest = & $script:realManifest $CacheDirectory
    [IO.File]::WriteAllText((Join-Path $PSScriptRoot 'manifest.json'), ($manifest | ConvertTo-Json -Compress))
    return $manifest
}

'@
$marker = "try {`n    if ([string]::IsNullOrWhiteSpace(`$Dossier))"
Assert-True ($template.Contains($marker)) 'Execution insertion point is missing.'
$shell = (Get-Process -Id $PID).Path
try {
    # Providers can represent an empty result as zero pages or one empty page.
    foreach ($pageCount in @(0, 1)) {
        $scriptPath = Join-Path $temporary ('empty-{0}.ps1' -f $pageCount)
        $source = $template.Replace('__CLOUDTALK_EXPORT_CONFIG_BASE64__', $encoded)
        $source = $source.Replace($marker, ('$script:emptyPageCount = ' + $pageCount + "`n") + $fixture + $marker)
        [IO.File]::WriteAllText($scriptPath, $source, $utf8)
        $output = Join-Path $temporary ('result-{0}' -f $pageCount)
        & $shell -NoLogo -NoProfile -File $scriptPath -Dossier $output
        Assert-True ($LASTEXITCODE -eq 0) ('Empty export must succeed for pageCount=' + $pageCount)
        $requests = @(Get-Content -LiteralPath (Join-Path $temporary 'requests.log'))
        Assert-True ($requests.Count -eq 1) 'Each empty export must make exactly one history request.'
        Remove-Item -LiteralPath (Join-Path $temporary 'requests.log')
        $manifest = [IO.File]::ReadAllText((Join-Path $temporary 'manifest.json')) | ConvertFrom-Json
        Assert-True ($manifest.pageCount -eq 0 -and $manifest.itemsCount -eq 0) 'An empty manifest must contain zero pages and calls.'
        $csvPath = Join-Path $output 'appels.csv'
        $lines = @(Get-Content -LiteralPath $csvPath)
        Assert-True ($lines.Count -eq 1 -and $lines[0].Contains('"call_id"') -and $lines[0].Contains('"audio_path"')) 'Empty CSV must contain only the normal header.'
        Assert-True (@(Get-ChildItem -LiteralPath (Join-Path $output 'audios') -File).Count -eq 0) 'An empty export must contain no audio files.'
        Assert-True (-not (Test-Path -LiteralPath (Join-Path $output 'appels.csv.part'))) 'Successful empty CSV must be published completely.'
    }
    Write-Host 'CloudTalk empty export fixture tests passed.'
} finally {
    Remove-Item -LiteralPath $temporary -Recurse -Force
}
