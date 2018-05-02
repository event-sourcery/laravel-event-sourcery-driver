<?php namespace EventSourcery\Laravel;

use DB;
use EventSourcery\PersonalData\CanNotFindCryptographyForPerson;
use EventSourcery\PersonalData\CouldNotFindCryptographyForPerson;
use EventSourcery\PersonalData\CryptographicDetails;
use EventSourcery\PersonalData\EncryptionKeyGenerator;
use EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\PersonalData\PersonalEncryptionKeyStore;
use EventSourcery\PersonalData\PersonalKey;
use Illuminate\Database\Query\Builder;

class LaravelPersonalCryptographyStore implements PersonalCryptographyStore {

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
