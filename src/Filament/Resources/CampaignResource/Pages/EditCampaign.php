<?php

namespace Mydnic\Subscribers\Filament\Resources\CampaignResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Mydnic\Subscribers\Filament\Resources\CampaignResource;
use Mydnic\Subscribers\Models\Campaign;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Campaign $record */
        $record = $this->getRecord();

        if (! $record->isDraft()) {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
        }

        return $data;
    }
}
