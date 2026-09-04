<?php

namespace App\Providers;

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
        \Carbon\Carbon::setLocale('id');
        \Carbon\CarbonImmutable::setLocale('id');

        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            if (\Illuminate\Support\Facades\Schema::hasTable('payment_gateways')) {
                $view->with('sidebarGateways', \App\Models\PaymentGateway::where('is_active', true)->get());
            } else {
                $view->with('sidebarGateways', collect());
            }
        });
    }
}
