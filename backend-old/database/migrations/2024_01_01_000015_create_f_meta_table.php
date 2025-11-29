<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('data_meta');
            $table->string('funcional', 16);
            $table->integer('produto_id');
            $table->decimal('meta_mensal', 18, 2)->default(0.00);
            $table->timestamps();
            
            $table->index('data_meta', 'ix_meta_data');
            $table->index(['funcional', 'data_meta'], 'ix_meta_func_data');
            $table->index('produto_id', 'fk_f_meta__produto');
            
            $table->foreign('funcional', 'fk_f_meta__d_estrutura')
                  ->references('funcional')
                  ->on('d_estrutura');
            $table->foreign('produto_id', 'fk_f_meta__produto')
                  ->references('id')
                  ->on('d_produtos');
            $table->foreign('data_meta', 'fk_fm_cal')
                  ->references('data')
                  ->on('d_calendario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('f_meta');
    }
}

