<?php namespace EventSourcery\Laravel;

use DB;
use EventSourcery\PersonalData\EncryptedPersonalData;
use EventSourcery\PersonalData\PersonalCryptographyStore;
use EventSourcery\PersonalData\PersonalData;
use EventSourcery\PersonalData\PersonalDataEncryption;
use EventSourcery\PersonalData\PersonalDataKey;
use EventSourcery\PersonalData\PersonalDataStore;
use EventSourcery\PersonalData\PersonalEncryptionKeyStore;
use EventSourcery\PersonalData\PersonalKey;
use EventSourcery\PersonalData\ProtectedData;
use EventSourcery\PersonalData\ProtectedDataKey;
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

    public function storeData(PersonalKey $personalKey, PersonalDataKey $dataKey, PersonalData $data) {
        $crypto = $this->cryptographyStore->getCryptographyFor($personalKey);

        return $this->table()->insert([
            'personal_key'            => $personalKey->serialize(),
            'data_key'                => $dataKey->serialize(),
            'encrypted_personal_data' => $this->encryption->encrypt($crypto, $data)->serialize(),
        ]);
    }

    function removeDataFor(PersonalKey $personalKey) {
        $this->table()->where('personal_key', '=', $personalKey->serialize())->delete();
    }

    private function table(): Builder {
        return DB::table('personal_data_store');
    }
}