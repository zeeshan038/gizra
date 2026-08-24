<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Verifies X-Gizra-Signature (HMAC-SHA256 over "timestamp.rawBody", keyed with
 * the partner's secret) and X-Gizra-Timestamp (unix seconds, must be within
 * 300s of now). Must run after PartnerAuth so $request['partner_key'] is set.
 */
class PartnerSignature
{
    private const MAX_SKEW_SECONDS = 300;

    public function handle(Request $request, Closure $next)
    {
        $partnerKey = $request['partner_key'] ?? null;
        if (!$partnerKey) {
            return response()->json([
                'errors' => [['code' => 'partner-signature', 'message' => 'Signature check requires prior authentication.']]
            ], 500);
        }

        $timestamp = $request->header('X-Gizra-Timestamp');
        $signatureHeader = $request->header('X-Gizra-Signature');

        if (!$timestamp || !$signatureHeader) {
            return response()->json([
                'errors' => [['code' => 'partner-signature', 'message' => 'Missing X-Gizra-Timestamp or X-Gizra-Signature header.']]
            ], 401);
        }

        if (!ctype_digit((string) $timestamp) || abs(time() - (int) $timestamp) > self::MAX_SKEW_SECONDS) {
            return response()->json([
                'errors' => [['code' => 'partner-signature', 'message' => 'Timestamp is missing, malformed, or too old/new.']]
            ], 401);
        }

        $providedSignature = str_starts_with($signatureHeader, 'sha256=')
            ? substr($signatureHeader, 7)
            : $signatureHeader;

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $partnerKey->secret());

        if (!hash_equals($expected, $providedSignature)) {
            return response()->json([
                'errors' => [['code' => 'partner-signature', 'message' => 'Signature verification failed.']]
            ], 401);
        }

        return $next($request);
    }
}
