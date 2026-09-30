<?php

namespace App\Providers;

use App\Http\Requests\Validator\TwoFactorsTokenValidator;
use Illuminate\Database\Schema\Builder;
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
        
        $this->app['validator']->resolver(function($translator, $data, $rules, $messages) {
            return new TwoFactorsTokenValidator($translator, $data, $rules, $messages);
        });
    }
}
