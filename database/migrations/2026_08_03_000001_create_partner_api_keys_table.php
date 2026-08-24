<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partner_api_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->string('name');
            $table->string('key_id', 64)->unique();
            // Reversibly encrypted (Laravel Crypt, keyed off APP_KEY), not a one-way hash —
            // the raw secret must be recoverable server-side to verify HMAC request signatures.
            $table->text('secret_hash');
            $table->json('scopes');
            $table->json('ip_allowlist')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->index('restaurant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_api_keys');
    }
};
