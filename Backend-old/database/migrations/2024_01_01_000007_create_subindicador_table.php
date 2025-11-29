<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubindicadorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subindicador', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('indicador_id');
            $table->string('nm_subindicador', 120);
            
            $table->unique(['indicador_id', 'nm_subindicador'], 'uq_indicador_sub');
            $table->foreign('indicador_id', 'subindicador_ibfk_1')
                  ->references('id')
                  ->on('indicador');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subindicador');
    }
}

