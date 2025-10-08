<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasNCMdl;
use DateTime;

class NotaCredito extends BaseController
{
    public function accion($tipoaccion, $idVentas)
    {
        $this->validaSesion();

        $mdlNC = new VentasNCMdl();
        $mdlVentas = new VentasMdl();

        $regVenta = $mdlVentas->where('nIdVentas', $idVentas)->first();

        switch ($tipoaccion) {
            case 'a':
                $stitulo = 'Agrega';
                break;
        }

        $data = [
            'titulo' => $stitulo . ' Nota Credito (remision: ' . $regVenta['nFolioRemision'] . ' )',
            'frmURL' => base_url('notacredito/' . $tipoaccion . '/' . $idVentas),
            'modo' => strtoupper($tipoaccion),
            'id' => $idVentas
        ];

        if ($this->request->getMethod() == 'post') {
            $a = $this->validaCampos();
            if ($a === false) {
                // valido si existe la nota.
                $a = [
                    'Fecha' => (new DateTime())->format('Y-m-d'),
                    'sMotivo' => $this->request->getVar('sMotivo'),
                    'nImporte' => round(floatval($this->request->getVar('nImporte')), 2)
                ];
                $regNC = $mdlNC->where('nIdVentas', $idVentas)->first();
                if($regNC === null) {
                    $a['nIdVentas'] = $idVentas;
                } else {
                    $a['nIdVentasNC'] = $regNC['nIdVentasNC'];
                }
                // se guarda la nota de credito para la venta dada
                $mdlNC->save($a);
                return 'oK';
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            $regNC = $mdlNC->where('nIdVentas', $idVentas)->first();
            if($regNC !== null) $data['registro'] = $regNC;
        }
        echo view('ventas/notacreditomtto', $data);
    }

    public function validaCampos()
    {
        $reglas = [
            'Fecha' => 'required',
            'sMotivo' => 'required|min_length[4]',
            'nImporte' => 'required|mayorDeCero'
        ];
        $reglasMsj = [
            'Fecha' => [
                'required'   => 'Falta la fecha'
            ],
            'sMotivo' => [
                'required'   => 'Falta el motivo de la nota de credito',
                'min_length' => 'Debe tener 4 caracteres como minimo'
            ],
            'nImporte' => [
                'required'   => 'Falta el Importe de la nota',
                'mayorDeCero' => 'Debe ser mayor de cero'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }
}
