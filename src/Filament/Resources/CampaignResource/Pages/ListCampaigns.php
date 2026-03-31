<?php

namespace Mydnic\Kanpen\Filament\Resources\CampaignResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use Mydnic\Kanpen\Enums\CampaignStatus;
use Mydnic\Kanpen\Filament\Resources\CampaignResource;
use Mydnic\Kanpen\Models\Campaign;

class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Campaign::count()),

            'draft' => Tab::make('Drafts')
                ->badge(Campaign::where('status', CampaignStatus::Draft)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CampaignStatus::Draft)),

            'sending' => Tab::make('Sending')
                ->badge(Campaign::where('status', CampaignStatus::Sending)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CampaignStatus::Sending)),

            'sent' => Tab::make('Sent')
                ->badge(Campaign::where('status', CampaignStatus::Sent)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CampaignStatus::Sent)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
