<?php namespace EventSourcery\Laravel;

use DB;
use EventSourcery\EventSourcery\PersonalData\CouldNotRetrievePersonalData;
use EventSourcery\EventSourcery\PersonalData\EncryptedPersonalData;
use EventSourcery\EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalData;
use EventSourcery\EventSourcery\PersonalData\PersonalDataEncryption;
use EventSourcery\EventSourcery\PersonalData\PersonalDataKey;
use EventSourcery\EventSourcery\PersonalData\PersonalDataStore;
use EventSourcery\EventSourcery\PersonalData\PersonalEncryptionKeyStore;
use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use EventSourcery\EventSourcery\PersonalData\ProtectedData;
use EventSourcery\EventSourcery\PersonalData\ProtectedDataKey;
use Illuminate\Database\Query\Builder;

class LaravelPersonalDataStore implements PersonalDataStore {

    /** @var PersonalCryptographyStore */
    private $cryptographyStore;

    /** @var PersonalDataEncryption */
    private $encryption;

    public function __construct(PersonalCryptographyStore $cryptographyStore, PersonalDataEncryption $encryption) {
        $this->cryptographyStore = $cryptographyStore;
        $this->encryption        = $encryption;
    }

    /**
     * retrieve data from the personal data store based on a personal key and data key.
     *
     * @param PersonalKey $personalKey
     * @param PersonalDataKey $dataKey
     * @return PersonalData
     * @throws CouldNotRetrievePersonalData
     */
    public function retrieveData(PersonalKey $personalKey, PersonalDataKey $dataKey): PersonalData {
        $data = $this->table()->where('data_key', '=', $dataKey->serialize())->first();

        if ( ! $data) {
            throw new CouldNotRetrievePersonalData($dataKey->serialize());
        }

        $decrypted = $this->encryption->decrypt(
            $this->cryptographyStore->getCryptographyFor($personalKey),
            EncryptedPersonalData::deserialize($data->encrypted_personal_data)
        )->toString();

        return PersonalData::fromString($decrypted);
    }

    /**
     * store data in the personal data store identified by a personal key and a data key
     *
     * @param PersonalKey $personalKey
     * @param PersonalDataKey $dataKey
     * @param PersonalData $data
     */
    public function storeData(PersonalKey $personalKey, PersonalDataKey $dataKey, PersonalData $data): void {
        $crypto = $this->cryptographyStore->getCryptographyFor($personalKey);

        return $this->table()->insert([
            'personal_key'            => $personalKey->serialize(),
            'data_key'                => $dataKey->serialize(),
            'encrypted_personal_data' => $this->encryption->encrypt($crypto, $data)->serialize(),
        ]);
    }

    /**
     * remove all data for a person from the data store
     *
     * @param PersonalKey $personalKey
     */
    function removeDataFor(PersonalKey $personalKey) {
        $this->table()->where('personal_key', '=', $personalKey->serialize())->delete();
    }

    /**
     * Obtain a reference to the data store table builder object with which to run queries.
     *
     * @return Builder
     */
    private function table(): Builder {
        return DB::table('personal_data_store');
    }
}