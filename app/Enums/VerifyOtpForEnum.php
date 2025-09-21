<?php

namespace App\Enums;

enum VerifyOtpForEnum: string
{
    case REGISTRATION = 'registration';
    case RESET_PASSWORD = 'reset_password';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
