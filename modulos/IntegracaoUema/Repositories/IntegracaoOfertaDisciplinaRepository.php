<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoOfertaDisciplina;
use Modulos\IntegracaoUema\Util\MSSQLConnection;

class IntegracaoOfertaDisciplinaRepository extends BaseRepository
{
    protected $mssqlConnection;

    public function __construct(IntegracaoOfertaDisciplina $integracaoOfertaDisciplina, MSSQLConnection $connection)
    {
        $this->model = $integracaoOfertaDisciplina;
        $this->mssqlConnection = $connection;
    }

    /**
     * Busca todos os matriculados na oferta fazendo left join com a tabela de integracao matriculas
     *
     * @param $ofertaid
     *
     * @return mixed
     */
    public function getMatriculadosByOferta($ofertaid)
    {
        return $this->model->select('alu_id', 'pes_nome', 'acd_matriculas_ofertas_disciplinas.*', 'itm_codigo_prog', 'itm_polo')
            ->join('acd_matriculas_ofertas_disciplinas', 'mof_ofd_id', '=', 'ito_ofd_id')
            ->join('acd_matriculas', 'mof_mat_id', '=', 'mat_id')
            ->join('acd_alunos', 'mat_alu_id', '=', 'alu_id')
            ->join('gra_pessoas', 'pes_id', '=', 'alu_pes_id')
            ->leftJoin('inu_integracoes_matriculas', 'itm_mat_id', '=', 'mat_id')
            ->where('ito_ofd_id', $ofertaid)
            ->orderby('pes_nome')
            ->get()
            ->toArray();
    }

    /**
     * Busca todas as ofertas de disciplinas de uma turma sendo integrada ou nao com a tabela de integracao oferta disciplina
     *
     * @param $turmaid
     *
     * @return mixed
     */
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

    /**
     * Agrupa as disciplina por periodo letivo
     *
     * @param $disciplinas
     *
     * @return array
     */
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

    /**
     * Busca disciplina oferecida fazendo join com a tabela integracao oferta disciplina
     *
     * @param $ofertaid
     *
     * @return mixed
     */
    public function getDisciplinaJoinOferta($ofertaid)
    {
        return $this->model->select('ito_id', 'ito_codigo_prog', 'ito_disciplina_prog', 'ofd_id', 'per_id', 'per_nome')
            ->join('acd_ofertas_disciplinas', 'ito_ofd_id', '=', 'ofd_id')
            ->join('acd_periodos_letivos', 'ofd_per_id', '=', 'per_id')
            ->where('ofd_id', $ofertaid)
            ->first();
    }

    /**
     * Retorna o quantitativo de alunos matriculados na PROG/UEMA na oferta da disciplina
     *
     * @param $codDisciplina
     * @param $semestre
     * @param $ano
     *
     * @return bool|string
     *
     * @throws \Exception
     */
    public function uemaGetCountMatriculadosByOferta($codDisciplina, $semestre, $ano)
    {
        try {
            $sql = "SELECT count(*) as qtd
                    FROM [carlitosan].[m{$semestre}{$ano}] AS m
                    WHERE COD_DISCi = '{$codDisciplina}'
                    GROUP BY COD_DISCi";

            $oferta = $this->mssqlConnection->fetch($sql);

            if (!isset($oferta['qtd'])) {
                return false;
            }

            return iconv('ISO-8859-1', 'UTF-8', $oferta['qtd']);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Busca as informações das disciplina na PROG/UEMA
     *
     * @param $codDisciplina
     * @param $semestre
     * @param $ano
     *
     * @return array|bool
     *
     * @throws \Exception
     */
    public function uemaGetDisciplinaInfo($codDisciplina, $semestre, $ano)
    {
        try {
            $sql = "SELECT mm.COD_DISCi, mm.NOM_DPL, count(*) as qtd
                    FROM(
                        SELECT COD_ALUNO,COD_DISCi, NOM_DPL
                        FROM [carlitosan].[m{$semestre}{$ano}] AS m
                            INNER JOIN [carlitosan].[DISCIPLINA] AS d ON m.COD_DISCi = d.REG_DPL
                        WHERE COD_DISCi = '{$codDisciplina}'
                        GROUP BY COD_ALUNO, COD_DISCi, NOM_DPL
                    ) as mm
                    GROUP BY mm.COD_DISCi, mm.NOM_DPL";

            $disciplina = $this->mssqlConnection->fetch($sql);

            if (!isset($disciplina['NOM_DPL']) || !isset($disciplina['qtd'])) {
                return false;
            }

            $ret = [
                'nome' => iconv('ISO-8859-1', 'UTF-8', $disciplina['NOM_DPL']),
                'qtd' => iconv('ISO-8859-1', 'UTF-8', $disciplina['qtd'])
            ];

            return $ret;
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }
}
