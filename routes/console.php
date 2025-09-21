<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --stop-when-empty')
    ->everyFiveSeconds()
    ->name('Queue Worker for Default Queue');
