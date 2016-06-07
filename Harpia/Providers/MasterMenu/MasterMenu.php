<?php

namespace Harpia\Providers\MasterMenu;

// use Modulos\Seguranca\Providers\Security\Security;

use DB;
use Cache;

class MasterMenu{
   	protected $request;
    protected $auth;

   	public function __construct($app)
   	{
    	$this->request = $app['request'];
      $this->auth = $app['auth'];
   	}

    public function make(){
        
    }

    public function render($orientation = 'v')
    {
      $usrId = $this->auth->user()->usr_pes_id;

      $path = preg_split('/\//', $this->request->path());
      $modulo = current($path);
      $controller = next($path);

      $menu = Cache::get($usrId);
      $menu = $menu[$modulo];

      $render = '<ul class="sidebar-menu">';

      foreach ($menu['CATEGORIAS'] as $key => $categorias){
        $render .= '<li class="treeview">';
        $render .= '<a href="#"><i class="'.$categorias['ctr_icone'].'"></i> <span>'.ucfirst($categorias['ctr_nome']).'</span> <i class="fa fa-angle-left pull-right"></i></a>';
        $render .= '<ul class="treeview-menu" style="display: block;">';
        
        if(count($categorias['ITENS'])){
          foreach ($categorias['ITENS'] as $key => $item){
            $render .= '<li><a href="'.url("/").'/'.$modulo.'/'.mb_strtolower($item['rcs_nome']).'/'.$item['prm_nome'].'"><i class="'.$item['rcs_icone'].'"></i>'.ucfirst($item['rcs_nome']).'</a></li>';
          }
        }

        if(count($categorias['SUBCATEGORIA'])){
          $render .= '<li class="treeview">';
          
          foreach ($categorias['SUBCATEGORIA'] as $key => $subcategoria){
            $render .= '<a href="#"><i class="'.$subcategoria['ctr_icone'].'"></i><span>'.$subcategoria['ctr_nome'].'</span><i class="fa fa-angle-left pull-right"></i></a>';

            if(count($subcategoria['ITENS'])){
              $render .= '<ul class="treeview-menu" style="display: block;">';

              foreach ($subcategoria['ITENS'] as $key => $subItem){
                $render .= '<li><a href="'.url("/").'/'.$modulo.'/'.mb_strtolower($subItem['rcs_nome']).'/'.$subItem['prm_nome'].'"><i class="'.$subItem['rcs_icone'].'"></i>'.ucfirst($subItem['rcs_nome']).'</a></li>';
              }

              $render .= '</ul>';
            }
          }

          $render .= '</li>';
        }

        $render .= '</ul>';
        $render .= '</li>';
      }

      $render .= '</ul>';

      return $render;
    }
}