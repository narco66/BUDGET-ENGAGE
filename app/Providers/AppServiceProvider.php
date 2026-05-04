<?php

namespace App\Providers;

use App\Models\ConnexionLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
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
        Event::listen(Login::class, function (Login $event): void {
            $subject = $event->user;

            if (! $subject instanceof User) {
                return;
            }

            $subject->forceFill(['last_login_at' => now()])->saveQuietly();

            ConnexionLog::query()->create([
                'user_id' => $subject->id,
                'email' => $subject->email,
                'succes' => true,
                'adresse_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'connecte_le' => now(),
            ]);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            $credentials = $event->credentials;
            $email = is_array($credentials) ? ($credentials['email'] ?? null) : null;

            ConnexionLog::query()->create([
                'user_id' => $event->user instanceof User ? $event->user->id : null,
                'email' => is_string($email) ? $email : null,
                'succes' => false,
                'adresse_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'connecte_le' => now(),
            ]);
        });
    }
}
