<?php

namespace Modulos\IntegracaoUema\Models;

use Modulos\Core\Model\BaseModel;

class IntegracaoOfertaDisciplina extends BaseModel
{
    protected $table = 'inu_integracoes_ofertas_disciplinas';

    protected $primaryKey = 'ito_id';

    protected $fillable = [
        'ito_ofd_id',
        'ito_disciplina_prog'
    ];

    protected $searchable = [
        'ito_disciplina_prog' => '='
    ];
}
