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
}
