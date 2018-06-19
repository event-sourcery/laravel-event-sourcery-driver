<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\EventSourcery\EventSourcing\DomainEventClassMap;
use EventSourcery\Laravel\EventSourceryServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Tests\EventSourcery\Laravel\Stubs\TestEvent;
use Tests\EventSourcery\Laravel\Stubs\TestPersonalEvent;

class TestCase extends OrchestraTestCase {

    protected function setUp() {
        parent::setUp();
        $this->artisan('migrate:reset', ['--database' => 'development']);
        $this->artisan('migrate', ['--database' => 'development']);

        $this->app->bind(DomainEventClassMap::class, function() {
            $classMap = new DomainEventClassMap();
            $classMap->add("TestEvent", TestEvent::class);
            $classMap->add("TestPersonalEvent", TestPersonalEvent::class);
            return $classMap;
        });
    }

    protected function getEnvironmentSetUp($app) {
        $app['config']->set('database.default', 'development');
        $app['config']->set('database.connections.development', [
            'driver'   => 'mysql',
            'host'     => '127.0.0.1',
            'port'     => '3306',
            'database' => 'development',
            'username' => 'root',
            'password' => 'password',
        ]);
    }

    protected function getPackageProviders($app) {
        return [
            EventSourceryServiceProvider::class
        ];
    }

    protected function getPackageAliases($app) {
        return [];
    }
}
