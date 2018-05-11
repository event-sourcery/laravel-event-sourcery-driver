<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EventSourceryCreatePersonalDataStoreTable extends Migration {

    public function up() {
        Schema::create('personal_data_store', function (Blueprint $t) {
            $t->increments('id');
            $t->string('personal_key');
            $t->string('data_key');
            $t->text('encrypted_personal_data');
            $t->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('personal_data_store');
    }
}
