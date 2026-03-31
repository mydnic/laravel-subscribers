<?php

namespace Mydnic\Kanpen\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mydnic\Kanpen\KanpenServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpRoutes();
    }

    protected function getPackageProviders($app): array
    {
        return [KanpenServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $config = $app->get('config');
        $config->set('logging.default', 'errorlog');
        $config->set('database.default', 'testbench');
        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpRoutes(): void
    {
        // Provide the 'home' named route that the web controller redirects to
        Route::get('/home', fn () => 'home')->name('home');
    }

    protected function setUpDatabase(): void
    {
        Schema::dropIfExists(config('kanpen.tables.campaign_clicks'));
        Schema::dropIfExists(config('kanpen.tables.campaign_deliveries'));
        Schema::dropIfExists(config('kanpen.tables.campaigns'));
        Schema::dropIfExists(config('kanpen.tables.subscribers'));

        Schema::create(config('kanpen.tables.subscribers'), function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('kanpen.tables.campaigns'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->longText('content_html')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('kanpen.tables.campaign_deliveries'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained(config('kanpen.tables.campaigns'))->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained(config('kanpen.tables.subscribers'))->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();
        });

        Schema::create(config('kanpen.tables.campaign_clicks'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_delivery_id')->constrained(config('kanpen.tables.campaign_deliveries'))->cascadeOnDelete();
            $table->string('url');
            $table->timestamp('clicked_at');
        });
    }
}
