<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Concerns\RedirectToIndex;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use RedirectToIndex;

    protected static string $resource = UserResource::class;
}
