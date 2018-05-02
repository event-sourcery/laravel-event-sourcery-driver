<?php namespace EventSourcery\Laravel;

use EventSourcery\Commands\CommandBus;
use EventSourcery\Commands\ReflectionResolutionCommandBus;
use EventSourcery\EventDispatch\EventDispatcher;
use EventSourcery\EventDispatch\ImmediateEventDispatcher;
use EventSourcery\EventSourcing\DomainEventClassMap;
use EventSourcery\EventSourcing\EventStore;
use EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\Queries\ProjectionManager;
use EventSourcery\Queries\Projections;
use EventSourcery\Serialization\DomainEventSerializer;
use EventSourcery\Serialization\ReflectionBasedDomainEventSerializer;
use Illuminate\Support\ServiceProvider;

class EventSourceryServiceProvider extends ServiceProvider {

    public function register() {

        $this->app->singleton(EventDispatcher::class, function ($app) {
            return new ImmediateEventDispatcher;
        });

        $this->app->singleton(DomainEventClassMap::class, function ($app) {
            return new DomainEventClassMap;
        });

        $this->app->bind(DomainEventSerializer::class, ReflectionBasedDomainEventSerializer::class);

        $this->app->bind(EventStore::class, function ($app) {
            return new RelationalEventStore($app[DomainEventSerializer::class]);
        });

        $this->app->singleton(ProjectionManager::class, function ($app) {
            return new ProjectionManager(Projections::make([]));
        });

        $this->app->bind(CommandBus::class, ReflectionResolutionCommandBus::class);

        $this->app->bind(PersonalCryptographyStore::class, LaravelPersonalCryptographyStore::class);
    }

    public function boot() {
        $dispatcher = $this->app[EventDispatcher::class];
        $dispatcher->addListener($this->app[ProjectionManager::class]);
    }
}
