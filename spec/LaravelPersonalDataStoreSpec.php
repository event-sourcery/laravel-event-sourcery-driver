<?php namespace spec\EventSourcery\Laravel;

use EventSourcery\Laravel\LaravelPersonalCryptographyStore;
use EventSourcery\Laravel\LaravelPersonalDataStore;
use EventSourcery\EventSourcery\PersonalData\AesPersonalDataEncryption;
use EventSourcery\EventSourcery\PersonalData\CanNotFindCryptographyForPerson;
use EventSourcery\EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\EventSourcery\PersonalData\EncryptionKey;
use EventSourcery\EventSourcery\PersonalData\InitializationVector;
use EventSourcery\EventSourcery\PersonalData\Pbkdf2KeyGenerator;
use EventSourcery\EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalData;
use EventSourcery\EventSourcery\PersonalData\PersonalDataKey;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use PhpSpec\Laravel\LaravelObjectBehavior;

class LaravelPersonalDataStoreSpec extends LaravelObjectBehavior {

    /** @var PersonalCryptographyStore */
    private $cryptoStore;

    function let() {
        $this->cryptoStore = new LaravelPersonalCryptographyStore();

        return $this->beConstructedWith(
            $this->cryptoStore,
            new AesPersonalDataEncryption()
        );
    }

    function it_is_initializable() {
        $this->shouldHaveType(LaravelPersonalDataStore::class);
    }

    function it_can_store_data() {
        $hats = PersonalKey::fromString("hats_981273");
        $this->cryptoStore->addPerson($hats, new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        ));
        $this->storeData($hats, PersonalDataKey::generate(), PersonalData::fromString('personal details'));
    }

    function it_throws_when_it_cannot_receive_data() {
        $this->shouldThrow(CouldNotRetrievePersonalData::class)->during('retrieveData', [PersonalKey::deserialize('person_key'), PersonalDataKey::fromString('non-existent data_key')]);
    }

    function it_can_retrieve_data() {
        $personalKey = PersonalKey::fromString("person_key_g751872");
        $this->cryptoStore->addPerson($personalKey, new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        ));

        $dataKey = PersonalDataKey::generate();

        $this->storeData($personalKey, $dataKey, PersonalData::fromString('personal details'));
        $data = $this->retrieveData($personalKey, $dataKey);
        $data->serialize()->shouldBe('personal details');
    }

    function it_cannot_store_data_without_cryptography_details() {
        $personalKey = PersonalKey::fromString("person_key_55123");
        $this->cryptoStore->removePerson($personalKey);

        $this->shouldThrow(CanNotFindCryptographyForPerson::class)->during('storeData', [$personalKey, PersonalDataKey::fromString("data_key_55123"), PersonalData::fromString('personal details')]);
    }

    function it_cannot_retrieve_data_without_cryptography_details() {
        $personalKey = PersonalKey::fromString("person_key_55eoaea3");
        $this->cryptoStore->addPerson($personalKey, new CryptographicDetails(
            EncryptionKey::generate(),
            InitializationVector::generate()
        ));

        $this->storeData($personalKey, PersonalDataKey::fromString("data_key_55123"), PersonalData::fromString('personal details'));

        $this->cryptoStore->removePerson($personalKey);

        $this->shouldThrow(CanNotFindCryptographyForPerson::class)->during('retrieveData', [PersonalKey::fromString('person_key_55123'), PersonalDataKey::fromString('data_key_55123')]);
    }
}
