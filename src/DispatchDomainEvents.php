<?php namespace EventSourcery\Laravel;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use EventSourcery\EventSourcery\EventSourcing\DomainEvent;
use EventSourcery\EventSourcery\EventSourcing\DomainEvents;
use EventSourcery\EventSourcery\EventSourcing\DomainEventSerializer;
use EventSourcery\EventSourcery\EventSourcing\EventDispatcher;

class DispatchDomainEvents implements ShouldQueue {

    use InteractsWithQueue, Queueable, SerializesModels;

    private $serializedEvents = [];

    public function __construct(DomainEvents $events) {
        $eventNames = $events->map(function (DomainEvent $event) {
            return $this->nameOfEvent($event);
        })->toArray();

        $serializedEvents = $events->map(function (DomainEvent $event) {
            return $event->serialize();
        })->toArray();

        for ($i = 0; $i < $events->count(); $i++) {
            $this->serializedEvents[] = [
                $eventNames[$i],
                $serializedEvents[$i]
            ];
        }
    }

    public function handle(DomainEventSerializer $serializer, EventDispatcher $dispatcher) {
        $events = $this->tryToDeserializeEvents($serializer);
        $dispatcher->dispatch($events);
    }

    private function nameOfEvent($class): string {
        $className = explode('\\', get_class($class));
        return $className[count($className) - 1];
    }

    /**
     * @param DomainEventSerializer $serializer
     * @return DomainEvents
     */
    private function deserializeEvents(DomainEventSerializer $serializer) {
        $events = [];
        foreach ($this->serializedEvents as $serializedEvent) {
            list($eventName, $eventData) = $serializedEvent;
            $events[] = $serializer->deserialize((object) [
                'event_name' => $eventName,
                'event_data' => $eventData,
            ]);
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