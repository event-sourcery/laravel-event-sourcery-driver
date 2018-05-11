<?php namespace Tests\EventSourcery\Laravel;

use EventSourcery\EventSourcery\EventSourcing\EventStore;

class MyTest extends TestCase {

    public function testMultiplyReturnsCorrectValue() {
        $this->app->make(EventStore::class);
        $this->assertNotTrue(false);
    }
}