<?php

//use Illuminate\Config\Repository;
//use Illuminate\Database\Capsule\Manager as Capsule;
//use Illuminate\Events\Dispatcher;
//use Illuminate\Container\Container;
//
//if ( ! ini_get('date.timezone')) {
//    date_default_timezone_set('Europe/Amsterdam');
//}
//
//require __DIR__ . '/../vendor/autoload.php';
//
//$app = new Container();
//$app->bind('config', new Repository());
//
//$capsule = new Capsule;
//$capsule->setContainer($app);
//$capsule->addConnection([
//    'driver'    => 'mysql',
//    'host'      => 'localhost',
//    'database'  => 'development',
//    'username'  => 'root',
//    'password'  => 'password',
//    'charset'   => 'utf8',
//    'collation' => 'utf8_unicode_ci',
//    'prefix'    => '',
//]);
//
//// Set the event dispatcher used by Eloquent models... (optional)
//$capsule->setEventDispatcher(new Dispatcher(new Container));
//
//// Set the cache manager instance used by connections... (optional)
//// $capsule->setCacheManager(...);
//
//// Make this Capsule instance available globally via static methods... (optional)
//$capsule->setAsGlobal();
//
//// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
// $capsule->bootEloquent();
//
//
//
//
// createApplication()[\Illuminate\Contracts\Console\Kernel]->call('migrate:fresh');
//
//$migrationFiles = glob("migrations/*.php");
//
//foreach ($migrationFiles as $migrationFile) {
//    include($migrationFile);
//}
//
//$migrations = [
//    CreatePersonalDataStoreTable::class,
//    CreatePersonalCryptographyStoreTable::class
//];
//
//foreach ($migrations as $migration) {
//    $migrate = new $migration;
//    $migrate->up();
//}
//
//
//abstract class CapsuleExtension {
//    public static function __callStatic($name, $arguments) {
//        Capsule::$name($arguments);
//    }
//}
//
//class Schema extends CapsuleExtension {}
//class DB extends CapsuleExtension {}
