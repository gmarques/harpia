<?php

namespace Modulos\IntegracaoUema\Http\Controllers\Async;

use Modulos\Academico\Repositories\OfertaDisciplinaRepository;
use Modulos\Academico\Repositories\TurmaRepository;
use Modulos\IntegracaoUema\Http\Requests\IntegrarMatriculaRequest;
use Modulos\IntegracaoUema\Http\Requests\IntegrarMatriculasTurmaRequest;
use Modulos\IntegracaoUema\Http\Requests\MigrarNotaAlunoRequest;
use Modulos\IntegracaoUema\Http\Requests\MigrarNotasAlunosOfertaRequest;
use Modulos\IntegracaoUema\Repositories\IntegracaoMatriculaRepository;
use Illuminate\Http\JsonResponse;
use Modulos\IntegracaoUema\Repositories\IntegracaoOfertaDisciplinaRepository;

class IntegracoesMatriculas
{
    protected $integracaoMatriculaRepository;
    protected $integracaoOfertaDisciplinaRepository;
    protected $turmaRepository;
    protected $ofertaDisciplinaRepository;

    public function __construct(
        IntegracaoMatriculaRepository $integracaoMatriculaRepository,
        IntegracaoOfertaDisciplinaRepository $integracaoOfertaDisciplinaRepository,
        TurmaRepository $turmaRepository,
        OfertaDisciplinaRepository $ofertaDisciplinaRepository
    ) {
        $this->integracaoMatriculaRepository = $integracaoMatriculaRepository;
        $this->integracaoOfertaDisciplinaRepository = $integracaoOfertaDisciplinaRepository;
        $this->turmaRepository = $turmaRepository;
        $this->ofertaDisciplinaRepository = $ofertaDisciplinaRepository;
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

    public function postIntegrar(IntegrarMatriculaRequest $request)
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

    public function postIntegrarMatriculasTurma(IntegrarMatriculasTurmaRequest $request)
    {
        try {
            $turma = $this->turmaRepository->find($request->trm_id);

            if (!$turma) {
                return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
            }

            $sync = $this->integracaoMatriculaRepository->sincronizarMatriculasTurmaUema($turma);

            if ($sync) {
                return new JsonResponse(['result' => $sync]);
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function postMigrarNotaAluno(MigrarNotaAlunoRequest $request)
    {
        try {
            $sync = $this->integracaoMatriculaRepository->uemaSetmigrarNotasOfertaAluno($request->ofd_id, $request->mat_id);

            if ($sync) {
                return new JsonResponse(['result' => $sync]);
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function postMigrarNotasTurma(MigrarNotasAlunosOfertaRequest $request)
    {
        try {
            $oferta = $this->ofertaDisciplinaRepository->find($request->ofd_id);

            $matriculados = $this->integracaoOfertaDisciplinaRepository->getMatriculadosByOferta($oferta->ofd_id);

            foreach ($matriculados as $matriculado) {
                $this->integracaoMatriculaRepository->uemaSetmigrarNotasOfertaAluno($request->ofd_id, $matriculado['mof_mat_id']);
            }

            return new JsonResponse(['result' => 'OK']);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                throw $e;
            }

            return new JsonResponse(null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
