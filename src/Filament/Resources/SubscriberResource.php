<?php

namespace Mydnic\Subscribers\Filament\Resources;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mydnic\Subscribers\Filament\Resources\SubscriberResource\Pages\ListSubscribers;
use Mydnic\Subscribers\Filament\Resources\SubscriberResource\Pages\ViewSubscriber;
use Mydnic\Subscribers\Models\Subscriber;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Newsletter';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Subscribers';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            DateTimePicker::make('email_verified_at')
                ->label('Verified At')
                ->nullable(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Subscriber Details')->schema([
                TextEntry::make('email')
                    ->copyable(),

                IconEntry::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->state(fn (Subscriber $record): bool => $record->hasVerifiedEmail()),

                TextEntry::make('email_verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->placeholder('Not verified'),

                TextEntry::make('created_at')
                    ->label('Subscribed At')
                    ->dateTime(),

                TextEntry::make('deleted_at')
                    ->label('Unsubscribed At')
                    ->dateTime()
                    ->placeholder('Active')
                    ->visible(fn (Subscriber $record): bool => $record->trashed()),
            ])->columns(2),

            Section::make('Campaign Activity')->schema([
                TextEntry::make('sends_count')
                    ->label('Campaigns Received')
                    ->state(fn (Subscriber $record): int => $record->sends()->count()),

                TextEntry::make('opens_count')
                    ->label('Campaigns Opened')
                    ->state(fn (Subscriber $record): int => $record->sends()->whereNotNull('opened_at')->count()),

                TextEntry::make('clicks_count')
                    ->label('Links Clicked')
                    ->state(fn (Subscriber $record): int => $record->sends()->whereNotNull('clicked_at')->count()),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->state(fn (Subscriber $record): bool => $record->hasVerifiedEmail())
                    ->sortable(),

                TextColumn::make('sends_count')
                    ->label('Campaigns')
                    ->counts('sends')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Subscribed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('deleted_at')
                    ->label('Unsubscribed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('verified')
                    ->label('Verified only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Filter::make('unverified')
                    ->label('Unverified only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),

                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('send_verification')
                    ->label('Resend Verification')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->visible(fn (Subscriber $record): bool => ! $record->hasVerifiedEmail())
                    ->requiresConfirmation()
                    ->action(fn (Subscriber $record) => $record->sendEmailVerificationNotification()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscribers::route('/'),
            'view' => ViewSubscriber::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
