<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EventSourceryCreatePersonalCryptographyStoreTable extends Migration {

    public function up() {
        Schema::create('personal_cryptography_store', function (Blueprint $t) {
            $t->increments('id');
            $t->string('personal_key');
            $t->text('cryptographic_details');
            $t->string('type');
            $t->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('personal_cryptography_store');
    }
}
