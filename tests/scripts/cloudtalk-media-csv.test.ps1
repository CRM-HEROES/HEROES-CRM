#Requires -Version 5.1
# Network-free media and spreadsheet safety regressions.
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0
Add-Type -AssemblyName System.Net.Http
$template = [IO.File]::ReadAllText((Join-Path $PSScriptRoot '../../resources/scripts/cloudtalk-export.ps1'))
$tokens = $null
$errors = $null
$ast = [Management.Automation.Language.Parser]::ParseInput($template, [ref]$tokens, [ref]$errors)
if ($errors.Count -ne 0) { throw ($errors | Out-String) }
$names = @('Get-AudioExtension', 'Get-Recording', 'Write-CsvRow')
$functions = $ast.FindAll({ param($node) $node -is [Management.Automation.Language.FunctionDefinitionAst] -and $node.Name -in $names }, $false)
foreach ($function in $functions) { . ([scriptblock]::Create($function.Extent.Text)) }
function Assert-True([bool]$Condition, [string]$Message) { if (-not $Condition) { throw $Message } }
$temporary = Join-Path ([IO.Path]::GetTempPath()) ('cloudtalk-media-test-' + [Guid]::NewGuid().ToString('N'))
$audioDirectory = Join-Path $temporary 'audios'
[void][IO.Directory]::CreateDirectory($audioDirectory)
$baseUrl = 'https://my.cloudtalk.io'
# Mono 8-bit PCM, 8 kHz, one sample plus RIFF padding.
$wav = [byte[]](82,73,70,70,38,0,0,0,87,65,86,69,102,109,116,32,16,0,0,0,1,0,1,0,64,31,0,0,64,31,0,0,1,0,8,0,100,97,116,97,1,0,0,0,128,0)
$script:payload = $wav
$script:mediaType = 'application/octet-stream'
$script:requests = 0
$script:brokenNetwork = $false
Add-Type -TypeDefinition @'
using System;
using System.IO;
using System.Threading;
using System.Threading.Tasks;
public sealed class CloudTalkBrokenStream : Stream {
    public override bool CanRead { get { return true; } }
    public override bool CanSeek { get { return false; } }
    public override bool CanWrite { get { return false; } }
    public override long Length { get { throw new NotSupportedException(); } }
    public override long Position { get { throw new NotSupportedException(); } set { throw new NotSupportedException(); } }
    public override void Flush() { }
    public override int Read(byte[] buffer, int offset, int count) { throw new IOException("Fixture remote disconnect"); }
    public override Task<int> ReadAsync(byte[] buffer, int offset, int count, CancellationToken token) {
        var task = new TaskCompletionSource<int>();
        task.SetException(new IOException("Fixture remote disconnect"));
        return task.Task;
    }
    public override long Seek(long offset, SeekOrigin origin) { throw new NotSupportedException(); }
    public override void SetLength(long value) { throw new NotSupportedException(); }
    public override void Write(byte[] buffer, int offset, int count) { throw new NotSupportedException(); }
}
'@
function Invoke-CloudTalkRequest([string]$Url, [bool]$Audio = $false) {
    Assert-True ($Audio -and $Url -match '/api/calls/recording/\d+\.json$') 'Recording endpoint must be used.'
    $script:requests++
    $result = New-Object Net.Http.HttpResponseMessage([Net.HttpStatusCode]::OK)
    if ($script:brokenNetwork) {
        $result.Content = New-Object Net.Http.StreamContent((New-Object CloudTalkBrokenStream))
    } else { $result.Content = New-Object Net.Http.ByteArrayContent(,$script:payload) }
    $result.Content.Headers.ContentType = New-Object Net.Http.Headers.MediaTypeHeaderValue($script:mediaType)
    return $result
}
try {
    $path = Get-Recording '1'
    Assert-True ($path -eq 'audios/1.wav') 'Binary WAV must be named from its signature even without an audio content type.'
    Assert-True ((Get-Item (Join-Path $temporary $path)).Length -eq $wav.Length) 'Streamed WAV bytes must be preserved.'
    Assert-True ((Get-Recording '1') -eq $path -and $script:requests -eq 1) 'Verified existing audio must be reused.'
    [IO.File]::WriteAllText((Join-Path $audioDirectory '1.wav'), '<html>stale download</html>')
    Assert-True ((Get-Recording '1') -eq $path -and $script:requests -eq 2) 'Corrupt existing audio must be downloaded again.'
    $script:mediaType = 'audio/wav'
    foreach ($bad in @('<html>error</html>', '{"url":"https://example.invalid/audio.wav"}', 'not an audio file')) {
        $script:payload = [Text.Encoding]::UTF8.GetBytes($bad)
        $rejected = $false
        try { [void](Get-Recording '2') } catch { $rejected = $_.Exception.Message -match 'audio reconnu' }
        Assert-True $rejected 'HTML, JSON and arbitrary payloads must not become WAV files despite audio headers.'
        Assert-True (-not (Test-Path (Join-Path $audioDirectory '2.part')) -and -not (Test-Path (Join-Path $audioDirectory '2.wav'))) 'Invalid audio must not leave temporary or completed files.'
    }
    $script:payload = $wav[0..43]
    $rejected = $false
    try { [void](Get-Recording '3') } catch { $rejected = $_.Exception.Message -match 'audio reconnu' }
    Assert-True $rejected 'Truncated WAV must be rejected.'
    $script:payload = $wav
    $script:mediaType = 'application/json'
    $rejected = $false
    try { [void](Get-Recording '4') } catch { $rejected = $_.Exception.Message -match 'message' }
    Assert-True $rejected 'Explicit JSON media type must be rejected.'
    # A destination that is a directory produces a real local I/O failure.
    [void][IO.Directory]::CreateDirectory((Join-Path $audioDirectory '5.part'))
    $script:mediaType = 'audio/wav'
    $localFailure = $false
    try { [void](Get-Recording '5') } catch {
        $cause = $_.Exception
        while ($null -ne $cause.InnerException) { $cause = $cause.InnerException }
        $localFailure = $cause -is [IO.IOException] -or $cause -is [UnauthorizedAccessException]
    }
    Assert-True $localFailure 'Local file failures must remain distinguishable and fatal to the export.'
    $script:brokenNetwork = $true
    $networkFailure = $false
    try { [void](Get-Recording '6') } catch {
        $cause = $_.Exception
        while ($null -ne $cause.InnerException) { $cause = $cause.InnerException }
        $networkFailure = $_.Exception.Message -match 'Lecture reseau audio interrompue' -and $cause -isnot [IO.IOException]
    }
    Assert-True $networkFailure 'A remote broken stream must remain a per-audio retry rather than a fatal local disk error.'
    Assert-True (-not (Test-Path (Join-Path $audioDirectory '6.part'))) 'A broken stream must not leave partial media.'
    $script:brokenNetwork = $false
    $signatures = @{
        mp3 = [byte[]](255,251,144,0)
        ogg = [byte[]](79,103,103,83,0) + (New-Object byte[] 22)
        flac = [byte[]](102,76,97,67,0,0,0,34) + (New-Object byte[] 34)
        m4a = [byte[]](0,0,0,16,102,116,121,112,77,52,65,32,0,0,0,0)
        webm = [byte[]](26,69,223,163) + (New-Object byte[] 12)
    }
    foreach ($format in $signatures.Keys) {
        $fixturePath = Join-Path $temporary ($format + '.fixture')
        [IO.File]::WriteAllBytes($fixturePath, $signatures[$format])
        Assert-True ((Get-AudioExtension $fixturePath) -eq $format) ('Known media signature must be recognized: ' + $format)
    }

    $csvPath = Join-Path $temporary 'safe.csv'
    $writer = New-Object IO.StreamWriter($csvPath, $false, ([Text.Encoding]::UTF8))
    $values = @('=1+1', '+33612345678', '-1+2', '@SUM(A1)', " `t=2+2", "`r+1", 'ordinary', "a,`"b`"`nc", '{"phone":"+336"}')
    try {
        Write-CsvRow @('formula', 'phone', 'minus', 'at', 'spaces', 'control', 'plain', 'quoted', 'raw_json')
        Write-CsvRow $values
    } finally { $writer.Dispose() }
    $row = Import-Csv -LiteralPath $csvPath
    foreach ($name in @('formula', 'phone', 'minus', 'at', 'spaces', 'control')) { Assert-True ($row.$name.StartsWith("'")) ('Formula-like cell must be escaped: ' + $name) }
    Assert-True ($row.plain -eq 'ordinary' -and $row.quoted -eq $values[7]) 'Ordinary and quoted multiline cells must retain their contents.'
    Assert-True ($row.raw_json -eq $values[8] -and ($row.raw_json | ConvertFrom-Json).phone -eq '+336') 'Original JSON must preserve phone numbers without a spreadsheet prefix.'
    Write-Host 'CloudTalk media and CSV fixture tests passed.'
} finally { Remove-Item -LiteralPath $temporary -Recurse -Force }
