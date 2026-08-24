<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\PartnerWebhook;
use App\Models\PartnerWebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Fires order.created / order.status_changed to every active webhook a
 * restaurant has registered. HMAC-signed with the webhook's own secret
 * (same scheme as partner request signing). Retries non-2xx responses with
 * exponential backoff for 24h, then disables the webhook and stops.
 *
 * On the 'sync' queue driver (this app's current default) this job runs
 * inline and only gets a single attempt — the 24h retry window only takes
 * effect once QUEUE_CONNECTION is switched to database/redis and a queue
 * worker is running. Either way it's wrapped in try/catch at the call site
 * so a slow/failing webhook can never block or break placing an order.
 */
class DispatchPartnerWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 8;

    public function __construct(
        public int $webhookId,
        public string $event,
        public int $orderId,
        public string $deliveryId
    ) {
    }

    public function retryUntil(): Carbon
    {
        return now()->addHours(24);
    }

    public function backoff(): array
    {
        // ~1m, 5m, 15m, 30m, 1h, 3h, 6h, then give up (retryUntil caps this at 24h regardless)
        return [60, 300, 900, 1800, 3600, 10800, 21600];
    }

    public function handle(): void
    {
        $webhook = PartnerWebhook::find($this->webhookId);
        if (!$webhook || !$webhook->active) {
            return;
        }

        $order = Order::find($this->orderId);
        if (!$order) {
            return;
        }

        $delivery = PartnerWebhookDelivery::firstOrCreate(
            ['partner_webhook_id' => $webhook->id, 'delivery_id' => $this->deliveryId],
            ['event' => $this->event, 'order_id' => $order->id, 'first_attempted_at' => now()]
        );

        $payload = [
            'event' => $this->event,
            'delivery_id' => $this->deliveryId,
            'created_at' => now()->toIso8601String(),
            'data' => [
                'order_id' => $order->id,
                'restaurant_id' => $order->restaurant_id,
                'order_status' => $order->order_status,
                'order_amount' => $order->order_amount,
                'payment_method' => $order->payment_method,
            ],
        ];
        $body = json_encode($payload);
        $timestamp = (string) time();
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $webhook->signing_secret);

        try {
            $response = Http::withBody($body, 'application/json')
                ->withHeaders([
                    'X-Gizra-Event' => $this->event,
                    'X-Gizra-Signature' => $signature,
                    'X-Gizra-Timestamp' => $timestamp,
                    'X-Gizra-Delivery' => $this->deliveryId,
                ])
                ->timeout(10)
                ->post($webhook->url);

            $delivery->increment('attempts');
            $delivery->update(['last_response_code' => $response->status()]);

            if ($response->successful()) {
                $delivery->update(['delivered_at' => now()]);
                $webhook->update(['consecutive_failures' => 0]);
                Log::channel('daily')->info('[PARTNER WEBHOOK] delivered', [
                    'webhook_id' => $webhook->id, 'order_id' => $order->id, 'delivery_id' => $this->deliveryId,
                ]);
                return;
            }

            throw new \RuntimeException('Webhook endpoint returned HTTP ' . $response->status());
        } catch (\Throwable $e) {
            $delivery->increment('attempts');
            Log::channel('daily')->warning('[PARTNER WEBHOOK] delivery attempt failed', [
                'webhook_id' => $webhook->id, 'order_id' => $order->id, 'delivery_id' => $this->deliveryId,
                'attempt' => $this->attempts(), 'message' => $e->getMessage(),
            ]);
            $webhook->increment('consecutive_failures');
            $this->fail($e);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $webhook = PartnerWebhook::find($this->webhookId);
        if (!$webhook) {
            return;
        }
        PartnerWebhookDelivery::where('partner_webhook_id', $webhook->id)
            ->where('delivery_id', $this->deliveryId)
            ->update(['given_up_at' => now()]);

        $webhook->update(['active' => false, 'disabled_at' => now()]);
        Log::channel('daily')->error('[PARTNER WEBHOOK] gave up after 24h, webhook disabled', [
            'webhook_id' => $webhook->id, 'order_id' => $this->orderId, 'delivery_id' => $this->deliveryId,
        ]);
    }
}
