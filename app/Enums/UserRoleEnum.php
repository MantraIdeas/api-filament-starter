<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'admin';
    case VENDOR = 'vendor';
    case RIDER = 'rider';
    case CUSTOMER = 'customer';

    public static function toArray(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }
}
