<?php

namespace App\Filament\Concerns;

trait RedirectToIndex
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
