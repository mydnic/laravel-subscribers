<?php

namespace Mydnic\Subscribers\Filament\Resources\SubscriberResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use Mydnic\Subscribers\Filament\Resources\SubscriberResource;
use Mydnic\Subscribers\Models\Subscriber;

class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Subscriber::withTrashed()->count()),

            'active' => Tab::make('Active')
                ->badge(Subscriber::count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),

            'verified' => Tab::make('Verified')
                ->badge(Subscriber::whereNotNull('email_verified_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('email_verified_at')->whereNull('deleted_at')),

            'unverified' => Tab::make('Unverified')
                ->badge(Subscriber::whereNull('email_verified_at')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('email_verified_at')->whereNull('deleted_at')),

            'unsubscribed' => Tab::make('Unsubscribed')
                ->badge(Subscriber::onlyTrashed()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('deleted_at')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
