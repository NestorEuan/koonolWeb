<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\TipoListaMdl;

class TipoLista extends BaseController
{
     public function index()
    {
        $this->validaSesion();
        $model = new TipoListaMdl();
        $data = [
            'registros' => $model->getRegistros(false, false, 8),
            'pager' => $model->pager
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/tipolista', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = ''){

        $model = new TipoListaMdl(); 

        switch ($tipoaccion) {
            case 'a':
                $stitulo = 'Agrega';
                break;
            case 'b':
                $stitulo = 'Borra';
                break;
            case 'e':
                $stitulo = 'Edita';
                break;
            default:
                $stitulo = 'Ver';
        }

        $data = [
            'titulo' => $stitulo . ' Tipo Lista de Precios',
            'frmURL' => base_url('tipolista/' . $tipoaccion . ($id != '' ? '/'. $id : '') ),
            'modo' => strtoupper($tipoaccion),
            'id' => $id
        ];
        
        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $bTapado = ($this->request->getVar('bImprimirNota') ?? false) == 'on' ? '1' : '0';
                $r = [
                    'bImprimirNota' => $bTapado,
                    'cTipo' => $this->request->getVar('cTipo'),
                    'cNombreTipo' => $this->request->getVar('cNombreTipo')
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdTipoLista'] = $id;
                }
                if($bTapado == '1') {
                    $model->set('bImprimirNota', '0')->update();
                }
                
                $model->save($r);

                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] = $model->getRegistros($id);
            }
        }
        echo view('catalogos/tipolistamtto', $data);
    }

    public function validaCampos()
    {

        $reglas = [
            'cTipo' => 'required|min_length[2]',
            'cNombreTipo' => 'required|min_length[5]'
        ];
        $reglasMsj = [
            'cTipo' => [
                'required'   => 'Falta la abreviatura',
                'min_length' => 'Debe tener 2 caracteres como minimo'
            ],
            'cNombreTipo' => [
                'required'   => 'Falta el nombre del tipo de lista',
                'min_length' => 'Debe tener 5 caracteres como minimo'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new TipoListaMdl();
        $reg =  $model->getRegistros($id);
        if($reg == null) {
            return json_encode(['ok' => '0']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }


}
