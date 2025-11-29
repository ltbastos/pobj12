<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDCalendarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_calendario', function (Blueprint $table) {
            $table->date('data')->primary();
            $table->integer('ano');
            $table->tinyInteger('mes');
            $table->string('mes_nome', 20);
            $table->tinyInteger('dia');
            $table->string('dia_da_semana', 20);
            $table->tinyInteger('semana');
            $table->tinyInteger('trimestre');
            $table->tinyInteger('semestre');
            $table->boolean('eh_dia_util');
            
            $table->index(['ano', 'mes'], 'idx_d_calendario_mes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('d_calendario');
    }
}

