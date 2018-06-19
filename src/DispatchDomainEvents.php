<?php namespace EventSourcery\Laravel;

use EventSourcery\EventSourcery\EventDispatch\EventDispatcher;
use EventSourcery\EventSourcery\Serialization\DomainEventSerializer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use EventSourcery\EventSourcery\EventSourcing\DomainEvent;
use EventSourcery\EventSourcery\EventSourcing\DomainEvents;

class DispatchDomainEvents implements ShouldQueue {

    use InteractsWithQueue, Queueable, SerializesModels;

    private $serializedEvents = [];

    /**
     * construct the queue job to dispatch domain events
     *
     * DispatchDomainEvents constructor.
     * @param DomainEvents $events
     */
    public function __construct(DomainEvents $events) {

        // generate a list of event names
        $eventNames = $events->map(function (DomainEvent $event) {
            return $this->nameOfEvent($event);
        })->toArray();

        // generate a list of serialized events
        /** @var DomainEventSerializer $serializer */
        $serializer = app(DomainEventSerializer::class);
        $serializedEvents = $events->map(function (DomainEvent $event) use ($serializer) {
            return $serializer->serialize($event);
        })->toArray();

        // create an array of tuples [event name, serialized event]
        for ($i = 0; $i < $events->count(); $i++) {
            $this->serializedEvents[$i] = [
                $eventNames[$i],
                $serializedEvents[$i]
            ];
        }
    }

    /**
     * this is the function that is run by the queue worker itself. handle is
     * like a constructor for this process in that it gets its dependencies auto-injected
     * from the container
     *
     * @param DomainEventSerializer $serializer
     * @param EventDispatcher $dispatcher
     * @throws \Exception
     */
    public function handle(DomainEventSerializer $serializer, EventDispatcher $dispatcher) {
        $events = $this->tryToDeserializeEvents($serializer);
        $dispatcher->dispatch($events);
    }

    /**
     * get the short name of the class without reflection as a minor optimization
     *
     * @param $class
     * @return string
     */
    private function nameOfEvent($class): string {
        $className = explode('\\', get_class($class));
        return $className[count($className) - 1];
    }

    /**
     * Ask the serializer to deserialize each event. Return a collection of the
     * deserialized events.
     *
     * @param DomainEventSerializer $serializer
     * @return DomainEvents
     */
    private function deserializeEvents(DomainEventSerializer $serializer) {
        $events = [];
        foreach ($this->serializedEvents as $serializedEvent) {
            list($eventName, $eventData) = $serializedEvent;
            $events[] = $serializer->deserialize(json_decode($eventData));
        }
        return DomainEvents::make($events);
    }

    /**
     * @param DomainEventSerializer $serializer
     * @return DomainEvents
     * @throws \Exception
     */
    private function tryToDeserializeEvents(DomainEventSerializer $serializer) {
        try {
            return $this->deserializeEvents($serializer);
        } catch (\Exception $e) {
            \Log::error(get_class($e) . ': ' . $e->getMessage());
            throw $e;
        }
    }
}
