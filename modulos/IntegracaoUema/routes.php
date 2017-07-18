<?php

Route::group(['prefix' => 'integracaouema', 'middleware' => ['auth']], function () {
    Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IndexController@getIndex')->name('integracaouema.index.index');

    Route::group(['prefix' => 'turmas'], function () {
        Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesCursosController@getIndex')->name('integracaouema.cursos.index');
    });
});
