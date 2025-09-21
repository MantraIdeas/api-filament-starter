<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return self::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Provide basic user details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('John Doe')
                            ->columnSpan(['sm' => 1, 'xl' => 2]),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('user@example.com')
                            ->columnSpan(['sm' => 1, 'xl' => 2]),

                        Forms\Components\Select::make('roles')
                            ->columnSpan(['sm' => 1, 'xl' => 2])
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(4)
                    ->collapsible(),

                Forms\Components\Section::make('Security')
                    ->description('Set password and security settings')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->maxLength(255)
                            ->confirmed()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->columnSpan(['sm' => 1, 'xl' => 2]),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->dehydrated(false)
                            ->required(fn (string $context): bool => $context === 'create')
                            ->same('password')
                            ->columnSpan(['sm' => 1, 'xl' => 2]),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->hiddenOn(['edit']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->email)
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('roles')
                    ->label('Roles')
                    ->getStateUsing(function (User $record) {
                        return $record->getRoleNames();
                    })->badge(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->label('Email Verified')
                    ->query(fn ($query) => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('unverified')
                    ->label('Email Not Verified')
                    ->query(fn ($query) => $query->whereNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Create your first user by clicking the button below.')
            ->emptyStateIcon('heroicon-o-user-plus')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create User')
                    ->icon('heroicon-o-plus'),
            ])
            ->deferLoading()
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('User Details')
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->size('lg'),
                                        Components\TextEntry::make('email')
                                            ->label('Email Address')
                                            ->icon('heroicon-m-envelope'),
                                        Components\TextEntry::make('roles')
                                            ->badge()
                                            ->formatStateUsing(fn (User $record) => $record->getRoleNames()->join(',')),
                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('email_verified_at')
                                            ->label('Email Verification')
                                            ->state(fn ($record
                                            ) => $record->email_verified_at ? 'Verified' : 'Not Verified')
                                            ->icon(fn ($record
                                            ) => $record->email_verified_at ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                                            ->color(fn ($record) => $record->email_verified_at ? 'success' : 'danger'),
                                        Components\TextEntry::make('created_at')
                                            ->label('Member Since')
                                            ->dateTime('M j, Y \\a\\t g:i A'),
                                        Components\TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime('M j, Y \\a\\t g:i A')
                                            ->since(),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
