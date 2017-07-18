<?php

namespace Modulos\IntegracaoUema\Models;

use Modulos\Core\Model\BaseModel;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Stevebauman\EloquentTable\TableCollection;

class IntegracaoCurso extends BaseModel
{
    protected $table = 'inu_integracoes_cursos';

    protected $primaryKey = 'itc_id';

    protected $fillable = [
        'itc_crs_id',
        'itc_codigo_prog'
    ];

    protected $searchable = [
        'itc_codigo_prog' => '='
    ];

    /**
     * Paginate the given query into a simple paginator.
     * @param  int  $perPage
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithBonds(TableCollection $collection, $perPage = 15, $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $total = $collection->count();

        $results = $total ? $collection->slice(($page - 1) * $perPage, $perPage) : [];

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}
