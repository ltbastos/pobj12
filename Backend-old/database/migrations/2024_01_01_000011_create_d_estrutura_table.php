<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDEstruturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_estrutura', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('funcional', 20);
            $table->string('nome', 255);
            $table->unsignedInteger('cargo_id');
            $table->unsignedInteger('segmento_id')->nullable();
            $table->unsignedInteger('diretoria_id')->nullable();
            $table->unsignedInteger('regional_id')->nullable();
            $table->unsignedInteger('agencia_id')->nullable();
            $table->timestamps();
            
            $table->unique('funcional');
            $table->index('cargo_id', 'fk_estrutura_cargo');
            $table->index('segmento_id', 'fk_estrutura_segmento');
            $table->index('diretoria_id', 'fk_estrutura_diretoria');
            $table->index('regional_id', 'fk_estrutura_regional');
            $table->index('agencia_id', 'fk_estrutura_agencia');
            
            $table->foreign('agencia_id', 'fk_estrutura_agencia')
                  ->references('id')
                  ->on('agencias');
            $table->foreign('cargo_id', 'fk_estrutura_cargo')
                  ->references('id')
                  ->on('cargos');
            $table->foreign('diretoria_id', 'fk_estrutura_diretoria')
                  ->references('id')
                  ->on('diretorias');
            $table->foreign('regional_id', 'fk_estrutura_regional')
                  ->references('id')
                  ->on('regionais');
            $table->foreign('segmento_id', 'fk_estrutura_segmento')
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
        Schema::dropIfExists('d_estrutura');
    }
}

