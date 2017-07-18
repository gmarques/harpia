<?php

namespace Modulos\IntegracaoUema\Http\Controllers;

use Modulos\Academico\Repositories\OfertaDisciplinaRepository;
use Modulos\Academico\Repositories\TurmaRepository;
use Modulos\IntegracaoUema\Repositories\IntegracaoMatriculaRepository;
use Modulos\IntegracaoUema\Repositories\IntegracaoOfertaDisciplinaRepository;
use Symfony\Component\HttpFoundation\Request;
use Modulos\Core\Http\Controller\BaseController;
use Modulos\IntegracaoUema\Repositories\IntegracaoCursoRepository;
use Modulos\Seguranca\Providers\ActionButton\Facades\ActionButton;

class IntegracoesTurmasController extends BaseController
{
    protected $integracaoOfertaDisciplinaRepository;
    protected $integracaoMatriculaRepository;
    protected $integracaoCursoRepository;
    protected $ofertaDisciplinaRepository;
    protected $turmaRepository;

    public function __construct(
        IntegracaoOfertaDisciplinaRepository $integracaoOfertaDisciplinaRepository,
        IntegracaoMatriculaRepository $integracaoMatriculaRepository,
        IntegracaoCursoRepository $integracaoCursoRepository,
        OfertaDisciplinaRepository $ofertaDisciplinaRepository,
        TurmaRepository $turmaRepository
    ) {
        $this->integracaoOfertaDisciplinaRepository = $integracaoOfertaDisciplinaRepository;
        $this->integracaoMatriculaRepository = $integracaoMatriculaRepository;
        $this->integracaoCursoRepository = $integracaoCursoRepository;
        $this->ofertaDisciplinaRepository = $ofertaDisciplinaRepository;
        $this->turmaRepository = $turmaRepository;
    }

    public function getIndex()
    {
        $tableData = $this->integracaoCursoRepository->getTurmasCursosIntegrados();

        if ($tableData->count()) {
            $tabela = $tableData->columns(array(
                'trm_id' => '#',
                'crs_nome' => 'Curso',
                'trm_nome' => 'Turma',
                'per_nome' => 'Período',
                'trm_action' => 'Ações'
            ))
            ->modifyCell('trm_action', function () {
                return array('style' => 'width: 140px;');
            })
            ->means('trm_action', 'trm_id')
            ->modify('trm_action', function ($id) {
                return ActionButton::grid([
                    'type' => 'SELECT',
                    'config' => [
                        'classButton' => 'btn-default',
                        'label' => 'Selecione'
                    ],
                    'buttons' => [
                        [
                            'classButton' => '',
                            'icon' => 'fa fa-exchange',
                            'route' => 'integracaouema.turmas.ofertasdisciplinas',
                            'parameters' => ['id' => $id],
                            'label' => 'Disciplinas',
                            'method' => 'get'
                        ],
                        [
                            'classButton' => '',
                            'icon' => 'fa fa-users',
                            'route' => 'integracaouema.turmas.alunos',
                            'parameters' => ['id' => $id],
                            'label' => 'Alunos',
                            'method' => 'get'
                        ]
                    ]
                ]);
            })
            ->sortable(array('trm_id', 'crs_nome'));
        }

        return view('IntegracaoUema::turmas.index', ['tabela' => $tabela]);
    }

    public function getAlunos(Request $request)
    {
        $turma = $this->turmaRepository->find($request->id);

        if (!$turma) {
            flash()->error('Turma não existe.');

            return redirect()->back();
        }

        $polos = $this->turmaRepository->getTurmaPolosByMatriculas($turma->trm_id);

        $polosData = [];
        foreach ($polos as $polo) {
            $matriculasPolos = $this->integracaoMatriculaRepository->getMatriculasByTurmaPolo($turma->trm_id, $polo->pol_id);

            $qtdMatriculasIntegradas = 0;
            foreach ($matriculasPolos as $matricula) {
                if (!empty($matricula['itm_codigo_prog'])) {
                    $qtdMatriculasIntegradas++;
                }
            }

            $polosData[] = [
                'pol_id' => $polo->pol_id,
                'pol_nome' => $polo->pol_nome,
                'matriculas' => $matriculasPolos,
                'qtd_matriculas' => count($matriculasPolos),
                'qtd_matriculas_integradas' => $qtdMatriculasIntegradas
            ];
        }

        return view('IntegracaoUema::turmas.alunos', ['turma' => $turma, 'polos' => $polosData]);
    }

    public function getOfertasDisciplinas(Request $request)
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

        return view('IntegracaoUema::turmas.ofertasdisciplinas', ['turma' => $turma, 'ofertasperiodos' => $ofertasperiodos]);
    }
}
