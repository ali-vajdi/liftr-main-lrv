<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfVerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'code',
        'ip_address',
        'download_token',
        'used',
        'verified',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'used' => 'boolean',
        'verified' => 'boolean',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Check if code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if code is valid (not used, not expired, and verified)
     */
    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired() && $this->verified;
    }

    /**
     * Scope for valid codes
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)
            ->where('verified', true)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for unexpired codes
     */
    public function scopeUnexpired($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
