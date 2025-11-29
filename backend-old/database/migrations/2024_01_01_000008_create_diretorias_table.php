<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiretoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diretorias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('segmento_id');
            $table->string('nome', 255);
            $table->timestamps();
            
            $table->unique(['segmento_id', 'nome'], 'uq_diretoria_segmento_nome');
            $table->foreign('segmento_id', 'fk_diretorias_segmento')
                  ->references('id')
                  ->on('segmentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diretorias');
    }
}

