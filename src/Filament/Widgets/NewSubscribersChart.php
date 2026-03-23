<?php

namespace Mydnic\Subscribers\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Mydnic\Subscribers\Models\Subscriber;

class NewSubscribersChart extends ChartWidget
{
    protected static ?string $heading = 'New Subscribers';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
            '365' => 'Last 12 months',
        ];
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $useMonths = $days >= 90;

        $dates = Subscriber::where('created_at', '>=', now()->subDays($days))
            ->select('created_at')
            ->get()
            ->pluck('created_at');

        if ($useMonths) {
            $grouped = $dates->sortBy(fn ($d) => $d->format('Y-m'))
                ->groupBy(fn ($d) => $d->format('Y-m'));

            $labels = $grouped->keys()->map(fn ($p) => Carbon::createFromFormat('Y-m', $p)->format('M Y'))->toArray();
        } else {
            $grouped = $dates->sortBy(fn ($d) => $d->format('Y-m-d'))
                ->groupBy(fn ($d) => $d->format('Y-m-d'));

            $labels = $grouped->keys()->map(fn ($p) => Carbon::parse($p)->format('M d'))->toArray();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Subscribers',
                    'data' => $grouped->map->count()->values()->toArray(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
