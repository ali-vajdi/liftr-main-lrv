<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianOtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'otp_code',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a 6-digit OTP code
     */
    public static function generateOtpCode(): string
    {
        return str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP verification
     */
    public static function createOtp(string $phoneNumber): self
    {
        // Invalidate any existing unused OTPs for this phone number
        self::where('phone_number', $phoneNumber)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->update(['used' => true]);

        return self::create([
            'phone_number' => $phoneNumber,
            'otp_code' => self::generateOtpCode(),
            'expires_at' => now()->addMinutes(5), // OTP expires in 5 minutes
            'used' => false,
        ]);
    }

    /**
     * Verify OTP code
     */
    public static function verifyOtp(string $phoneNumber, string $otpCode): bool
    {
        $verification = self::where('phone_number', $phoneNumber)
            ->where('otp_code', $otpCode)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($verification) {
            $verification->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP is valid (not expired and not used)
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
