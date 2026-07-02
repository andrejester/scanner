<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Models\Backend\Inbox;
use App\Models\Master\MasterInbox;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\Statistik;

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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Admin') ? true : null;
        });

        Event::listen(
            LogSuccessfulLogin::class,
        );
    }
}
