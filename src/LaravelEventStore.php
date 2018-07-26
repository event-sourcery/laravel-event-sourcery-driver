<?php namespace EventSourcery\Laravel;

use DB;
use EventSourcery\EventSourcery\EventSourcing\DomainEvent;
use EventSourcery\EventSourcery\EventSourcing\DomainEvents;
use EventSourcery\EventSourcery\EventSourcing\EventStore;
use EventSourcery\EventSourcery\EventSourcing\StreamEvent;
use EventSourcery\EventSourcery\EventSourcing\StreamEvents;
use EventSourcery\EventSourcery\EventSourcing\StreamId;
use EventSourcery\EventSourcery\EventSourcing\StreamVersion;
use EventSourcery\EventSourcery\Serialization\DomainEventSerializer;
use Illuminate\Support\Collection;

/**
 * The LaravelEventStore is a Laravel-specific implementation of
 * an EventStore. It uses the default relational driver configured
 * in the Laravel application.
 */
class LaravelEventStore implements EventStore {

    /** @var DomainEventSerializer */
    private $serializer;

    private $table = 'event_store';

    public function __construct(DomainEventSerializer $serializer) {
        $this->serializer = $serializer;
    }

    /**
     * persist events in an event stream
     *
     * @param StreamEvents $events
     */
    public function storeStream(StreamEvents $events): void {
        // store events
        $events->each(function (StreamEvent $stream) {
            $this->store($stream->id(), $stream->event(), $stream->version());
        });

        // queue event dispatch
        $job = new DispatchDomainEvents($events->toDomainEvents());
        dispatch($job->onQueue('event_dispatch'));
    }

    /**
     * persist a single event
     *
     * @param DomainEvent $event
     */
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

    /**
     * retrieve an event stream based on its id
     *
     * @param StreamId $id
     * @return StreamEvents
     */
    public function getStream(StreamId $id): StreamEvents {
        return StreamEvents::make(
            $this->getStreamRawEventData($id)->map(function ($e) {

                $e->event_data = json_decode($e->event_data, true);

                return new StreamEvent(
                    StreamId::fromString($e->stream_id),
                    StreamVersion::fromInt($e->stream_version),
                    $this->serializer->deserialize($e->event_data)
                );

            })->toArray()
        );
    }

    /**
     * a pagination function for processing events by pages
     * 0 is the first event in the store
     *
     * @param int $take
     * @param int $skip
     * @return DomainEvents
     */
    public function getEvents($take = 0, $skip = 0): DomainEvents {
        $eventData = $this->getRawEvents($take, $skip);

        $events = $eventData->map(function ($e) {
            $e->event_data = json_decode($e->event_data, true);
            return $this->serializer->deserialize($e);
        })->toArray();

        return DomainEvents::make($events);
    }

    /**
     * retrieve raw stream data from the database
     *
     * @param StreamId $id
     * @return Collection
     */
    private function getStreamRawEventData(StreamId $id): Collection {
        return DB::table($this->table)
            ->where('stream_id', '=', $id->toString())
            ->orderBy('stream_version', 'asc')
            ->get();
    }

    /**
     * get raw event data for pagination
     *
     * @param int $take
     * @param int $skip
     * @return mixed
     */
    private function getRawEvents($take = 0, $skip = 0) {
        return DB::table('event_store')
            ->orderBy('id')
            ->take($take)
            ->skip($skip)
            ->get();
    }

    /**
     * execute the relational persistence
     *
     * @param StreamId $id
     * @param DomainEvent $event
     * @param StreamVersion $version
     * @param string $metadata
     */
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
}
