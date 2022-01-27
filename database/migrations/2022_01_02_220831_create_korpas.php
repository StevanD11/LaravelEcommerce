<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKorpas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('korpas', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('velicina');
            $table->string('boja');
            $table->integer('kolicina');
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
        Schema::dropIfExists('korpas');
    }
}
