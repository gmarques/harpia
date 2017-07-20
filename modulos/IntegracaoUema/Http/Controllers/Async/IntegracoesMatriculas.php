<?php

namespace Modulos\IntegracaoUema\Http\Controllers\Async;

use Modulos\IntegracaoUema\Repositories\IntegracaoMatriculaRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IntegracoesMatriculas
{
    public function __construct(IntegracaoMatriculaRepository $integracaoMatriculaRepository)
    {
        $this->integracaoMatriculaRepository = $integracaoMatriculaRepository;
    }

    /**
     * @param $codprog
     *
     * Busca as informações do aluno de acordo com a matrícula na prog
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function getMatriculaInfo($codprog)
    {
        try {
            $aluno = $this->integracaoMatriculaRepository->uemaGetAlunoByCodigoProg($codprog);

            if (!$aluno) {
                return new JsonResponse(null);
            }

            return new JsonResponse($aluno);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
