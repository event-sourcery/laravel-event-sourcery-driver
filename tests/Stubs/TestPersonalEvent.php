<?php namespace Tests\EventSourcery\Laravel\Stubs;

use EventSourcery\EventSourcery\EventSourcing\DomainEvent;

class TestPersonalEvent implements DomainEvent {

    /**
     * @var TestEmail
     */
    public $email;

    public function __construct(TestEmail $email) {
        $this->email = $email;
    }
}
