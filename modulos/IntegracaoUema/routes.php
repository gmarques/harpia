<?php

Route::group(['prefix' => 'integracaouema', 'middleware' => ['auth']], function () {
    Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IndexController@getIndex')->name('integracaouema.index.index');

    Route::group(['prefix' => 'cursos'], function () {
        Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesCursosController@getIndex')->name('integracaouema.cursos.index');
    });

    Route::group(['prefix' => 'turmas'], function () {
        Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesTurmasController@getIndex')->name('integracaouema.turmas.index');
        Route::get('/ofertasdisciplinas/{id}', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesTurmasController@getOfertasDisciplinas')->name('integracaouema.turmas.ofertasdisciplinas');
        Route::get('/alunos/{id}', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesTurmasController@getAlunos')->name('integracaouema.turmas.alunos');
    });
});
