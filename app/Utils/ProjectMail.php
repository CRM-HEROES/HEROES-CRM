<?php

namespace App\Utils;

use App\Models\Project;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class ProjectMail
{
    public static function configure(Project $project): bool
    {
        $settings = ProjectSetting::get($project, 'email') ?: self::environmentSettings();

        if (!$settings) {
            return false;
        }

        if (data_get($settings, 'api_key')) {
            Config::set([
                'mail.default' => 'brevo',
                'mail.mailers.brevo.transport' => 'brevo',
                'mail.mailers.brevo.api_key' => data_get($settings, 'api_key'),
                'mail.mailers.brevo.from_address' => data_get($settings, 'from.address'),
                'mail.mailers.brevo.from_name' => data_get($settings, 'from.name'),
                'mail.from.address' => data_get($settings, 'from.address'),
                'mail.from.name' => data_get($settings, 'from.name'),
            ]);
            Mail::purge('brevo');
            return true;
        }

        Config::set([
            'mail.mailers.smtp.transport' => data_get($settings, 'driver'),
            'mail.mailers.smtp.host' => data_get($settings, 'host'),
            'mail.mailers.smtp.port' => data_get($settings, 'port'),
            'mail.from.address' => data_get($settings, 'from.address'),
            'mail.from.name' => data_get($settings, 'from.name'),
            'mail.mailers.smtp.encryption' => data_get($settings, 'encryption'),
            'mail.mailers.smtp.username' => data_get($settings, 'username'),
            'mail.mailers.smtp.password' => data_get($settings, 'password'),
        ]);
        Mail::purge('smtp');

        return true;
    }

    protected static function environmentSettings(): ?array
    {
        if (!env('BREVO_API_KEY') || !env('MAIL_FROM_ADDRESS')) {
            return null;
        }

        return [
            'api_key' => env('BREVO_API_KEY'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS'),
                'name' => env('MAIL_FROM_NAME', config('app.name')),
            ],
        ];
    }
}