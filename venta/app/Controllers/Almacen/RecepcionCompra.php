<?php

namespace App\Controllers\Almacen;

use App\Controllers\BaseController;
use App\Models\Compras\ComprasMdl;
use App\Models\Compras\CompraDetalleMdl;

use App\Models\Seguridad\MenuMdl;

class RecepcionCompra extends BaseController
{   
    public int $nIdUsuario = 0;

    public function __construct()
    {
        helper(['cookie','form','vistas','url']);
        $this->nIdUsuario = get_cookie('nIdUsuario');

    }

    public function index()
    {

        $validation = \Config\Services::validation();

        $data = [];
        $a = $this->validaCampos();
        if ($a === false) {
            // guardar datos
            $compra = new ComprasMdl();
            $compradet = new CompraDetalleMdl();
            $r = $compra->getRegistros($this->request->getVar('nIdCompra'));
            if($r !== null)
            {
                return redirect()->to(base_url('entrada/r/' . $r['nIdCompra']));
            }
        }
        else{
            $data['error'] = $a['amsj'];
        }

        $menubuilder = new MenuMdl();
        $miArr = $menubuilder->buildMenu(0,$this->nIdUsuario);

        $data['navbar'] = $miArr[1];
        echo view('templates/header',$data);
        echo view('almacen/recepcioncomprabuscar', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function validaCampos()
    {

        $reglas = [
            'nIdCompra' => 'required|if_exist',
        ];
        $reglasMsj = [
            'nIdCompra' => [
                'required'   => 'Falta folio de orden de compra',
                'if_exist' => 'Ya existe'
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }


}

?>
