<?php

namespace Modulos\Seguranca\Repositories;

use DB;

class ModuloRepository
{
    public function getByUser($userId)
    {
        $modulos = DB::table('seg_modulos')
            ->join('seg_perfis', 'prf_mod_id', '=', 'mod_id')
            ->join('seg_perfis_usuarios', 'pru_prf_id', '=', 'prf_id')
            ->select('seg_modulos.*')
            ->where('pru_usr_id', '=', $userId)
            ->get();

        $permissoes = Cache::get('PERMISSOES_'.$userId);

        for ($i = 0; $i < $modulos->count(); $i++) {
            if (!in_array($modulos[$i]->mod_slug.'.index.index', $permissoes)) {
                unset($modulos[$i]);
            }
        }

        return $modulos;
    }
}
