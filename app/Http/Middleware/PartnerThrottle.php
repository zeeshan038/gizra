<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Per-key_id rate limiting (not per-IP) — 120/min reads, 60/min writes, per README.
 * Must run after PartnerAuth so $request['partner_key'] is set.
 */
class PartnerThrottle
{
    private const LIMITS = ['read' => 120, 'write' => 60];

    public function handle(Request $request, Closure $next, string $type = 'read')
    {
        $partnerKey = $request['partner_key'] ?? null;
        if (!$partnerKey) {
            return response()->json([
                'errors' => [['code' => 'partner-throttle', 'message' => 'Throttle check requires prior authentication.']]
            ], 500);
        }

        $limit = self::LIMITS[$type] ?? self::LIMITS['read'];
        $limiterKey = 'partner-api:' . $partnerKey->key_id . ':' . $type;

        if (RateLimiter::tooManyAttempts($limiterKey, $limit)) {
            $retryAfter = RateLimiter::availableIn($limiterKey);
            return response()->json([
                'errors' => [['code' => 'partner-throttle', 'message' => 'Rate limit exceeded.']]
            ], 429)->header('Retry-After', $retryAfter);
        }

        RateLimiter::hit($limiterKey, 60);

        return $next($request);
    }
}
