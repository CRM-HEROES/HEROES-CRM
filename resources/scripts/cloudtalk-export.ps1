#Requires -Version 5.1
<#
Export CloudTalk genere par le CRM. Ce fichier contient les identifiants API.
Conservez-le dans un emplacement prive et supprimez-le apres utilisation.
Relancez exactement le meme script et dossier pour reprendre un export interrompu.
#>
[CmdletBinding()]
param([string]$Dossier = '')

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
Add-Type -AssemblyName System.Net.Http

$config = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String('__CLOUDTALK_EXPORT_CONFIG_BASE64__')) | ConvertFrom-Json
$script:queryDateTo = $config.date_to
$utf8 = New-Object Text.UTF8Encoding($false)
$csvEncoding = New-Object Text.UTF8Encoding($true)
$baseUrl = 'https://my.cloudtalk.io'
$authorization = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes(('{0}:{1}' -f $config.api_key_id, $config.api_key_secret)))
$script:lastRequestAt = [DateTime]::MinValue
$script:client = $null
$writer = $null
$exportLock = $null
$failed = $false
$historyComplete = $false
$callsCount = 0
$audioCount = 0
$audioErrors = 0
$csvPublished = $false
$pageSize = 100

function Get-Field($Object, [string]$Name, $Default = '') {
    if ($null -ne $Object) {
        $property = $Object.PSObject.Properties[$Name]
        if ($null -ne $property -and $null -ne $property.Value) { return $property.Value }
    }
    return $Default
}

function Save-JsonAtomically([string]$Path, $Value) {
    $temporary = $Path + '.tmp'
    [IO.File]::WriteAllText($temporary, ($Value | ConvertTo-Json -Depth 100 -Compress), $utf8)
    Move-Item -LiteralPath $temporary -Destination $Path -Force
}

function Invoke-CloudTalkRequest([string]$Url, [bool]$Audio = $false) {
    # Automatic redirects are disabled: never forward the API credentials to a CDN.
    for ($attempt = 0; $attempt -lt 6; $attempt++) {
        $currentUrl = [Uri]$Url
        $response = $null
        try {
            for ($redirect = 0; $redirect -le 5; $redirect++) {
                if ($currentUrl.Scheme -ne 'https' -or $currentUrl.UserInfo -ne '') {
                    throw 'Redirection CloudTalk non securisee refusee.'
                }
                $elapsed = ([DateTime]::UtcNow - $script:lastRequestAt).TotalMilliseconds
                if ($elapsed -lt 1250) { Start-Sleep -Milliseconds ([int][Math]::Ceiling(1250 - $elapsed)) }
                $request = New-Object Net.Http.HttpRequestMessage([Net.Http.HttpMethod]::Get, $currentUrl)
                if ($currentUrl.GetLeftPart([UriPartial]::Authority) -eq $baseUrl) {
                    $request.Headers.Authorization = New-Object Net.Http.Headers.AuthenticationHeaderValue('Basic', $authorization)
                }
                if (-not $Audio) { $request.Headers.Accept.ParseAdd('application/json') }
                try {
                    $script:lastRequestAt = [DateTime]::UtcNow
                    $response = $script:client.SendAsync($request, [Net.Http.HttpCompletionOption]::ResponseHeadersRead).GetAwaiter().GetResult()
                } finally { $request.Dispose() }
                $status = [int]$response.StatusCode
                if ($status -ge 300 -and $status -lt 400) {
                    if (-not $Audio -or $redirect -eq 5 -or $null -eq $response.Headers.Location) {
                        throw 'Redirection CloudTalk inattendue.'
                    }
                    $currentUrl = New-Object Uri($currentUrl, $response.Headers.Location)
                    $response.Dispose()
                    $response = $null
                    continue
                }
                break
            }
            if ($status -eq 429 -or $status -ge 500) {
                $delay = [Math]::Min(60, [Math]::Pow(2, $attempt + 1))
                if ($null -ne $response.Headers.RetryAfter) {
                    if ($null -ne $response.Headers.RetryAfter.Delta) {
                        $delay = [Math]::Max($delay, $response.Headers.RetryAfter.Delta.TotalSeconds)
                    } elseif ($null -ne $response.Headers.RetryAfter.Date) {
                        $delay = [Math]::Max($delay, ($response.Headers.RetryAfter.Date.UtcDateTime - [DateTime]::UtcNow).TotalSeconds)
                    }
                }
                $response.Dispose()
                $response = $null
                if ($attempt -eq 5) { throw ('CloudTalk HTTP {0} apres plusieurs tentatives. Relancez le script pour reprendre.' -f $status) }
                Write-Host ('Limite ou indisponibilite CloudTalk (HTTP {0}), nouvelle tentative dans {1}s.' -f $status, [int][Math]::Ceiling($delay))
                Start-Sleep -Seconds ([int][Math]::Ceiling($delay))
                continue
            }
            if ($status -lt 200 -or $status -ge 300) {
                $response.Dispose()
                $response = $null
                throw ('CloudTalk HTTP {0}.' -f $status)
            }
            return $response
        } catch {
            if ($null -ne $response) { $response.Dispose() }
            # Network exceptions only. HTTP/authentication and local disk failures must stay visible.
            $exception = $_.Exception
            $networkFailure = $false
            while ($null -ne $exception) {
                if ($exception -is [Net.Http.HttpRequestException] -or $exception -is [Threading.Tasks.TaskCanceledException] -or $exception -is [IO.IOException]) {
                    $networkFailure = $true
                    break
                }
                $exception = $exception.InnerException
            }
            if (-not $networkFailure -or $attempt -eq 5) { throw }
            $delay = [int][Math]::Min(60, [Math]::Pow(2, $attempt + 1))
            Write-Host ('Connexion interrompue, nouvelle tentative dans {0}s.' -f $delay)
            Start-Sleep -Seconds $delay
        }
    }
}

function Get-CallsPage([int]$Page) {
    $query = 'limit={0}&page={1}&date_from={2}&date_to={3}' -f $pageSize, $Page, [Uri]::EscapeDataString($config.date_from), [Uri]::EscapeDataString($script:queryDateTo)
    $response = Invoke-CloudTalkRequest ($baseUrl + '/api/calls/index.json?' + $query)
    try {
        $body = $response.Content.ReadAsStringAsync().GetAwaiter().GetResult() | ConvertFrom-Json
    } finally { $response.Dispose() }
    $envelope = Get-Field $body 'responseData' $null
    if ($null -eq $envelope) { throw 'Reponse CloudTalk invalide : responseData absent.' }
    $status = [int](Get-Field $envelope 'status' 200)
    if ($status -ne 200) { throw ('CloudTalk a refuse la page {0} (statut {1}).' -f $Page, $status) }
    if ($null -eq $envelope.PSObject.Properties['data']) { throw 'Reponse CloudTalk invalide : liste des appels absente.' }
    $items = @(Get-Field $envelope 'data' @())
    $pageNumber = [int](Get-Field $envelope 'pageNumber' 0)
    $pageCount = [int](Get-Field $envelope 'pageCount' -1)
    $itemsCount = [int](Get-Field $envelope 'itemsCount' -1)
    if ($pageCount -lt 0 -or $itemsCount -lt 0 -or ($itemsCount -gt 0 -and $pageCount -lt 1) -or ($pageNumber -ne $Page -and $itemsCount -gt 0)) {
        throw ('Pagination CloudTalk invalide pour la page {0}.' -f $Page)
    }
    if ($items.Count -eq 0 -and $itemsCount -gt 0) {
        throw ('La page {0} est vide mais CloudTalk annonce encore des appels. Export incomplet.' -f $Page)
    }
    # Save an envelope so empty/single-item arrays remain arrays in Windows PowerShell.
    return [PSCustomObject]@{ page = $Page; pageCount = $pageCount; itemsCount = $itemsCount; items = $items }
}

function Get-AudioExtension([string]$Path) {
    # Inspect only a small header; never load a recording into memory to identify it.
    $source = [IO.File]::OpenRead($Path)
    try {
        $header = New-Object byte[] 64
        $count = $source.Read($header, 0, $header.Length)
        $ascii = [Text.Encoding]::ASCII.GetString($header, 0, $count)
        if ($count -ge 44 -and $ascii.Substring(0, 4) -eq 'RIFF' -and $ascii.Substring(8, 4) -eq 'WAVE') {
            $declaredLength = [long][BitConverter]::ToUInt32($header, 4) + 8
            if ($declaredLength -gt $source.Length -or $declaredLength -lt 44) { return '' }
            $hasFormat = $false
            $hasData = $false
            $offset = [long]12
            while ($offset + 8 -le $declaredLength) {
                [void]$source.Seek($offset, [IO.SeekOrigin]::Begin)
                if ($source.Read($header, 0, 8) -ne 8) { return '' }
                $chunk = [Text.Encoding]::ASCII.GetString($header, 0, 4)
                $size = [long][BitConverter]::ToUInt32($header, 4)
                if ($offset + 8 + $size -gt $declaredLength) { return '' }
                if ($chunk -eq 'fmt ' -and $size -ge 16) { $hasFormat = $true }
                if ($chunk -eq 'data' -and $size -gt 0) { $hasData = $true }
                if ($hasFormat -and $hasData) { return 'wav' }
                $offset += 8 + $size + ($size % 2)
            }
            return ''
        }
        if ($count -ge 27 -and $ascii.Substring(0, 4) -eq 'OggS' -and $header[4] -eq 0) { return 'ogg' }
        if ($count -ge 42 -and $ascii.Substring(0, 4) -eq 'fLaC' -and ($header[4] -band 127) -eq 0 -and $header[5] -eq 0 -and $header[6] -eq 0 -and $header[7] -eq 34) { return 'flac' }
        if ($count -ge 16 -and $ascii.Substring(4, 4) -eq 'ftyp') { return 'm4a' }
        if ($count -ge 16 -and $header[0] -eq 0x1A -and $header[1] -eq 0x45 -and $header[2] -eq 0xDF -and $header[3] -eq 0xA3) { return 'webm' }
        $mp3Offset = [long]0
        if ($count -ge 10 -and $ascii.Substring(0, 3) -eq 'ID3') {
            if ($header[3] -notin @(2, 3, 4) -or @($header[6..9] | Where-Object { $_ -ge 128 }).Count -gt 0) { return '' }
            $mp3Offset = 10 + ([long]$header[6] * 2097152) + ([long]$header[7] * 16384) + ([long]$header[8] * 128) + $header[9]
            if ($header[3] -eq 4 -and ($header[5] -band 16) -ne 0) { $mp3Offset += 10 }
        }
        if ($mp3Offset + 4 -le $source.Length) {
            [void]$source.Seek($mp3Offset, [IO.SeekOrigin]::Begin)
            if ($source.Read($header, 0, 4) -eq 4 -and $header[0] -eq 255 -and ($header[1] -band 224) -eq 224 -and ($header[1] -band 24) -ne 8 -and ($header[1] -band 6) -ne 0 -and ($header[2] -band 240) -notin @(0, 240) -and ($header[2] -band 12) -ne 12) { return 'mp3' }
        }
        return ''
    } finally { $source.Dispose() }
}

function Get-CallId($Item) {
    $cdr = Get-Field $Item 'Cdr' (Get-Field $Item 'Call' $Item)
    $id = [string](Get-Field $cdr 'id')
    if ($id -notmatch '^\d+$') { throw 'Identifiant appel CloudTalk invalide.' }
    return $id
}

function Get-VerifiedCallManifest([string]$CacheDirectory) {
    # Refresh metadata on every launch. Cached pages are staging files, never an API snapshot.
    $ids = New-Object 'Collections.Generic.HashSet[string]'
    $page = 1
    $expectedCount = -1
    $expectedPages = -1
    do {
        Write-Host ('Recuperation de l historique, page {0}...' -f $page)
        $data = Get-CallsPage $page
        if ($page -eq 1) {
            $expectedCount = [int]$data.itemsCount
            $expectedPages = [int]$data.pageCount
        } elseif ([int]$data.itemsCount -ne $expectedCount -or [int]$data.pageCount -ne $expectedPages) {
            throw 'La pagination CloudTalk a change pendant la lecture. Relancez le script.'
        }
        foreach ($item in @($data.items)) {
            if (-not $ids.Add((Get-CallId $item))) {
                throw 'Un appel apparait sur plusieurs pages CloudTalk. Relancez le script.'
            }
        }
        Save-JsonAtomically (Join-Path $CacheDirectory ('page-{0:D8}.json' -f $page)) $data
        $page++
    } while ($page -le $expectedPages)
    if ($ids.Count -ne $expectedCount) { throw 'Le nombre d appels recuperes ne correspond pas au total CloudTalk. Relancez le script.' }

    # An empty result has no pages to verify or process, regardless of whether the API reports 0 or 1 page.
    if ($expectedCount -eq 0) { return [PSCustomObject]@{ pageCount = 0; itemsCount = 0 } }

    # Verify the full ID set again before downloading audio; equal counts alone are insufficient.
    $verified = New-Object 'Collections.Generic.HashSet[string]'
    $pagesToRead = $expectedPages
    for ($page = 1; $page -le $pagesToRead; $page++) {
        Write-Host ('Verification des identifiants, page {0}/{1}...' -f $page, $pagesToRead)
        $data = Get-CallsPage $page
        if ([int]$data.itemsCount -ne $expectedCount -or [int]$data.pageCount -ne $expectedPages) {
            throw 'Les appels CloudTalk ont change entre les deux lectures. Relancez le script.'
        }
        foreach ($item in @($data.items)) {
            $id = Get-CallId $item
            if (-not $ids.Contains($id) -or -not $verified.Add($id)) {
                throw 'Les identifiants CloudTalk ont change entre les deux lectures. Relancez le script.'
            }
        }
    }
    if ($verified.Count -ne $expectedCount) { throw 'La verification des appels CloudTalk est incomplete. Relancez le script.' }
    return [PSCustomObject]@{ pageCount = $pagesToRead; itemsCount = $expectedCount }
}

function Get-Recording([string]$CallId) {
    $existing = @(Get-ChildItem -LiteralPath $audioDirectory -File -Filter ($CallId + '.*') | Where-Object { $_.Extension -ne '.part' -and $_.Length -gt 0 })
    foreach ($candidate in $existing) {
        $verifiedExtension = Get-AudioExtension $candidate.FullName
        if ($verifiedExtension -ne '' -and $candidate.Extension -eq ('.' + $verifiedExtension)) { return 'audios/' + $candidate.Name }
    }
    $temporary = Join-Path $audioDirectory ($CallId + '.part')
    $response = $null
    $stream = $null
    $file = $null
    $cancellation = $null
    try {
        $response = Invoke-CloudTalkRequest ($baseUrl + '/api/calls/recording/' + [Uri]::EscapeDataString($CallId) + '.json') $true
        $mediaType = ''
        if ($null -ne $response.Content.Headers.ContentType) { $mediaType = $response.Content.Headers.ContentType.MediaType.ToLowerInvariant() }
        if ($mediaType -match 'json|text|xml|html') { throw 'CloudTalk a retourne un message au lieu du fichier audio.' }
        try { $stream = $response.Content.ReadAsStreamAsync().GetAwaiter().GetResult() }
        catch { throw 'Lecture reseau audio interrompue. Relancez le script pour retenter cet appel.' }
        $file = [IO.File]::Open($temporary, [IO.FileMode]::Create, [IO.FileAccess]::Write, [IO.FileShare]::None)
        # Separate network reads from local writes: a disk failure stops the export;
        # a broken audio response can be retried on the next launch.
        $cancellation = New-Object Threading.CancellationTokenSource
        $cancellation.CancelAfter([TimeSpan]::FromMinutes(5))
        $buffer = New-Object byte[] 65536
        while ($true) {
            try { $read = $stream.ReadAsync($buffer, 0, $buffer.Length, $cancellation.Token).GetAwaiter().GetResult() }
            catch { throw 'Lecture reseau audio interrompue. Relancez le script pour retenter cet appel.' }
            if ($read -eq 0) { break }
            $file.Write($buffer, 0, $read)
        }
        if ($file.Length -eq 0) { throw 'Le fichier audio retourne est vide.' }
        if ($null -ne $response.Content.Headers.ContentLength -and $file.Length -ne $response.Content.Headers.ContentLength) { throw 'Le telechargement audio est incomplet. Relancez le script pour retenter cet appel.' }
        $file.Dispose()
        $file = $null
        $extension = Get-AudioExtension $temporary
        if ($extension -eq '') { throw 'Le contenu retourne ne correspond pas a un fichier audio reconnu.' }
        $destination = Join-Path $audioDirectory ($CallId + '.' + $extension)
        Move-Item -LiteralPath $temporary -Destination $destination -Force
        return 'audios/' + [IO.Path]::GetFileName($destination)
    } finally {
        if ($null -ne $file) { $file.Dispose() }
        if ($null -ne $stream) { $stream.Dispose() }
        if ($null -ne $response) { $response.Dispose() }
        if ($null -ne $cancellation) { $cancellation.Dispose() }
        if (Test-Path -LiteralPath $temporary) { Remove-Item -LiteralPath $temporary -Force }
    }
}

function Write-CsvRow([object[]]$Values) {
    $escaped = foreach ($value in $Values) {
        $text = [string]$value
        # CSV quotes do not prevent Excel/LibreOffice from evaluating formulas.
        # raw_json preserves the original values, including international numbers.
        if ($text -match '^[\s\x00-\x20]*[=+@-]') { $text = "'" + $text }
        '"' + $text.Replace('"', '""') + '"'
    }
    $writer.WriteLine(($escaped -join ','))
}

try {
    if ([string]::IsNullOrWhiteSpace($Dossier)) {
        $Dossier = Join-Path $PSScriptRoot ('export-cloudtalk_{0}_{1}' -f (Get-Field $config 'date_start' $config.date_from.Substring(0, 10)), (Get-Field $config 'date_end' $config.date_to.Substring(0, 10)))
    }
    $Dossier = [IO.Path]::GetFullPath($Dossier)
    $cacheDirectory = Join-Path $Dossier '.cloudtalk-export'
    $audioDirectory = Join-Path $Dossier 'audios'
    [void][IO.Directory]::CreateDirectory($cacheDirectory)
    # An exclusive lock prevents two terminals from overwriting the same export.
    $exportLock = [IO.File]::Open((Join-Path $cacheDirectory 'export.lock'), [IO.FileMode]::OpenOrCreate, [IO.FileAccess]::ReadWrite, [IO.FileShare]::None)
    if ($config.download_audio) { [void][IO.Directory]::CreateDirectory($audioDirectory) }
    # The state identifies the account and dates, but never stores credentials.
    $sha = [Security.Cryptography.SHA256]::Create()
    try {
        $fingerprint = [BitConverter]::ToString($sha.ComputeHash($utf8.GetBytes(('{0}|{1}|{2}' -f $config.api_key_id, $config.date_from, $config.date_to)))).Replace('-', '')
    } finally { $sha.Dispose() }
    $statePath = Join-Path $cacheDirectory 'export.json'
    if (Test-Path -LiteralPath $statePath) {
        $state = [IO.File]::ReadAllText($statePath, $utf8) | ConvertFrom-Json
        if ((Get-Field $state 'fingerprint') -ne $fingerprint) { throw 'Ce dossier contient un autre export. Choisissez un dossier different avec -Dossier.' }
        $script:queryDateTo = Get-Field $state 'effective_date_to' $config.date_to
    } else {
        # Freeze an ongoing period at the first launch so newly started calls cannot shift page offsets.
        $nowUtc = [DateTime]::UtcNow.ToString('yyyy-MM-dd HH:mm:ss', [Globalization.CultureInfo]::InvariantCulture)
        if ([string]::CompareOrdinal($config.date_from, $nowUtc) -le 0 -and [string]::CompareOrdinal($nowUtc, $config.date_to) -lt 0) {
            $script:queryDateTo = $nowUtc
        }
        Save-JsonAtomically $statePath ([PSCustomObject]@{ fingerprint = $fingerprint; date_from = $config.date_from; date_to = $config.date_to; effective_date_to = $script:queryDateTo })
    }
    $handler = New-Object Net.Http.HttpClientHandler
    $handler.AllowAutoRedirect = $false
    $script:client = New-Object Net.Http.HttpClient($handler)
    $script:client.Timeout = [TimeSpan]::FromMinutes(5)
    $manifest = Get-VerifiedCallManifest $cacheDirectory
    $csvTemporary = Join-Path $Dossier 'appels.csv.part'
    $writer = New-Object IO.StreamWriter($csvTemporary, $false, $csvEncoding)
    Write-CsvRow @('call_id', 'uuid', 'type', 'status', 'started_at', 'answered_at', 'ended_at', 'talking_time', 'waiting_time', 'wrapup_time', 'public_external', 'public_internal', 'recorded', 'agent_id', 'agent_name', 'agent_email', 'contact_id', 'contact_name', 'audio_path', 'audio_status', 'audio_error', 'raw_json')
    $seen = New-Object 'Collections.Generic.HashSet[string]'
    Write-Host ('Export du {0} au {1}. Dossier : {2}' -f $config.date_from, $script:queryDateTo, $Dossier)
    Write-Host 'Les filtres envoyes a CloudTalk sont en UTC.'
    if ((Get-Field $config 'timezone') -ne '') { Write-Host ('Fuseau des dates selectionnees dans le CRM : {0}' -f $config.timezone) }
    Write-Host 'Gardez ce terminal ouvert. Relancez le meme script pour reprendre et retenter les audios en erreur.'
    $page = 1
    while ($page -le [int]$manifest.pageCount) {
        $pagePath = Join-Path $cacheDirectory ('page-{0:D8}.json' -f $page)
        $pageData = [IO.File]::ReadAllText($pagePath, $utf8) | ConvertFrom-Json
        $items = @($pageData.items)
        if ($items.Count -eq 0) { break }
        foreach ($item in $items) {
            $cdr = Get-Field $item 'Cdr' (Get-Field $item 'Call' $item)
            $callId = [string](Get-Field $cdr 'id')
            if ($callId -notmatch '^\d+$') { throw ('Identifiant appel CloudTalk invalide a la page {0}.' -f $page) }
            if (-not $seen.Add($callId)) { continue }
            $agent = Get-Field $item 'Agent' $null
            $contact = Get-Field $item 'Contact' $null
            $recorded = Get-Field $cdr 'recorded' $null
            $recordedText = ([string]$recorded).ToLowerInvariant()
            $audioPath = ''
            $audioStatus = 'not_requested'
            $audioError = ''
            if ($config.download_audio) {
                if ($null -ne $recorded -and $recordedText -in @('0', 'false', 'no', '')) {
                    $audioStatus = 'not_recorded'
                } else {
                    try {
                        $audioPath = Get-Recording $callId
                        $audioStatus = 'downloaded'
                        $audioCount++
                    } catch {
                        # A local disk failure must stop the export rather than mark every remaining audio failed.
                        $cause = $_.Exception
                        while ($null -ne $cause.InnerException) { $cause = $cause.InnerException }
                        if ($cause -is [IO.IOException] -or $cause -is [UnauthorizedAccessException]) { throw }
                        if ($_.Exception.Message -match 'CloudTalk HTTP (401|403)\b') { throw }
                        $audioStatus = 'error'
                        $audioError = 'Enregistrement indisponible ou telechargement echoue. Relancez le script pour retenter.'
                        if ($_.Exception.Message -match 'CloudTalk HTTP (\d{3})\b') { $audioError = 'HTTP ' + $Matches[1] + '. ' + $audioError }
                        $audioErrors++
                        Write-Warning ('Audio de l appel {0} indisponible. Il sera retente au prochain lancement.' -f $callId)
                    }
                }
            }
            $agentName = ('{0} {1}' -f (Get-Field $agent 'firstname'), (Get-Field $agent 'lastname')).Trim()
            $contactName = ('{0} {1}' -f (Get-Field $contact 'firstname'), (Get-Field $contact 'lastname')).Trim()
            $agentName = Get-Field $agent 'fullname' (Get-Field $agent 'name' $agentName)
            $contactName = Get-Field $contact 'name' $contactName
            Write-CsvRow @(
                $callId, (Get-Field $cdr 'uuid' (Get-Field $cdr 'call_uuid')), (Get-Field $cdr 'type' (Get-Field $cdr 'direction')), (Get-Field $cdr 'status'),
                (Get-Field $cdr 'started_at'), (Get-Field $cdr 'answered_at'), (Get-Field $cdr 'ended_at'),
                (Get-Field $cdr 'talking_time'), (Get-Field $cdr 'waiting_time'), (Get-Field $cdr 'wrapup_time'),
                (Get-Field $cdr 'public_external' (Get-Field $cdr 'external_number')), (Get-Field $cdr 'public_internal' (Get-Field $cdr 'internal_number')),
                $recorded, (Get-Field $agent 'id'), $agentName, (Get-Field $agent 'email'), (Get-Field $contact 'id'), $contactName,
                $audioPath, $audioStatus, $audioError, ($item | ConvertTo-Json -Depth 100 -Compress)
            )
            $callsCount++
            Write-Progress -Activity 'Export CloudTalk' -Status ('Page {0} : {1} appels, {2} audios, {3} erreurs audio' -f $page, $callsCount, $audioCount, $audioErrors)
        }
        $writer.Flush()
        if ($page -ge [int]$manifest.pageCount) { break }
        $page++
    }
    if ($callsCount -ne [int]$manifest.itemsCount) {
        throw ('CloudTalk annonce {0} appels mais {1} identifiants uniques ont ete recuperes. Les donnees ont peut-etre change pendant l export. Recommencez dans un nouveau dossier avec -Dossier.' -f $manifest.itemsCount, $callsCount)
    }
    $historyComplete = $true
} catch {
    $failed = $true
    # Do not echo HTTP bodies, signed URLs, or credentials into the terminal/export files.
    Write-Warning ('Export interrompu : {0}' -f $_.Exception.Message)
    Write-Warning 'Relancez le meme script pour retenter. Le fichier appels.csv precedent est conserve. Si des lignes ont ete ecrites, elles restent dans appels.csv.part.'
} finally {
    if (-not $historyComplete) { $failed = $true }
    try {
        if ($null -ne $writer) {
            $writer.Dispose()
            if ($historyComplete -and -not $failed -and $audioErrors -eq 0) {
                $destination = Join-Path $Dossier 'appels.csv'
                # Publish only a completed export. Replacement is atomic and never deletes the old CSV first.
                if ([IO.File]::Exists($destination)) { [IO.File]::Replace($csvTemporary, $destination, [NullString]::Value) }
                else { [IO.File]::Move($csvTemporary, $destination) }
                $csvPublished = $true
            } else {
                Write-Warning 'CSV non publie : consultez appels.csv.part et bilan.txt. Un fichier appels.csv precedent reste intact.'
            }
            $result = @(
                ('Periode CloudTalk UTC : {0} -> {1}' -f $config.date_from, $script:queryDateTo),
                ('Historique exporte et coherence controlee : {0}' -f (-not $failed)),
                ('Appels exportes : {0}' -f $callsCount),
                ('Audios disponibles localement : {0}' -f $audioCount),
                ('Erreurs audio a retenter : {0}' -f $audioErrors),
                ('Nouveau CSV publie : {0}' -f $csvPublished),
                ('CSV de cette execution : {0}' -f $(if ($csvPublished) { 'appels.csv' } else { 'appels.csv.part' })),
                'audio_path est relatif au dossier contenant appels.csv.',
                'raw_json conserve tous les champs retournes par CloudTalk pour chaque appel.',
                'Les metadonnees sont relues a chaque lancement. Les audios valides sont reutilises.',
                'Le controle de coherence ne remplace pas un instantane immuable fourni par CloudTalk.',
                'Les cellules CSV pouvant etre interpretees comme formules sont prefixees par une apostrophe ; raw_json conserve les valeurs originales.',
                'Les fichiers et le script contiennent des donnees privees. Conservez-les dans un dossier protege.'
            )
            [IO.File]::WriteAllLines((Join-Path $Dossier 'bilan.txt'), $result, $utf8)
        }
    } finally {
        if ($null -ne $script:client) { $script:client.Dispose() }
        if ($null -ne $exportLock) { $exportLock.Dispose() }
        Write-Progress -Activity 'Export CloudTalk' -Completed
    }
}
if ($failed) { exit 1 }
if ($audioErrors -gt 0) { exit 2 }
Write-Host ('Export termine : {0} appels, {1} audios, {2} erreurs audio. Fichiers dans {3}' -f $callsCount, $audioCount, $audioErrors, $Dossier)
