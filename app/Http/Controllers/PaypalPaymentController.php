<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Support\Facades\Validator;


class PaypalPaymentController extends Controller
{
    use Processor;

    // 🔐 Static Credentials
    private $base_url = 'https://pay.hyp.co.il/p/';
    private $masof = '4502319132';
    private $passP = '2LT1VTXNTA';
    private $apiKey = '2a03239cbe63ea7dab547a2aaaf115ce5fb0d1f1';

    // HYP approved transaction codes per https://developers.hyp.co.il/pay/security/transaction-validation
    // 0=Approved, 600=J2 card-check, 700=J5 authorization, 800=Postponed
    private const SUCCESS_CCODES = ['0', '600', '700', '800'];

    /**
     * 🚀 STEP 1: Create Payment
     */
    public function payment(Request $request)
    {
        // ── HYP DEBUG (temporary) ─────────────────────────────────────────────
        Log::channel('daily')->info('[HYP DEBUG] === Incoming request to /payment/hyperpay/pay ===', [
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'HTTP_REFERER' => $request->header('Referer'),
            'ORIGIN' => $request->header('Origin'),
            'USER_AGENT' => $request->header('User-Agent'),
            'HOST' => $request->header('Host'),
            'X_FORWARDED_FOR' => $request->header('X-Forwarded-For'),
            'REMOTE_ADDR' => $request->ip(),
            'all_headers' => $request->headers->all(),
        ]);
        // ─────────────────────────────────────────────────────────────────────

        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid payment_id'], 400);
        }

        $data = PaymentRequest::where(['id' => $request->payment_id, 'is_paid' => 0])->first();

        if (!$data) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $payer = json_decode($data->payer_information, true);

        $fullName = $payer['name'] ?? 'Customer';
        $nameParts = explode(' ', trim($fullName));

        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? 'User';

        $email = $payer['email'] ?? 'test@test.com';
        $phone = $payer['phone'] ?? '9999999999';

        $params = [
            'action' => 'APISign',
            'What' => 'SIGN',
            'KEY' => $this->apiKey,
            'PassP' => $this->passP,
            'Masof' => $this->masof,

            'Order' => $data->id,
            'Info' => 'Payment ID: ' . $data->id,
            'Amount' => $data->payment_amount,

            'ClientName' => $firstName,
            'ClientLName' => $lastName,
            'UserId' => $data->payer_id ?? 'guest',

            'email' => $email,
            'phone' => $phone,

            'Coin' => '1',
            'UTF8' => 'True',
            'UTF8out' => 'True',
            'Sign' => 'True',
            'tmp' => '3',

            'SuccessUrl' => route('hypay.success', ['payment_id' => $data->id]),
            'ErrorUrl' => route('hypay.failed', ['payment_id' => $data->id]),
        ];

        $response = Http::get($this->base_url, $params);

        // ── HYP DEBUG (temporary) ─────────────────────────────────────────────
        Log::channel('daily')->info('[HYP DEBUG] === HYP APISign request/response ===', [
            'request_url' => $this->base_url . '?' . http_build_query($params),
            'request_params' => $params,
            'response_status' => $response->status(),
            'response_headers' => $response->headers(),
            'response_body' => $response->body(),
        ]);
        // ─────────────────────────────────────────────────────────────────────

        parse_str($response->body(), $signedParams);

        if (!isset($signedParams['signature'])) {
            return response()->json([
                'error' => 'Signature not generated',
                'data' => $signedParams
            ]);
        }

        $paymentUrl = $this->base_url . '?' . http_build_query($signedParams);

        // ── HYP DEBUG (temporary) ─────────────────────────────────────────────
        Log::channel('daily')->info('[HYP DEBUG] === Redirecting browser to HYP payment page ===', [
            'payment_url' => $paymentUrl,
            'signed_params' => $signedParams,
        ]);
        // ─────────────────────────────────────────────────────────────────────

        return Redirect::away($paymentUrl);
    }

    /**
     * ✅ STEP 2: Success Callback (browser redirect from HyP)
     */
    public function success(Request $request)
    {
        $allParams = $request->all();

        // payment_id is our own query param on SuccessUrl — NOT part of HYP's signature.
        // Order is HYP's field that echoes back our payment_request UUID.
        $paymentId = $request->payment_id ?? $request->Order ?? null;

        // Strip our custom param so it doesn't contaminate the VERIFY signature check.
        $hypParams = $request->except(['payment_id']);

        Log::channel('daily')->info('[HYP SUCCESS] === Browser redirect received ===', [
            'payment_id' => $paymentId,
            'all_params' => $allParams,
        ]);

        // HYP portal does a connectivity test with empty params after URL config — ignore.
        if (empty($hypParams) || !isset($hypParams['Order'])) {
            Log::channel('daily')->info('[HYP SUCCESS] Connectivity test or missing Order — skipping processing');
            return response('<html><body><p>OK</p></body></html>', 200)->header('Content-Type', 'text/html');
        }

        $data = PaymentRequest::where('id', $paymentId)->first();

        if (!$data) {
            Log::channel('daily')->error('[HYP SUCCESS] PaymentRequest not found', ['payment_id' => $paymentId]);
            return $this->htmlResultPage('fail');
        }

        // Cache Sign for cron re-verification.
        if (!empty($hypParams['Sign'])) {
            $data->update(['gateway_callback_url' => $hypParams['Sign']]);
        }

        // Verify signature — only pass HYP's own params, not our custom payment_id.
        if (!$this->verifyWithHyp($hypParams)) {
            Log::channel('daily')->error('[HYP SUCCESS] Signature verification FAILED', [
                'payment_id' => $paymentId,
                'hyp_params' => $hypParams,
            ]);
            // Don't call failure_hook here — signature fail could be a replay attack.
            // The S2S webhook is the authoritative source; let it handle the real outcome.
            return $this->sendFinalResponse($data, 'fail');
        }

        // Accept HYP approved codes: 0=Approved, 600=J2, 700=J5, 800=Postponed
        if (in_array((string) ($hypParams['CCode'] ?? ''), self::SUCCESS_CCODES)) {
            if (!$data->is_paid) {
                $data->update([
                    'payment_method' => 'hypay',
                    'is_paid' => 1,
                    'transaction_id' => $hypParams['Id'] ?? null,
                ]);

                if (function_exists($data->success_hook)) {
                    try {
                        call_user_func($data->success_hook, $data);
                        Log::channel('daily')->info('[HYP SUCCESS] success_hook called', [
                            'hook' => $data->success_hook,
                            'attribute_id' => $data->attribute_id,
                        ]);
                    } catch (\Exception $e) {
                        Log::channel('daily')->error('[HYP SUCCESS] success_hook threw exception', ['error' => $e->getMessage()]);
                    }
                } else {
                    Log::channel('daily')->warning('[HYP SUCCESS] success_hook not found', ['hook' => $data->success_hook]);
                }
            }

            return $this->sendFinalResponse($data, 'success');
        }

        // Non-success CCode
        if (!$data->is_paid && function_exists($data->failure_hook)) {
            try {
                call_user_func($data->failure_hook, $data);
            } catch (\Exception $e) {
                Log::channel('daily')->error('[HYP SUCCESS] failure_hook threw exception on non-success CCode', ['error' => $e->getMessage()]);
            }
        }

        return $this->sendFinalResponse($data, 'fail');
    }

    /**
     * ❌ Failed Callback (browser redirect from HyP)
     */
    public function failed(Request $request)
    {
        $allParams = $request->all();
        $paymentId = $request->payment_id ?? $request->Order ?? null;

        Log::channel('daily')->info('[HYP FAILED] === Browser redirect received ===', [
            'payment_id' => $paymentId,
            'all_params' => $allParams,
        ]);

        // HYP portal connectivity test — empty params, nothing to process.
        if (empty($allParams) || !isset($allParams['Order'])) {
            Log::channel('daily')->info('[HYP FAILED] Connectivity test or missing Order — skipping processing');
            return response('<html><body><p>OK</p></body></html>', 200)->header('Content-Type', 'text/html');
        }

        $data = PaymentRequest::where('id', $paymentId)->first();

        if (!$data) {
            Log::channel('daily')->error('[HYP FAILED] PaymentRequest not found', ['payment_id' => $paymentId]);
            return $this->htmlResultPage('fail');
        }

        if (!$data->is_paid && function_exists($data->failure_hook)) {
            try {
                call_user_func($data->failure_hook, $data);
                Log::channel('daily')->info('[HYP FAILED] failure_hook called', ['hook' => $data->failure_hook]);
            } catch (\Exception $e) {
                Log::channel('daily')->error('[HYP FAILED] failure_hook threw exception', [
                    'hook' => $data->failure_hook,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->sendFinalResponse($data, 'fail');
    }

    /**
     * 🔔 STEP 3: Server-to-Server Webhook from HyP
     *
     * This endpoint must be registered with HyP support (*6488 ext. 3 or WhatsApp)
     * so they configure it at the terminal level. HyP calls it independently of the
     * browser and retries every 10 min for up to 4h 40m if our server is unavailable.
     *
     * Webhook URL: https://backend.gizra.app/payment/hyperpay/notify
     */
    public function webhook(Request $request)
    {
        $responseData = $request->all();

        Log::channel('daily')->info('[HYP WEBHOOK] === Incoming server-to-server callback ===', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'all_params' => $responseData,
        ]);

        $paymentId = $responseData['Order'] ?? null;

        if (!$paymentId) {
            Log::channel('daily')->warning('[HYP WEBHOOK] Missing Order field in request');
            return response('Missing Order', 400);
        }

        $data = PaymentRequest::where('id', $paymentId)->first();

        if (!$data) {
            Log::channel('daily')->warning('[HYP WEBHOOK] PaymentRequest not found', ['order' => $paymentId]);
            // Acknowledge with 200 so HyP's endpoint verification/connectivity checks succeed
            // even when the Order id is a test value that doesn't exist in payment_requests.
            return response('OK', 200);
        }

        // Cache Sign for cron re-verification
        if (!empty($responseData['Sign'])) {
            $data->update(['gateway_callback_url' => $responseData['Sign']]);
        }

        $ccode = (string) ($responseData['CCode'] ?? '');
        $isSuccess = in_array($ccode, self::SUCCESS_CCODES);

        // Only verify signature for transactions that will trigger order fulfilment.
        // Declined/failed webhooks (CCode ≠ 0/600/700/800) are still verified for
        // integrity, but a verify failure on a non-success just logs a warning and
        // returns 200 so HYP stops retrying and sends the browser redirect promptly.
        if (!$this->verifyWithHyp($responseData)) {
            if ($isSuccess) {
                Log::channel('daily')->error('[HYP WEBHOOK] Signature verification FAILED — rejecting success webhook', [
                    'order' => $paymentId,
                    'CCode' => $ccode,
                ]);
                return response('Invalid signature', 400);
            }
            Log::channel('daily')->warning('[HYP WEBHOOK] Signature verification failed on non-success webhook — acknowledging anyway', [
                'order' => $paymentId,
                'CCode' => $ccode,
            ]);
        }

        if ($isSuccess) {
            if ($data->is_paid) {
                Log::channel('daily')->info('[HYP WEBHOOK] Already marked paid, skipping', ['order' => $paymentId]);
                return response('OK', 200);
            }

            $data->update([
                'payment_method' => 'hypay',
                'is_paid' => 1,
                'transaction_id' => $responseData['Id'] ?? null,
            ]);

            Log::channel('daily')->info('[HYP WEBHOOK] Payment confirmed — triggering order workflow', [
                'order' => $paymentId,
                'success_hook' => $data->success_hook,
                'attribute_id' => $data->attribute_id,
            ]);

            if (function_exists($data->success_hook)) {
                try {
                    call_user_func($data->success_hook, $data);
                } catch (\Exception $e) {
                    Log::channel('daily')->error('[HYP WEBHOOK] success_hook threw an exception', [
                        'hook' => $data->success_hook,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } else {
            Log::channel('daily')->info('[HYP WEBHOOK] Payment NOT successful', [
                'order' => $paymentId,
                'CCode' => $responseData['CCode'] ?? 'N/A',
            ]);

            // Guard: skip failure hook if browser redirect already confirmed this payment
            if (!$data->is_paid && $data->failure_hook && function_exists($data->failure_hook)) {
                try {
                    call_user_func($data->failure_hook, $data);
                } catch (\Exception $e) {
                    Log::channel('daily')->error('[HYP WEBHOOK] failure_hook threw an exception', [
                        'hook' => $data->failure_hook,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // HYP terminal may redirect the browser to this same notify URL instead of
        // using the per-transaction SuccessUrl/ErrorUrl. Detect browser requests by
        // Accept header and serve the proper result rather than a plain "OK".
        if (str_contains($request->header('Accept', ''), 'text/html')) {
            Log::channel('daily')->info('[HYP WEBHOOK] Browser request detected — redirecting to result page', [
                'order' => $paymentId,
                'isSuccess' => $isSuccess,
            ]);
            return $this->sendFinalResponse($data, $isSuccess ? 'success' : 'fail');
        }

        return response('OK', 200);
    }

    /**
     * Build the final redirect/response after payment processing.
     *
     * If external_redirect_link points back to our own server we serve HTML
     * directly — redirecting there would hit PaymentController@success which
     * does a stale-session order lookup and may 302 to a non-existent URL.
     */
    private function sendFinalResponse(PaymentRequest $data, string $flag)
    {
        $data->refresh();

        $tokenString = 'payment_method=' . ($data->payment_method ?? 'hypay')
            . '&&attribute_id=' . $data->attribute_id
            . '&&transaction_reference=' . $data->transaction_id;

        if (in_array($data->payment_platform, ['web', 'app']) && !empty($data->external_redirect_link)) {
            $redirectUrl = $data->external_redirect_link . '?flag=' . $flag . '&&token=' . base64_encode($tokenString);
            $ourHost = parse_url(config('app.url'), PHP_URL_HOST);
            $redirectHost = parse_url($redirectUrl, PHP_URL_HOST);

            // If redirect points back to our own host, serve HTML directly.
            if ($redirectHost === $ourHost) {
                return $this->htmlResultPage($flag);
            }

            // For app deep links (e.g. gizraapp://), use JS redirect.
            // HTTP 302 to custom schemes is unreliable in webviews;
            // JS location change is intercepted by shouldOverrideUrlLoading / WKNavigationDelegate.
            $scheme = parse_url($redirectUrl, PHP_URL_SCHEME);
            if (!in_array($scheme, ['http', 'https'])) {
                $safeUrl = htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8');
                return response(
                    '<!DOCTYPE html><html><head><meta charset="UTF-8">' .
                    '<script>window.location.replace("' . $safeUrl . '");</script>' .
                    '</head><body></body></html>',
                    200
                )->header('Content-Type', 'text/html');
            }

            return redirect($redirectUrl);
        }

        return redirect()->route('payment-' . $flag, ['token' => base64_encode($tokenString)]);
    }

    private function htmlResultPage(string $flag)
    {
        if ($flag === 'success') {
            return response(
                '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payment Successful</title></head>' .
                '<body style="font-family:sans-serif;text-align:center;padding:2rem;background:#f8fff8">' .
                '<h2 style="color:#2a7a2a">&#10003; Payment Successful</h2>' .
                '<p>Your order has been confirmed. You may close this window.</p>' .
                '</body></html>',
                200
            )->header('Content-Type', 'text/html');
        }

        return response(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Payment Failed</title></head>' .
            '<body style="font-family:sans-serif;text-align:center;padding:2rem;background:#fff8f8">' .
            '<h2 style="color:#b00020">&#10007; Payment Failed</h2>' .
            '<p>Your payment could not be processed. Please try again.</p>' .
            '</body></html>',
            402
        )->header('Content-Type', 'text/html');
    }

    /**
     * Verify transaction signature via HyP APISign VERIFY.
     * Docs: https://developers.hyp.co.il/pay/security/transaction-validation
     *
     * Auth params must come first in the request, followed by all callback fields.
     * Fild1/Fild2/Fild3 default to '' so the signature matches HYP's stored value.
     * VERIFY returns CCode=0 when the signature is valid.
     */
    private function verifyWithHyp(array $responseData): bool
    {
        // Auth params first (matches docs example parameter order)
        $params = [
            'action' => 'APISign',
            'What' => 'VERIFY',
            'Masof' => $this->masof,
            'KEY' => $this->apiKey,
            'PassP' => $this->passP,
        ];

        // Convert null values to '' so http_build_query includes them in the VERIFY
        // request exactly as HYP included them when computing the original signature.
        // Fields like ACode and Fild3 arrive as null when HYP sent an empty value.
        $normalized = array_map(fn($v) => $v === null ? '' : $v, $responseData);

        // Merge all callback fields; Fild1/Fild2/Fild3 default to '' if absent
        $params = array_merge($params, ['Fild1' => '', 'Fild2' => '', 'Fild3' => ''], $normalized);

        try {
            $response = Http::timeout(15)->get($this->base_url, $params);

            Log::channel('daily')->info('[HYP VERIFY] Signature check', [
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            parse_str($response->body(), $result);

            return ($result['CCode'] ?? null) == '0';
        } catch (\Exception $e) {
            Log::channel('daily')->error('[HYP VERIFY] Exception calling VERIFY endpoint', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
