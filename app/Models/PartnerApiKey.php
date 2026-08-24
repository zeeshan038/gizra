<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PartnerApiKey extends Model
{
    protected $fillable = [
        'restaurant_id', 'name', 'key_id', 'secret_hash', 'scopes', 'ip_allowlist', 'last_used_at', 'revoked_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'ip_allowlist' => 'array',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = ['secret_hash'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? [], true);
    }

    public function ipAllowed(?string $ip): bool
    {
        $allowlist = $this->ip_allowlist ?? [];
        if (empty($allowlist)) {
            return true;
        }
        return in_array($ip, $allowlist, true);
    }

    public function secret(): string
    {
        return Crypt::decryptString($this->secret_hash);
    }

    public static function encryptSecret(string $plaintext): string
    {
        return Crypt::encryptString($plaintext);
    }
}
