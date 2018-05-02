<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalCryptographyStoreTable extends Migration {

    public function up() {
        Schema::create('personal_cryptography_store', function (Blueprint $t) {
            $t->increments('id');
            $t->string('personal_key');
            $t->text('cryptographic_details');
            $t->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('personal_cryptography_store');
    }
}
