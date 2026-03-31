<?php

namespace Mydnic\Kanpen\Filament\Resources\CampaignResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Mydnic\Kanpen\Actions\SendCampaignAction;
use Mydnic\Kanpen\Actions\SendTestCampaignAction;
use Mydnic\Kanpen\Filament\Resources\CampaignResource;

class ViewCampaign extends ViewRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => $this->record->isDraft()),

            Action::make('send_test')
                ->label('Send Test Email')
                ->icon('heroicon-o-beaker')
                ->color('gray')
                ->form([
                    TextInput::make('email')
                        ->label('Send test to')
                        ->email()
                        ->required()
                        ->placeholder('you@example.com'),
                ])
                ->action(function (array $data): void {
                    try {
                        app(SendTestCampaignAction::class)->execute($this->record, $data['email']);

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
                ->label('Send Campaign')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (): bool => $this->record->isDraft())
                ->requiresConfirmation()
                ->modalHeading('Send Campaign')
                ->modalDescription(fn (): string => "This will send \"{$this->record->name}\" to all active subscribers. This cannot be undone.")
                ->modalSubmitActionLabel('Yes, send now')
                ->action(function (): void {
                    try {
                        app(SendCampaignAction::class)->execute($this->record);

                        Notification::make()
                            ->title('Campaign dispatched')
                            ->body('The campaign is being sent to all subscribers. Refresh this page to check progress.')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status', 'sent_at']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Could not send campaign')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make()
                ->visible(fn (): bool => $this->record->isDraft()),
        ];
    }
}
