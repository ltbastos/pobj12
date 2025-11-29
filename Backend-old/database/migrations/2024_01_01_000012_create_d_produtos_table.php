<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_produtos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('familia_id');
            $table->integer('indicador_id');
            $table->integer('subindicador_id')->nullable();
            $table->decimal('peso', 10, 2)->default(0.00);
            $table->string('metrica', 30)->default('valor');
            
            $table->unique(['indicador_id', 'subindicador_id'], 'uq_indicador_sub');
            $table->index('familia_id', 'idx_familia');
            $table->index('indicador_id', 'idx_indicador');
            $table->index('subindicador_id', 'idx_subindicador');
            
            $table->foreign('familia_id', 'd_produtos_ibfk_1')
                  ->references('id')
                  ->on('familia');
            $table->foreign('indicador_id', 'd_produtos_ibfk_2')
                  ->references('id')
                  ->on('indicador');
            $table->foreign('subindicador_id', 'd_produtos_ibfk_3')
                  ->references('id')
                  ->on('subindicador');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('d_produtos');
    }
}

