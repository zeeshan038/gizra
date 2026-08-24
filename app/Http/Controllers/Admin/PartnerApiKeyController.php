<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerApiKey;
use App\Models\PartnerWebhook;
use App\Models\Restaurant;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PartnerApiKeyController extends Controller
{
    private const AVAILABLE_SCOPES = ['orders:read', 'orders:write', 'menu:read'];

    public function index(Request $request)
    {
        $keys = PartnerApiKey::with('restaurant')->latest()->paginate(20);
        $webhooks = PartnerWebhook::with('restaurant')->latest()->paginate(20, ['*'], 'webhooks_page');
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        return view('admin-views.business-settings.partner-api-keys.index', compact('keys', 'webhooks', 'restaurants'))
            ->with('availableScopes', self::AVAILABLE_SCOPES);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:191',
            'scopes' => 'required|array|min:1',
            'scopes.*' => 'in:' . implode(',', self::AVAILABLE_SCOPES),
            'ip_allowlist' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back();
        }

        $keyId = 'gzk_' . Str::random(24);
        $plaintextSecret = Str::random(48);

        $ipAllowlist = null;
        if ($request->filled('ip_allowlist')) {
            $ipAllowlist = array_values(array_filter(array_map('trim', explode(',', $request->ip_allowlist))));
        }

        PartnerApiKey::create([
            'restaurant_id' => $request->restaurant_id,
            'name' => $request->name,
            'key_id' => $keyId,
            'secret_hash' => PartnerApiKey::encryptSecret($plaintextSecret),
            'scopes' => $request->scopes,
            'ip_allowlist' => $ipAllowlist,
        ]);

        // Shown exactly once — not recoverable from the admin panel after this redirect.
        return redirect()->route('admin.partner-api-keys.index')->with([
            'new_key_id' => $keyId,
            'new_key_secret' => $plaintextSecret,
        ]);
    }

    public function revoke(PartnerApiKey $partnerApiKey)
    {
        $partnerApiKey->update(['revoked_at' => now()]);
        Toastr::success(translate('messages.Partner_API_key_revoked'));
        return back();
    }

    public function storeWebhook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'url' => 'required|url|max:2048',
            'events' => 'required|array|min:1',
            'events.*' => 'in:order.created,order.status_changed',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back();
        }

        $url = $request->url;

        if (!str_starts_with(strtolower($url), 'https://')) {
            Toastr::error(translate('messages.Webhook_URL_must_use_HTTPS'));
            return back();
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || $this->isPrivateOrLoopbackHost($host)) {
            Toastr::error(translate('messages.Webhook_URL_host_is_not_allowed'));
            return back();
        }

        PartnerWebhook::create([
            'restaurant_id' => $request->restaurant_id,
            'url' => $url,
            'events' => $request->events,
            'signing_secret' => Str::random(48),
            'active' => true,
        ]);

        Toastr::success(translate('messages.Webhook_registered'));
        return back();
    }

    public function toggleWebhook(PartnerWebhook $webhook)
    {
        $webhook->update([
            'active' => !$webhook->active,
            'disabled_at' => $webhook->active ? now() : null,
            'consecutive_failures' => 0,
        ]);
        return back();
    }

    /**
     * SSRF guard — refuse registering a webhook URL that resolves to a
     * private, loopback, or link-local address, per README security spec.
     */
    private function isPrivateOrLoopbackHost(string $host): bool
    {
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return true; // couldn't resolve — refuse rather than risk it
        }
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
