<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\RazonSocialMdl;
use App\Models\Catalogos\RegimenFiscalMdl;

class RazonSocial extends BaseController
{
     public function index()
    {
        $this->validaSesion();
        $model = new RazonSocialMdl(); 

        $data = [
            'registros' => $model->getRegistros(false, 10),
            'pager' => $model->pager
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/razonsocial', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0){

        $model = new RazonSocialMdl(); 

        $aTitulo = ['a'=>'Agrega','b'=>'Borra','e'=>'Edita'];
        $stitulo = $aTitulo[$tipoaccion] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Razon Social Para Timbrar',
            'frmURL' => base_url('razonsocial/' . $tipoaccion . ($id > 0 ? '/'. $id : '') ),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'param1' => $tipoaccion,
            'param2' => $id
        ];
        
        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $r = [
                    'sRFC' => $this->request->getVar('sRFC'),
                    'sRazonSocial' => $this->request->getVar('sRazonSocial'),
                    'cIdRegimenFiscal' => $this->request->getVar('cIdRegimenFiscal'),
                    'sIdPAC' => $this->request->getVar('sIdPAC'),
                    'sPswPAC' => $this->request->getVar('sPswPAC'),
                    'sCertificado' => $this->request->getVar('sCertificado'),
                    'sLeyenda' => $this->request->getVar('sLeyenda')
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdRazonSocial'] = $id;
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
        $mdlRegFis = new RegimenFiscalMdl();
        $data['regRegFis'] = $mdlRegFis->getRegistros(false, null, null, true);
        echo view('catalogos/razonsocialmtto', $data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sRFC' => 'required|min_length[10]|max_length[13]',
            'sRazonSocial' => 'required|min_length[2]|max_length[120]',
            'cIdRegimenFiscal' => 'required',
            'sIdPAC' => 'required',
            'sPswPAC' => 'required',
            'sPswPAC2' => 'required|matches[sPswPAC]',
            'sCertificado' => 'required|min_length[20]',
            'sLeyenda' => 'required|min_length[20]'
        ];
        $reglasMsj = [
            'sRFC' => [
                'required'   => 'Falta el RFC',
                'min_length' => 'Debe tener 3 caracteres como minimo'
            ],
            'sRazonSocial' => [
                'required'   => 'Falta la razon social',
                'min_length' => 'Debe tener 2 caracteres como minimo'
            ],
            'cIdRegimenFiscal' => [
                'required'   => 'Falta el regimen fiscal'
            ],
            'sIdPAC' => [
                'required'   => 'Falta el usuario para el PAC'
            ],
            'sPswPAC' => [
                'required'   => 'Falta la contraseña para el PAC'
            ],
            'sPswPAC2' => [
                'required'   => 'Confirmar contraseña',
                'matches' => 'No coincide'
            ],
            'sCertificado' => [
                'required'   => 'Falta el folio de certificado SAT',
                'min_length' => 'El folio debe tener como minimo 20 digitos'
            ],
            'sLeyenda' => [
                'required'   => 'Falta leyenda de impresion',
                'min_length' => 'La leyenda de impresion debe tener como minimo 20 caracteres'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }
}
