<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoMatricula;

class IntegracaoMatriculaRepository extends BaseRepository
{
    public function __construct(IntegracaoMatricula $integracaoMatricula)
    {
        $this->model = $integracaoMatricula;
    }
}
