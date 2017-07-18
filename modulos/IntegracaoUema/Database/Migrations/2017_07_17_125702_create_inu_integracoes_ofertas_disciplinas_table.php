<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInuIntegracoesOfertasDisciplinasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inu_integracoes_ofertas_disciplinas', function (Blueprint $table) {
            $table->increments('ito_id');
            $table->integer('ito_ofd_id')->unsigned();
            $table->string('ito_codigo_prog', 45);
            $table->string('ito_disciplina_prog', 255);

            $table->timestamps();

            $table->foreign('ito_ofd_id')->references('ofd_id')->on('acd_ofertas_disciplinas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('inu_integracoes_ofertas_disciplinas');
    }
}
