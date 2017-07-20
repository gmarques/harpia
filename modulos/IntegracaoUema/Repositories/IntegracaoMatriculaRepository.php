<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoMatricula;
use Modulos\IntegracaoUema\Util\MSSQLConnection;

class IntegracaoMatriculaRepository extends BaseRepository
{
    protected $mssqlConnection;

    public function __construct(IntegracaoMatricula $integracaoMatricula, MSSQLConnection $connection)
    {
        $this->model = $integracaoMatricula;
        $this->mssqlConnection = $connection;
    }

    /**
     * @param $turmaid
     * @param $poloid
     *
     * Busca todas os alunos matrículados na turma por polo fazendo join com tabela de integração UEMA
     *
     * @return mixed
     */
    public function getMatriculasByTurmaPolo($turmaid, $poloid)
    {
        $matriculas = $this->model->select('itm_id', 'itm_mat_id', 'itm_codigo_prog', 'itm_nome_prog', 'itm_polo', 'mat_id', 'pes_nome')
            ->rightJoin('acd_matriculas', 'itm_mat_id', '=', 'mat_id')
            ->join('acd_alunos', 'mat_alu_id', '=', 'alu_id')
            ->join('gra_pessoas', 'alu_pes_id', '=', 'pes_id')
            ->where('mat_trm_id', $turmaid)
            ->where('mat_pol_id', $poloid)
            ->orderby('pes_nome')
            ->get()->toArray();

        return $matriculas;
    }

    /**
     * Funções que buscam dados nas tabelas da UEMA
     */

    /**
     * @param $codProg
     *
     * Busca as informações do aluno de acordo com a matrícula na prog
     *
     * @return mixed
     *
     * @throws \Exception
     *
     */
    public function uemaGetAlunoByCodigoProg($codProg)
    {
        try {
            $sql = "SELECT COD_ALU, NOM_ALU, polo
                    FROM [carlitosan].[ALUNOs]
                    WHERE COD_ALU = '{$codProg}'";

            $aluno = $this->mssqlConnection->fetch($sql);

            if (!isset($aluno['NOM_ALU']) || !isset($aluno['polo'])) {
                return false;
            }

            $ret = [
                'nome' => iconv('ISO-8859-1', 'UTF-8', $aluno['NOM_ALU']),
                'polo' => mb_strtoupper(trim(iconv('ISO-8859-1', 'UTF-8', $aluno['polo'])))
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
