<?php

namespace Mydnic\Kanpen\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mydnic\Kanpen\Filament\Resources\CampaignResource;
use Mydnic\Kanpen\Filament\Resources\SubscriberResource;
use Mydnic\Kanpen\Filament\Widgets\NewSubscribersChart;
use Mydnic\Kanpen\Filament\Widgets\SubscribersOverview;

class KanpenPlugin implements Plugin
{
    protected bool $subscriberResource = true;

    protected bool $campaignResource = true;

    protected bool $subscribersOverviewWidget = true;

    protected bool $newSubscribersChartWidget = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'kanpen';
    }

    public function register(Panel $panel): void
    {
        $resources = [];
        $widgets = [];

        if ($this->subscriberResource) {
            $resources[] = SubscriberResource::class;
        }

        if ($this->campaignResource) {
            $resources[] = CampaignResource::class;
        }

        if ($this->subscribersOverviewWidget) {
            $widgets[] = SubscribersOverview::class;
        }

        if ($this->newSubscribersChartWidget) {
            $widgets[] = NewSubscribersChart::class;
        }

        $panel
            ->resources($resources)
            ->widgets($widgets);
    }

    public function boot(Panel $panel): void {}

    public function subscriberResource(bool $condition = true): static
    {
        $this->subscriberResource = $condition;

        return $this;
    }

    public function campaignResource(bool $condition = true): static
    {
        $this->campaignResource = $condition;

        return $this;
    }

    public function subscribersOverviewWidget(bool $condition = true): static
    {
        $this->subscribersOverviewWidget = $condition;

        return $this;
    }

    public function newSubscribersChartWidget(bool $condition = true): static
    {
        $this->newSubscribersChartWidget = $condition;

        return $this;
    }
}
