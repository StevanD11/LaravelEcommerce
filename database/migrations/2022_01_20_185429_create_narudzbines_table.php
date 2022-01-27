<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNarudzbinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('narudzbines', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('ime');
            $table->string('prezime');
            $table->string('email');
            $table->string('telefon');
            $table->string('grad');
            $table->string('postanski_broj');
            $table->string('adresa');
            $table->string('placanje');
            $table->double('ukupan_iznos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('narudzbines');
    }
}
