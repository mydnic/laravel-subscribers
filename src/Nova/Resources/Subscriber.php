<?php

namespace Mydnic\Kanpen\Nova\Resources;

use App\Nova\Resource;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mydnic\Kanpen\Nova\Metrics\NewSubscribers;

class Subscriber extends Resource
{
    public static string $model = \Mydnic\Kanpen\Models\Subscriber::class;

    public static $title = 'email';

    public static $search = ['id', 'email'];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:255')
                ->creationRules('unique:subscribers,email')
                ->updateRules('unique:subscribers,email,{{resourceId}}'),

            DateTime::make('Verified At', 'email_verified_at')
                ->nullable()
                ->sortable(),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            new NewSubscribers,
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [];
    }

    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
