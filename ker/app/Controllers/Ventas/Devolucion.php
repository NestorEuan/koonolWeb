<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;

use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\EsCajaMdl;

use App\Models\Ventas\VentasDevMdl;
use App\Models\Ventas\VentasDevDetalleMdl;
use App\Models\Ventas\VentasDevMovimientoMdl;
use App\Models\Ventas\VentasDevMovimientoDetMdl;

use App\Models\Almacen\EntradaMdl;
use App\Models\Almacen\EntradaDetalleMdl;
use App\Models\Almacen\InventarioMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Almacen\MovimientoDetalleMdl;
use App\Models\Almacen\InventarioMdlMdl;

use App\Models\Catalogos\ClienteMdl;

class Devolucion extends BaseController
{

    public function index()
    {
        $this->validaSesion();

        echo view('templates/header', $this->dataMenu);
        echo "HELLO devolution";
        echo view('templates/footer', $this->dataMenu);
    }

    private function consultaRemision($idVentas)
    {
        $this->validaSesion();
        if (($l = $this->validaTurnoCorte(true))) return $l;
        $mdlVentas = new VentasMdl();
        $mdlVentasDet = new VentasDetMdl();

        $rv = $mdlVentas->getRegistros($idVentas);

        if (!array_key_exists('dtDevolucion', $rv)) {
            $timestamp = strtotime(date("d-m-Y"));
            $rv['dtDevolucion'] = date("Y-m-d", $timestamp);
        }
        $rvd = $mdlVentasDet->getDetalleDev($rv['nIdVentas']);
        $mdlCli = new ClienteMdl();

        $rc = $mdlCli->getRegistros($rv['nIdCliente']);

        $data = [
            'registro' => $rv,
            'registros' => $rvd,
            'cliente' => $rc,
        ];

        return  $data;
    }

    private function guardaDevolucion($idVentas)
    {
        $devMdl = new VentasDevMdl();
        $devdetMdl = new VentasDevDetalleMdl();
        $devmovMdl = new VentasDevMovimientoMdl();
        $devmovdetMdl = new VentasDevMovimientoDetMdl();
        $movMdl = new MovimientoMdl();
        $movdetMdl = new MovimientoDetalleMdl();

        $rdv = $devMdl->getReg($idVentas);
        if ($rdv == null) {
            $rdv = ['nIdVentas' => $idVentas];
            $devMdl->save($rdv);
            $idDev = $devMdl->insertID();
        } else
            $idDev = $rdv['nIdVentasDevolucion'];

        $rmov = [
            'nIdVentas' => $idVentas,
            //'nIdCorte' => $this->request->getVar('nIdCorte'),
            'nIdVentasDevolucion' => $idDev,
            'sObservacion' => $this->request->getVar('sObservacion'),
            'dtDevolucion' => $this->request->getVar('dtDevolucion'),
        ];
        $devmovMdl->save($rmov);
        $idmovdev = $devmovMdl->insertID();
        $arrDevuelve = $this->request->getVar('devolucion');
        $arrDevPrecio = $this->request->getVar('devolprecio');
        $totalDevolver = 0;

        $rgEntrada = [
            "dSolicitud" => $rmov['dtDevolucion'],
            "dEntrada" => $rmov['dtDevolucion'],
            'nIdSucursal' => $this->nIdSucursal,
            'nIdProveedor' => 0,
            'sObservacion' => 'Devolución de mercancía idVenta ' . $idVentas,
            'cEdoEntrega' => '3',
        ];
        $entradaMdl = new EntradaMdl();
        $entradaDetMdl = new EntradaDetalleMdl();
        $inventario = new InventarioMdl();

        $entradaMdl->save($rgEntrada);
        $idEntrada = $entradaMdl->getInsertID();

        $rmov = [
            'nIdOrigen' => $idEntrada,
            'cOrigen' => 'entrada',
            'dMovimiento' => $rmov['dtDevolucion'],
            'sObservacion' => $rmov['sObservacion'],
            //
        ];
        $movMdl->save($rmov);
        $idmov = $movMdl->getInsertID();

        $totprod = 0;

        foreach ($arrDevuelve as $art2dev => $fDevolver) {
            if ($fDevolver == 0) continue;
            $devdet = $devdetMdl->getRegs($idVentas, $art2dev);
            $rdt = [
                'nIdVentasDevolucion' => $idDev,
                'nIdVentas' => $idVentas,
                'nIdArticulo' => $art2dev,
                'fDevuelto' => $fDevolver,
                'nPrecio' => $arrDevPrecio[$art2dev], //  array_search( $art2dev, $arrDevPrecio )
            ];
            $totprod += $fDevolver;
            $totalDevolver +=  $arrDevPrecio[$art2dev] * $fDevolver;
            if ($devdet == null)
                $devdetMdl->save($rdt);
            else
                $devdetMdl->updtDevuelto($devdet['nIdVentasDevDetalle'], $fDevolver);

            $det = [
                'nIdEntrada' => $idEntrada,
                'nIdArticulo' => $art2dev,
                'fCantidad' => $fDevolver,
                'fRecibido' => $fDevolver,
                'fPorRecibir' => 0,
                //'fImporte' => $precioart,
            ];
            $entradaDetMdl->save($det);

            $rdt['nIdVentasDevMovimiento'] = $idmovdev;
            $devmovdetMdl->save($rdt);

            $ar2d2 = $inventario->getArticuloSucursal($art2dev, $this->nIdSucursal);
            $saldoIni = $ar2d2['fExistencia'] ?? 0;
            $det = [
                'nIdMovimiento' => $idmov,
                'nIdArticulo' => $art2dev,
                'nIdSucursal' => $this->nIdSucursal,
                'fCantidad' => $fDevolver,
                'fSaldoInicial' => $saldoIni,
            ];
            $movdetMdl->save($det);
            $inventario->updtArticuloSucursalInventario(
                $this->nIdSucursal,
                $art2dev,
                $fDevolver,
                0
            );

        }
        $devmovMdl->updtDevuelto($idmovdev, $totalDevolver);
        $entradaMdl->set('nProductos', $totprod)->where('nIdEntrada', $idEntrada);

        /**** Agregar el movimiento de salida de caja por el importe devuelto */
        $r = [
            'cTipoMov' => 'S',
            'sMotivo' => 'Devolución de mercancía idVenta ' . $idVentas,
            'nImporte' => $totalDevolver,
            'nIdSucursal' => $this->nIdSucursal
        ];
        $esCaja = new EsCajaMdl();
        $esCaja->save($r);
        $idEsCaja = $esCaja->getInsertID();

        $mdlCorte = new CorteCajaMdl();
        $mdlCorte->guardaFolioMovimiento($this->nIdUsuario, $this->nIdSucursal, $idEsCaja, '2');
        /*** Agregar el movimiento de salida en el corte de caja */
        /*
        */
    }

    public function devolver($idVentas = 0)
    {
        $this->validaSesion();
        if (($l = $this->validaTurnoCorte(true))) return $l;

        if ($idVentas == 0) {
            echo view('templates/header', $this->dataMenu);
            echo "REMISIÓN NO VALIDA";
            echo view('templates/footer', $this->dataMenu);
            return;
        }
        if (strtoupper($this->request->getMethod()) === 'POST') {
            $this->guardaDevolucion($idVentas);
            unset($_SESSION['devolucion']);
            return redirect()->to(base_url('remisiones'));
        }

        $data = $this->consultaRemision($idVentas);
        $_SESSION['devolucion']['registro'] = $data['registro'];
        $_SESSION['devolucion']['registros'] = $data['registros'];
        $_SESSION['devolucion']['cliente'] = $data['cliente'];
        //$_SESSION['devolucion']['detxent'] = $data['detxent'];
        $timestamp = strtotime(date("d-m-Y"));
        $_SESSION['devolucion']['registro']['dtDevolucion'] = date("Y-m-d", $timestamp);
        /*
        if (!isset($_SESSION['devolucion']['registro'])) {
        } else {
            $data['registro'] = $_SESSION['devolucion']['registro'];
            $data['registros'] = $_SESSION['devolucion']['registros'];
            $data['cliente'] = $_SESSION['devolucion']['cliente'];
            //$data['detxent'] = $_SESSION['devolucion']['detxent'];
        }
        */

        $data['titulo'] = 'Devolucion';
        $data['frmURL'] = "devolucion/$idVentas";

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/verRemision', $data);
        echo view('templates/footer', $this->dataMenu);
    }
}
