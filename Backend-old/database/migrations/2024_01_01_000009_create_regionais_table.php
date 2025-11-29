<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regionais', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('diretoria_id');
            $table->string('nome', 255);
            $table->timestamps();
            
            $table->unique(['diretoria_id', 'nome'], 'uq_regional_diretoria_nome');
            $table->foreign('diretoria_id', 'fk_regionais_diretoria')
                  ->references('id')
                  ->on('diretorias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regionais');
    }
}

