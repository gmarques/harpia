<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoCurso;

class IntegracaoCursoRepository extends BaseRepository
{
    public function __construct(IntegracaoCurso $integracaoCurso)
    {
        $this->model = $integracaoCurso;
    }

    public function getCursosGraduacaoTecnologos()
    {
        $result = $this->model->select('itc_id', 'itc_codigo_prog', 'itc_nome_curso_prog', 'crs_id', 'crs_nome')
            ->rightJoin('acd_cursos', function ($join) {
                $join->on('itc_crs_id', '=', 'crs_id');
            })->join('acd_niveis_cursos', function ($join) {
                $join->on('crs_nvc_id', '=', 'nvc_id');
            })->where('crs_nvc_id', '=', 1)->orWhere('crs_nvc_id', 3);

        return $result->get();
    }

    public function getTurmasCursosIntegrados()
    {
        $result = $this->model->select('crs_id', 'crs_nome', 'trm_id', 'trm_nome', 'per_nome')
            ->join('acd_cursos', function ($join) {
                $join->on('itc_crs_id', '=', 'crs_id');
            })->join('acd_niveis_cursos', function ($join) {
                $join->on('crs_nvc_id', '=', 'nvc_id');
            })->join('acd_ofertas_cursos', function ($join) {
                $join->on('ofc_crs_id', '=', 'crs_id');
            })->join('acd_turmas', function ($join) {
                $join->on('trm_ofc_id', '=', 'ofc_id');
            })->join('acd_periodos_letivos', function ($join) {
                $join->on('trm_per_id', '=', 'per_id');
            });

        return $result->get();
    }
}
