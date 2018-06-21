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

/**
 * The LaravelPersonalCryptographyStore is the Laravel-specific implementation
 * of a PersonalCryptographyStore. It uses the default relational driver for
 * the Laravel application.
 */
class LaravelPersonalCryptographyStore implements PersonalCryptographyStore {

    /**
     * add a person (identified by personal key) and their cryptographic details
     *
     * @param PersonalKey $person
     * @param CryptographicDetails $crypto
     */
    function addPerson(PersonalKey $person, CryptographicDetails $crypto): void {
        $this->table()->insert([
            'personal_key'          => $person->serialize(),
            'cryptographic_details' => $crypto->serialize(),
            'encryption'            => $crypto->encryption(),
        ]);
    }

    /**
     * get cryptography details for a person (identified by personal key)
     *
     * @param PersonalKey $person
     * @throws CanNotFindCryptographyForPerson
     * @return CryptographicDetails
     * @throws \EventSourcery\EventSourcery\PersonalData\CannotDeserializeCryptographicDetails
     */
    function getCryptographyFor(PersonalKey $person): CryptographicDetails {
        $crypto = $this->table()->where('personal_key', '=', $person->serialize())->first();

        if ( ! $crypto) {
            throw new CanNotFindCryptographyForPerson($person->serialize());
        }

        return CryptographicDetails::deserialize($crypto->cryptographic_details);
    }

    /**
     * remove cryptographic details for a person (identified by personal key)
     *
     * @param PersonalKey $person
     */
    function removePerson(PersonalKey $person): void {
        $this->table()->where('personal_key', '=', $person->serialize())->delete();
    }

    private function table(): Builder {
        return DB::table('personal_cryptography_store');
    }
}
