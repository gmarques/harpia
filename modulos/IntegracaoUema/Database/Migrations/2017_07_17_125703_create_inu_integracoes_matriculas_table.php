<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInuIntegracoesMatriculasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inu_integracoes_matriculas', function (Blueprint $table) {
            $table->increments('itm_id');
            $table->integer('itm_mat_id')->unsigned();
            $table->string('itm_codigo_prog', 45);
            $table->string('itm_polo', 45);

            $table->timestamps();

            $table->foreign('itm_mat_id')->references('mat_id')->on('acd_matriculas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('inu_integracoes_matriculas');
    }
}
