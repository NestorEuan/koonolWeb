<?php

namespace App\Controllers\Ventas\Reportes;

use App\Controllers\BaseController;
use App\Models\Almacen\EntregaDetalleMdl;
use App\Models\Almacen\EntregaMdl;
use App\Models\Almacen\InventarioMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Envios\EnvioMdl;
use App\Models\Envios\ViajeEnvioDetalleMdl;
use App\Models\Envios\ViajeEnvioMdl;
use App\Models\Ventas\MovPagAdelMdl;
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
            'b.nIdArticulo, c.sDescripcion, b.fCantidad AS cant, b.fRecibido, b.fPorRecibir, ' .
            'IFNULL(m.dtAlta, \'\') AS fecMovto', false)
            ->join('alentregadetalle b', 'alentrega.nIdEntrega = b.nIdEntrega', 'inner')
            ->join('alarticulo c', 'b.nIdArticulo = c.nIdArticulo', 'inner')
            ->join('alsucursal d', 'alentrega.nIdSucursal = d.nIdSucursal', 'inner')
            ->join('almovimiento m', 'm.cOrigen = \'entrega\' and m.nIdOrigen = alentrega.nIdEntrega', 'left')
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
        // eztraer pagos de efectivo anticipado

        $mdlMovPagAdel = new MovPagAdelMdl();

        $data['regEfecAnti'] = $mdlMovPagAdel
            ->select('vtmovpagosadelantados.*, p.nImporte AS impPagAdel, s.sDescripcion AS sucDes')
            ->join('vtpagosadelantados p', 'vtmovpagosadelantados.nIdPagosAdelantados = p.nIdPagosAdelantados', 'inner')
            ->join('alsucursal s', 'p.nIdSucursal = s.nIdSucursal', 'inner')
            ->where('nIdVentas', $idVentas)->findAll();
        $data['conEfecAnti'] = count($data['regEfecAnti']) > 0;
        // calcular saldo producto y credito
        echo view('ventas/reportes/resumenMovtosRemision', $data);
    }

    public function cancelaEntrega($idEntrega)
    {
        // es un post
        // bloqueo la entrega
        // si tiene estado 3 ya no se puede cancelar (ya esta entregado)
        $mdlEntrega = new EntregaMdl();
        $mdlEntregaD = new EntregaDetalleMdl();
        $mdlInventario = new InventarioMdl();
        $mdlVentasDet = new VentasDetMdl();

        $mdlEntrega->db->transStart();
        $re = $mdlEntrega->where('nIdEntrega', $idEntrega . ' for update ', false)
            ->builder()->get()->getFirstRow('array');
        $msj = '';
        if ($re == null) {
            $msj = 'Ya no existe la entrega, ya fue cancelada. Vuelva a consultar.';
        } elseif ($re['cEdoEntrega'] == '3') {
            $msj = 'Ya se realizó la entrega al cliente, no se puede cancelar este folio.';
        } elseif ($re['cEdoEntrega'] == '2') {
            $msj = 'La entrega se está procesando. Vuelva a consultar.';
        }
        if ($msj != '') {
            $mdlEntrega->db->transRollback();
            return 'nooKmsj' . $msj;
        }
        $mdlEntrega->update($idEntrega, ['cEdoEntrega' => '2']);
        $mdlEntrega->db->transComplete();
        // registros entregas detalle
        $red = $mdlEntregaD->select('alentregadetalle.nIdArticulo, alentregadetalle.fPorRecibir, b.nIdArticuloAcumulador, b.nMedida, b.cSinExistencia')
            ->join('alarticulo b', 'alentregadetalle.nIdArticulo = b.nIdArticulo', 'inner')
            ->where('alentregadetalle.nIdEntrega', $idEntrega)
            ->findAll();
        // registros ventas detalle
        $rvd = $mdlVentasDet->select('vtventasdet.nIdVentasDet, vtventasdet.nIdArticulo')
            ->join('alarticulo b', 'vtventasdet.nIdArticulo = b.nIdArticulo', 'inner')
            ->where([
                'nIdVentas' => $re['nIdOrigen'],
                'b.cConArticuloRelacionado' => '1'
            ])
            ->findAll();
        $aKeysrvd = array_column($rvd, 'nIdArticulo');
        foreach ($red as $r) {
            $nCant = round(floatval($r['fPorRecibir']), 3);
            $nCantEntrega = $nCant; // porque nCant se modifica para el calculo en inventario si procede
            if ($r['cSinExistencia'] == '0') {
                $mdlInventario->db->transStart();
                $regArtInv = $mdlInventario->getArticuloSucursal($r['nIdArticulo'], $this->nIdSucursal, true, true);
                $nSobreComprometido = round(floatval($regArtInv['fSobreComprometido']), 3);
                $nComprometido = round(floatval($regArtInv['fComprometido']), 3);

                if ($nCant <= $nSobreComprometido) {
                    $nSobreComprometido -= $nCant;
                    $mdlInventario->set('fSobreComprometido', round($nSobreComprometido, 3));
                } else {
                    $nCant -= $nSobreComprometido;    // sobrecomprometido se vuelve cero.
                    if ($nCant <= $nComprometido) {
                        $nComprometido -= $nCant;
                    } else {
                        $nComprometido = 0;
                    }
                    $mdlInventario
                        ->set('fComprometido', $nComprometido)
                        ->set('fSobreComprometido', 0);
                }
                // descomprometemos el inventario del producto
                $mdlInventario
                    ->where([
                        'nIdSucursal' => $this->nIdSucursal,
                        'nIdArticulo' => intval($r['nIdArticulo'])
                    ])->update();
                $mdlInventario->db->transComplete();
            }
            if ($r['nIdArticuloAcumulador'] == '0') {
                $mdlVentasDet->builder()->set([
                    'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nCantEntrega
                ], '', false)->where([
                    'nIdVentas' => $re['nIdOrigen'],
                    'nIdArticulo' => $r['nIdArticulo']
                ])->update();
            } else {
                $keyArt = array_search($r['nIdArticuloAcumulador'], $aKeysrvd);
                if ($keyArt === false) {
                    $mdlVentasDet->builder()->set([
                        'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nCantEntrega
                    ], '', false)->where([
                        'nIdVentas' => $re['nIdOrigen'],
                        'nIdArticulo' => $r['nIdArticulo']
                    ])->update();
                } else {
                    $nMedida = round(floatval($r['nMedida']) * $nCantEntrega, 2);
                    $mdlVentasDet->builder()->set([
                        'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nMedida
                    ], '', false)->where([
                        'nIdVentasDet' => $rvd[$keyArt]['nIdVentasDet']
                    ])->update();
                }
            }
        }
        $mdlEntregaD->where('nIdEntrega', $idEntrega)->delete();
        $mdlEntrega->delete($idEntrega);


        $this->resMovtos($re['nIdOrigen']);
    }

    public function cancelaEnvio($idEnvio, $idVentas)
    {
        //  si el envio no tiene nada que reasignar se devuelve un mensaje
        //  envio, enviodetalle    ventasdet
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $mdlVentasDet = new VentasDetMdl();

        $mdlEnvio->db->transStart();
        $regE = $mdlEnvio->where('nIdEnvio', $idEnvio . ' for update ', false)
            ->builder()->get()->getFirstRow('array');
        $msj = '';
        if ($regE == null) {
            $msj = 'Ya no existe el envio, ya fue cancelado. Vuelva a consultar.';
        } elseif ($regE['cEstatus'] == '4') {
            $msj = 'El envio tiene asignado producto parcial. Solicite a logistica para recuperar el producto no asignado.';
        } elseif ($regE['cEstatus'] == '5') {
            $msj = 'El envio tiene asignado todo el producto. Solicite a logistica si se puede recuperar.';
        }
        if ($msj != '') {
            $mdlEnvio->db->transRollback();
            return 'nooKmsj' . $msj;
        }
        $mdlEnvio->update($idEnvio, ['cEstatus' => '5']);
        $mdlEnvio->db->transComplete();

        $regsE = $mdlEnvioDet->select('enenviodetalle.nIdArticulo, enenviodetalle.fRecibido, enenviodetalle.fPorRecibir, b.nIdArticuloAcumulador, b.nMedida')
            ->join('alarticulo b', 'enenviodetalle.nIdArticulo = b.nIdArticulo', 'inner')
            ->where([
                'enenviodetalle.nIdEnvio' => $idEnvio,
                'enenviodetalle.fPorRecibir >' => 0
            ])->findAll();
        // registros ventas detalle (este proceso solo se realiza en remisiones, por lo tanto siempre es una venta el envio y no un traspaso)
        $rvd = $mdlVentasDet->select('vtventasdet.nIdVentasDet, vtventasdet.nIdArticulo')
            ->join('alarticulo b', 'vtventasdet.nIdArticulo = b.nIdArticulo', 'inner')
            ->where([
                'nIdVentas' => $regE['nIdOrigen'],
                'b.cConArticuloRelacionado' => '1'
            ])
            ->findAll();
        $aKeysrvd = array_column($rvd, 'nIdArticulo');
        foreach ($regsE as $r) {
            $nPorRecibir = round(floatval($r['fPorRecibir']), 3);
            $nRecibido = round(floatval($r['fRecibido']), 3);
            if ($nPorRecibir == 0) continue;

            if ($nRecibido == 0)
                $mdlEnvioDet->where([
                    'nIdEnvio' => $idEnvio,
                    'nIdArticulo' => $r['nIdArticulo']
                ])->delete();
            else
                $mdlEnvioDet->set([
                    'fPorRecibir' => 0,
                    'fCantidad' => $nRecibido
                ])->where([
                    'nIdEnvio' => $idEnvio,
                    'nIdArticulo' => $r['nIdArticulo']
                ])->update();

            if ($r['nIdArticuloAcumulador'] == '0') {
                // falta agregar si es con articulo relacionado
                $mdlVentasDet->builder()->set([
                    'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nPorRecibir
                ], '', false)->where([
                    'nIdVentas' => $idVentas,
                    'nIdArticulo' => $r['nIdArticulo']
                ])->update();
            } else {
                $keyArt = array_search($r['nIdArticuloAcumulador'], $aKeysrvd);
                if ($keyArt === false) {
                    $mdlVentasDet->builder()->set([
                        'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nPorRecibir
                    ], '', false)->where([
                        'nIdVentas' => $regE['nIdOrigen'],
                        'nIdArticulo' => $r['nIdArticulo']
                    ])->update();
                } else {
                    $nMedida = round(floatval($r['nMedida']) * floatval($r['fPorRecibir']), 2);
                    $mdlVentasDet->builder()->set([
                        'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nMedida
                    ], '', false)->where([
                        'nIdVentasDet' => $rvd[$keyArt]['nIdVentasDet']
                    ])->update();
                }
            }
        }
        $regSuma = $mdlEnvioDet->select('SUM(enenviodetalle.fCantidad) AS fCantidad', false)
            ->where('nIdEnvio', $idEnvio)->first();

        if ($regSuma == null || round(floatval($regSuma['fCantidad'] ?? 0), 3) == 0)
            $mdlEnvio->delete($idEnvio);
        else
            $mdlEnvio->set('cEstatus', '5')->where('nIdEnvio', $idEnvio)->update();

        $this->resMovtos($idVentas);
    }
}
