<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agencias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('regional_id');
            $table->string('nome', 255);
            $table->string('porte', 30);
            $table->timestamps();
            
            $table->unique(['regional_id', 'nome'], 'uq_agencia_regional_nome');
            $table->foreign('regional_id', 'fk_agencias_regional')
                  ->references('id')
                  ->on('regionais');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agencias');
    }
}

