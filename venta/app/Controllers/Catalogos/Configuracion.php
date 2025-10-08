<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\ConfiguracionMdl;

class Configuracion extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new ConfiguracionMdl(); 

        $data['registros'] =  $model->paginate(8);
        $data['pager'] = $model->pager;
        
        echo view('templates/header',$this->dataMenu);
        echo view('catalogos/configuracion',$data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0){

        $model = new ConfiguracionMdl(); 

        $aTitulo = ['a'=>'Agrega','b'=>'Borra','e'=>'Edita'];
        $stitulo = $aTitulo['tipoaccion'] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Configuración',
            'frmURL' => base_url('configuracion/' . $tipoaccion . ($id > 0 ? '/'. $id : '') ),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];
        
        if ($this->request->getMethod() == 'post') {
            if($tipoaccion==='b'){
                $model = new ConfiguracionMdl();
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $model = new ConfiguracionMdl();
                $r = [
                    'sID' => $this->request->getVar('sID'),
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'sValor' => $this->request->getVar('sValor'),
                    'fValor' => $this->request->getVar('fValor'),
                    ];
                if($id > 0){
                    $r['nIdConfiguracion'] = $id;
                }
                $model->save($r);
                echo 'oK';
                return;
            }
            else{
                $data['error'] = $a['amsj'];
            }
        }
        else{
            if($tipoaccion !== 'a'){
                $model = new ConfiguracionMdl();
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        //if( $id > 0 ) $data['registro'] =  $model->getRegistros($id);      
        echo view('catalogos/configuracionmtto',$data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sID' => 'required|min_length[3]|is_unique[grconfiguracion.sID, nIdConfiguracion, {nIdConfiguracion}]',
            'sDescripcion' => 'required|min_length[3]',
        ];
        $reglasMsj = [
            'sID' => [
                'required'   => 'Se requiere el id de la configuración',
                'min_length' => 'Debe tener 3 caracteres como mínimo',
                'is_unique'  => 'Ya se ha registrado el ID para una configuración'
            ],
            'sDescripcion' => [
                'required'   => 'Se requiere la descripcion',
                'min_length' => 'Debe tener 3 caracteres como mínimo'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new ConfiguracionMdl();
        $reg =  $model->getRegistros($id);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion)
    {
        $model = new ConfiguracionMdl();
        $reg =  $model->getRegistrosByName($sDescripcion);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

}

?>