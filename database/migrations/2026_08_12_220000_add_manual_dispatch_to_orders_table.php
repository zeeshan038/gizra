<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks orders created through the restaurant app's "Request GIZRA Driver"
 * form. Both columns are nullable and unused by every existing query, so
 * current order flows are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_manual_dispatch')) {
                $table->boolean('is_manual_dispatch')->default(0)->after('order_type');
            }
            if (!Schema::hasColumn('orders', 'manual_dispatch_key')) {
                // Idempotency key from the app. A repeated submit returns the
                // original order instead of dispatching a second driver.
                $table->string('manual_dispatch_key', 100)->nullable()->unique()->after('is_manual_dispatch');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'manual_dispatch_key')) {
                $table->dropUnique(['manual_dispatch_key']);
                $table->dropColumn('manual_dispatch_key');
            }
            if (Schema::hasColumn('orders', 'is_manual_dispatch')) {
                $table->dropColumn('is_manual_dispatch');
            }
        });
    }
};
