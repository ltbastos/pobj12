<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFHistoricoRankingPobjTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_historico_ranking_pobj', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('data');
            $table->string('funcional', 20);
            $table->integer('grupo')->nullable();
            $table->integer('ranking')->nullable();
            $table->decimal('realizado', 18, 2)->nullable();
            
            $table->index('data', 'idx_hist_data');
            $table->index('funcional', 'idx_hist_funcional');
            $table->index('ranking', 'idx_hist_ranking');
            
            $table->foreign('data', 'fk_hist_pobj_calendario')
                  ->references('data')
                  ->on('d_calendario')
                  ->onUpdate('cascade');
            $table->foreign('funcional', 'fk_hist_pobj_estrutura')
                  ->references('funcional')
                  ->on('d_estrutura')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('f_historico_ranking_pobj');
    }
}

