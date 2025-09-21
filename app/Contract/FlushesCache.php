<?php

namespace App\Contract;

use Illuminate\Support\Facades\Cache;

trait FlushesCache
{
    public static function bootFlushesCache(): void
    {
        static::created(fn () => static::flushModelCache());
        static::updated(fn () => static::flushModelCache());
        static::deleted(fn () => static::flushModelCache());

    }

    protected static function flushModelCache(): void
    {
        Cache::flush();
    }
}
