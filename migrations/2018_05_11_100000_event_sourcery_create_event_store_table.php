<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EventSourceryCreateEventStoreTable extends Migration
{
    public function up() {
        Schema::table('event_store', function(Blueprint $t) {
            $t->create();
            $t->increments('id');
            $t->string('stream_id')->index();
            $t->integer('stream_version');
            $t->string('event_name');
            $t->text('event_data');
            $t->text('meta_data');
            $t->timestamp('raised_at');
        });
    }

    public function down() {
        Schema::drop('event_store');
    }
}