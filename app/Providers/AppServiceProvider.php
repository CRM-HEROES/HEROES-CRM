<?php

namespace App\Providers;

use App\Http\Requests\Validator\TwoFactorsTokenValidator;
use App\Mail\BrevoTransport;
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
        //
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
