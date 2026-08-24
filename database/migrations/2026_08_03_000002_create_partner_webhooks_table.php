<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partner_webhooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->string('url', 2048);
            $table->json('events');
            $table->string('signing_secret');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();

            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->index('restaurant_id');
        });

        Schema::create('partner_webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_webhook_id');
            $table->uuid('delivery_id');
            $table->string('event');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedSmallInteger('last_response_code')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('first_attempted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('given_up_at')->nullable();
            $table->timestamps();

            $table->foreign('partner_webhook_id')->references('id')->on('partner_webhooks')->onDelete('cascade');
            $table->index(['partner_webhook_id', 'delivered_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('partner_webhook_deliveries');
        Schema::dropIfExists('partner_webhooks');
    }
};
