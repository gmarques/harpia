<?php

namespace Modulos\IntegracaoUema\Http\Controllers;

use App\Http\Controllers\Controller;

/**
 * Class IndexController.
 */
class IndexController extends Controller
{

    /**
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        return view('IntegracaoUema::index.index');
    }
}
