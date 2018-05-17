<?php namespace Tests\EventSourcery\Laravel\Stubs;

use EventSourcery\EventSourcery\EventSourcing\DomainEvent;

class TestEvent implements DomainEvent {

    /** @var int $number */
    public $number;

    public function __construct(int $number = 0) {
        $this->number = $number;
    }
}
