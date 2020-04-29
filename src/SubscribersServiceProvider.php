<?php

namespace Mydnic\Subscribers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SubscribersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->registerRoutes();
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/../resources/js/components' => resource_path('js/components/Subscribers'),
        ], 'subscribers-vue-component');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'subscribers-migrations');

        $this->publishes([
            __DIR__.'/../config/laravel-subscribers.php' => config_path('laravel-subscribers.php'),
        ], 'subscribers-config');
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group($this->apiRouteConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
        Route::group($this->webRouteConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Get the Subscribers route group configuration array for web middleware.
     *
     * @return array
     */
    protected function webRouteConfiguration()
    {
        return [
            'namespace' => 'Mydnic\Subscribers\Http\Controllers',
            'as' => 'subscribers.',
            'prefix' => 'subscribers',
            'middleware' => 'web',
        ];
    }

    /**
     * Get the Subscribers route group configuration array for api middleware.
     *
     * @return array
     */
    protected function apiRouteConfiguration()
    {
        return [
            'namespace' => 'Mydnic\Subscribers\Http\Controllers\Api',
            'as' => 'subscribers.api.',
            'prefix' => 'subscribers-api',
            'middleware' => 'api',
        ];
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/laravel-subscribers.php', 'laravel-subscribers');
        }
    }
}
