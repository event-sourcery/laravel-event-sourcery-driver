# Laravel Driver for the Event Sourcery PHP CQRS / ES Library #

This is the Laravel driver for the [Event Sourcery](https://github.com/event-sourcery/event-sourcery) framework.

**Library is under conceptual development. Do not use.** 

todo

1. documentation
2. *shrug*
3. tooling

This code is extremely proof-of-concepty. Once we work in production with it for a few months we'll have it cleaned up and documented.

# Installation #

`composer require event-sourcery/laravel`

``

Laravel will auto-detect the package and the service provider will be run by default.


# Testing #

A vagrant virtualmachine is provided for testing. 

1. Clone with submodule initialization.
2. Make sure vagrant, virtualbox, and ansible are installed.
3. Run the following:

`vagrant up`

`vagrant ssh`

`bin/phpunit`