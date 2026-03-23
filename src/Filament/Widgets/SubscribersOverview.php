<?php

namespace Mydnic\Subscribers\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Mydnic\Subscribers\Enums\CampaignStatus;
use Mydnic\Subscribers\Models\Campaign;
use Mydnic\Subscribers\Models\Subscriber;

class SubscribersOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total = Subscriber::count();
        $verified = Subscriber::whereNotNull('email_verified_at')->count();
        $thisMonth = Subscriber::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $lastMonth = Subscriber::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $growth = $lastMonth > 0
            ? round(($thisMonth - $lastMonth) / $lastMonth * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);

        $sentCampaigns = Campaign::where('status', CampaignStatus::Sent)->count();

        return [
            Stat::make('Total Subscribers', number_format($total))
                ->description($thisMonth . ' new this month')
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger')
                ->chart(
                    Subscriber::selectRaw('COUNT(*) as count')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->groupByRaw('DATE(created_at)')
                        ->orderByRaw('DATE(created_at)')
                        ->pluck('count')
                        ->toArray()
                ),

            Stat::make('Verified Subscribers', number_format($verified))
                ->description($total > 0 ? round($verified / $total * 100, 1) . '% of total' : '0% of total')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            Stat::make('Campaigns Sent', number_format($sentCampaigns))
                ->description(Campaign::where('status', CampaignStatus::Draft)->count() . ' drafts pending')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('warning'),
        ];
    }
}
