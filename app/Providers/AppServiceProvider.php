<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
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
        // Deteksi saat user berhasil Login
        Event::listen(Login::class, function (Login $event) {
            // Update langsung ke database berdasarkan ID user yang login
            User::where('id', $event->user->getAuthIdentifier())
                ->update(['is_online' => true]);
        });

        // Deteksi saat user menekan Logout
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                // Update langsung ke database saat logout
                User::where('id', $event->user->getAuthIdentifier())
                    ->update(['is_online' => false]);
            }
        });
    }
}
