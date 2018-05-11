<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\EventSourcery\PersonalData\CanNotFindCryptographyForPerson;
use EventSourcery\EventSourcery\PersonalData\CouldNotRetrievePersonalData;
use EventSourcery\EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\EventSourcery\PersonalData\EncryptionKey;
use EventSourcery\EventSourcery\PersonalData\InitializationVector;
use EventSourcery\EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalData;
use EventSourcery\EventSourcery\PersonalData\PersonalDataKey;
use EventSourcery\EventSourcery\PersonalData\PersonalDataStore;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use EventSourcery\Laravel\LaravelPersonalCryptographyStore;
use EventSourcery\Laravel\LaravelPersonalDataStore;

class LaravelPersonalDataStoreTest extends TestCase {

    /** @var PersonalDataStore */
    private $dataStore;
    /** @var PersonalCryptographyStore */
    private $cryptoStore;

    function setUp() {
        parent::setUp();
        $this->dataStore = $this->app->make(LaravelPersonalDataStore::class);
        $this->cryptoStore = new LaravelPersonalCryptographyStore();
    }

    public function testThrowsWhenDataCantBeRetrieved() {
        $this->expectException(CouldNotRetrievePersonalData::class);
        $this->dataStore->retrieveData(PersonalKey::fromString("personal"), PersonalDataKey::generate());
    }

    public function testPersonalDataCanBeStored() {
        $personalKey = PersonalKey::fromString("test123");
        $dataKey = PersonalDataKey::generate();
        $dataString = "this is just some regular stuff that doesn't need encryption";

        $this->cryptoStore->addPerson($personalKey, new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        ));

        $this->dataStore->storeData($personalKey, $dataKey, PersonalData::fromString($dataString));

        $data = $this->dataStore->retrieveData($personalKey, $dataKey);
        $this->assertSame($data->toString(), $dataString);
    }

    public function testCannotStoreDataWithoutPersonalCrypto() {
        $this->expectException(CanNotFindCryptographyForPerson::class);
        $personalKey = PersonalKey::fromString("test456");
        $dataKey = PersonalDataKey::generate();
        $this->dataStore->storeData($personalKey, $dataKey, PersonalData::fromString('arbitrary data'));
    }
}