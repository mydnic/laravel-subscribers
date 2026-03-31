<?php

namespace Mydnic\Kanpen;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mydnic\Kanpen\Commands\DispatchScheduledCampaignsCommand;
use Mydnic\Kanpen\Commands\SyncSubscribersCommand;

class KanpenServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishing();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kanpen');
        $this->registerRoutes();
        $this->registerSchedule();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncSubscribersCommand::class,
                DispatchScheduledCampaignsCommand::class,
            ]);
        }
    }

    protected function registerSchedule(): void
    {
        if (! config('kanpen.campaigns.schedule', true)) {
            return;
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(DispatchScheduledCampaignsCommand::class)->everyMinute();
        });
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/2018_01_01_000000_create_subscribers_table.php' => database_path('migrations/2018_01_01_000000_kanpen_create_subscribers_table.php'),
                __DIR__.'/../database/migrations/2024_01_01_000001_create_campaigns_table.php' => database_path('migrations/2024_01_01_000001_kanpen_create_campaigns_table.php'),
                __DIR__.'/../database/migrations/2024_01_01_000002_create_campaign_deliveries_table.php' => database_path('migrations/2024_01_01_000002_kanpen_create_campaign_deliveries_table.php'),
                __DIR__.'/../database/migrations/2024_01_01_000004_create_campaign_clicks_table.php' => database_path('migrations/2024_01_01_000004_kanpen_create_campaign_clicks_table.php'),
            ], 'kanpen-migrations');

            $this->publishes([
                __DIR__.'/../config/kanpen.php' => config_path('kanpen.php'),
            ], 'kanpen-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/kanpen'),
            ], 'kanpen-views');

            $this->publishes([
                __DIR__.'/Notifications/SubscriberVerifyEmail.php' => app_path('Notifications/SubscriberVerifyEmail.php'),
            ], 'kanpen-notifications');
        }
    }

    protected function registerRoutes(): void
    {
        Route::group($this->webRouteConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        Route::group($this->apiRouteConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });

        if (config('kanpen.campaigns.enabled', true)) {
            Route::group($this->campaignRouteConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/campaigns.php');
            });
        }
    }

    protected function webRouteConfiguration(): array
    {
        return [
            'as' => 'kanpen.',
            'prefix' => 'kanpen',
            'middleware' => 'web',
        ];
    }

    protected function apiRouteConfiguration(): array
    {
        return [
            'as' => 'kanpen.api.',
            'prefix' => 'kanpen-api',
            'middleware' => 'api',
        ];
    }

    protected function campaignRouteConfiguration(): array
    {
        return [
            'as' => 'kanpen.api.',
            'prefix' => 'kanpen-api',
            'middleware' => config('kanpen.campaigns.middleware', ['api']),
        ];
    }

    public function register(): void
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/kanpen.php', 'kanpen');
        }
    }
}
