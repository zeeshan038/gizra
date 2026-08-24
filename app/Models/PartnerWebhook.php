<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerWebhook extends Model
{
    protected $fillable = [
        'restaurant_id', 'url', 'events', 'signing_secret', 'active', 'consecutive_failures', 'disabled_at',
    ];

    protected $casts = [
        'events' => 'array',
        'active' => 'boolean',
        'disabled_at' => 'datetime',
    ];

    protected $hidden = ['signing_secret'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }

    public function deliveries()
    {
        return $this->hasMany(PartnerWebhookDelivery::class);
    }
}
