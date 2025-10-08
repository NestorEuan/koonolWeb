<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\AgenteVentasMdl;

class AgenteVentas extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new AgenteVentasMdl();

        $data['registros'] =  $model->paginate(10);
        $data['pager'] = $model->pager;
        
        echo view('templates/header',$this->dataMenu);
        echo view('catalogos/agenteventas',$data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0){

        $model = new AgenteVentasMdl(); 

        $aTitulo = ['a'=>'Agrega','b'=>'Borra','e'=>'Edita'];
        $stitulo = $aTitulo[$tipoaccion] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Agente Ventas',
            'frmURL' => base_url('agentesventas/' . $tipoaccion . ($id > 0 ? '/'. $id : '') ),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];
        
        if ($this->request->getMethod() == 'post') {
            if($tipoaccion==='b'){
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $r = [
                    'sNombre' => $this->request->getVar('sNombre')
                    ];
                if($id > 0){
                    $r['nIdAgenteVentas'] = $id;
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
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        echo view('catalogos/agenteventasmtto',$data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sNombre' => 'required|min_length[3]'
        ];
        $reglasMsj = [
            'sNombre' => [
                'required'   => 'Se requiere el nombre',
                'min_length' => 'Debe tener 3 caracteres como mínimo'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }
}

?>