<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFRealizadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_realizados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('id_contrato', 10);
            $table->string('funcional', 16);
            $table->date('data_realizado');
            $table->decimal('realizado', 18, 2)->default(0.00);
            $table->integer('produto_id')->nullable();
            
            $table->index('data_realizado', 'idx_fr_data');
            $table->index(['funcional', 'data_realizado'], 'idx_fr_func_data');
            $table->index('id_contrato', 'idx_fr_contrato');
            $table->index('produto_id', 'idx_fr_produto');
            
            $table->foreign('data_realizado', 'fk_fr_calendario')
                  ->references('data')
                  ->on('d_calendario')
                  ->onUpdate('cascade');
            $table->foreign('funcional', 'fk_fr_estrutura')
                  ->references('funcional')
                  ->on('d_estrutura')
                  ->onUpdate('cascade');
            $table->foreign('produto_id', 'fk_fr_produto')
                  ->references('id')
                  ->on('d_produtos')
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
        Schema::dropIfExists('f_realizados');
    }
}

