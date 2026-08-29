<?php

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PaymobController;
use App\Http\Controllers\FlutterwaveV3Controller;
use App\Http\Controllers\PaytmController;
use App\Http\Controllers\PaypalPaymentController;
use App\Http\Controllers\PaytabsController;
use App\Http\Controllers\LiqPayController;
use App\Http\Controllers\RazorPayController;
use App\Http\Controllers\SenangPayController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\BkashPaymentController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\FirebaseController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/subscribeToTopic', [FirebaseController::class, 'subscribeToTopic']);

// Android App Links verification — must be served before any middleware groups
Route::get('/.well-known/assetlinks.json', function () {
    return response()->file(public_path('.well-known/assetlinks.json'), [
        'Content-Type' => 'application/json',
    ]);
});

Route::get('/', 'HomeController@index')->name('home');
Route::view('subscription/payment/view', 'Subscription_payment_view')->name('subscription_payment_view');
Route::get('maintenance-mode', 'HomeController@maintenanceMode')->name('maintenance_mode');
// ->middleware('maintenance')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

//login

Route::get('login/{tab}', 'LoginController@login')->name('login');
Route::post('login_submit', 'LoginController@submit')->name('login_post')->middleware('actch');
Route::get('logout', 'LoginController@logout')->name('logout');
Route::get('/reload-captcha', 'LoginController@reloadCaptcha')->name('reload-captcha');
Route::get('/reset-password', 'LoginController@reset_password_request')->name('reset-password');
Route::post('/vendor-reset-password', 'LoginController@vendor_reset_password_request')->name('vendor-reset-password');
Route::get('/password-reset', 'LoginController@reset_password')->name('change-password');
Route::post('verify-otp', 'LoginController@verify_token')->name('verify-otp');
Route::post('reset-password-submit', 'LoginController@reset_password_submit')->name('reset-password-submit');
Route::get('otp-resent', 'LoginController@otp_resent')->name('otp_resent');



Route::get('lang/{locale}', 'HomeController@lang')->name('lang');
Route::get('terms-and-conditions', 'HomeController@terms_and_conditions')->name('terms-and-conditions');
Route::get('about-us', 'HomeController@about_us')->name('about-us');
Route::match(['get', 'post'], 'contact-us', 'HomeController@contact_us')->name('contact-us');
Route::get('privacy-policy', 'HomeController@privacy_policy')->name('privacy-policy');
Route::post('newsletter/subscribe', 'NewsletterController@newsLetterSubscribe')->name('newsletter.subscribe');

Route::get('refund-policy', 'HomeController@refund_policy')->name('refund-policy');
Route::get('shipping-policy', 'HomeController@shipping_policy')->name('shipping-policy');
Route::get('cancellation-policy', 'HomeController@cancellation_policy')->name('cancellation-policy');



Route::get('subscription-invoice/{id}', 'HomeController@subscription_invoice')->name('subscription_invoice');



Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthenticated.']);
    return response()->json([
        'errors' => $errors,
    ], 401);
})->name('authentication-failed');

Route::group(['prefix' => 'payment-mobile'], function () {
    Route::get('/', 'PaymentController@payment')->name('payment-mobile');
    Route::get('set-payment-method/{name}', 'PaymentController@set_payment_method')->name('set-payment-method');
});

Route::get('payment-success', 'PaymentController@success')->name('payment-success');
Route::get('payment-fail', 'PaymentController@fail')->name('payment-fail');
Route::get('payment-cancel', 'PaymentController@cancel')->name('payment-cancel');

Route::get('wallet-payment', 'WalletPaymentController@make_payment')->name('wallet.payment');

$is_published = 0;
try {
    $full_data = include('Modules/Gateways/Addon/info.php');
    $is_published = $full_data['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {
}

if (!$is_published) {
    Route::group(['prefix' => 'payment'], function () {

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::get('success', [StripePaymentController::class, 'success'])->name('success')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index']);
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('callback', [RazorPayController::class, 'callback'])->name('callback')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYPAL
        Route::group(['prefix' => 'paypal', 'as' => 'paypal.'], function () {
            Route::get('pay', [PaypalPaymentController::class, 'payment']);
            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('cancel', [PaypalPaymentController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        Route::group(['prefix' => 'hyperpay', 'as' => 'hypay.'], function () {

            Route::get('pay', [PaypalPaymentController::class, 'payment']);

            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

            Route::any('failed', [PaypalPaymentController::class, 'failed'])->name('failed')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

            // Server-to-server IPN/webhook — called by HyP independently of the browser session
            Route::any('notify', [PaypalPaymentController::class, 'webhook'])->name('notify')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        });

        //EASYCOUNT
        Route::any('easycount/webhook', [\App\Http\Controllers\EasycountWebhookController::class, 'webhook'])
            ->name('easycount.webhook')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment']);
            Route::any('response', [PaytmController::class, 'callback'])->name('response')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //BKASH

        Route::group(['prefix' => 'bkash', 'as' => 'bkash.'], function () {
            // Payment Routes for bKash
            Route::get('make-payment', [BkashPaymentController::class, 'make_tokenize_payment'])->name('make-payment');
            Route::any('callback', [BkashPaymentController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

            // Refund Routes for bKash
            // Route::get('refund', 'BkashRefundController@index')->name('bkash-refund');
            // Route::post('refund', 'BkashRefundController@refund')->name('bkash-refund');
        });

        //Liqpay
        Route::group(['prefix' => 'liqpay', 'as' => 'liqpay.'], function () {
            Route::get('pay', [LiqPayController::class, 'payment'])->name('payment');
            Route::any('callback', [LiqPayController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //MERCADOPAGO
        Route::group(['prefix' => 'mercadopago', 'as' => 'mercadopago.'], function () {
            Route::get('pay', [MercadoPagoController::class, 'index'])->name('index');
            Route::any('make-payment', [MercadoPagoController::class, 'make_payment'])->name('make_payment')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::get('success', [MercadoPagoController::class, 'success'])->name('success');
            Route::get('failed', [MercadoPagoController::class, 'failed'])->name('failed');
        });

        //PAYMOB
        Route::group(['prefix' => 'paymob', 'as' => 'paymob.'], function () {
            Route::any('pay', [PaymobController::class, 'credit'])->name('pay');
            Route::any('callback', [PaymobController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYTABS
        Route::group(['prefix' => 'paytabs', 'as' => 'paytabs.'], function () {
            Route::any('pay', [PaytabsController::class, 'payment'])->name('pay');
            Route::any('callback', [PaytabsController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('response', [PaytabsController::class, 'response'])->name('response')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });
    });
}





Route::get('/test', function () {
    return view('errors.404');
});

Route::get('/api-docs.json', function () {
    return response()->file(public_path('api-docs.json'), [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*'
    ]);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::view('/swagger', 'swagger')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::view('/swagger', 'swagger')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
    return response()->json([
        'errors' => $errors
    ], 401);
})->name('authentication-failed');


//Restaurant Registration
Route::group(['prefix' => 'restaurant', 'as' => 'restaurant.'], function () {
    Route::get('apply', 'VendorController@create')->name('create');
    Route::post('apply', 'VendorController@store')->name('store');

    Route::get('back/{restaurant_id}', 'VendorController@back')->name('back');
    Route::post('payment', 'VendorController@payment')->name('payment');
    Route::post('business-plan', 'VendorController@business_plan')->name('business_plan');
    Route::get('final-step', 'VendorController@final_step')->name('final_step');
});

//Deliveryman Registration
Route::group(['prefix' => 'deliveryman', 'as' => 'deliveryman.'], function () {
    Route::get('apply', 'DeliveryManController@create')->name('create');
    Route::post('apply', 'DeliveryManController@store')->name('store');
});

// SMS4Free OTP Test (temporary)
Route::match(['GET', 'POST'], '/sms-test', function (\Illuminate\Http\Request $request) {
    $result = null;

    if ($request->isMethod('POST')) {
        $raw = preg_replace('/\D/', '', trim($request->input('phone', '')));
        // Normalize to Israel format: strip leading 972, keep 05xxxxxxxx
        if (strlen($raw) === 12 && str_starts_with($raw, '972')) {
            $raw = '0' . substr($raw, 3);
        }
        $phone = $raw;
        $otp = rand(100000, 999999);

        $payload = json_encode([
            'key' => 'HlbUyun9G',
            'user' => '0509222392',
            'pass' => '13683843',
            'sender' => 'Gizra',
            'recipient' => $phone,
            'msg' => "קוד האימות שלך: {$otp}",
        ]);

        $ch = curl_init('https://api.sms4free.co.il/ApiSMS/v2/SendSMS');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = [
            'phone' => $phone,
            'otp' => $otp,
            'http' => $httpCode,
            'response' => $response ?: ('cURL error: ' . $curlErr),
        ];
    }

    $phoneVal = htmlspecialchars($request->input('phone', ''));

    $html = '<!DOCTYPE html><html dir="ltr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SMS OTP Test</title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:-apple-system,sans-serif;background:#f0f2f5;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}.card{background:#fff;border-radius:16px;padding:32px;width:100%;max-width:420px;box-shadow:0 4px 24px rgba(0,0,0,.10)}h2{font-size:20px;color:#1e2022;margin-bottom:4px;font-weight:700}p.sub{font-size:12px;color:#8c98a4;margin-bottom:28px}label{font-size:12px;font-weight:700;color:#374151;display:block;margin-bottom:8px;letter-spacing:.4px}.phone-wrap{display:flex;border:1.5px solid #dee2e6;border-radius:10px;overflow:hidden;transition:border .2s}.phone-wrap:focus-within{border-color:#0d6efd}.prefix{background:#f3f4f6;padding:10px 14px;font-size:15px;font-weight:600;color:#374151;border-right:1.5px solid #dee2e6;white-space:nowrap}input[type=tel]{flex:1;border:none;padding:10px 14px;font-size:15px;outline:none;background:#fff}button{width:100%;background:#0d6efd;color:#fff;border:none;border-radius:10px;padding:13px;font-size:16px;font-weight:600;cursor:pointer;margin-top:20px;transition:opacity .2s}button:hover{opacity:.88}.result{margin-top:24px;border-radius:10px;overflow:hidden;border:1.5px solid #e5e7eb}.result-header{padding:12px 16px;font-size:13px;font-weight:700;letter-spacing:.3px}.success{background:#d1fae5;color:#065f46;border-color:#6ee7b7}.fail{background:#fee2e2;color:#991b1b;border-color:#fca5a5}.result-body{padding:14px 16px;font-size:13px}.row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f3f4f6}.row:last-child{border:none}.k{color:#6b7280;font-weight:600}.v{color:#111827;font-weight:500}.otp{font-size:28px;font-weight:800;color:#0d6efd;letter-spacing:4px}</style>
</head><body><div class="card">
<h2>📱 SMS OTP Test</h2>
<p class="sub">Send OTP to any Israel mobile number</p>
<form method="POST" action="/sms-test">
<input type="hidden" name="_token" value="' . csrf_token() . '">
<label>Mobile Number</label>
<div class="phone-wrap">
  <span class="prefix">🇮🇱 +972</span>
  <input type="tel" name="phone" value="' . $phoneVal . '" placeholder="05X-XXX-XXXX" required autofocus inputmode="numeric">
</div>
<button type="submit">Send OTP →</button>
</form>';

    if ($result) {
        $decoded = json_decode($result['response'], true);
        $apiStatus = $decoded['status'] ?? -99;
        $apiMsg = $decoded['message'] ?? $result['response'];
        $success = $apiStatus == 1;
        $headerClass = $success ? 'success' : 'fail';
        $headerText = $success ? '✅ SMS Sent Successfully' : '❌ Failed to Send';
        $html .= '<div class="result">
<div class="result-header ' . $headerClass . '">' . $headerText . '</div>
<div class="result-body">
<div class="row"><span class="k">Sent to</span><span class="v">+972 ' . htmlspecialchars(ltrim($result['phone'], '0')) . '</span></div>
<div class="row"><span class="k">OTP Code</span><span class="v otp">' . $result['otp'] . '</span></div>
<div class="row"><span class="k">API response</span><span class="v">' . htmlspecialchars($apiMsg) . '</span></div>
</div></div>';
    }

    $html .= '</div></body></html>';
    return response($html);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('dl', function (\Illuminate\Http\Request $request) {
    $allowed = ['gizraweb_30june_admin.zip'];
    $file = $request->query('f', '');
    if (!in_array($file, $allowed, true)) {
        abort(404);
    }
    $path = public_path($file);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->download($path, $file, ['Content-Type' => 'application/zip']);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
