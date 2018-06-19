<?php namespace EventSourcery\Laravel;

use EventSourcery\EventSourcery\Commands\CommandBus;
use EventSourcery\EventSourcery\Commands\ReflectionResolutionCommandBus;
use EventSourcery\EventSourcery\EventDispatch\EventDispatcher;
use EventSourcery\EventSourcery\EventDispatch\ImmediateEventDispatcher;
use EventSourcery\EventSourcery\EventSourcing\DomainEventClassMap;
use EventSourcery\EventSourcery\EventSourcing\EventStore;
use EventSourcery\EventSourcery\PersonalData\LibSodiumEncryption;
use EventSourcery\EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalDataEncryption;
use EventSourcery\EventSourcery\PersonalData\PersonalDataStore;
use EventSourcery\EventSourcery\Queries\ProjectionManager;
use EventSourcery\EventSourcery\Queries\Projections;
use EventSourcery\EventSourcery\Serialization\DomainEventSerializer;
use EventSourcery\EventSourcery\Serialization\ReflectionBasedDomainEventSerializer;
use Illuminate\Support\ServiceProvider;

class EventSourceryServiceProvider extends ServiceProvider {

    public function register() {
        $this->app->bind(DomainEventSerializer::class, ReflectionBasedDomainEventSerializer::class);

        $this->app->singleton(DomainEventClassMap::class);

        $this->app->singleton(EventDispatcher::class, function($app) {
            return new ImmediateEventDispatcher();
        });

        $this->app->singleton(EventStore::class, function ($app) {
            return new RelationalEventStore($app[DomainEventSerializer::class]);
        });

        $this->app->singleton(ProjectionManager::class, function ($app) {
            return new ProjectionManager(Projections::make([]));
        });

        $this->app->bind(CommandBus::class, ReflectionResolutionCommandBus::class);
        $this->app->bind(PersonalCryptographyStore::class, LaravelPersonalCryptographyStore::class);
        $this->app->bind(PersonalDataStore::class, LaravelPersonalDataStore::class);
        $this->app->bind(PersonalDataEncryption::class, LibSodiumEncryption::class);
    }

    public function boot() {
        $migrationPath = __DIR__ . '/../migrations';

        $this->loadMigrationsFrom($migrationPath);

        $this->publishes([
            $migrationPath => database_path('migrations'),
        ], 'migrations');

        $dispatcher = $this->app[EventDispatcher::class];
        $dispatcher->addListener($this->app[ProjectionManager::class]);
    }
}
