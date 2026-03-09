<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

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
        // 👇 Diz ao Laravel para construir o link apontando para o React 👇
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            // O link vai levar o Token de segurança e o e-mail
            return "{$frontendUrl}/redefinir-senha?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
        });
}
}