#Requires -Version 5.1
# Exercises the real HTTP helper using an in-memory .NET transport, without network access.
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0
Add-Type -AssemblyName System.Net.Http
$template = [IO.File]::ReadAllText((Join-Path $PSScriptRoot '../../resources/scripts/cloudtalk-export.ps1'))
$tokens = $null
$parseErrors = $null
$ast = [Management.Automation.Language.Parser]::ParseInput($template, [ref]$tokens, [ref]$parseErrors)
if ($parseErrors.Count -gt 0) { throw ($parseErrors | Out-String) }
foreach ($definition in $ast.FindAll({ param($node) $node -is [Management.Automation.Language.FunctionDefinitionAst] }, $false)) {
    . ([scriptblock]::Create($definition.Extent.Text))
}
Add-Type -TypeDefinition @'
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Threading;
using System.Threading.Tasks;
public class CloudTalkFixtureContent : StringContent {
    public bool IsDisposed;
    public CloudTalkFixtureContent() : base("{}") {}
    protected override void Dispose(bool disposing) {
        IsDisposed = true;
        base.Dispose(disposing);
    }
}
public class CloudTalkFixtureHandler : HttpMessageHandler {
    public readonly List<string> Auth = new List<string>();
    public readonly List<string> Accept = new List<string>();
    public readonly List<string> Urls = new List<string>();
    public readonly Queue<object> Results = new Queue<object>();
    protected override Task<HttpResponseMessage> SendAsync(HttpRequestMessage request, CancellationToken token) {
        Auth.Add(request.Headers.Authorization == null ? "" : request.Headers.Authorization.ToString());
        Accept.Add(request.Headers.Accept.ToString());
        Urls.Add(request.RequestUri.ToString());
        var result = Results.Dequeue();
        if (result is Exception) return Task.FromException<HttpResponseMessage>((Exception)result);
        return Task.FromResult((HttpResponseMessage)result);
    }
}
'@
$baseUrl = 'https://my.cloudtalk.io'
$authorization = 'fixture-encoded-credentials'
# No actual waits: capture backoff separately from the per-request rate limiter.
function Start-Sleep([int]$Seconds = 0, [int]$Milliseconds = 0) {
    if ($Seconds -gt 0) { [void]$script:delays.Add($Seconds) }
    $script:lastRequestAt = [DateTime]::MinValue
}
function New-FixtureResponse([int]$Status = 200, [string]$Location = '') {
    $response = New-Object Net.Http.HttpResponseMessage([Net.HttpStatusCode]$Status)
    $response.Content = New-Object CloudTalkFixtureContent
    if ($Location -ne '') { $response.Headers.Location = New-Object Uri($Location, [UriKind]::RelativeOrAbsolute) }
    return $response
}
function Invoke-Fixture([object[]]$Results, [bool]$Audio = $false, [string]$ExpectedError = '', [string]$Url = ($baseUrl + '/api/calls/index.json')) {
    $script:handler = New-Object CloudTalkFixtureHandler
    foreach ($result in $Results) { $script:handler.Results.Enqueue($result) }
    $script:delays = New-Object 'Collections.Generic.List[int]'
    $script:client = New-Object Net.Http.HttpClient($script:handler)
    $script:lastRequestAt = [DateTime]::MinValue
    $caught = ''
    try {
        try {
            $response = Invoke-CloudTalkRequest $Url $Audio
            $response.Dispose()
        } catch { $caught = $_.Exception.Message }
    } finally { $script:client.Dispose() }
    if ($ExpectedError -eq '' -and $caught -ne '') { throw $caught }
    if ($ExpectedError -ne '' -and $caught -notmatch $ExpectedError) { throw ('Expected error {0}; received {1}' -f $ExpectedError, $caught) }
    foreach ($result in $Results) {
        if ($result -is [Net.Http.HttpResponseMessage] -and -not $script:handler.Results.Contains($result) -and -not $result.Content.IsDisposed) {
            throw 'Every received response, including errors and redirects, must be disposed.'
        }
    }
}
Invoke-Fixture @((New-FixtureResponse))
if ($handler.Auth[0] -ne ('Basic ' + $authorization) -or $handler.Accept[0] -ne 'application/json') {
    throw 'Call history must send Basic authentication and the JSON accept header.'
}
Invoke-Fixture @((New-FixtureResponse 302 'https://cdn.example.invalid/audio.wav'), (New-FixtureResponse)) $true
if ($handler.Auth.Count -ne 2 -or $handler.Auth[0] -eq '' -or $handler.Auth[1] -ne '') {
    throw 'A redirected recording must never forward credentials to another host.'
}
Invoke-Fixture @((New-FixtureResponse 302 '/recordings/123.wav'), (New-FixtureResponse)) $true
if ($handler.Urls[1] -ne ($baseUrl + '/recordings/123.wav') -or $handler.Auth[1] -eq '') {
    throw 'Relative redirects on CloudTalk must retain the origin and authentication.'
}
Invoke-Fixture @((New-FixtureResponse 302 'http://cdn.example.invalid/audio.wav')) $true 'non securisee'
if ($handler.Auth.Count -ne 1) { throw 'An insecure redirect must never be requested.' }
Invoke-Fixture @((New-FixtureResponse 302 'https://user:password@cdn.example.invalid/audio.wav')) $true 'non securisee'
Invoke-Fixture @((New-FixtureResponse 302)) $true 'inattendue'
Invoke-Fixture @((New-FixtureResponse 302 '/recordings/123.wav')) $false 'inattendue'
$loop = @(1..6 | ForEach-Object { New-FixtureResponse 302 '/loop' })
Invoke-Fixture $loop $true 'inattendue'
if ($handler.Auth.Count -ne 6) { throw 'Redirect loops must stop after the configured hop limit.' }
foreach ($status in @(401, 403, 404)) {
    Invoke-Fixture @((New-FixtureResponse $status)) $false ('CloudTalk HTTP ' + $status)
    if ($handler.Auth.Count -ne 1 -or $delays.Count -ne 0) { throw 'Authentication and other permanent errors must not retry.' }
}
$throttle = New-FixtureResponse 429
$throttle.Headers.RetryAfter = New-Object Net.Http.Headers.RetryConditionHeaderValue([TimeSpan]::FromSeconds(7))
Invoke-Fixture @($throttle, (New-FixtureResponse))
if ($handler.Auth.Count -ne 2 -or $delays[0] -ne 7) { throw 'HTTP 429 must honor Retry-After seconds.' }
$throttle = New-FixtureResponse 429
$throttle.Headers.RetryAfter = New-Object Net.Http.Headers.RetryConditionHeaderValue([DateTimeOffset]::UtcNow.AddSeconds(30))
Invoke-Fixture @($throttle, (New-FixtureResponse))
if ($delays[0] -lt 25 -or $delays[0] -gt 30) { throw 'HTTP 429 must honor Retry-After dates.' }
Invoke-Fixture @((New-FixtureResponse 500), (New-FixtureResponse))
if ($handler.Auth.Count -ne 2 -or $delays[0] -ne 2) { throw 'Transient HTTP failures must retry with backoff.' }
$failures = @(1..6 | ForEach-Object { New-FixtureResponse 503 })
Invoke-Fixture $failures $false 'CloudTalk HTTP 503 apres plusieurs tentatives'
if ($handler.Auth.Count -ne 6 -or ($delays -join ',') -ne '2,4,8,16,32') { throw 'HTTP retries must be bounded and stop without a final sleep.' }
# A failed SendAsync never assigned a response. Catch must preserve the original exception and retry safely.
Invoke-Fixture @((New-Object Net.Http.HttpRequestException('transport fixture failure')), (New-FixtureResponse))
if ($handler.Auth.Count -ne 2 -or $delays[0] -ne 2) { throw 'Transport failure before a response must retry without null dereference.' }
$failures = @(1..6 | ForEach-Object { New-Object Net.Http.HttpRequestException('transport fixture failure') })
Invoke-Fixture $failures $false 'transport fixture failure'
if ($handler.Auth.Count -ne 6) { throw 'Transport retries must be bounded.' }
Write-Host 'CloudTalk HTTP authentication, redirects, disposal, Retry-After and bounded retry tests passed.'
