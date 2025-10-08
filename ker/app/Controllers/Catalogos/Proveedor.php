<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\ProveedorMdl;

use App\Models\Seguridad\MenuMdl;

class Proveedor extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new ProveedorMdl();

        $data['registros'] =  $model->paginate(8, 'proveedor');
        $data['provpager'] = $model->pager;

        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/proveedores', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0)
    {

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo['tipoaccion'] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Proveedor',
            'frmURL' => base_url('proveedor/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];

        $model = new ProveedorMdl();

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $r = [
                    'sNombre' => $this->request->getVar('sNombre'),
                    'sRepresentante' => $this->request->getVar('sRepresentante'),
                    'sRFC' => $this->request->getVar('sRFC'),
                    'sDireccion' => $this->request->getVar('sDireccion'),
                    'sCelular' => $this->request->getVar('sCelular'),
                    'email' => $this->request->getVar('email'),
                ];
                if ($id > 0) {
                    $r['nIdProveedor'] = $this->request->getVar('nIdProveedor');
                }
                $model->save($r);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        //if( $id > 0 ) $data['registro'] =  $model->getRegistros($id);    
        echo view('catalogos/proveedormtto', $data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sNombre' => 'required|min_length[8]|is_unique[cpproveedor.sNombre, nIdProveedor, {nIdProveedor}]',
        ];
        $reglasMsj = [
            'sNombre' => [
                'required'   => 'Falta el nombre',
                'min_length' => 'Debe tener 8 caracteres como minimo'
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new Proveedormdl();
        $reg =  $model->getRegistros($id);
        if ($reg == null) {
            return json_encode(['ok' => '0']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion)
    {
        $model = new ProveedorMdl();
        $reg =  $model->getRegistrosByName($sDescripcion);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
