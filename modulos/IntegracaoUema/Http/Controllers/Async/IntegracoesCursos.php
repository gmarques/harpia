<?php

namespace Modulos\IntegracaoUema\Http\Controllers\Async;

use Modulos\IntegracaoUema\Repositories\IntegracaoCursoRepository;
use Illuminate\Http\JsonResponse;
use Modulos\IntegracaoUema\Http\Requests\IntegrarCursoRequest;

class IntegracoesCursos
{
    protected $integracaoCursoRepository;

    public function __construct(IntegracaoCursoRepository $integracaoCursoRepository)
    {
        $this->integracaoCursoRepository = $integracaoCursoRepository;
    }

    public function getNomeCurso($nomecurso)
    {
        try {
            $curso = $this->integracaoCursoRepository->uemaGetNomeCurso($nomecurso);

            if (!$curso) {
                return new JsonResponse(null);
            }

            return new JsonResponse($curso);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function postIntegrar(IntegrarCursoRequest $request)
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
