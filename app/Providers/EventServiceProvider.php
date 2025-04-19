<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\BookingCreated::class => [
            \App\Listeners\NotifyUsers::class,
            \App\Listeners\LogBookingActivity::class,
        ],
        \App\Events\BookingStatusUpdated::class => [
            \App\Listeners\NotifyUsers::class,
            \App\Listeners\LogBookingActivity::class,
        ],
        \App\Events\RiderLocationUpdated::class => [],
        \App\Events\RiderStatusUpdated::class => [],
    ];

    public function boot(): void
    {
        //
    }
}