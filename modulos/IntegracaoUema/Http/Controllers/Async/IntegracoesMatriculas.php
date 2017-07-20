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

    public function postIntegrar(Request $request)
    {
        try {
            $matricula = $this->integracaoMatriculaRepository->search([['itm_mat_id', '=', $request->mat_id]])->first();

            if ($matricula) {
                $matricula->itm_codigo_prog = mb_strtoupper(trim($request->codigo_prog));
                $matricula->itm_nome_prog = $request->nome_prog;
                $matricula->itm_polo = mb_strtoupper(trim($request->polo));

                $matricula->save();
            } else {
                $matriculaData = [
                    "itm_mat_id" => $request->mat_id,
                    "itm_codigo_prog" => $request->codigo_prog,
                    "itm_nome_prog" => $request->nome_prog,
                    "itm_polo" => $request->polo
                ];

                $matricula = $this->integracaoMatriculaRepository->create($matriculaData);
            }

            return new JsonResponse(['result' => $matricula->itm_mat_id]);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
