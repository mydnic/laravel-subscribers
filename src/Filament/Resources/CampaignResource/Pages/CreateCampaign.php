<?php

namespace Mydnic\Kanpen\Filament\Resources\CampaignResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Mydnic\Kanpen\Filament\Resources\CampaignResource;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
