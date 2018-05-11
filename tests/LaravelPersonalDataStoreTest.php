<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\EventSourcery\PersonalData\EncryptionKey;
use EventSourcery\EventSourcery\PersonalData\InitializationVector;
use EventSourcery\EventSourcery\PersonalData\PersonalData;
use EventSourcery\EventSourcery\PersonalData\PersonalDataKey;
use EventSourcery\EventSourcery\PersonalData\PersonalDataStore;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use EventSourcery\Laravel\LaravelPersonalCryptographyStore;
use EventSourcery\Laravel\LaravelPersonalDataStore;

class LaravelPersonalDataStoreTest extends TestCase {

    public function testPersonalDataCanBeStored() {
        /** @var PersonalDataStore $dataStore */
        $dataStore = $this->app->make(LaravelPersonalDataStore::class);
        $cryptoStore = new LaravelPersonalCryptographyStore();

        $personalKey = PersonalKey::fromString("test123");
        $cryptoStore->addPerson($personalKey, new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        ));

        $dataStore->storeData($personalKey, PersonalDataKey::generate(), PersonalData::fromString('personal details'));
    }
}