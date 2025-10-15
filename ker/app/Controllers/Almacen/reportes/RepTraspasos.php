<?php

namespace App\Controllers\Almacen\reportes;

use App\Controllers\BaseController;
use App\Models\Almacen\EntradaDetalleMdl;
use App\Models\Almacen\EntradaMdl;
use App\Models\Almacen\MovimientoDetalleMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Almacen\TraspasoDetalleMdl;
use App\Models\Almacen\TraspasoMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Compras\CompraDetalleMdl;
use App\Models\Compras\ComprasMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class RepTraspasos extends BaseController
{
    public function index()
    {
        $this->validaSesion();

        $mdlSucursal = new SucursalMdl();
        $sucursales = $mdlSucursal->getRegistros(false, false, false, false);

        $data = [
            'sucursales' => $sucursales,
            'verTodasSuc' => isset($this->aPermiso['oVerTodasSuc']),
            'idSucursal' => $this->nIdSucursal,
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/reportes/repTraspasos', $data);
        echo view('templates/footer');
    }

    public function exportaXLS()
    {
        $this->validaSesion();

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $dIni = $this->request->getVar('dFecIni');
        $dFin = $this->request->getVar('dFecFin');
        $sucursal = intval($this->request->getVar('lstSucursales') ?? '0');
        if ($sucursal == '0') $sucursal = $this->nIdSucursal;
        $filtro = [
            'dIni' => $dIni,
            'dFin' => $dFin,
            'Edo' => ['2', '3'],
        ];
        $aAcum = [];
        // genera datos
        $this->generaTraspasos($wb, $aAcum, $sucursal, $filtro, $objFecha);
        $this->generaCompras($wb, $aAcum, $sucursal, $filtro, $objFecha);
        $this->generaEntradas($wb, $aAcum, $sucursal, $filtro, $objFecha);

        $this->generaResumen($wb, $aAcum, $sucursal);

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'reprep');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('reporterecepcion' . $this->nIdSucursal . '.xlsx', $text);
    }

    private function generaTraspasos(Spreadsheet &$wb, &$aAcum, $sucursal, $filtro, $objFecha)
    {
        // traspaso, detalle de traspaso, 
        $mdlTraspaso = new TraspasoMdl();
        $mdlTraspasoDet = new TraspasoDetalleMdl();
        $mdlMovto = new MovimientoMdl();
        $mdlMovtoDet = new MovimientoDetalleMdl();
        $regs = $mdlTraspaso->getRegistros($sucursal, 0, 0, $filtro);
        $ws = $wb->createSheet();
        $ws->setTitle('Detalle de Traspasos');
        $fila = 1;
        $columna = 1;
        $ws->getCell([$columna, $fila])->setValue('fecha');
        $ws->getCell([$columna + 1, $fila])->setValue('folio' . chr(10) . 'traspaso');
        $ws->getCell([$columna + 2, $fila])->setValue('folio' . chr(10) . 'envio');
        $ws->getCell([$columna + 3, $fila])->setValue('Solicitado a');
        $ws->getCell([$columna + 4, $fila])->setValue('Observaciones');
        $ws->getCell([$columna + 5, $fila])->setValue('cantidad');
        $ws->getCell([$columna + 6, $fila])->setValue('Producto');
        $ws->getCell([$columna + 7, $fila])->setValue('fecha' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 8, $fila])->setValue('folio' . chr(10) . 'viaje');
        $ws->getCell([$columna + 9, $fila])->setValue('capturó' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 10, $fila])->setValue('Observacion' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 11, $fila])->setValue('Producto' . chr(10) . 'recepcionado');
        $ws->getCell([$columna + 12, $fila++])->setValue('cantidad' . chr(10) . 'recepcionada');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getFont()->setBold(true);
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setHorizontal('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setVertical('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setWrapText(true);
        $aViajes = [];
        $regsTraspaso = [];
        foreach ($regs as $r) {
            if (!isset($regsTraspaso[$r['nIdTraspaso']])) {
                $regsTraspaso[$r['nIdTraspaso']] = $r;
                $aViajes[$r['nIdTraspaso']] = [];
            }
            $aViajes[$r['nIdTraspaso']][] = $r['nIdViaje'];
        }

        foreach ($regsTraspaso as $r) {
            // if ($sucursal != 0) {
            //     if ($sucursal != intval($r['nIdProveedor'])) continue;
            // }
            $f = $fila;
            $fecha = $objFecha->PHPToExcel($r['dtAlta']);
            $ws->getCell([$columna, $f])->setValue($fecha);
            $ws->getCell([$columna + 1, $f])->setValue($r['nIdTraspaso']);
            $ws->getCell([$columna + 2, $f])->setValue($r['nIdEnvio']);
            $ws->getCell([$columna + 3, $f])->setValue($r['sNombre']);
            $ws->getCell([$columna + 4, $f])->setValue($r['sObservacion']);

            $regsDet = $mdlTraspasoDet->getRegistros($r['nIdTraspaso'], 0);
            // se agrega el acumulado por articulo
            $filaMax = $f;
            foreach ($regsDet as $rd) {
                if (!isset($aAcum[$rd['nIdArticulo']])) $aAcum[$rd['nIdArticulo']] = [
                    'nom' => $rd['sDescripcion'],
                    'cant' => 0,
                    'recepcion' => 0,
                ];
                $aAcum[$rd['nIdArticulo']]['cant'] += floatval($rd['fCantidad']);
                $ws->getCell([$columna + 5, $f])->setValue($rd['fCantidad']);
                $ws->getCell([$columna + 6, $f])->setValue($rd['sDescripcion']);
                $filaMax = $f;
                $f++;
            }

            // leer movimientos de recepcion
            $regsMovto = $mdlMovto
                ->select('almovimiento.*, IFNULL(u.sNombre, \'\') AS nomUsu')
                ->join('sgusuario u', 'almovimiento.nIdUsuario = u.nIdUsuario', 'left')
                ->where('cOrigen', 'traspaso')->where('nIdOrigen', $r['nIdTraspaso'])
                ->findAll();
            $f = $fila;
            $filaMax2 = $f;
            $indViaje = 0;
            foreach ($regsMovto as $rdm) {
                $regsMovtoD = $mdlMovtoDet
                    ->select('almovimientodetalle.nIdArticulo, almovimientodetalle.fCantidad, a.sDescripcion')
                    ->join('alarticulo a', 'almovimientodetalle.nIdArticulo = a.nIdArticulo', 'inner')
                    ->where('nIdMovimiento', $rdm['nIdMovimiento'])
                    ->where('fCantidad <>', 0)
                    ->findAll();
                $folioViaje = (isset($aViajes[$r['nIdTraspaso']][$indViaje]) ? $aViajes[$r['nIdTraspaso']][$indViaje] : '');
                $ws->getCell([$columna + 7, $f])->setValue(
                    $objFecha->PHPToExcel($rdm['dtAlta'])
                );
                $ws->getCell([$columna + 8, $f])->setValue($folioViaje);
                $ws->getCell([$columna + 9, $f])->setValue($rdm['nomUsu']);
                $ws->getCell([$columna + 10, $f])->setValue($rdm['sObservacion']);
                foreach ($regsMovtoD as $rdd) {
                    $ws->getCell([$columna + 11, $f])->setValue($rdd['sDescripcion']);
                    $ws->getCell([$columna + 12, $f])->setValue($rdd['fCantidad']);
                    $aAcum[$rdd['nIdArticulo']]['recepcion'] += floatval($rdd['fCantidad']);
                    $filaMax2 = $f;
                    $f++;
                }
                $indViaje++;
            }
            $filaMax = ($filaMax2 > $filaMax ? $filaMax2 : $filaMax);
            $ws->getStyle([$columna, $fila, $columna + 12, $filaMax])->getBorders()->getOutline()->setBorderStyle('thin');
            $fila = ++$filaMax;
        }
        $ws->getStyle([1, 2, 1, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $ws->getStyle([1, 2, 1, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([8, 2, 8, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $ws->getStyle([8, 2, 8, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([6, 2, 6, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([13, 2, 13, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        for ($i = 1; $i <= 13; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        $ws->freezePane('A2');
    }

    private function generaCompras(Spreadsheet &$wb, &$aAcum, $sucursal, $filtro, $objFecha)
    {
        // traspaso, detalle de traspaso, 
        $mdlCompra = new ComprasMdl();
        $mdlCompraDet = new CompraDetalleMdl();
        $mdlMovto = new MovimientoMdl();
        $mdlMovtoDet = new MovimientoDetalleMdl();
        $regs = $mdlCompra->getRegistros($sucursal, 0, 0, $filtro);
        $ws = $wb->createSheet();
        $ws->setTitle('Detalle de Compras');
        $fila = 1;
        $columna = 1;
        $ws->getCell([$columna, $fila])->setValue('fecha'); //dtAlta
        $ws->getCell([$columna + 1, $fila])->setValue('folio' . chr(10) . 'compra');
        // $ws->getCell([$columna + 2, $fila])->setValue('folio' . chr(10) . 'envio');
        $ws->getCell([$columna + 3, $fila])->setValue('Solicitado a'); //sNombre
        $ws->getCell([$columna + 4, $fila])->setValue('Observaciones'); //sObservacion
        $ws->getCell([$columna + 5, $fila])->setValue('cantidad'); //fCantidad
        $ws->getCell([$columna + 6, $fila])->setValue('Producto'); 
        $ws->getCell([$columna + 7, $fila])->setValue('Costo');
        $ws->getCell([$columna + 8, $fila])->setValue('fecha' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 9, $fila])->setValue('capturó' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 10, $fila])->setValue('Observacion' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 11, $fila])->setValue('Producto' . chr(10) . 'recepcionado');
        $ws->getCell([$columna + 12, $fila++])->setValue('cantidad' . chr(10) . 'recepcionada');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getFont()->setBold(true);
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setHorizontal('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setVertical('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setWrapText(true);

        foreach ($regs as $r) {
            $f = $fila;
            $fecha = $objFecha->PHPToExcel($r['dtAlta']);
            $ws->getCell([$columna, $f])->setValue($fecha);
            $ws->getCell([$columna + 1, $f])->setValue($r['nIdCompra']);
            // $ws->getCell([$columna + 2, $f])->setValue('');
            $ws->getCell([$columna + 3, $f])->setValue($r['sNombre']);
            $ws->getCell([$columna + 4, $f])->setValue($r['sObservacion']);

            $regsDet = $mdlCompraDet->getRegistros($r['nIdCompra'], 0);
            // se agrega el acumulado por articulo
            $filaMax = $f;
            foreach ($regsDet as $rd) {
                if (!isset($aAcum[$rd['nIdArticulo']])) $aAcum[$rd['nIdArticulo']] = [
                    'nom' => $rd['sDescripcion'],
                    'cant' => 0,
                    'recepcion' => 0,
                ];
                $aAcum[$rd['nIdArticulo']]['cant'] += floatval($rd['fCantidad']);
                $ws->getCell([$columna + 5, $f])->setValue($rd['fCantidad']);
                $ws->getCell([$columna + 6, $f])->setValue($rd['sDescripcion']);
                $ws->getCell([$columna + 7, $f])->setValue($rd['fImporte']);
                $filaMax = $f;
                $f++;
            }

            // leer movimientos de recepcion
            $regsMovto = $mdlMovto
                ->select('almovimiento.*, IFNULL(u.sNombre, \'\') AS nomUsu')
                ->join('sgusuario u', 'almovimiento.nIdUsuario = u.nIdUsuario', 'left')
                ->where('cOrigen', 'compra')->where('nIdOrigen', $r['nIdCompra'])
                ->findAll();
            $f = $fila;
            $filaMax2 = $f;
            $indViaje = 0;
            foreach ($regsMovto as $rdm) {
                $regsMovtoD = $mdlMovtoDet
                    ->select('almovimientodetalle.nIdArticulo, almovimientodetalle.fCantidad, a.sDescripcion')
                    ->join('alarticulo a', 'almovimientodetalle.nIdArticulo = a.nIdArticulo', 'inner')
                    ->where('nIdMovimiento', $rdm['nIdMovimiento'])
                    ->where('fCantidad <>', 0)
                    ->findAll();
                // $folioViaje = (isset($aViajes[$r['nIdTraspaso']][$indViaje]) ? $aViajes[$r['nIdTraspaso']][$indViaje] : '');
                $ws->getCell([$columna + 8, $f])->setValue(
                    $objFecha->PHPToExcel($rdm['dtAlta'])
                );
                // $ws->getCell([$columna + 8, $f])->setValue('');
                $ws->getCell([$columna + 9, $f])->setValue($rdm['nomUsu']);
                $ws->getCell([$columna + 10, $f])->setValue($rdm['sObservacion']);
                foreach ($regsMovtoD as $rdd) {
                    $ws->getCell([$columna + 11, $f])->setValue($rdd['sDescripcion']);
                    $ws->getCell([$columna + 12, $f])->setValue($rdd['fCantidad']);
                    $aAcum[$rdd['nIdArticulo']]['recepcion'] += floatval($rdd['fCantidad']);
                    $filaMax2 = $f;
                    $f++;
                }
                $indViaje++;
            }
            $filaMax = ($filaMax2 > $filaMax ? $filaMax2 : $filaMax);
            $ws->getStyle([$columna, $fila, $columna + 12, $filaMax])->getBorders()->getOutline()->setBorderStyle('thin');
            $fila = ++$filaMax;
        }
        $ws->getStyle([1, 2, 1, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $ws->getStyle([1, 2, 1, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([8, 2, 8, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([9, 2, 9, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $ws->getStyle([9, 2, 9, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([6, 2, 6, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([13, 2, 13, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        for ($i = 1; $i <= 13; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        $ws->freezePane('A2');
    }

    private function generaEntradas(Spreadsheet &$wb, &$aAcum, $sucursal, $filtro, $objFecha)
    {
        // traspaso, detalle de traspaso, 
        $mdlEntrada = new EntradaMdl();
        $mdlEntradaDet = new EntradaDetalleMdl();
        $mdlMovto = new MovimientoMdl();
        $mdlMovtoDet = new MovimientoDetalleMdl();
        $regs = $mdlEntrada->getRegistros($sucursal, 0, 0, $filtro);
        $ws = $wb->createSheet();
        $ws->setTitle('Detalle de Entradas Especiales');
        $fila = 1;
        $columna = 1;
        $ws->getCell([$columna, $fila])->setValue('fecha'); //dtAlta
        $ws->getCell([$columna + 1, $fila])->setValue('folio' . chr(10) . 'entrada');
        // $ws->getCell([$columna + 2, $fila])->setValue('folio' . chr(10) . 'envio');
        // $ws->getCell([$columna + 3, $fila])->setValue('Solicitado a'); //sNombre
        $ws->getCell([$columna + 4, $fila])->setValue('Observaciones'); //sObservacion
        $ws->getCell([$columna + 5, $fila])->setValue('cantidad'); //fCantidad
        $ws->getCell([$columna + 6, $fila])->setValue('Producto'); 
        $ws->getCell([$columna + 7, $fila])->setValue('fecha' . chr(10) . 'recepcion');
        // $ws->getCell([$columna + 8, $fila])->setValue('folio' . chr(10) . 'viaje');
        $ws->getCell([$columna + 9, $fila])->setValue('capturó' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 10, $fila])->setValue('Observacion' . chr(10) . 'recepcion');
        $ws->getCell([$columna + 11, $fila])->setValue('Producto' . chr(10) . 'recepcionado');
        $ws->getCell([$columna + 12, $fila++])->setValue('cantidad' . chr(10) . 'recepcionada');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getFont()->setBold(true);
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setHorizontal('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setVertical('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setWrapText(true);

        foreach ($regs as $r) {
            $f = $fila;
            $fecha = $objFecha->PHPToExcel($r['dtAlta']);
            $ws->getCell([$columna, $f])->setValue($fecha);
            $ws->getCell([$columna + 1, $f])->setValue($r['nIdEntrada']);
            // $ws->getCell([$columna + 2, $f])->setValue('');
            // $ws->getCell([$columna + 3, $f])->setValue('');
            $ws->getCell([$columna + 4, $f])->setValue($r['sObservacion']);

            $regsDet = $mdlEntradaDet->getRegistros($r['nIdEntrada'], 0);
            // se agrega el acumulado por articulo
            $filaMax = $f;
            foreach ($regsDet as $rd) {
                if (!isset($aAcum[$rd['nIdArticulo']])) $aAcum[$rd['nIdArticulo']] = [
                    'nom' => $rd['sDescripcion'],
                    'cant' => 0,
                    'recepcion' => 0,
                ];
                $aAcum[$rd['nIdArticulo']]['cant'] += floatval($rd['fCantidad']);
                $ws->getCell([$columna + 5, $f])->setValue($rd['fCantidad']);
                $ws->getCell([$columna + 6, $f])->setValue($rd['sDescripcion']);
                $filaMax = $f;
                $f++;
            }

            // leer movimientos de recepcion
            $regsMovto = $mdlMovto
                ->select('almovimiento.*, IFNULL(u.sNombre, \'\') AS nomUsu')
                ->join('sgusuario u', 'almovimiento.nIdUsuario = u.nIdUsuario', 'left')
                ->where('cOrigen', 'entrada')->where('nIdOrigen', $r['nIdEntrada'])
                ->findAll();
            $f = $fila;
            $filaMax2 = $f;
            $indViaje = 0;
            foreach ($regsMovto as $rdm) {
                $regsMovtoD = $mdlMovtoDet
                    ->select('almovimientodetalle.nIdArticulo, almovimientodetalle.fCantidad, a.sDescripcion')
                    ->join('alarticulo a', 'almovimientodetalle.nIdArticulo = a.nIdArticulo', 'inner')
                    ->where('nIdMovimiento', $rdm['nIdMovimiento'])
                    ->where('fCantidad <>', 0)
                    ->findAll();
                // $folioViaje = (isset($aViajes[$r['nIdTraspaso']][$indViaje]) ? $aViajes[$r['nIdTraspaso']][$indViaje] : '');
                $ws->getCell([$columna + 7, $f])->setValue(
                    $objFecha->PHPToExcel($rdm['dtAlta'])
                );
                // $ws->getCell([$columna + 8, $f])->setValue('');
                $ws->getCell([$columna + 9, $f])->setValue($rdm['nomUsu']);
                $ws->getCell([$columna + 10, $f])->setValue($rdm['sObservacion']);
                foreach ($regsMovtoD as $rdd) {
                    $ws->getCell([$columna + 11, $f])->setValue($rdd['sDescripcion']);
                    $ws->getCell([$columna + 12, $f])->setValue($rdd['fCantidad']);
                    $aAcum[$rdd['nIdArticulo']]['recepcion'] += floatval($rdd['fCantidad']);
                    $filaMax2 = $f;
                    $f++;
                }
                $indViaje++;
            }
            $filaMax = ($filaMax2 > $filaMax ? $filaMax2 : $filaMax);
            $ws->getStyle([$columna, $fila, $columna + 12, $filaMax])->getBorders()->getOutline()->setBorderStyle('thin');
            $fila = ++$filaMax;
        }
        $ws->getStyle([1, 2, 1, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $ws->getStyle([1, 2, 1, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([8, 2, 8, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        $ws->getStyle([8, 2, 8, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([6, 2, 6, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([13, 2, 13, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        for ($i = 1; $i <= 13; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        $ws->freezePane('A2');
    }

    private function generaResumen(Spreadsheet &$wb, &$aAcum, $sucursal)
    {
        $sucursalMdl = new SucursalMdl();
        $regSucursal = $sucursalMdl->getRegistros($sucursal);
        $ws = $wb->setActiveSheetIndex(0);
        $columna = 1;
        $ws->getCell('A2')->setValue('Producto');
        $ws->getCell('B2')->setValue('Cantidad' . chr(10) . 'Solicitada');
        $ws->getCell('C2')->setValue('Cantidad' . chr(10) . 'Recibida');
        $ws->getStyle([1, 1, 3, 2])->getAlignment()->setHorizontal('center');
        $ws->getStyle([1, 1, 3, 2])->getAlignment()->setVertical('center');
        $ws->getStyle([1, 1, 3, 2])->getFont()->setBold(true);
        $ws->getStyle('A2:C2')->getBorders()->getAllBorders()->setBorderStyle('thin');
        $ws->getStyle('A2:C2')->getAlignment()->setWrapText(true);

        $ws->getStyle([$columna, 1, $columna + 12, 1])->getFont()->setBold(true);
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setHorizontal('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setVertical('center');
        $ws->getStyle([$columna, 1, $columna + 12, 1])->getAlignment()->setWrapText(true);
        $ws->setTitle('Resumen');
        $ws->mergeCells('A1:C1');
        $ws->getStyle('A1:C1')->getAlignment()->setVertical('center');
        $ws->getStyle('A1:C1')->getAlignment()->setHorizontal('center');
        $ws->getStyle('A1:C1')->getFont()->setBold(true)->setSize(14);
        $ws->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle('thin');
        $ws->getCell('A1')->setValue($regSucursal['sDescripcion']);



        $fila = 3;
        foreach ($aAcum as $k => $v) {
            $ws->getCell('A' . $fila)->setValue($v['nom']);
            $ws->getCell('B' . $fila)->setValue($v['cant']);
            $ws->getCell('C' . $fila)->setValue($v['recepcion']);
            $fila++;
        }
        $ws->getStyle('A3:C' . $fila)->getBorders()->getOutline()->setBorderStyle('thin');
        for ($i = 1; $i <= 3; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
    }
}
