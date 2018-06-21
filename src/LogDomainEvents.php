<?php namespace EventSourcery\Laravel;

use EventSourcery\EventSourcery\EventDispatch\Listener;
use EventSourcery\EventSourcery\EventSourcing\DomainEvent;

/**
 * LogDomainEvents is a basic event Listener that will store
 * a string serialized version of each domain event that it
 * observes.
 */
class LogDomainEvents implements Listener {

    public function handle(DomainEvent $event): void {
        $logData = get_class($event) . ': ' . json_encode($event->serialize()) . "\n\n";
        file_put_contents(storage_path('logs/domain-events.log'), $logData, FILE_APPEND);
    }
}