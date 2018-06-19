<?php namespace EventSourcery\Laravel;

use DB;
use EventSourcery\EventSourcery\PersonalData\CanNotFindCryptographyForPerson;
use EventSourcery\EventSourcery\PersonalData\CouldNotFindCryptographyForPerson;
use EventSourcery\EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\EventSourcery\PersonalData\EncryptionKeyGenerator;
use EventSourcery\EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalEncryptionKeyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use Illuminate\Database\Query\Builder;

class LaravelPersonalCryptographyStore implements PersonalCryptographyStore {

    /**
     * add a person (identified by personal key) and their cryptographic details
     *
     * @param PersonalKey $person
     * @param CryptographicDetails $crypto
     */
    function addPerson(PersonalKey $person, CryptographicDetails $crypto) {
        $this->table()->insert([
            'personal_key'          => $person->serialize(),
            'cryptographic_details' => $crypto->serialize(),
        ]);
    }


    function getCryptographyFor(PersonalKey $person): CryptographicDetails {
        $crypto = $this->table()->where('personal_key', '=', $person->serialize())->first();
        if ( ! $crypto) {
            throw new CanNotFindCryptographyForPerson($person->serialize());
        }
        return CryptographicDetails::deserialize($crypto->cryptographic_details);
    }

    function removePerson(PersonalKey $person) {
        $this->table()->where('personal_key', '=', $person->serialize())->delete();
    }

    private function table(): Builder {
        return DB::table('personal_cryptography_store');
    }
}
