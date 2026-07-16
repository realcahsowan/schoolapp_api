<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ImpersonationToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $dates = ['expires_at', 'used_at'];

    public static function generate(User $target): self
    {
        return static::create([
            'token' => Str::random(64),
            'user_id' => $target->id,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public function isValid(): bool
    {
        // Pastikan expires_at adalah Carbon instance
        $expiresAt = $this->expires_at instanceof \Carbon\Carbon ? $this->expires_at : \Carbon\Carbon::parse($this->expires_at);

        return is_null($this->used_at) && $expiresAt->isFuture();
    }
}
