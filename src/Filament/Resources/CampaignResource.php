<?php

namespace Mydnic\Kanpen\Filament\Resources;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Mydnic\Kanpen\Actions\SendCampaignAction;
use Mydnic\Kanpen\Actions\SendTestCampaignAction;
use Mydnic\Kanpen\Enums\CampaignStatus;
use Mydnic\Kanpen\Filament\Resources\CampaignResource\Pages\CreateCampaign;
use Mydnic\Kanpen\Filament\Resources\CampaignResource\Pages\EditCampaign;
use Mydnic\Kanpen\Filament\Resources\CampaignResource\Pages\ListCampaigns;
use Mydnic\Kanpen\Filament\Resources\CampaignResource\Pages\ViewCampaign;
use Mydnic\Kanpen\Models\Campaign;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Newsletter';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Campaigns';
    }

    public static function getNavigationBadge(): ?string
    {
        $drafts = Campaign::where('status', CampaignStatus::Draft)->count();

        return $drafts > 0 ? (string) $drafts : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Campaign Details')->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('from_name')
                    ->placeholder(config('kanpen.campaigns.from.name'))
                    ->maxLength(255),

                TextInput::make('from_email')
                    ->email()
                    ->placeholder(config('kanpen.campaigns.from.email'))
                    ->maxLength(255),

                TextInput::make('reply_to')
                    ->email()
                    ->maxLength(255),

                TextInput::make('view')
                    ->label('Blade View')
                    ->placeholder('emails.newsletter')
                    ->helperText('Use a custom Blade view instead of the HTML editor below.')
                    ->maxLength(255),
            ])->columns(2),

            Section::make('Content')->schema([
                RichEditor::make('content_html')
                    ->label('Email Content')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike',
                        'h2', 'h3',
                        'bulletList', 'orderedList',
                        'link', 'blockquote',
                        'redo', 'undo',
                    ])
                    ->columnSpanFull()
                    ->helperText('Leave empty if you are using a custom Blade view.'),
            ]),

            Section::make('Scheduling')->schema([
                DateTimePicker::make('scheduled_at')
                    ->label('Schedule Send')
                    ->nullable()
                    ->after('now')
                    ->helperText('Optional. Leave empty to send manually.'),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Stats')->schema([
                Grid::make(5)->schema([
                    TextEntry::make('sent_count')
                        ->label('Sent')
                        ->numeric(),

                    TextEntry::make('opens')
                        ->label('Opened')
                        ->state(fn (Campaign $record): int => $record->sends()->whereNotNull('opened_at')->count()),

                    TextEntry::make('open_rate')
                        ->label('Open Rate')
                        ->state(function (Campaign $record): string {
                            $sent = $record->sends()->whereNotNull('sent_at')->count();
                            if ($sent === 0) {
                                return '—';
                            }
                            $opened = $record->sends()->whereNotNull('opened_at')->count();

                            return round($opened / $sent * 100, 1).'%';
                        }),

                    TextEntry::make('clicks')
                        ->label('Clicked')
                        ->state(fn (Campaign $record): int => $record->sends()->whereNotNull('clicked_at')->count()),

                    TextEntry::make('click_rate')
                        ->label('Click Rate')
                        ->state(function (Campaign $record): string {
                            $sent = $record->sends()->whereNotNull('sent_at')->count();
                            if ($sent === 0) {
                                return '—';
                            }
                            $clicked = $record->sends()->whereNotNull('clicked_at')->count();

                            return round($clicked / $sent * 100, 1).'%';
                        }),
                ]),
            ])->visible(fn (Campaign $record): bool => $record->isSent() || $record->isSending()),

            InfoSection::make('Details')->schema([
                TextEntry::make('name'),

                TextEntry::make('subject'),

                TextEntry::make('status')
                    ->badge()
                    ->color(fn (CampaignStatus $state): string => match ($state) {
                        CampaignStatus::Draft => 'gray',
                        CampaignStatus::Sending => 'warning',
                        CampaignStatus::Sent => 'success',
                        CampaignStatus::Cancelled => 'danger',
                    }),

                TextEntry::make('from_email')
                    ->label('From')
                    ->state(fn (Campaign $record): string => implode(' ', array_filter([
                        $record->from_name,
                        $record->from_email ? "<{$record->from_email}>" : null,
                    ])) ?: '—'),

                TextEntry::make('reply_to')
                    ->placeholder('—'),

                TextEntry::make('view')
                    ->label('Blade View')
                    ->placeholder('None (uses HTML content)'),

                TextEntry::make('scheduled_at')
                    ->dateTime()
                    ->placeholder('Not scheduled'),

                TextEntry::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->placeholder('Not yet sent'),

                TextEntry::make('created_at')
                    ->dateTime(),
            ])->columns(2),

            InfoSection::make('Content')->schema([
                TextEntry::make('content_html')
                    ->label('')
                    ->html()
                    ->columnSpanFull()
                    ->placeholder('Uses a custom Blade view.'),
            ])->visible(fn (Campaign $record): bool => ! empty($record->content_html)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (CampaignStatus $state): string => match ($state) {
                        CampaignStatus::Draft => 'gray',
                        CampaignStatus::Sending => 'warning',
                        CampaignStatus::Sent => 'success',
                        CampaignStatus::Cancelled => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('sent_count')
                    ->label('Sent')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('open_rate')
                    ->label('Open Rate')
                    ->state(function (Campaign $record): string {
                        if ($record->sent_count === 0) {
                            return '—';
                        }
                        $opened = $record->sends()->whereNotNull('opened_at')->count();

                        return round($opened / $record->sent_count * 100, 1).'%';
                    }),

                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(CampaignStatus::cases())->mapWithKeys(
                        fn (CampaignStatus $status) => [$status->value => ucfirst($status->value)]
                    )),
            ])
            ->actions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn (Campaign $record): bool => $record->isDraft()),

                Action::make('send_test')
                    ->label('Send Test')
                    ->icon('heroicon-o-beaker')
                    ->color('gray')
                    ->form([
                        TextInput::make('email')
                            ->label('Send test to')
                            ->email()
                            ->required()
                            ->placeholder('you@example.com'),
                    ])
                    ->action(function (Campaign $record, array $data): void {
                        try {
                            app(SendTestCampaignAction::class)->execute($record, $data['email']);

                            Notification::make()
                                ->title('Test email sent')
                                ->body("A test copy was sent to {$data['email']}.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Could not send test email')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('send')
                    ->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Campaign $record): bool => $record->isDraft())
                    ->requiresConfirmation()
                    ->modalHeading('Send Campaign')
                    ->modalDescription(fn (Campaign $record): string => "This will send \"{$record->name}\" to all active subscribers. This cannot be undone.")
                    ->modalSubmitActionLabel('Yes, send now')
                    ->action(function (Campaign $record): void {
                        try {
                            app(SendCampaignAction::class)->execute($record);

                            Notification::make()
                                ->title('Campaign dispatched')
                                ->body('The campaign is being sent to all subscribers.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Could not send campaign')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListCampaigns::route('/'),
            'create' => CreateCampaign::route('/create'),
            'view' => ViewCampaign::route('/{record}'),
            'edit' => EditCampaign::route('/{record}/edit'),
        ];
    }
}
