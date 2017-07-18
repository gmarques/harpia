<?php

namespace Modulos\IntegracaoUema\Http\Controllers;

use Modulos\Core\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Modulos\IntegracaoUema\Repositories\IntegracaoOfertaDisciplinaRepository;
use Modulos\Academico\Repositories\OfertaDisciplinaRepository;
use Modulos\Academico\Repositories\TurmaRepository;

class IntegracoesOfertasDisciplinas extends BaseController
{
    protected $integracaoOfertaDisciplinaRepository;
    protected $ofertaDisciplinaRepository;
    protected $turmaRepository;

    public function __construct(
        IntegracaoOfertaDisciplinaRepository $integracaoOfertaDisciplinaRepository,
        OfertaDisciplinaRepository $ofertaDisciplinaRepository,
        TurmaRepository $turmaRepository
    ) {
        $this->integracaoOfertaDisciplinaRepository = $integracaoOfertaDisciplinaRepository;
        $this->ofertaDisciplinaRepository = $ofertaDisciplinaRepository;
        $this->turmaRepository = $turmaRepository;
    }

    public function getIndex(Request $request)
    {
        $turma = $this->turmaRepository->find($request->id);

        if (!$turma) {
            flash()->error('Turma não existe.');

            return redirect()->back();
        }

        $ofertas = $this->integracaoOfertaDisciplinaRepository->getOfertasByTurma($turma->trm_id);

        $ofertasperiodos = $this->integracaoOfertaDisciplinaRepository->groupDisciplinasByPeriodosLetivos($ofertas);

        foreach ($ofertasperiodos as $kof => $oferta) {
            foreach ($oferta['disciplinas'] as $kdi => $disciplina) {
                $ofertasperiodos[$kof]['disciplinas'][$kdi]['qtd_matriculas'] = $this->ofertaDisciplinaRepository->countMatriculadosByOferta($disciplina['ofd_id']);

                // TODO: Buscar quantidade de matriculados no sistema da PROG
                $ofertasperiodos[$kof]['disciplinas'][$kdi]['qtd_matriculas_uema'] = 154;
            }
        }

        return view('IntegracaoUema::ofertas.index', ['turma' => $turma, 'ofertasperiodos' => $ofertasperiodos]);
    }

    public function getMigrar(Request $request)
    {
        $oferta = $this->ofertaDisciplinaRepository->find($request->id);

        if (!$oferta) {
            flash()->error('Oferta não existe.');

            return redirect()->back();
        }

        return view('IntegracaoUema::ofertas.migrar', ['oferta' => $oferta]);
    }
}
