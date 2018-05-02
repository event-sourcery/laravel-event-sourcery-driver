<?php namespace EventSourcery\Laravel;

use DB;
use Illuminate\Support\Collection;
use EventSourcery\EventSourcing\DomainEvent;
use EventSourcery\EventSourcing\DomainEvents;
use EventSourcery\EventSourcing\DomainEventSerializer;
use EventSourcery\EventSourcing\EventStore;
use EventSourcery\EventSourcing\StreamEvent;
use EventSourcery\EventSourcing\StreamEvents;
use EventSourcery\EventSourcing\StreamId;
use EventSourcery\EventSourcing\StreamVersion;

class RelationalEventStore implements EventStore {

    /** @var DomainEventSerializer */
    private $serializer;

    private $table = 'event_store';

    // The DomainEventSerializer will transform events to/from objects
    public function __construct(DomainEventSerializer $serializer) {
        $this->serializer = $serializer;
    }

    // Retrieve an entire event stream (domain events) as a collection of objects.
    public function getStream(StreamId $id): StreamEvents {
        return StreamEvents::make(
            $this->getStreamRawEventData($id)
                ->map(function($e) {
                    $e->event_data = (array) json_decode($e->event_data);
                    return new StreamEvent(
                        StreamId::fromString($e->stream_id),
                        StreamVersion::fromInt($e->stream_version),
                        $this->serializer->deserialize($e)
                    );
                })
                ->toArray()
        );
    }

    // Retrieve a stream's raw event data from the database
    private function getStreamRawEventData(StreamId $id): Collection {
        return DB::table($this->table)
            ->where('stream_id', '=', $id->toString())
            ->orderBy('stream_version', 'asc')
            ->get();
    }

    // Store a collection of stream events
    public function storeStream(StreamEvents $events): void {
        // store events
        $events->each(function ($stream) {
            $this->store($stream->id(), $stream->event(), $stream->version());
        });

        // queue event dispatch
        $job = new DispatchDomainEvents($events->toDomainEvents());
        dispatch($job->onQueue('event_dispatch'));
    }

    // store a single event
    public function storeEvent(DomainEvent $event): void {
        $this->store(
            StreamId::fromString(0),
            $event,
            StreamVersion::zero(),
            ''
        );

        // @todo move this into a new object, injected in constructor
        $job = new DispatchDomainEvents(DomainEvents::make([$event]));
        dispatch($job->onQueue('event_dispatch'));
    }

    private function store(StreamId $id, DomainEvent $event, StreamVersion $version, $metadata = ''): void {
        DB::table($this->table)->insert([
            'stream_id'      => $id->toString(),
            'stream_version' => $version->toInt(),
            'event_name'     => $this->serializer->eventNameForClass(get_class($event)),
            'event_data'     => $this->serializer->serialize($event),
            'raised_at'      => date('Y-m-d H:i:s'),
            'meta_data'      => $metadata,
        ]);
    }

    public function getEvents($take = 0, $skip = 0): DomainEvents {
        $eventData = $this->getRawEvents($take, $skip);
        $events = $eventData->map(function($e) {
            $e->event_data = (array) json_decode($e->event_data);
            return $this->serializer->deserialize($e);
        })->toArray();

        return DomainEvents::make($events);
    }

    private function getRawEvents($take = 0, $skip = 0) {
        return DB::table('event_store')
            ->orderBy('id')
            ->take($take)
            ->skip($skip)
            ->get();
    }

}
