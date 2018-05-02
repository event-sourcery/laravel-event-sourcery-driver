<?php namespace spec\EventSourcery\Laravel;

use EventSourcery\Laravel\LaravelPersonalCryptographyStore;
use EventSourcery\PersonalData\CanNotFindCryptographyForPerson;
use EventSourcery\PersonalData\CouldNotFindCryptographyForPerson;
use EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\PersonalData\EncryptionKey;
use EventSourcery\PersonalData\InitializationVector;
use EventSourcery\PersonalData\Pbkdf2KeyGenerator;
use EventSourcery\PersonalData\PersonalKey;
use PhpSpec\Laravel\LaravelObjectBehavior;

class LaravelPersonalCryptographyStoreSpec extends LaravelObjectBehavior {

    function it_is_initializable() {
        $this->shouldHaveType(LaravelPersonalCryptographyStore::class);
    }

    function it_can_add_a_person_and_retrieve_keys() {
        $person = PersonalKey::fromString("hats");
        $crypto = new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        );

        $this->addPerson($person, $crypto);
        $this->shouldNotThrow(CanNotFindCryptographyForPerson::class)->during('getCryptographyFor', [$person]);
    }

    function it_generates_unique_keys() {
        $person = PersonalKey::fromString("hats");
        $crypto = new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        );

        $this->addPerson($person, $crypto);
        $key1 = $this->getCryptographyFor($person);

        $person2 = PersonalKey::fromString("cats");
        $crypto2 = new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        );

        $this->addPerson($person2, $crypto2);
        $key2 = $this->getCryptographyFor($person2);

        $key1->serialize()->shouldNotBe($key2->serialize()->getWrappedObject());
    }

    function it_can_remove_people() {
        $person = PersonalKey::fromString("hats");
        $crypto = new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        );

        $this->addPerson($person, $crypto);
        $this->removePerson($person);
        $this->shouldThrow(CanNotFindCryptographyForPerson::class)->during('getCryptographyFor', [$person]);
    }
}
