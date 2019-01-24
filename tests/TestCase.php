<?php

namespace Mydnic\Subscribers\Test;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Mydnic\Subscribers\SubscribersServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [SubscribersServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = $app->get('config');
        $config->set('logging.default', 'errorlog');
        $config->set('database.default', 'testbench');
        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app->when(DatabaseEntriesRepository::class)
            ->needs('$connection')
            ->give('testbench');
    }

    protected function setUpDatabase()
    {
        Schema::dropIfExists('subscribers');

        Schema::create('subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }
}
