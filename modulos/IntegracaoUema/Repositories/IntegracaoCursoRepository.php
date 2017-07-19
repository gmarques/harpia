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
        return $this->model->select('itc_id', 'itc_codigo_prog', 'itc_nome_curso_prog', 'crs_id', 'crs_nome')
            ->rightJoin('acd_cursos', 'itc_crs_id', '=', 'crs_id')
            ->join('acd_niveis_cursos', 'crs_nvc_id', '=', 'nvc_id')
            ->where('crs_nvc_id', '=', 1)->orWhere('crs_nvc_id', 3)
            ->get();
    }

    public function getTurmasCursosIntegrados()
    {
        return $this->model->select('crs_id', 'crs_nome', 'trm_id', 'trm_nome', 'per_nome')
            ->join('acd_cursos', 'itc_crs_id', '=', 'crs_id')
            ->join('acd_niveis_cursos', 'crs_nvc_id', '=', 'nvc_id')
            ->join('acd_ofertas_cursos', 'ofc_crs_id', '=', 'crs_id')
            ->join('acd_turmas', 'trm_ofc_id', '=', 'ofc_id')
            ->join('acd_periodos_letivos', 'trm_per_id', '=', 'per_id')
            ->get();
    }
}
