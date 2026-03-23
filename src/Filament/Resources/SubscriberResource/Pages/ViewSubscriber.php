<?php

namespace Mydnic\Subscribers\Filament\Resources\SubscriberResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Mydnic\Subscribers\Filament\Resources\SubscriberResource;
use Mydnic\Subscribers\Models\Subscriber;

class ViewSubscriber extends ViewRecord
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_verification')
                ->label('Resend Verification Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->visible(fn (): bool => ! $this->record->hasVerifiedEmail() && ! $this->record->trashed())
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->sendEmailVerificationNotification();

                    Notification::make()
                        ->title('Verification email sent')
                        ->success()
                        ->send();
                }),

            RestoreAction::make()
                ->visible(fn (): bool => $this->record->trashed()),

            DeleteAction::make()
                ->label('Unsubscribe')
                ->visible(fn (): bool => ! $this->record->trashed()),

            ForceDeleteAction::make()
                ->visible(fn (): bool => $this->record->trashed()),
        ];
    }
}
