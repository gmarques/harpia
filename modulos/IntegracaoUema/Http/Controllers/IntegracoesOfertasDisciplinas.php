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

                $periodo = substr($oferta['per_nome'], -1);
                $ano = substr($oferta['per_nome'], -4, 2);
                $qtdMatriculasUema = $this->integracaoOfertaDisciplinaRepository->uemaGetCountMatriculadosByOferta($disciplina['ito_codigo_prog'], $periodo, $ano);

                $ofertasperiodos[$kof]['disciplinas'][$kdi]['qtd_matriculas_uema'] = $qtdMatriculasUema;
            }
        }

        return view('IntegracaoUema::ofertas.index', ['turma' => $turma, 'ofertasperiodos' => $ofertasperiodos]);
    }

    public function getMigrar(Request $request)
    {
        $ofertaDisciplina = $this->ofertaDisciplinaRepository->find($request->id);

        if (!$ofertaDisciplina) {
            flash()->error('Oferta não existe.');

            return redirect()->back();
        }

        $ofertaIntegrada = $this->integracaoOfertaDisciplinaRepository->search([['ito_ofd_id', '=', $ofertaDisciplina->ofd_id]], ['ito_codigo_prog'])->first();

        if (!$ofertaIntegrada) {
            flash()->error('Oferta não possui integração cadastrada.');

            return redirect()->back();
        }

        $oferta = [
            'dis_nome' => $ofertaDisciplina->modulodisciplina->disciplina->dis_nome,
            'trm_nome' => $ofertaDisciplina->turma->trm_nome,
            'per_nome' => $ofertaDisciplina->periodoletivo->per_nome,
            'ito_codigo_prog' => $ofertaIntegrada->ito_codigo_prog
        ];

        $matriculados = $this->integracaoOfertaDisciplinaRepository->getMatriculadosByOferta($ofertaDisciplina->ofd_id);

        return view('IntegracaoUema::ofertas.migrar', ['oferta' => $oferta, 'matriculados' => $matriculados]);
    }
}
