<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PartnerApiKey;

/**
 * Resolves the X-Gizra-Key header to a restaurant and enforces scopes + IP
 * allowlist. On success it sets $request['vendor'] the same way the existing
 * VendorTokenIsValid middleware does, so the reused VendorController methods
 * work completely unmodified — they scope themselves off $request['vendor']
 * already.
 */
class PartnerAuth
{
    public function handle(Request $request, Closure $next, string $scope = '')
    {
        $keyId = $request->header('X-Gizra-Key');
        if (!$keyId) {
            return response()->json([
                'errors' => [['code' => 'partner-auth', 'message' => 'Missing X-Gizra-Key header.']]
            ], 401);
        }

        $partnerKey = PartnerApiKey::where('key_id', $keyId)->first();
        if (!$partnerKey || $partnerKey->isRevoked()) {
            return response()->json([
                'errors' => [['code' => 'partner-auth', 'message' => 'Invalid or revoked API key.']]
            ], 401);
        }

        if (!$partnerKey->ipAllowed($request->ip())) {
            return response()->json([
                'errors' => [['code' => 'partner-auth', 'message' => 'IP address not allowed for this key.']]
            ], 403);
        }

        if ($scope && !$partnerKey->hasScope($scope)) {
            return response()->json([
                'errors' => [['code' => 'partner-auth', 'message' => "Key is missing required scope: {$scope}."]]
            ], 403);
        }

        $restaurant = $partnerKey->restaurant;
        $vendor = $restaurant?->vendor;
        if (!$restaurant || !$vendor) {
            return response()->json([
                'errors' => [['code' => 'partner-auth', 'message' => 'Key is not linked to an active restaurant/vendor.']]
            ], 500);
        }

        $partnerKey->update(['last_used_at' => now()]);

        // Audit trail: key_id + route only, never request/response payloads —
        // orders carry customer name, phone, email and address.
        \Illuminate\Support\Facades\Log::channel('daily')->info('[PARTNER API] call', [
            'key_id' => $partnerKey->key_id,
            'restaurant_id' => $restaurant->id,
            'method' => $request->method(),
            'path' => $request->path(),
            'order_id' => $request->input('order_id'),
        ]);

        $request['vendor'] = $vendor;
        $request['partner_key'] = $partnerKey;

        return $next($request);
    }
}
