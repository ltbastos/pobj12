<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFVariavelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_variavel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('funcional', 20);
            $table->decimal('meta', 18, 2)->default(0.00);
            $table->decimal('variavel', 18, 2)->default(0.00);
            $table->date('dt_atualizacao');
            
            $table->index('funcional', 'idx_fv_funcional');
            $table->index('dt_atualizacao', 'idx_fv_dt');
            
            $table->foreign('dt_atualizacao', 'fk_f_variavel_calendario')
                  ->references('data')
                  ->on('d_calendario')
                  ->onUpdate('cascade');
            $table->foreign('funcional', 'fk_f_variavel_estrutura')
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
        Schema::dropIfExists('f_variavel');
    }
}

