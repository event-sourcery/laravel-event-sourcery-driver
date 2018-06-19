<?php namespace EventSourcery\Laravel;

use EventSourcery\EventSourcery\Queries\Projection;
use EventSourcery\EventSourcery\EventSourcing\DomainEvent;
use EventSourcery\EventSourcery\EventSourcing\EventStore;

abstract class RelationalProjection implements Projection {

    /** @var EventStore $events */
    protected $events;

    public function __construct(EventStore $events) {
        $this->events = $events;
    }

    /**
     * return the name of the projection
     *
     * @return string
     */
    abstract public function name() : string;

    /**
     * clear the entire projection's state
     */
    abstract public function reset() : void;

    /**
     * receives domain events and routes them to a method with their name
     *
     * @param DomainEvent $event
     */
    public function handle(DomainEvent $event) : void {
        $method = lcfirst($this->getShortName($event));
        if (method_exists($this, $method)) {
            $this->$method($event);
        }
    }

    /**
     * takes a fully namespaced class name and returns a short class-name
     * only version to match conventions.
     *
     * @param $class
     * @return string
     */
    private function getShortName($class) : string {
        $className = explode('\\', get_class($class));
        return $className[count($className) - 1];
    }
}
