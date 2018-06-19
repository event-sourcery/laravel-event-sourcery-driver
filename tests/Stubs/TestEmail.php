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

    public function serialize(): string {
        return json_encode([
            'personalKey' => $this->personalKey->toString(),
            'email' => $this->email,
        ]);
    }

    public static function deserialize(string $string) {
        $values = json_decode($string);
        return new static(PersonalKey::fromString($values->personalKey), $values->email);
    }
}
