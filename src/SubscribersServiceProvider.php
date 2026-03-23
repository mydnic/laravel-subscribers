<?php

namespace Mydnic\Subscribers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mydnic\Subscribers\Commands\DispatchScheduledCampaignsCommand;
use Mydnic\Subscribers\Commands\SyncSubscribersCommand;

class SubscribersServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishing();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-subscribers');
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
        if (! config('laravel-subscribers.campaigns.schedule', true)) {
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
                __DIR__ . '/../resources/js/components' => resource_path('js/components/Subscribers'),
            ], 'subscribers-vue-component');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'subscribers-migrations');

            $this->publishes([
                __DIR__ . '/../config/laravel-subscribers.php' => config_path('laravel-subscribers.php'),
            ], 'subscribers-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-subscribers'),
            ], 'subscribers-views');
        }
    }

    protected function registerRoutes(): void
    {
        Route::group($this->webRouteConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });

        Route::group($this->apiRouteConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        if (config('laravel-subscribers.campaigns.enabled', true)) {
            Route::group($this->campaignRouteConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/campaigns.php');
            });
        }
    }

    protected function webRouteConfiguration(): array
    {
        return [
            'as' => 'subscribers.',
            'prefix' => 'subscribers',
            'middleware' => 'web',
        ];
    }

    protected function apiRouteConfiguration(): array
    {
        return [
            'as' => 'subscribers.api.',
            'prefix' => 'subscribers-api',
            'middleware' => 'api',
        ];
    }

    protected function campaignRouteConfiguration(): array
    {
        return [
            'as' => 'subscribers.api.',
            'prefix' => 'subscribers-api',
            'middleware' => config('laravel-subscribers.campaigns.middleware', ['api']),
        ];
    }

    public function register(): void
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/laravel-subscribers.php', 'laravel-subscribers');
        }
    }
}
