<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\ChoferMdl;

class Chofer extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new ChoferMdl();

        $data['registros'] =  $model->getRegistros(false, 10);
        $data['pager'] = $model->pager;

        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/choferes', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0)
    {

        $model = new ChoferMdl();

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo['tipoaccion'] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Chofer',
            'frmURL' => base_url('chofer/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model = new ChoferMdl();
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $model = new ChoferMdl();
                $r = [
                    'nIdChofer' => $this->request->getVar('nIdChofer'),
                    'sChofer' => $this->request->getVar('sChofer'),
                    'sLicenciaNo' => $this->request->getVar('sLicenciaNo'),
                    'sCelular' => $this->request->getVar('sCelular'),
                    'dVencimientoLic' => $this->request->getVar('dVencimientoLic'),
                    'nIdVehiculo' => $this->request->getVar('nIdVehiculo'),
                ];
                if ($id > 0) {
                    $r['nIdChofer'] = $this->request->getVar('nIdChofer');
                }
                $model->save($r);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $model = new ChoferMdl();
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        //if( $id > 0 ) $data['registro'] =  $model->getRegistros($id);      
        echo view('catalogos/chofermtto', $data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sChofer' => 'required|min_length[5]|is_unique[enchofer.sChofer, nIdChofer, {nIdChofer}]',
        ];
        $reglasMsj = [
            'sChofer' => [
                'required'   => 'Se requiere el nombre del chofer',
                'min_length' => 'Debe tener 5 caracteres como m&iacute;nimo',
                'is_unique'  => 'Ya se ha registrado un chofer con el mismo nombre'
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new ChoferMdl();
        $reg =  $model->getRegistros($id);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion)
    {
        $model = new ChoferMdl();
        $reg =  $model->getRegistrosByName($sDescripcion);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
