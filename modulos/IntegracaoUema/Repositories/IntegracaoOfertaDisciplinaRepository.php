<?php

namespace Modulos\IntegracaoUema\Repositories;

use Modulos\Core\Repository\BaseRepository;
use Modulos\IntegracaoUema\Models\IntegracaoOfertaDisciplina;

class IntegracaoOfertaDisciplinaRepository extends BaseRepository
{
    public function __construct(IntegracaoOfertaDisciplina $integracaoOfertaDisciplina)
    {
        $this->model = $integracaoOfertaDisciplina;
    }
}
