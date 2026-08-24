<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerWebhookDelivery extends Model
{
    protected $fillable = [
        'partner_webhook_id', 'delivery_id', 'event', 'order_id', 'last_response_code',
        'attempts', 'first_attempted_at', 'delivered_at', 'next_attempt_at', 'given_up_at',
    ];

    protected $casts = [
        'first_attempted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'given_up_at' => 'datetime',
    ];

    public function webhook()
    {
        return $this->belongsTo(PartnerWebhook::class, 'partner_webhook_id');
    }
}
