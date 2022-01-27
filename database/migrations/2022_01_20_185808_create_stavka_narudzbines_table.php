<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStavkaNarudzbinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stavka_narudzbines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('narudzbina_id')->constrained('narudzbines');
            $table->integer('product_id');
            $table->string('boja');
            $table->integer('velicina');
            $table->integer('kolicina');
            $table->double('cena');
            $table->double('iznos');
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
        Schema::dropIfExists('stavka_narudzbines');
    }
}
