<?php

namespace App\Services;

use App\Models\Otp;
use Random\RandomException;

class OtpService
{
    /**
     * @throws RandomException
     */
    public function saveOtpToDatabase(string $email): Otp
    {
        $otp = $this->generateOtp();

        return Otp::updateOrCreate(
            ['email' => $email],
            [
                'otp' => $otp,
                'expires_at' => now()->addMinutes(60),
            ]
        );

    }

    /**
     * @throws RandomException
     */
    private function generateOtp(): int
    {
        return random_int(1000, 9999);

    }

    public function verifyOtp(string $email, int $otp): bool
    {
        $check = Otp::where('email', $email)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->first();
        if ($check) {
            return true;
        }

        return false;
    }

    public function deleteOtp(string $email): void
    {
        Otp::where('email', $email)->delete();
    }
}
