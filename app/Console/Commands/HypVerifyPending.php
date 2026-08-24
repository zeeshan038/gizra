<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentRequest;

class HypVerifyPending extends Command
{
    protected $signature   = 'hyp:verify-pending';
    protected $description = 'Verify pending HyP payments and confirm orders for successful ones';

    // Must match PaypalPaymentController
    private string $base_url = 'https://pay.hyp.co.il/p/';
    private string $masof    = '4502319132';
    private string $passP    = '2LT1VTXNTA';
    private string $apiKey   = '2a03239cbe63ea7dab547a2aaaf115ce5fb0d1f1';
     
    public function handle(): void
    {
        // Find PaymentRequests that are still unpaid, created between 3 minutes and 24 hours ago.
        // payment_method='paypal' is how the app identifies HyP payments in this system.
        $pending = PaymentRequest::where('is_paid', 0)
            ->whereIn('payment_method', ['paypal', 'hyper_pay'])
            ->where('created_at', '<=', now()->subMinutes(3))
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        Log::channel('daily')->info('[HYP CRON] Checking ' . $pending->count() . ' pending HyP payment(s)');

        foreach ($pending as $data) {
            $this->verifyOne($data);
        }
    }

    private function verifyOne(PaymentRequest $data): void
    {
        // gateway_callback_url is unused in this integration; we repurpose it to cache
        // the HyP Sign value whenever a real callback arrives (set in the controller).
        $storedSign = $data->gateway_callback_url;

        $params = [
            'action' => 'APISign',
            'What'   => 'VERIFY',
            'KEY'    => $this->apiKey,
            'PassP'  => $this->passP,
            'Masof'  => $this->masof,
            'Order'  => $data->id,
        ];

        // If we have a cached Sign from a previous redirect/webhook, include it.
        if ($storedSign) {
            $params['Sign'] = $storedSign;
        }

        try {
            $response = Http::timeout(10)->get($this->base_url, $params);
            parse_str($response->body(), $result);

            Log::channel('daily')->info('[HYP CRON] VERIFY response', [
                'order'     => $data->id,
                'has_sign'  => (bool) $storedSign,
                'ccode'     => $result['CCode'] ?? 'N/A',
                'raw'       => $response->body(),
            ]);

            if (in_array((string) ($result['CCode'] ?? ''), ['0', '600', '700', '800'])) {
                // Already paid guard
                $data->refresh();
                if ($data->is_paid) {
                    return;
                }

                $data->update([
                    'payment_method' => 'hypay',
                    'is_paid'        => 1,
                    'transaction_id' => $result['Id'] ?? $data->transaction_id,
                ]);

                Log::channel('daily')->info('[HYP CRON] Payment confirmed — triggering order workflow', [
                    'order'        => $data->id,
                    'success_hook' => $data->success_hook,
                    'attribute_id' => $data->attribute_id,
                ]);

                if (function_exists($data->success_hook)) {
                    call_user_func($data->success_hook, $data);
                }
            }
        } catch (\Exception $e) {
            Log::channel('daily')->error('[HYP CRON] VERIFY exception', [
                'order'   => $data->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
