<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\EventSourcery\PersonalData\CanNotFindCryptographyForPerson;
use EventSourcery\EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\EventSourcery\PersonalData\EncryptionKey;
use EventSourcery\EventSourcery\PersonalData\InitializationVector;
use EventSourcery\EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use EventSourcery\Laravel\LaravelPersonalCryptographyStore;

class LaravelPersonalCryptographyStoreTest extends TestCase {

    /** @var PersonalCryptographyStore */
    private $cryptoStore;

    function setUp() {
        parent::setUp();
        $this->cryptoStore = new LaravelPersonalCryptographyStore();
    }

    function testPeopleCanBeAdded() {
        $person = PersonalKey::fromString("this is a person's identity");

        $crypto = new CryptographicDetails('stub', []);

        $this->cryptoStore->addPerson($person, $crypto);

        $newCrypto = $this->cryptoStore->getCryptographyFor($person);

        $this->assertSame($crypto->serialize(), $newCrypto->serialize());
    }

    function testPeopleCanBeRemoved() {
        $person = PersonalKey::fromString("a personal identity token");

        $crypto = new CryptographicDetails('stub', []);

        $this->cryptoStore->addPerson($person, $crypto);
        $this->cryptoStore->removePerson($person);

        $this->expectException(CanNotFindCryptographyForPerson::class);
        $this->cryptoStore->getCryptographyFor($person);
    }
}