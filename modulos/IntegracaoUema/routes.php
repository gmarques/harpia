<?php

Route::group(['prefix' => 'integracaouema', 'middleware' => ['auth']], function () {
    Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IndexController@getIndex')->name('integracaouema.index.index');

    Route::group(['prefix' => 'cursos'], function () {
        Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesCursosController@getIndex')->name('integracaouema.cursos.index');
    });

    Route::group(['prefix' => 'turmas'], function () {
        Route::get('/', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesTurmasController@getIndex')->name('integracaouema.turmas.index');
        Route::get('/alunos/{id}', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesTurmasController@getAlunos')->name('integracaouema.turmas.alunos');
    });

    Route::group(['prefix' => 'ofertas'], function () {
        Route::get('/{id}', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesOfertasDisciplinas@getIndex')->name('integracaouema.ofertas.index');
        Route::get('/migrar/{id}', '\Modulos\IntegracaoUema\Http\Controllers\IntegracoesOfertasDisciplinas@getMigrar')->name('integracaouema.ofertas.migrar');
    });

    // Rotas das funções assíncronas.
    Route::group(['prefix' => 'async'], function () {
        Route::group(['prefix' => 'cursos'], function () {
            Route::get('/{nomecurso}', '\Modulos\IntegracaoUema\Http\Controllers\Async\IntegracoesCursos@getNomeCurso')->name('integracaouema.async.cursos.getnomecurso');
            Route::post('/integrar', '\Modulos\IntegracaoUema\Http\Controllers\Async\IntegracoesCursos@postIntegrar')->name('integracaouema.async.cursos.integrar');
        });

        Route::group(['prefix' => 'ofertas'], function () {
            Route::get('/{coddisciplina}/{semestre}/{ano}', '\Modulos\IntegracaoUema\Http\Controllers\Async\IntegracoesOfertas@getDisciplinaInfo')->name('integracaouema.async.ofertas.getdisciplinainfo');
            Route::post('/integrar', '\Modulos\IntegracaoUema\Http\Controllers\Async\IntegracoesOfertas@postIntegrar')->name('integracaouema.async.ofertas.integrar');
        });

        Route::group(['prefix' => 'matriculas'], function () {
            Route::get('/{codprog}', '\Modulos\IntegracaoUema\Http\Controllers\Async\IntegracoesMatriculas@getMatriculaInfo')->name('integracaouema.async.matriculas.getmatriculainfo');
        });
    });
});
