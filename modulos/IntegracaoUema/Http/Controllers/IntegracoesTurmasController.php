<?php

namespace Modulos\IntegracaoUema\Http\Controllers;

use Modulos\Academico\Repositories\TurmaRepository;
use Modulos\IntegracaoUema\Repositories\IntegracaoMatriculaRepository;
use Symfony\Component\HttpFoundation\Request;
use Modulos\Core\Http\Controller\BaseController;
use Modulos\IntegracaoUema\Repositories\IntegracaoCursoRepository;
use Modulos\Seguranca\Providers\ActionButton\Facades\ActionButton;

class IntegracoesTurmasController extends BaseController
{
    protected $integracaoCursoRepository;
    protected $turmaRepository;
    protected $integracaoMatriculaRepository;

    public function __construct(
        IntegracaoCursoRepository $integracaoCursoRepository,
        IntegracaoMatriculaRepository $integracaoMatriculaRepository,
        TurmaRepository $turmaRepository
    ) {
        $this->integracaoCursoRepository = $integracaoCursoRepository;
        $this->integracaoMatriculaRepository = $integracaoMatriculaRepository;
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

        $polos = $this->turmaRepository->getTurmaPolosByMatriculas($turma->trm_id);

        $polosData = [];
        foreach ($polos as $polo) {
            $matriculasPolos = $this->integracaoMatriculaRepository->getMatriculasByTurmaPolo($turma->trm_id, $polo->pol_id);

            $polosData[] = [
                'pol_id' => $polo->pol_id,
                'pol_nome' => $polo->pol_nome,
                'matriculas' => $matriculasPolos
            ];
        }

        return view('IntegracaoUema::turmas.alunos', ['turma' => $turma, 'polos' => $polosData]);
    }
}
