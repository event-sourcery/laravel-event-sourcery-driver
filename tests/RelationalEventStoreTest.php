<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\EventSourcery\EventSourcing\StreamId;
use EventSourcery\EventSourcery\PersonalData\EncryptionKey;
use EventSourcery\EventSourcery\PersonalData\InitializationVector;
use EventSourcery\EventSourcery\PersonalData\LibSodiumEncryption;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use EventSourcery\Laravel\LaravelPersonalCryptographyStore;
use EventSourcery\Laravel\RelationalEventStore;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Tests\EventSourcery\Laravel\Stubs\TestEmail;
use Tests\EventSourcery\Laravel\Stubs\TestEvent;
use Tests\EventSourcery\Laravel\Stubs\TestPersonalEvent;

class RelationalEventStoreTest extends TestCase {

    use InteractsWithDatabase;

    /** @var RelationalEventStore */
    private $dataStore;

    /** @var LaravelPersonalCryptographyStore */
    private $cryptoStore;

    /** @var LibSodiumEncryption */
    private $encryption;

    function setUp() {
        parent::setUp();
        $this->dataStore   = $this->app->make(RelationalEwventStore::class);
        $this->cryptoStore = new LaravelPersonalCryptographyStore();
        $this->encryption  = new LibSodiumEncryption();

        $person = PersonalKey::fromString('123');

        $crypto = $this->encryption->generateCryptographicDetails();

        $this->cryptoStore->addPerson($person, $crypto);
    }

    public function testItCanStoreAnEventWithPersonalData() {
        $event = new TestPersonalEvent(new TestEmail(PersonalKey::fromString('123'), 'test@abc.com'));
        $this->dataStore->storeEvent($event);
        $this->assertDatabaseHas('event_store', [
            'event_name' => 'TestPersonalEvent',
        ]);
    }

    public function testItCanDeSerializeAnEventWithPersonalData() {
        $event = new TestPersonalEvent(new TestEmail(PersonalKey::fromString('123'), 'test@abc.com'));
        $this->dataStore->storeEvent($event);
        $events = $this->dataStore->getStream(StreamId::fromString(0));
        $this->assertTrue($event == $events->toDomainEvents()->first());
    }

    public function testItCanStoreASimpleEvent() {
        $event = new TestEvent(1);
        $this->dataStore->storeEvent($event);
        $this->assertDatabaseHas('event_store', [
            'event_name' => 'TestEvent',
            'event_data' => json_encode([
                'eventName' => 'TestEvent',
                'fields'    => [
                    'number' => 1,
                ],
            ]),
        ]);
    }
}
