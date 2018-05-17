<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\EventSourcery\EventSourcing\StreamId;
use EventSourcery\EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\EventSourcery\PersonalData\EncryptionKey;
use EventSourcery\EventSourcery\PersonalData\InitializationVector;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use EventSourcery\Laravel\LaravelPersonalCryptographyStore;
use EventSourcery\Laravel\RelationalEventStore;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Tests\EventSourcery\Laravel\Stubs\TestEmail;
use Tests\EventSourcery\Laravel\Stubs\TestPersonalEvent;
use Tests\EventSourcery\Laravel\Stubs\TestEvent;

class RelationalEventStoreTest extends TestCase {

    use InteractsWithDatabase;

    /** @var RelationalEventStore */
    private $dataStore;

    /** @var LaravelPersonalCryptographyStore */
    private $cryptoStore;

    function setUp() {
        parent::setUp();
        $this->dataStore = $this->app->make(RelationalEventStore::class);
        $this->cryptoStore = new LaravelPersonalCryptographyStore();

        $person = PersonalKey::fromString("123");

        $crypto = new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        );

        $this->cryptoStore->addPerson($person, $crypto);
    }

    public function testItCanStoreAnEventWithPersonalData() {
        $event = new TestPersonalEvent(new TestEmail(PersonalKey::fromString("123"), "test@abc.com"));
        $this->dataStore->storeEvent($event);
        $this->assertDatabaseHas('event_store', [
            'event_name' => "TestPersonalEvent",
        ]);
    }

    public function testItCanDeSerializeAnEventWithPersonalData() {
        $event = new TestPersonalEvent(new TestEmail(PersonalKey::fromString("123"), "test@abc.com"));
        $this->dataStore->storeEvent($event);
        $events = $this->dataStore->getStream(StreamId::fromString(0));
        $this->assertTrue($event == $events->toDomainEvents()->first());
    }

   public function testItCanStoreASimpleEvent() {
        $event = new TestEvent(1);
        $this->dataStore->storeEvent($event);
        $this->assertDatabaseHas('event_store', [
            'event_name' => "TestEvent",
            'event_data' => json_encode([
                'eventName' => "TestEvent",
                'fields' => [
                    'number' => 1
                ]
            ])
        ]);
    }
}
