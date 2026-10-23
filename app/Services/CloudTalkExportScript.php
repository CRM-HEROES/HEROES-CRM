<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class CloudTalkExportScript
{
    public function generate(array $credentials, string $from, string $to, string $timezone, bool $audio): string
    {
        $config = [
            'api_key_id' => $credentials['api_key_id'],
            'api_key_secret' => $credentials['api_key_secret'],
            'date_from' => CarbonImmutable::parse($from, $timezone)->startOfDay()->utc()->format('Y-m-d H:i:s'),
            'date_to' => CarbonImmutable::parse($to, $timezone)->endOfDay()->utc()->format('Y-m-d H:i:s'),
            'timezone' => $timezone,
            'date_start' => $from,
            'date_end' => $to,
            'download_audio' => $audio,
        ];

        // Data is encoded separately so quotes/newlines in a credential cannot become PowerShell code.
        // Base64 is an encoding, not encryption: the downloaded script contains the API credentials.
        $encoded = base64_encode(json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $template = file_get_contents(resource_path('scripts/cloudtalk-export.ps1'));
        $script = str_replace('__CLOUDTALK_EXPORT_CONFIG_BASE64__', $encoded, $template);

        // Windows PowerShell 5.1 needs the BOM to interpret UTF-8 correctly.
        return "\xEF\xBB\xBF" . str_replace("\n", "\r\n", str_replace("\r\n", "\n", $script));
    }
}
