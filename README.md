# Laravel Driver for the Event Sourcery PHP CQRS / ES Library #

This is the Laravel driver for the [Event Sourcery](https://github.com/event-sourcery/event-sourcery) framework.

**Library is under conceptual development. Do not use.** 

# Table of Contents

  * [Todo List](#todo-list)
  * [Documentation](#documentation)
  * [Installation](#installation)
  * [Testing](#testing)

# Todo List

1. documentation
2. *shrug*
3. tooling

This code is extremely proof-of-concepty. Once we work in production with it for a few months we'll have it cleaned up and documented.

# Documentation

Documentation and more information about Event Sourcery can be found in the core [event-sourcery/event-sourcery](https://github.com/event-sourcery/event-sourcery) repository.

# Installation #

`composer require event-sourcery/laravel`

Laravel will auto-detect the package and the service provider will be installed automatically.

`php artisan migrate`

The migrations will create database tables for the Event Store, Personal Data Store and Personal Cryptography Store.


# Testing #

A vagrant virtualmachine is provided for testing. 

1. Clone with submodule initialization.
2. Make sure vagrant, virtualbox, and ansible are installed.
3. Run the following:

`vagrant up`

`vagrant ssh`

`bin/phpunit`
