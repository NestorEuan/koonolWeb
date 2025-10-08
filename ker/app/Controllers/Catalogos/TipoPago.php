<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\TipoPagoMdl;

class TipoPago extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new TipoPagoMdl();

        $data = [
            'registros' => $model->getRegistros(false, true),
            'pager' => $model->pager
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/tipopago', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0)
    {

        $model = new TipoPagoMdl();

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo['tipoaccion'] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Tipos de Pagos',
            'frmURL' => base_url('TipoPago/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'param1' => $tipoaccion,
            'param2' => $id,
            'opcTipo' => [
                '0' => 'Normal',
                '1' => 'Para efectivo anticipado',
                '2' => 'Para Crédito',
                '3' => 'Para deposito'
            ]
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $r = [
                    'sLeyenda' => $this->request->getVar('sLeyenda'),
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'nComision' => $this->request->getVar('nComision'),
                    'sTipoSAT' => $this->request->getVar('sTipoSAT'),
                    'cTipo' => $this->request->getVar('cTipo')
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdTipoPago'] = $id;
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
        echo view('catalogos/tipopagomtto', $data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sLeyenda' => 'required|min_length[3]|max_length[25]',
            'sDescripcion' => 'required|min_length[3]',
            'nComision' => 'required|decimal',
            'sTipoSAT' => 'required'
        ];
        $reglasMsj = [
            'sLeyenda' => [
                'required'   => 'Falta la leyenda',
                'min_length' => 'Debe tener 3 caracteres como minimo'
            ],
            'sDescripcion' => [
                'required'   => 'Falta la descripcion',
                'min_length' => 'Debe tener 3 caracteres como minimo'
            ],
            'nComision' => [
                'required'   => 'Falta la comision',
                'decimal' => 'Debe ser un numero'
            ],
            'sTipoSAT' => [
                'required'   => 'Falta el codigo de SAT para CFDI'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new TipoPagoMdl();
        $reg =  $model->getRegistros($id);
        if ($reg == null) {
            return json_encode(['ok' => '0']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
