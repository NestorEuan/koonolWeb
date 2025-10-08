<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Cuentas\CompraPagoMdl;
use App\Models\Cuentas\CreditoPagoMdl;
use App\Models\Ventas\EsCajaMdl;
use App\Models\Ventas\PagAdelMdl;
use DateTime;

class CancelarPago extends BaseController
{
    // se llama a la interface de cancelacion de pago segun el tipo
    // CxC, CxP o Deposito Clientes
    /*
    en CxC se muestra un listado de remisiones con saldo pendiente
    se selecciona un saldo a cancelar y se valida que . 
    en la interface de pagos canceelados se debe mostrar el listado de 
    pagos y deben estar deshabilitados mostrando informacion de porque estan
    deshabilitados.
        */
    public function index($operacion, $idOperacion)
    {
        $this->validaSesion();
        $msjerr = '';
        switch ($operacion) {
            case 'venta':
                $titulo = 'Cancela Pago/Abono del Cliente ';
                // nIdCreditoPago
                // se busca el registro de EScreditoPago
                // se busca el registro de escaja
                $mdlPago = new CreditoPagoMdl();
                $reg = $mdlPago->getIdEScaja($idOperacion);
                if ($reg == null) {
                    $msjerr = 'No existe registro de pago';
                } else {
                    $mdlEScaja = new EsCajaMdl();
                    $reg = $mdlEScaja->getRegistros($reg['nIdEScaja'], false, false, false, true);
                }
                break;
            case 'compra':
                // nIdCompraPago
                $titulo = 'Cancela Pago al Proveedor';
                $mdlPago = new CompraPagoMdl();
                $reg = $mdlPago->getRegistros($idOperacion, false, false, false, true);
                break;
            case 'deposito':
                $titulo = 'Cancela Deposito del Cliente';
                $mdlPago = new PagAdelMdl();
                $reg = $mdlPago->getRegistros($idOperacion, false, false, true);
                break;
        }



        $urlAnt = $this->request->getVar('urlant') ?? '';

        $data = [
            'titulo' => $titulo,
            'frmURL' => base_url('cancelapago/' . $operacion . '/' . $idOperacion),
            'operacion' => $operacion,
            'frmURLant' => $urlAnt,
            'sePuedeCancelar' => true,
        ];


        if ($this->request->getMethod() == 'post') {
            // if ($operacion == 'venta') {
            //     if ($reg['cierreCorte'] != null) {
            //         $data['sePuedeCancelar'] = false;
            //         $data['msjerr'] = 'No se puede cancelar el pago porque ya se cerró el corte.';
            //     } else {
            //         $mdlEScaja = new EsCajaMdl();
            //         $mdlEScaja->delete($reg['nIdEScaja']);
            //         $mdl
            //     }
            // }
        } else {
            if ($msjerr != '') {
                $data['sePuedeCancelar'] = false;
                $data['msjerr'] = $msjerr;
            } else {
                if ($operacion == 'venta') {
                    if ($reg['nFolioRemision']  != '') {
                        $motivo = 'Remision: ' . $reg['nFolioRemision'] .
                            ' Cliente: ' . $reg['nomCliente'] .
                            '. ' . $reg['sMotivo'];
                    } else {
                        $motivo = $reg['sMotivo'];
                    }
                    $data['registro'] = [
                        'folio' => $reg['nIdEScaja'],
                        'usuario' => $reg['nomUsuario'],
                        'fecha' => (new DateTime($reg['dtAlta']))->format('d-m-Y'),
                        'importe' => number_format(round(floatval($reg['nImporte']), 2), 2),
                        'motivo' => $motivo,
                    ];
                    if ($reg['cierreCorte'] != null) {
                        $data['sePuedeCancelar'] = false;
                        $data['msjerr'] = 'No se puede cancelar el pago porque ya se cerró el corte.';
                    }
                } elseif ($operacion == 'compra') {
                    $data['registro'] = [
                        'folio' => $reg['nIdEScaja'],
                        'usuario' => $reg['nomUsuario'],
                        'fecha' => (new DateTime($reg['dtAlta']))->format('d-m-Y'),
                        'importe' => number_format(round(floatval($reg['nImporte']), 2), 2),
                        'motivo' => $reg['sMotivo'],
                    ];
                } elseif ($operacion == 'deposito') {
                    $data['registro'] = [
                        'folio' => $reg['nIdPagosAdelantados'],
                        'usuario' => $reg['nomUsuario'],
                        'fecha' => (new DateTime($reg['dtAlta']))->format('d-m-Y'),
                        'importe' => number_format(round(floatval($reg['nImporte']), 2), 2),
                        'motivo' => $reg['sObservaciones'],
                        'cliente' => $reg['nomCli'],
                    ];
                    $data['esDeposito'] = true;
                    if ($reg['cierreCorte'] != null) {
                        $data['sePuedeCancelar'] = false;
                        $data['msjerr'] = 'No se puede cancelar el deposito porque ya se cerró el corte.';
                    }
                }
            }
        }

        echo view('ventas/cancelarpagos', $data);
        return;
    }
}
