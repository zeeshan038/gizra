<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\ManualDispatchObserver;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the settlement observer for manual dispatch orders.
 *
 * Kept in its own provider rather than added to AppServiceProvider so the
 * feature can be switched off by removing a single line from config/app.php.
 */
class ManualDispatchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Order::observe(ManualDispatchObserver::class);
    }
}
