<?php

namespace App\Providers;

use App\Events\EngagementApproved;
use App\Events\EngagementStatusChanged;
use App\Listeners\CreateAuditAlertOnSensitiveTransition;
use App\Listeners\DispatchRiskScoring;
use App\Listeners\NotifyEngagementStatusChanged;
use App\Listeners\RecordEngagementReservation;
use App\Listeners\ReleaseEngagementReservation;
use App\Models\ConnexionLog;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Observers\LigneBudgetaireObserver;
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

        LigneBudgetaire::observe(LigneBudgetaireObserver::class);

        Event::listen(EngagementApproved::class, DispatchRiskScoring::class);
        Event::listen(EngagementApproved::class, RecordEngagementReservation::class);
        Event::listen(EngagementStatusChanged::class, CreateAuditAlertOnSensitiveTransition::class);
        Event::listen(EngagementStatusChanged::class, NotifyEngagementStatusChanged::class);
        Event::listen(EngagementStatusChanged::class, ReleaseEngagementReservation::class);
    }
}
