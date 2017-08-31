<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Academico\Models\Turma;
use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoMatricula;
use Modulos\IntegracaoUema\Util\MSSQLConnection;
use Illuminate\Support\Facades\DB;

class IntegracaoMatriculaRepository extends BaseRepository
{
    protected $mssqlConnection;
    protected $integracaoCursoRepository;
    protected $integracaoOfertaDisciplinaRepository;

    public function __construct(
        IntegracaoMatricula $integracaoMatricula,
        MSSQLConnection $connection,
        IntegracaoCursoRepository $integracaoCursoRepository,
        IntegracaoOfertaDisciplinaRepository $integracaoOfertaDisciplinaRepository
    ) {
        $this->model = $integracaoMatricula;
        $this->mssqlConnection = $connection;
        $this->integracaoCursoRepository = $integracaoCursoRepository;
        $this->integracaoOfertaDisciplinaRepository = $integracaoOfertaDisciplinaRepository;
    }

    /**
     * Busca todas os alunos matriculados na turma e polo fazendo right join com tabela de integração UEMA
     *
     * @param $turmaid
     * @param $poloid
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
            ->where('mat_situacao', '<>', 'desistente')
            ->orderby('pes_nome')
            ->get()->toArray();

        return $matriculas;
    }

    /**
     * Busca todas os alunos matriculados na turma fazendo right join com tabela de integração UEMA
     *
     * @param $turmaid
     *
     * @return mixed
     */
    public function getMatriculasByTurma($turmaid)
    {
        $matriculas = $this->model->select('itm_id', 'itm_mat_id', 'itm_codigo_prog', 'itm_nome_prog', 'itm_polo', 'mat_id', 'pes_nome', 'doc_conteudo as cpf')
            ->rightJoin('acd_matriculas', 'itm_mat_id', '=', 'mat_id')
            ->join('acd_alunos', 'mat_alu_id', '=', 'alu_id')
            ->join('gra_pessoas', 'alu_pes_id', '=', 'pes_id')
            ->leftJoin('gra_documentos', function ($join) {
                $join->on('pes_id', '=', 'doc_pes_id')->where('doc_tpd_id', '=', 2, 'and', true);
            })
            ->where('mat_trm_id', $turmaid)
            ->where('mat_situacao', '<>', 'desistente')
            ->get()->toArray();

        return $matriculas;
    }

    /**
     * Faz a integração/sincronização de todas os matriculados no curso com suas matriculas na PROG/UEMA
     * A busca do aluno na PROG é feita através do CPF do aluno
     *
     * @param Turma $turma
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sincronizarMatriculasTurmaUema(Turma $turma)
    {
        $cursointegrado = $this->integracaoCursoRepository->search([['itc_crs_id', '=', $turma->ofertacurso->curso->crs_id]])->first();

        if (!$cursointegrado) {
            return false;
        }

        $matriculas = $this->getMatriculasByTurma($turma->trm_id);

        try {
            DB::beginTransaction();

            foreach ($matriculas as $matricula) {
                // Atualiza a matricula buscando novamente o nome e polo do aluno na PROG.
                if (isset($matricula['itm_codigo_prog'])) {
                    $matprog = $this->uemaGetAlunoByCodigoProg($matricula['itm_codigo_prog']);

                    $matintegrada = $this->search([['itm_mat_id', '=', $matricula['itm_mat_id']]])->first();
                    $matintegrada->itm_nome_prog = $matprog['nome'];
                    $matintegrada->itm_polo = $matprog['polo'];

                    $matintegrada->save();
                }

                // Tenta localizar o aluno na PROG para então integrar os dados.
                if (!isset($matricula['itm_codigo_prog'])) {
                    $codcursoprog = $cursointegrado->itc_codigo_prog;
                    $periodo = substr($turma->periodo->per_nome, -1);
                    $ano = substr($turma->periodo->per_nome, 0, -2);

                    $matprog = $this->uemaGetAlunoByCpfCursoProg($matricula['cpf'], $codcursoprog, $periodo, $ano);

                    if ($matprog) {
                        $matData = [
                            "itm_mat_id" => $matricula['mat_id'],
                            "itm_codigo_prog" => $matprog['cod_prog'],
                            "itm_nome_prog" => $matprog['nome_prog'],
                            "itm_polo" => $matprog['polo']
                        ];

                        $this->create($matData);
                    }
                }
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Busca a matricula do aluno na oferta de disciplina junto com suas notas fazendo right join com tabela de integracao matricula
     *
     * @param $ofertaid
     * @param $matriculaid
     *
     * @return mixed
     */
    public function getMatriculaJoinNotasByOferta($ofertaid, $matriculaid)
    {
        $matriculas = $this->model->select('itm_mat_id', 'itm_codigo_prog', 'itm_polo', 'mof_nota1', 'mof_nota2', 'mof_nota3', 'mof_conceito', 'mof_recuperacao', 'mof_final', 'mof_mediafinal')
            ->join('acd_matriculas_ofertas_disciplinas', 'mof_mat_id', '=', 'itm_mat_id')
            ->where('mof_ofd_id', $ofertaid)
            ->where('itm_mat_id', $matriculaid)
            ->first();

        return $matriculas;
    }

    /**
     * Busca as informações do aluno de acordo com a matrícula na PROG/UEMA
     *
     * @param $codProg
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function uemaGetAlunoByCodigoProg($codProg)
    {
        try {
            $codProg = iconv('UTF-8', 'ISO-8859-1', $codProg);

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

    /**
     * Busca o aluno na PROG através do CPF, curso, ano e semestre de ingresso
     *
     * @param $cpf
     * @param $codcurso
     * @param $semestre
     * @param $ano
     *
     * @return array|bool
     *
     * @throws \Exception
     */
    public function uemaGetAlunoByCpfCursoProg($cpf, $codcurso, $semestre, $ano)
    {
        try {
            $sql = "SELECT
                      a.COD_ALU, a.NOM_ALU, a.POLO
                    FROM [carlitosan].[ALUNOs] AS a
                    LEFT JOIN [carlitosan].[documentos] AS d ON a.COD_ALU = d.COD_ALU
                    WHERE
                      d.CPF = '{$cpf}'
                      AND curso = '{$codcurso}'
                      AND SEM_ANO = '{$semestre}'
                      AND ANO_INGRESSO = '{$ano}'";

            $aluno = $this->mssqlConnection->fetch($sql);

            if (!isset($aluno['COD_ALU']) || !isset($aluno['NOM_ALU']) || !isset($aluno['POLO'])) {
                return false;
            }

            $ret = [
                'cod_prog' => iconv('ISO-8859-1', 'UTF-8', $aluno['COD_ALU']),
                'nome_prog' => iconv('ISO-8859-1', 'UTF-8', $aluno['NOM_ALU']),
                'polo' => mb_strtoupper(trim(iconv('ISO-8859-1', 'UTF-8', $aluno['POLO'])))
            ];

            return $ret;
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Busca as notas do aluno na disciplina nas tabelas da PROG/UEMA
     *
     * @param $codAluno
     * @param $codDisciplina
     * @param $semestre
     * @param $ano
     *
     * @return array|bool
     *
     * @throws \Exception
     */
    public function uemaGetAlunoNotasByDisciplinaProg($codAluno, $codDisciplina, $semestre, $ano)
    {
        try {
            $codAluno = iconv('UTF-8', 'ISO-8859-1', $codAluno);

            $sql = "SELECT COD_ALUNO, polo, COD_DISCi, NOTA01, NOTA02, NOTA03, NOTA04, NOTA05, MEDIA
                    FROM [carlitosan].[m{$semestre}{$ano}]
                    WHERE
                      COD_DISCi = '{$codDisciplina}'
                      AND COD_ALUNO = '{$codAluno}'";

            $aluno = $this->mssqlConnection->fetch($sql);

            if (!isset($aluno['COD_ALUNO']) || !isset($aluno['COD_DISCi'])) {
                return false;
            }

            $ret = [
                'cod_aluno' => iconv('ISO-8859-1', 'UTF-8', $aluno['COD_ALUNO']),
                'polo' => iconv('ISO-8859-1', 'UTF-8', $aluno['polo']),
                'cod_disc' => iconv('ISO-8859-1', 'UTF-8', $aluno['COD_DISCi']),
                'nota1' => iconv('ISO-8859-1', 'UTF-8', $aluno['NOTA01']),
                'nota2' => iconv('ISO-8859-1', 'UTF-8', $aluno['NOTA02']),
                'nota3' => iconv('ISO-8859-1', 'UTF-8', $aluno['NOTA03']),
                'nota4' => iconv('ISO-8859-1', 'UTF-8', $aluno['NOTA04']),
                'nota5' => iconv('ISO-8859-1', 'UTF-8', $aluno['NOTA05']),
                'media' => iconv('ISO-8859-1', 'UTF-8', $aluno['MEDIA'])
            ];

            return $ret;
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }


    /**
     * Atualiza as notas do aluno na PROG/UEMA
     *
     * @param $ofertaid
     * @param $matriculaid
     *
     * @return bool|\PDOStatement|string
     *
     * @throws \Exception
     */
    public function uemaSetmigrarNotasOfertaAluno($ofertaid, $matriculaid)
    {
        $oferta = $this->integracaoOfertaDisciplinaRepository->getDisciplinaJoinOferta($ofertaid);
        $matricula = $this->getMatriculaJoinNotasByOferta($ofertaid, $matriculaid);

        if (!$oferta || !$matricula) {
            return false;
        }

        $codDisc = $oferta->ito_codigo_prog;
        $semestre = substr($oferta->per_nome, -1);
        $ano = substr($oferta->per_nome, -4, 2);
        $codProg = $matricula->itm_codigo_prog;
        $codProg = iconv('UTF-8', 'ISO-8859-1', $codProg);

        try {
            $sql = "UPDATE [carlitosan].[m{$semestre}{$ano}] SET ";

            if (!is_null($matricula->mof_nota1)) {
                $sql .= " NOTA01='{$matricula->mof_nota1}', NOTA02='{$matricula->mof_nota1}', NOTA03='{$matricula->mof_nota1}', NOTA04='-1', ";
            }

            if (!is_null($matricula->mof_final)) {
                $sql .= " NOTA05='{$matricula->mof_final}', ";
            }

            if (!is_null($matricula->mof_mediafinal)) {
                $sql .= " media='{$matricula->mof_mediafinal}' ";
            }

            $sql .= " WHERE COD_ALUNO = '{$codProg}' AND COD_DISCi='{$codDisc}' ";

            return $this->mssqlConnection->query($sql);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }
}
