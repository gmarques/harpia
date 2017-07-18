<?php

namespace Modulos\IntegracaoUema\Http\Controllers;

use Modulos\Core\Http\Controller\BaseController;
use Modulos\IntegracaoUema\Repositories\IntegracaoCursoRepository;

class IntegracoesCursosController extends BaseController
{
    protected $integracaoCursoRepository;

    public function __construct(IntegracaoCursoRepository $integracaoCursoRepository)
    {
        $this->integracaoCursoRepository = $integracaoCursoRepository;
    }

    public function getIndex()
    {
        $tableData = $this->integracaoCursoRepository->getCursosGraduacaoTecnologos();

        if ($tableData->count()) {
            $tabela = $tableData->columns(array(
                'crs_id' => '#',
                'crs_nome' => 'Curso',
                'itc_codigo_prog' => 'COD. PROG',
                'itc_nome_curso_prog' => 'CURSO PROG',
                'itc_action' => 'Ações'
            ))
            ->modifyCell('itc_nome_curso_prog', function () {
                return array('style' => 'width: 400px;');
            })
            ->modifyCell('alu_action', function () {
                return array('style' => 'width: 140px;');
            })
            ->modify('itc_codigo_prog', function ($data) {
                return "<input type='text' class='form-control fc-cod-prog' value='{$data->itc_codigo_prog}'>";
            })
            ->modify('itc_nome_curso_prog', function ($data) {
                return "<input type='text' disabled='disabled' class='disabled form-control fc-nome-curso-prog' value='{$data->itc_nome_curso_prog}'>";
            })
            ->means('itc_action', 'trm_id')
            ->modify('itc_action', function ($id) {
                return "<a href='#' class='btn btn-primary disabled btn-integrar' disabled='disabled' data-trm_id='{$id}'><i class='fa fa-floppy-o'></i> Mapear curso</a>";
            })
            ->sortable(array('trm_id', 'crs_nome'));
        }

        return view('IntegracaoUema::cursos.index', ['tabela' => $tabela]);
    }
}
