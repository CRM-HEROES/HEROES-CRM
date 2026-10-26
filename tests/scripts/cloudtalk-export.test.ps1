#Requires -Version 5.1
# Standalone fixture tests: pwsh -NoProfile -File tests/scripts/cloudtalk-export.test.ps1
# No CloudTalk credentials or network requests are used.
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0
$templatePath = Join-Path $PSScriptRoot '../../resources/scripts/cloudtalk-export.ps1'
$template = [IO.File]::ReadAllText([IO.Path]::GetFullPath($templatePath))
$tokens = $null
$parseErrors = $null
[void][Management.Automation.Language.Parser]::ParseInput($template, [ref]$tokens, [ref]$parseErrors)
if ($parseErrors.Count -gt 0) { throw ($parseErrors | Out-String) }

function Assert-True([bool]$Condition, [string]$Message) {
    if (-not $Condition) { throw $Message }
}

$temporary = Join-Path ([IO.Path]::GetTempPath()) ('cloudtalk-export-test-' + [Guid]::NewGuid().ToString('N'))
[void][IO.Directory]::CreateDirectory($temporary)
$utf8 = New-Object Text.UTF8Encoding($false)
$config = @{
    api_key_id = 'fixture-key'; api_key_secret = 'fixture-secret'
    date_from = '2026-10-01 00:00:00'; date_to = '2026-10-02 23:59:59'; download_audio = $true
} | ConvertTo-Json -Compress
$encoded = [Convert]::ToBase64String($utf8.GetBytes($config))
$fixture = @'

# This override is inserted after the real functions, immediately before execution.
$script:historyRequests = 0
function Invoke-CloudTalkRequest([string]$Url, [bool]$Audio = $false) {
    [IO.File]::AppendAllText((Join-Path $PSScriptRoot 'requests.log'), $Url + "`n")
    $response = New-Object Net.Http.HttpResponseMessage([Net.HttpStatusCode]::OK)
    if ($Audio) {
        if ($Url -match '/recording/103\.json' -and (Test-Path (Join-Path $PSScriptRoot 'fatal-audio'))) {
            $response.Dispose()
            throw 'CloudTalk HTTP 401.'
        }
        if ($Url -match '/recording/103\.json' -and -not (Test-Path (Join-Path $PSScriptRoot 'allow-103'))) {
            $response.Dispose()
            throw 'CloudTalk HTTP 404.'
        }
        $response.Content = New-Object Net.Http.ByteArrayContent(,[Convert]::FromBase64String('UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAIA+AAACABAAZGF0YQIAAAAAAA=='))
        $response.Content.Headers.ContentType = New-Object Net.Http.Headers.MediaTypeHeaderValue('audio/x-wav')
        return $response
    }
    $script:historyRequests++
    $page = 0
    if ($Url -match '[?&]page=(\d+)') { $page = [int]$Matches[1] }
    if ($page -eq 1) {
        $data = @(
            @{ Cdr = @{ id = '101'; recorded = $true; started_at = '2026-10-01 12:00:00'; public_external = '+33612345678'; note = "line one`nline two, with `"quotes`"" }; Agent = @{ id = 7; fullname = 'Ranto Test' }; Contact = @{ name = 'Contact Test' } },
            @{ Cdr = @{ id = '102'; recorded = $false } }
        )
    } elseif ($page -eq 2) {
        $data = @(@{ Cdr = @{ id = '103'; recorded = 1 } })
    } else { throw ('Unexpected page: ' + $page) }
    if ($script:historyRequests -gt 2 -and $page -eq 1 -and (Test-Path (Join-Path $PSScriptRoot 'drift-history'))) {
        $data[0].Cdr.id = '199'
    }
    if (Test-Path (Join-Path $PSScriptRoot 'empty-page')) { $data = @() }
    $payload = @{ responseData = @{ pageNumber = $page; pageCount = 2; itemsCount = 3; limit = 2; data = $data } } | ConvertTo-Json -Depth 20 -Compress
    $response.Content = New-Object Net.Http.StringContent($payload, [Text.Encoding]::UTF8, 'application/json')
    return $response
}

'@
$marker = "try {`n    if ([string]::IsNullOrWhiteSpace(`$Dossier))"
Assert-True ($template.Contains($marker)) 'Execution insertion point is missing.'
$source = $template.Replace('__CLOUDTALK_EXPORT_CONFIG_BASE64__', $encoded).Replace($marker, $fixture + $marker)
$scriptPath = Join-Path $temporary 'export.ps1'
[IO.File]::WriteAllText($scriptPath, $source, $utf8)
$shell = (Get-Process -Id $PID).Path
$output = Join-Path $temporary 'result'
try {
    [void][IO.Directory]::CreateDirectory($output)
    [IO.File]::WriteAllText((Join-Path $output 'appels.csv'), 'previous successful export')
    & $shell -NoLogo -NoProfile -File $scriptPath -Dossier $output
    Assert-True ($LASTEXITCODE -eq 2) 'The first export must report a partial audio failure.'
    Assert-True ([IO.File]::ReadAllText((Join-Path $output 'appels.csv')) -eq 'previous successful export') 'Missing audio must not overwrite an existing complete CSV.'
    $rows = @(Import-Csv -LiteralPath (Join-Path $output 'appels.csv.part'))
    Assert-True ($rows.Count -eq 3) 'Both pages, including the page with fewer items than requested, must be exported.'
    Assert-True ($rows[0].audio_path -eq 'audios/101.wav') ('CSV must contain the relative audio path. Actual: ' + $rows[0].audio_path)
    Assert-True ($rows[0].agent_name -eq 'Ranto Test' -and $rows[0].contact_name -eq 'Contact Test') 'Names must follow the documented CloudTalk fields.'
    Assert-True ((Get-Item -LiteralPath (Join-Path $output $rows[0].audio_path)).Length -eq 46) 'Audio must be streamed to the expected file.'
    Assert-True ($rows[1].audio_status -eq 'not_recorded') 'Calls without recordings must not trigger a download.'
    Assert-True ($rows[2].audio_status -eq 'error' -and $rows[2].audio_path -eq '') 'Unavailable audio must leave an empty path and a visible error.'
    $raw = $rows[0].raw_json | ConvertFrom-Json
    Assert-True ($raw.Cdr.note -eq "line one`nline two, with `"quotes`"") 'CSV must preserve quoted, multiline metadata.'
    $requests = @(Get-Content -LiteralPath (Join-Path $temporary 'requests.log'))
    Assert-True ($requests.Count -eq 6) 'Both history pages must be read twice before downloading two recorded calls.'
    [IO.File]::WriteAllText((Join-Path $temporary 'allow-103'), '')
    & $shell -NoLogo -NoProfile -File $scriptPath -Dossier $output
    Assert-True ($LASTEXITCODE -eq 0) 'Resuming must retry the missing audio successfully.'
    $rows = @(Import-Csv -LiteralPath (Join-Path $output 'appels.csv'))
    Assert-True ($rows.Count -eq 3 -and $rows[2].audio_path -eq 'audios/103.wav') 'Resume must not duplicate calls and must update audio paths.'
    Assert-True (-not (Test-Path (Join-Path $output 'appels.csv.part'))) 'Successful publication must consume the temporary CSV.'
    $requests = @(Get-Content -LiteralPath (Join-Path $temporary 'requests.log'))
    Assert-True ($requests.Count -eq 11) 'Resume must refresh and verify metadata while reusing previously downloaded audios.'
    [IO.File]::WriteAllText((Join-Path $temporary 'fatal-audio'), '')
    $interruptedOutput = Join-Path $temporary 'interrupted-result'
    [void][IO.Directory]::CreateDirectory($interruptedOutput)
    [IO.File]::WriteAllText((Join-Path $interruptedOutput 'appels.csv'), 'complete CSV before fatal failure')
    & $shell -NoLogo -NoProfile -File $scriptPath -Dossier $interruptedOutput
    Assert-True ($LASTEXITCODE -eq 1) 'A fatal audio authentication failure must fail the export.'
    Assert-True ([IO.File]::ReadAllText((Join-Path $interruptedOutput 'appels.csv')) -eq 'complete CSV before fatal failure') 'A mid-export fatal failure must preserve the previous complete CSV.'
    $interruptedRows = @(Import-Csv -LiteralPath (Join-Path $interruptedOutput 'appels.csv.part'))
    Assert-True ($interruptedRows.Count -eq 2) 'Rows written before a fatal failure must remain in the partial file.'
    Remove-Item -LiteralPath (Join-Path $temporary 'fatal-audio')
    $state = [IO.File]::ReadAllText((Join-Path $output '.cloudtalk-export/export.json'))
    Assert-True (-not $state.Contains('fixture-key') -and -not $state.Contains('fixture-secret')) 'Resume state must not contain credentials.'
    $withoutAudio = $config | ConvertFrom-Json
    $withoutAudio.download_audio = $false
    $withoutAudio.date_from = '2000-01-01 00:00:00'
    $withoutAudio.date_to = '2099-12-31 23:59:59'
    $withoutAudioEncoded = [Convert]::ToBase64String($utf8.GetBytes(($withoutAudio | ConvertTo-Json -Compress)))
    $noAudioSource = $template.Replace('__CLOUDTALK_EXPORT_CONFIG_BASE64__', $withoutAudioEncoded).Replace($marker, $fixture + $marker)
    $noAudioScript = Join-Path $temporary 'without-audio.ps1'
    [IO.File]::WriteAllText($noAudioScript, $noAudioSource, $utf8)
    $noAudioOutput = Join-Path $temporary 'without-audio'
    & $shell -NoLogo -NoProfile -File $noAudioScript -Dossier $noAudioOutput
    Assert-True ($LASTEXITCODE -eq 0) 'Export without audio must succeed.'
    Assert-True (-not (Test-Path (Join-Path $noAudioOutput 'audios'))) 'Unchecked audio must not create an audio directory.'
    $noAudioRows = @(Import-Csv -LiteralPath (Join-Path $noAudioOutput 'appels.csv'))
    Assert-True ($noAudioRows.Count -eq 3 -and $noAudioRows[0].audio_path -eq '' -and $noAudioRows[0].audio_status -eq 'not_requested') 'Unchecked audio must export all calls with empty paths.'
    $snapshot = [IO.File]::ReadAllText((Join-Path $noAudioOutput '.cloudtalk-export/export.json')) | ConvertFrom-Json
    Assert-True ($snapshot.effective_date_to -lt $withoutAudio.date_to) 'Ongoing periods must be frozen at first launch.'
    $requests = @(Get-Content -LiteralPath (Join-Path $temporary 'requests.log'))
    Assert-True ($requests.Count -eq 21) 'Unchecked audio must request two history pages twice without any recording request.'
    [IO.File]::WriteAllText((Join-Path $temporary 'drift-history'), '')
    $driftOutput = Join-Path $temporary 'drift-result'
    [void][IO.Directory]::CreateDirectory($driftOutput)
    [IO.File]::WriteAllText((Join-Path $driftOutput 'appels.csv'), 'previous successful export')
    & $shell -NoLogo -NoProfile -File $scriptPath -Dossier $driftOutput
    Assert-True ($LASTEXITCODE -eq 1) 'Changed IDs with equal totals must be rejected before audio downloads.'
    Assert-True ([IO.File]::ReadAllText((Join-Path $driftOutput 'appels.csv')) -eq 'previous successful export') 'An inconsistent manifest must not overwrite the previous CSV.'
    Remove-Item -LiteralPath (Join-Path $temporary 'drift-history')
    [IO.File]::WriteAllText((Join-Path $temporary 'empty-page'), '')
    & $shell -NoLogo -NoProfile -File $scriptPath -Dossier (Join-Path $temporary 'empty-result')
    Assert-True ($LASTEXITCODE -eq 1) 'An empty page with advertised calls must report an incomplete export.'
    Write-Host 'CloudTalk PowerShell export fixture tests passed.'
} finally {
    Remove-Item -LiteralPath $temporary -Recurse -Force
}
