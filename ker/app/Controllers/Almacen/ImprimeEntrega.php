<?php
namespace App\Controllers\Almacen;

use App\Controllers\BaseController;
use App\Models\Almacen\EntregaMdl;

class ImprimeEntrega extends BaseController 
{
    public function index($id = false)
    {
        $this->validaSesion();
        
        $data = [];
        if (strtoupper($this->request->getMethod()) === 'POST') {
            $mdlEntrega = new EntregaMdl();
            $r = $mdlEntrega->where('nIdEntrega', $id)->first();
            $msj = '';
            if($r == null) 
                $msj = 'No se encontro el folio de entrega';
            else {
                if($r['nIdSucursal'] != $this->nIdSucursal) 
                $msj = 'El folio de entrega no es de esta sucursal';
            }
            if($msj != '') {
                return json_encode(['ok' => '0', 'msj' => $msj]);
            }
            return json_encode(['ok' => '1', 'idVentas' => $r['nIdOrigen']]);
        }
        echo view('templates/header', $this->dataMenu);
        echo view('almacen/imprimeentrega', $data);
        echo view('templates/footer', $this->dataMenu);
        return;
    }
}