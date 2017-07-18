<?php

namespace Modulos\IntegracaoUema\Models;

use Modulos\Core\Model\BaseModel;

class IntegracaoMatricula extends BaseModel
{
    protected $table = 'inu_integracoes_matriculas';

    protected $primaryKey = 'itm_id';

    protected $fillable = [
        'itm_mat_id',
        'itm_codigo_prog',
        'itm_polo'
    ];

    protected $searchable = [
        'itm_codigo_prog' => '='
    ];
}
