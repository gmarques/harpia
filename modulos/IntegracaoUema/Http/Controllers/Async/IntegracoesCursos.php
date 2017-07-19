<?php

namespace Modulos\IntegracaoUema\Http\Controllers\Async;

use Modulos\IntegracaoUema\Repositories\IntegracaoCursoRepository;
use Modulos\IntegracaoUema\Util\MSSQLConnection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IntegracoesCursos
{
    protected $mssqlConnection;
    protected $integracaoCursoRepository;

    public function __construct(MSSQLConnection $connection, IntegracaoCursoRepository $integracaoCursoRepository)
    {
        $this->mssqlConnection = $connection;
        $this->integracaoCursoRepository = $integracaoCursoRepository;
    }

    public function getNomeCurso($nomecurso)
    {
        try {
            $sql = "SELECT * FROM [carlitosan].[cursos] WHERE CD_CURSO = '{$nomecurso}'";

            $curso = $this->mssqlConnection->fetch($sql);

            if (!isset($curso['NOM_CURSO'])) {
                return new JsonResponse(null);
            }

            $curso = iconv('ISO-8859-1', 'UTF-8', $curso['NOM_CURSO']);

            return new JsonResponse($curso);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function postIntegrar(Request $request)
    {
        try {
            $integracaoCurso = $this->integracaoCursoRepository->search([['itc_crs_id', '=', $request->crs_id]])->first();

            if ($integracaoCurso) {
                $integracaoCurso->itc_codigo_prog = $request->codigo_prog;
                $integracaoCurso->itc_nome_curso_prog = $request->nome_curso;

                $integracaoCurso->save();
            } else {
                $cursoData = [
                    "itc_crs_id" => $request->crs_id,
                    "itc_codigo_prog" => $request->codigo_prog,
                    "itc_nome_curso_prog" => $request->nome_curso
                ];

                $integracaoCurso = $this->integracaoCursoRepository->create($cursoData);
            }

            return new JsonResponse(['result' => $integracaoCurso->itc_crs_id]);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
