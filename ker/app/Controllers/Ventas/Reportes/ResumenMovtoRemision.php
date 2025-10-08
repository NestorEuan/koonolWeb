<?php

namespace App\Controllers\Ventas\Reportes;

use App\Controllers\BaseController;
use App\Models\Almacen\EntregaMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Envios\EnvioMdl;
use App\Models\Envios\ViajeEnvioDetalleMdl;
use App\Models\Envios\ViajeEnvioMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\VentasMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ResumenMovtoRemision extends BaseController
{
    public function resMovtos($idVentas)
    {
        $this->validaSesion();
        // buscar entregas
        $mdlEnvio = new EnvioMdl();
        $mdlEnvio->select(
            '\'EV\' AS tipoMov, enenvio.nIdEnvio AS id, \'\' AS dEntrega, enenvio.cEstatus,' .
                'enenvio.dtAlta, enenvio.dtBaja, enenvio.nIdSucursal, d.sDescripcion AS sucEntrega,' .
                'b.nIdArticulo, c.sDescripcion, b.fCantidad AS cant, b.fRecibido, b.fPorRecibir, b.cModoEnv',
            false
        );
        $mdlEnvio->join('enenviodetalle b', 'enenvio.nIdEnvio = b.nIdEnvio', 'inner');
        $mdlEnvio->join('alarticulo c', 'b.nIdArticulo = c.nIdArticulo', 'inner');
        $mdlEnvio->join('alsucursal d', 'enenvio.nIdSucursal = d.nIdSucursal', 'inner');
        // $mdlEnvio->join('enviajeenvio ve', 'enenvio.nIdEnvio = ve.nIdEnvio AND ve.dtBaja IS NULL', 'left');
        $mdlEnvio->where('enenvio.nIdOrigen', $idVentas);
        $mdlEnvio->where('enenvio.cOrigen', 'ventas');
        $mdlEnvio->where('enenvio.dtBaja IS NULL', null, false);
        $mdlEnvio->where('b.dtBaja IS NULL', null, false);
        $mdlEnvio->orderBy('enenvio.nIdEnvio', 'asc');

        // echo $mdlEnvio->builder()->getCompiledSelect(); exit;
        $data = [
            'registros' => $mdlEnvio->findAll()
        ];
        $data['conEnvios'] = count($data['registros']) > 0;
        $data['regviajes'] = [];
        $idenvio = '0';
        foreach ($data['registros'] as $r) {
            if ($r['id'] != $idenvio) {
                $idenvio = $r['id'];
                $data['regviajes'][$idenvio] = [];
                $mdlViajeEnvio = new ViajeEnvioMdl();
                $regsviaje = $mdlViajeEnvio->select('enviajeenvio.nIdViaje, enviajeenvio.sObservacion, ' .
                    'd.nIdArticulo, ar.sDescripcion AS nomArticulo, d.fPorRecibir, ' .
                    's.sDescripcion AS nomSucursal, ch.sChofer AS nomChofer')
                    ->join('enviajeenviodetalle d', 'enviajeenvio.nIdViajeEnvio = d.nIdViajeEnvio and d.dtBaja IS NULL', 'inner')
                    ->join('enviaje e', 'enviajeenvio.nIdViaje = e.nIdViaje and e.dtBaja IS NULL', 'inner')
                    ->join('alarticulo ar', 'd.nIdArticulo = ar.nIdArticulo', 'inner')
                    ->join('alsucursal s', 'e.nIdSucursal = s.nIdSucursal', 'inner')
                    ->join('enchofer ch', ' e.nIdChofer = ch.nIdChofer', 'inner')
                    ->where('enviajeenvio.nIdEnvio', $idenvio)
                    ->where('enviajeenvio.dtBaja IS NULL')
                    ->orderBy('enviajeenvio.nIdViaje', 'asc')
                    ->findAll();
                $idviajeactual = '0';
                foreach ($regsviaje as $rv) {
                    if ($idviajeactual != $rv['nIdViaje']) {
                        $idviajeactual = $rv['nIdViaje'];
                        $data['regviajes'][$idenvio][$idviajeactual] = [
                            'obs' => $rv['sObservacion'],
                            'suc' => $rv['nomSucursal'],
                            'cho' => $rv['nomChofer'],
                            'det' => []
                        ];
                    }
                    $data['regviajes'][$idenvio][$idviajeactual]['det'][] = [
                        $rv['nIdArticulo'],
                        $rv['nomArticulo'],
                        $rv['fPorRecibir']
                    ];
                }
            }
        }

        $mdlEntrega = new EntregaMdl();
        $mdlEntrega->select('\'EB\' AS tipoMov, alentrega.nIdEntrega AS id, alentrega.dEntrega, alentrega.cEdoEntrega AS cEstatus, ' .
            'alentrega.dtAlta, alentrega.dtBaja, alentrega.nIdSucursal, d.sDescripcion AS sucEntrega,' .
            'b.nIdArticulo, c.sDescripcion, b.fCantidad AS cant, b.fRecibido, b.fPorRecibir', false)
            ->join('alentregadetalle b', 'alentrega.nIdEntrega = b.nIdEntrega', 'inner')
            ->join('alarticulo c', 'b.nIdArticulo = c.nIdArticulo', 'inner')
            ->join('alsucursal d', 'alentrega.nIdSucursal = d.nIdSucursal', 'inner')
            ->where('alentrega.nIdOrigen', $idVentas)
            ->where('alentrega.dtBaja IS NULL', null, false)
            ->orderBy('alentrega.nIdEntrega', 'asc');
        $data['registros2'] = $mdlEntrega->findAll();
        $data['conEntregas'] = count($data['registros2']) > 0;

        $mdlVenta = new VentasMdl();
        $mdlVenta->select('vtventas.*, ' .
            's.sDescripcion as nomSucursal, c.sNombre as nomCliente, u.sNombre as nomUsuario');
        $data['regVenta'] = $mdlVenta->join('alsucursal s', 'vtventas.nIdSucursal = s.nIdSucursal', 'inner')
            ->join('vtcliente c', 'vtventas.nIdCliente = c.nIdCliente', 'inner')
            ->join('sgusuario u', 'vtventas.nIdUsuario = u.nIdUsuario', 'inner')
            ->where('vtventas.nIdVentas', $idVentas)->findAll();
        $mdlVentad = new VentasDetMdl();
        $data['regVentadet'] = $mdlVentad->select('vtventasdet.*, a.sDescripcion')
            ->join('alarticulo a', 'vtventasdet.nIdArticulo = a.nIdArticulo', 'inner')
            ->where('vtventasdet.nIdVentas', $idVentas)
            ->findAll();
        // calcular saldo producto y credito
        // echo view('templates/header', $this->dataMenu);
        echo view('ventas/reportes/resumenMovtosRemision', $data);
        // echo view('templates/footer', $this->dataMenu);
    }
}
