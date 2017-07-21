<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInuIntegracoesCursosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inu_integracoes_cursos', function (Blueprint $table) {
            $table->increments('itc_id');
            $table->integer('itc_crs_id')->unsigned();
            $table->string('itc_codigo_prog', 45);
            $table->string('itc_nome_curso_prog', 150);

            $table->timestamps();

            $table->foreign('itc_crs_id')->references('crs_id')->on('acd_cursos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('inu_integracoes_cursos');
    }
}
