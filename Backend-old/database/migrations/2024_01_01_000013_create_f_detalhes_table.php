<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFDetalhesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_detalhes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contrato_id', 80);
            $table->string('registro_id', 60);
            $table->string('funcional', 20);
            $table->integer('id_produto');
            $table->date('dt_cadastro');
            $table->date('competencia');
            $table->decimal('valor_meta', 18, 2)->nullable();
            $table->decimal('valor_realizado', 18, 2)->nullable();
            $table->decimal('quantidade', 18, 4)->nullable();
            $table->decimal('peso', 18, 4)->nullable();
            $table->decimal('pontos', 18, 4)->nullable();
            $table->date('dt_vencimento')->nullable();
            $table->date('dt_cancelamento')->nullable();
            $table->string('motivo_cancelamento', 255)->nullable();
            $table->string('canal_venda', 50)->nullable();
            $table->string('tipo_venda', 50)->nullable();
            $table->string('condicao_pagamento', 50)->nullable();
            $table->string('status_id', 20)->nullable();
            
            $table->unique('contrato_id', 'uq_fd_contrato');
            $table->index('funcional', 'idx_fd_funcional');
            $table->index('registro_id', 'idx_fd_registro');
            $table->index('id_produto', 'idx_fd_produto');
            $table->index('dt_cadastro', 'idx_fd_dt_cadastro');
            $table->index('competencia', 'idx_fd_competencia');
            $table->index('status_id', 'fk_fd_status');
            
            $table->foreign('competencia', 'fk_fd_comp')
                  ->references('data')
                  ->on('d_calendario')
                  ->onUpdate('cascade');
            $table->foreign('dt_cadastro', 'fk_fd_dt_cadastro')
                  ->references('data')
                  ->on('d_calendario')
                  ->onUpdate('cascade');
            $table->foreign('funcional', 'fk_fd_estrutura')
                  ->references('funcional')
                  ->on('d_estrutura')
                  ->onUpdate('cascade');
            $table->foreign('id_produto', 'fk_fd_produto')
                  ->references('id')
                  ->on('d_produtos')
                  ->onUpdate('cascade');
            $table->foreign('status_id', 'fk_fd_status')
                  ->references('id')
                  ->on('d_status_indicadores')
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
        Schema::dropIfExists('f_detalhes');
    }
}

