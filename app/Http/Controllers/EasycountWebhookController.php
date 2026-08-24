<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentRequest;

class EasycountWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        $payload = $request->all();

        Log::channel('daily')->info('[EASYCOUNT WEBHOOK] Incoming', [
            'ip'      => $request->ip(),
            'payload' => $payload,
        ]);

        // Easycount GET ping or connectivity test
        if ($request->isMethod('GET') || !empty($payload['EZCOUNT_TEST_REQUEST'])) {
            Log::channel('daily')->info('[EASYCOUNT WEBHOOK] Verification ping — acknowledged');
            return response()->json(['order_id' => '0', 'success' => true], 200);
        }

        // Validate API key if present in payload
        $incomingKey = $payload['api_key'] ?? $request->header('X-API-KEY');
        $expectedKey = config('services.easycount.api_key');
        if ($incomingKey && $expectedKey && !hash_equals((string) $expectedKey, (string) $incomingKey)) {
            Log::channel('daily')->warning('[EASYCOUNT WEBHOOK] API key mismatch — rejected');
            return response()->json(['order_id' => null, 'success' => false], 401);
        }

        $isSuccess = !empty($payload['success']);
        $docNumber = $payload['doc_number'] ?? null;
        $docUuid   = $payload['doc_uuid']   ?? null;

        // EZCount sends order_id = HYP Order param = our payment_request UUID when connected
        // to HYP at the gateway level. meta_data.payment_request_id is used when invoices
        // are created via the EZCount API with custom metadata.
        $paymentRequestId = $payload['order_id'] ?? null;

        if (!$paymentRequestId && !empty($payload['meta_data']) && is_array($payload['meta_data'])) {
            foreach ($payload['meta_data'] as $meta) {
                if (($meta['key'] ?? null) === 'payment_request_id') {
                    $paymentRequestId = $meta['value'] ?? null;
                    break;
                }
            }
        }

        $orderId = null;

        if ($paymentRequestId) {
            $paymentRequest = PaymentRequest::where('id', $paymentRequestId)->first();

            if ($paymentRequest) {
                $orderId = $paymentRequest->attribute_id;

                if ($isSuccess) {
                    // Save invoice details
                    $paymentRequest->update([
                        'easycount_doc_number' => $docNumber,
                        'easycount_doc_uuid'   => $docUuid,
                    ]);

                    if (!$paymentRequest->is_paid) {
                        $paymentRequest->update([
                            'payment_method' => 'hypay',
                            'is_paid'        => 1,
                        ]);

                        $paymentRequest->refresh();

                        Log::channel('daily')->info('[EASYCOUNT WEBHOOK] Payment confirmed — triggering success hook', [
                            'payment_request_id' => $paymentRequestId,
                            'order_id'           => $orderId,
                            'success_hook'       => $paymentRequest->success_hook,
                            'doc_number'         => $docNumber,
                        ]);

                        if (function_exists($paymentRequest->success_hook)) {
                            call_user_func($paymentRequest->success_hook, $paymentRequest);
                        }
                    } else {
                        Log::channel('daily')->info('[EASYCOUNT WEBHOOK] Already paid — invoice details saved', [
                            'payment_request_id' => $paymentRequestId,
                            'doc_number'         => $docNumber,
                        ]);
                    }
                } else {
                    Log::channel('daily')->warning('[EASYCOUNT WEBHOOK] success=false — payment/invoice failed', [
                        'payment_request_id' => $paymentRequestId,
                    ]);

                    // Only call failure hook if payment was never confirmed
                    if (!$paymentRequest->is_paid && function_exists($paymentRequest->failure_hook)) {
                        call_user_func($paymentRequest->failure_hook, $paymentRequest);
                    }
                }
            } else {
                Log::channel('daily')->warning('[EASYCOUNT WEBHOOK] PaymentRequest not found', [
                    'payment_request_id' => $paymentRequestId,
                ]);
            }
        } else {
            Log::channel('daily')->warning('[EASYCOUNT WEBHOOK] No payment_request_id in meta_data', [
                'doc_number' => $docNumber,
                'doc_uuid'   => $docUuid,
            ]);
        }

        return response()->json(['order_id' => $orderId, 'success' => true], 200);
    }
}
