<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoCurso;
use Modulos\IntegracaoUema\Util\MSSQLConnection;

class IntegracaoCursoRepository extends BaseRepository
{
    protected $mssqlConnection;

    public function __construct(IntegracaoCurso $integracaoCurso, MSSQLConnection $connection)
    {
        $this->model = $integracaoCurso;
        $this->mssqlConnection = $connection;
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

    /**
     * Funções que buscam dados nas tabelas da UEMA
     */
    public function uemaGetNomeCurso($nomecurso)
    {
        try {
            $sql = "SELECT * FROM [carlitosan].[cursos] WHERE CD_CURSO = '{$nomecurso}'";

            $curso = $this->mssqlConnection->fetch($sql);

            if (!isset($curso['NOM_CURSO'])) {
                return false;
            }

            return iconv('ISO-8859-1', 'UTF-8', $curso['NOM_CURSO']);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return false;
        }
    }
}
