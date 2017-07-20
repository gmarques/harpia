<?php

namespace Modulos\IntegracaoUema\Http\Controllers\Async;

use Modulos\IntegracaoUema\Repositories\IntegracaoOfertaDisciplinaRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IntegracoesOfertas
{
    protected $integracaoOfertaDisciplinaRepository;

    public function __construct(IntegracaoOfertaDisciplinaRepository $integracaoOfertaDisciplinaRepository)
    {
        $this->integracaoOfertaDisciplinaRepository = $integracaoOfertaDisciplinaRepository;
    }

    public function getDisciplinaInfo($coddisciplina, $semestre, $ano)
    {
        try {
            $disciplina = $this->integracaoOfertaDisciplinaRepository->uemaGetDisciplinaInfo($coddisciplina, $semestre, $ano);

            if (!$disciplina) {
                return new JsonResponse(null);
            }

            return new JsonResponse($disciplina);
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
            $integracaoOferta = $this->integracaoOfertaDisciplinaRepository->search([['ito_ofd_id', '=', $request->ofd_id]])->first();

            if ($integracaoOferta) {
                $integracaoOferta->ito_codigo_prog = $request->codigo_prog;
                $integracaoOferta->ito_disciplina_prog = $request->disciplina_prog;

                $integracaoOferta->save();
            } else {
                $ofertaData = [
                    "ito_ofd_id" => $request->ofd_id,
                    "ito_codigo_prog" => $request->codigo_prog,
                    "ito_disciplina_prog" => $request->disciplina_prog
                ];

                $integracaoOferta = $this->integracaoOfertaDisciplinaRepository->create($ofertaData);
            }

            return new JsonResponse(['result' => $integracaoOferta->ito_ofd_id]);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
