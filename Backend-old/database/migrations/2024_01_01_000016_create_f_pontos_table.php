<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFPontosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_pontos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('funcional', 20);
            $table->integer('produto_id');
            $table->decimal('meta', 18, 2)->default(0.00);
            $table->decimal('realizado', 18, 2)->default(0.00);
            $table->date('data_realizado')->nullable();
            $table->dateTime('dt_atualizacao')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->index('funcional', 'idx_fp_funcional');
            $table->index('produto_id', 'idx_fp_produto');
            $table->index('data_realizado', 'idx_fp_data_realizado');
            
            $table->foreign('data_realizado', 'fk_fpontos_calendario')
                  ->references('data')
                  ->on('d_calendario')
                  ->onUpdate('cascade');
            $table->foreign('funcional', 'fk_fpontos_estrutura')
                  ->references('funcional')
                  ->on('d_estrutura')
                  ->onUpdate('cascade');
            $table->foreign('produto_id', 'fk_fpontos_produto')
                  ->references('id')
                  ->on('d_produtos')
                  ->onUpdate('cascade');
        });
        
        // Adicionar ON UPDATE CURRENT_TIMESTAMP para dt_atualizacao
        DB::statement('ALTER TABLE f_pontos MODIFY dt_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('f_pontos');
    }
}

