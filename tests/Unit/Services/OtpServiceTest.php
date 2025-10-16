<?php

beforeEach(function (): void {
    $this->service = new \App\Services\OtpService;
    $this->email = 'test@example.com';
});

it('Can Save OTP to Database', function (): void {
    $otpRecord = $this->service->saveOtpToDatabase($this->email);
    expect($otpRecord)->toBeInstanceOf(\App\Models\Otp::class)
        ->and($otpRecord->email)->toBe($this->email)
        ->and($otpRecord->otp)->toBeGreaterThanOrEqual(1000)
        ->and($otpRecord->otp)->toBeLessThanOrEqual(9999)
        ->and($otpRecord->expires_at)->toBeGreaterThan(now());
});

it('Can Verify OTP', function (): void {
    $otpRecord = $this->service->saveOtpToDatabase($this->email);
    $isValid = $this->service->verifyOtp($this->email, $otpRecord->otp);
    expect($isValid)->toBeTrue();

    // Test with invalid OTP
    $isInvalid = $this->service->verifyOtp($this->email, 1234);
    expect($isInvalid)->toBeFalse();
});

it('Can Delete OTP', function (): void {
    $this->service->saveOtpToDatabase($this->email);
    $this->service->deleteOtp($this->email);
    $otpRecord = \App\Models\Otp::where('email', $this->email)->first();
    expect($otpRecord)->toBeNull();
});
