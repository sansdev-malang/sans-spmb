<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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

        // Custom Branded Reset Password Email
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $schoolName = Setting::get('school_name', 'Sekolah Anak Saleh');
            $schoolTagline = Setting::get('school_tagline', 'Yayasan Pendidikan Anak Saleh');
            $schoolLogo = Setting::get('school_logo_url', '');
            $primaryColor = Setting::get('portal_primary_color', '#0D3B2C');
            $secondaryColor = Setting::get('portal_secondary_color', '#ffc107');
            $whatsapp = Setting::get('spmb_whatsapp_general', '081234567890');

            return (new MailMessage)
                ->subject("Permintaan Reset Password - {$schoolName}")
                ->view('emails.reset-password', [
                    'user' => $notifiable,
                    'token' => $token,
                    'resetUrl' => $resetUrl,
                    'schoolName' => $schoolName,
                    'schoolTagline' => $schoolTagline,
                    'schoolLogo' => $schoolLogo,
                    'primaryColor' => $primaryColor,
                    'secondaryColor' => $secondaryColor,
                    'whatsapp' => $whatsapp,
                ]);
        });
    }
}
