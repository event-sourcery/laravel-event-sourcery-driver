<?php namespace Tests\EventSourcery\Laravel\Stubs;

use EventSourcery\EventSourcery\PersonalData\PersonalKey;
use EventSourcery\EventSourcery\PersonalData\SerializablePersonalDataValue;

class TestEmail implements SerializablePersonalDataValue {
    /**
     * @var PersonalKey
     */
    private $personalKey;
    /**
     * @var string
     */
    private $email;

    public function __construct(PersonalKey $personalKey, string $email) {
        $this->personalKey = $personalKey;
        $this->email = $email;
    }

    public function personalKey(): PersonalKey {
        return $this->personalKey;
    }

    public function serialize(): array {
        return [
            'personalKey' => $this->personalKey->toString(),
            'email' => $this->email,
        ];
    }

    public static function deserialize(array $data) {
        return new static(PersonalKey::fromString($data['personalKey']), $data['email']);
    }

    /**
     * the factory method to build this data from erased state
     *
     * @param PersonalKey $personalKey
     * @return mixed
     */
    public static function fromErasedState(PersonalKey $personalKey) {
        return new static($personalKey, 'email@testemail.com');
    }

    /**
     * the wasErased method returns true if built fromErasedState.
     * due to the requirements for individual value objects, this must
     * be implemented manually
     *
     * @return bool
     */
    public function wasErased(): bool {

    }
}
