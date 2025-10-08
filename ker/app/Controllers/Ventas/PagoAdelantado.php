<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Ventas\PagoAdelantadoMdl;
//use App\Models\Catalogos\Cliente Mdl;

class PagoAdelantado extends BaseController
{
    public function index()
    {
        $model = new PagoAdelantadoMdl(); 

        $data['registros'] =  $model->paginate(8,'pagoadela');
        $data['adelapager'] = $model->pager;

        $data['navbar'] = get_cookie('mainMenu');
	echo view('templates/header',$data);

        echo view('ventas/adelas',$data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0){
        helper(['form']);

        $model = new PagoAdelantadoMdl(); 

        $stitulo = 'Ver';
        switch($tipoaccion){
            case 'a': $stitulo = 'Agrega'; break;
            case 'b': $stitulo = 'Borra'; break;
            case 'e': $stitulo = 'Edita'; break;
        }
        $data = [
            'titulo' => $stitulo . ' Pago Adelantado',
            'frmURL' => base_url('adela/' . $tipoaccion . ($id > 0 ? '/'. $id : '') ),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];
        
        if (strtoupper($this->request->getMethod()) === 'POST') {
            if($tipoaccion==='b'){
                $model = new PagoAdelantadoMdl();
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $model = new PagoAdelantadoMdl();
                $r = [
                    'nIdCliente' => $this->request->getVar('nIdCliente'),
                    'dPago' => $this->request->getVar('dPago'),
                    'fImporte' => $this->request->getVar('fImporte'),
                    'fSaldo' => $this->request->getVar('fSaldo'),
                    'nIdUsuario' => $this->request->getVar('nIdUsuario'),
                        ];
                if($id > 0){
                    $r['nIdPagoAdelantado'] = $this->request->getVar('nIdPagoAdelantado');
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
                $model = new PagoAdelantadoMdl();
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        //if( $id > 0 ) $data['registro'] =  $model->getRegistros($id);            
        echo view('ventas/adelamtto',$data);
    }

    public function validaCampos()
    {
        $reglas = [
            'nIdCliente' => 'required',
            'fImporte' => 'required'
        ];
        $reglasMsj = [
            'nIdCliente' => [
                'required'   => 'Se requiere el cliente',
            ],
            'fImporte' => [
                'required'   => 'Se requiere importe recibido',
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new PagoAdelantadomdl();
        $reg =  $model->getRegistros($id);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
    
    /*
    public function buscaNombre($sDescripcion)
    {
        $model = new PagoAdelantadoMdl();
        $reg =  $model->getRegistrosByName($sDescripcion);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
    */

}

?>
