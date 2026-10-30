<?php

namespace App\Providers;

use App\Http\Requests\Validator\TwoFactorsTokenValidator;
use App\Mail\BrevoTransport;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\Actions\MakeCallAction;
use App\Workflows\Actions\SendEmailAction;
use App\Workflows\Actions\SendSmsAction;
use Illuminate\Database\Schema\Builder;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One registry of workflow actions per process. The legacy campaign
        // actions are reused as they are; the communication actions are new.
        $this->app->singleton(ActionRegistry::class, function ($app) {
            $registry = new ActionRegistry();
            $registry->register('send_email', $app->make(SendEmailAction::class));
            $registry->register('send_sms', $app->make(SendSmsAction::class));
            $registry->register('make_call', $app->make(MakeCallAction::class));

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::defaultStringLength(191);

        $this->app->resolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('brevo', function (array $config) {
                return new BrevoTransport(
                    $config['api_key'],
                    $config['from_address'],
                    $config['from_name'] ?? '',
                );
            });
        });
        
        $this->app['validator']->resolver(function($translator, $data, $rules, $messages) {
            return new TwoFactorsTokenValidator($translator, $data, $rules, $messages);
        });
    }
}
