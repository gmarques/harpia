<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoOfertaDisciplina;

class IntegracaoOfertaDisciplinaRepository extends BaseRepository
{
    public function __construct(IntegracaoOfertaDisciplina $integracaoOfertaDisciplina)
    {
        $this->model = $integracaoOfertaDisciplina;
    }

    public function getMatriculadosByOferta($ofertaid)
    {
        return $this->model->select('alu_id', 'pes_nome', 'acd_matriculas_ofertas_disciplinas.*', 'itm_codigo_prog', 'itm_polo')
            ->join('acd_matriculas_ofertas_disciplinas', 'mof_ofd_id', '=', 'ito_ofd_id')
            ->join('acd_matriculas', 'mof_mat_id', '=', 'mat_id')
            ->join('acd_alunos', 'mat_alu_id', '=', 'alu_id')
            ->join('gra_pessoas', 'pes_id', '=', 'alu_pes_id')
            ->leftJoin('inu_integracoes_matriculas', 'itm_mat_id', '=', 'mat_id')
            ->where('ito_ofd_id', $ofertaid)
            ->get();
    }

    public function getOfertasByTurma($turmaid)
    {
        return $this->model->select('ito_id', 'ito_codigo_prog', 'ito_disciplina_prog', 'ofd_id', 'per_id', 'per_nome', 'dis_nome')
        ->rightJoin('acd_ofertas_disciplinas', 'ito_ofd_id', '=', 'ofd_id')
        ->join('acd_periodos_letivos', 'ofd_per_id', '=', 'per_id')
        ->join('acd_modulos_disciplinas', 'ofd_mdc_id', '=', 'mdc_id')
        ->join('acd_disciplinas', 'mdc_dis_id', '=', 'dis_id')
        ->where('ofd_trm_id', $turmaid)
        ->get();
    }

    public function groupDisciplinasByPeriodosLetivos($disciplinas)
    {
        $periodos = [];
        foreach ($disciplinas as $disciplina) {
            if (!isset($periodos[$disciplina->per_id])) {
                $periodos[$disciplina->per_id] = [
                    'per_id' => $disciplina->per_id,
                    'per_nome' => $disciplina->per_nome
                ];
            }

            $periodos[$disciplina->per_id]['disciplinas'][] = [
                'ito_codigo_prog' => $disciplina->ito_codigo_prog,
                'ito_disciplina_prog' => $disciplina->ito_disciplina_prog,
                'ito_id' => $disciplina->ito_id,
                'ofd_id' => $disciplina->ofd_id,
                'dis_nome' => $disciplina->dis_nome
            ];
        }

        return $periodos;
    }
}
