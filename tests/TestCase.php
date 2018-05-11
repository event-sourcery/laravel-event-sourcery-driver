<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\Laravel\EventSourceryServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase {

    protected function setUp() {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'development']);
    }

    protected function tearDown() {
        $this->artisan('migrate:reset', ['--database' => 'development']);
        parent::tearDown();
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
