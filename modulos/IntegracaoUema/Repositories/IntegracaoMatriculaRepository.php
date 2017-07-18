<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoMatricula;

class IntegracaoMatriculaRepository extends BaseRepository
{
    public function __construct(IntegracaoMatricula $integracaoMatricula)
    {
        $this->model = $integracaoMatricula;
    }

    public function getMatriculasByTurmaPolo($turmaid, $poloid)
    {
        $matriculas = $this->model->select('itm_id', 'itm_mat_id', 'itm_codigo_prog', 'itm_polo', 'mat_id', 'pes_nome')
            ->rightJoin('acd_matriculas', 'itm_mat_id', '=', 'mat_id')
            ->join('acd_alunos', 'mat_alu_id', '=', 'alu_id')
            ->join('gra_pessoas', 'alu_pes_id', '=', 'pes_id')
            ->where('mat_trm_id', $turmaid)
            ->where('mat_pol_id', $poloid)
            ->get()->toArray();

        return $matriculas;
    }
}
