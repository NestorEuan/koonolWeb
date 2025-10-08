<?php

namespace App\Controllers\Ventas\Reportes;

use App\Controllers\BaseController;
use App\Models\Seguridad\UsuarioMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\VentasMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RepResumenVentasCorte extends BaseController
{
    public function resCortes()
    {
        $this->validaSesion();
        $mdlUsuarios = new UsuarioMdl();
        $reg = $mdlUsuarios->getRegistros(false, false, $this->nIdSucursal);

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/reportes/resumenCortes');
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function getusuarios()
    {
        $f = initFechasFiltro($this->request->getVar('dIni'), $this->request->getVar('dFin'), 'M');
        $mdlCorteCaja = new CorteCajaMdl();
        $regs = $mdlCorteCaja->repCorteVentas($f[0], $f[1], $this->nIdSucursal, true);
        return json_encode(['ok' => '1', 'lst' => $regs]);
    }

    /*
    public function resumenTTVentasXLS()
    {
        $aDias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $aMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();
        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'), 'M');

        $mdlVenta = new VentasMdl();
        $regs = $mdlVenta->repVentas($f[0], $f[1], $this->nIdSucursal);

        $aAcum = [];
        $aTiposPagos = [];

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();

        $ws->getCell([1, 1])->setValue('Reporte de Resumen de Ventas del ' .
            $f[0]->format('Y-m-d') . ' al ' . $f[1]->format('Y-m-d'))->getStyle()
            ->getFont()->setBold(true)->setSize(20);

        $fila = 3;
        $col = 1;

        $ws->getCell([$col++, $fila])->setValue('Folio Remision');
        $ws->getCell([$col++, $fila])->setValue('Fecha');
        $ws->getCell([$col++, $fila])->setValue('Importe Venta');
        $ws->getCell([$col++, $fila])->setValue('Abonos');
        $ws->getCell([$col++, $fila])->setValue('Saldo');
        $ws->getCell([$col++, $fila])->setValue('Forma de Pago');
        $estilo = $ws->getStyle([1, $fila, 6, $fila]);
        $estilo->getBorders()->getOutline()->setBorderStyle('medium');
        $estilo->getFont()->setBold(true);
        $estilo->getAlignment()->setHorizontal('center');

        $sumAbonos = 0;
        $idVentaProcesado = '0';
        $bCreditoProcesado = '0';
        $filaProcesada = $fila + 1;
        $rAnterior = false;
        $bCambioFecha = false;
        $dFechaActual = '';
        foreach ($regs as $r) {
            if ($r['conCredito'] == '1') continue;
            if ($dFechaActual != substr($r['dtAlta'], 0, 10)) {
                if ($dFechaActual != '') {
                    $ws->getStyle([1, $fila, 6, $fila])->getBorders()->getBottom()->setBorderStyle('thick');
                }
                $dFechaActual = substr($r['dtAlta'], 0, 10);
            }
            if ($r['nIdVentas'] !== $idVentaProcesado) {
                $fila++;

                $ws->getCell([1, $fila])->setValue($r['nFolioRemision']);
                $ws->getCell([2, $fila])->setValue(
                    $objFecha->PHPToExcel(date("Y/m/d", strtotime($r['dtAlta'])))
                );
                $ws->getCell([3, $fila])->setValue(round(floatval($r['nTotal']), 2));
                $ws->getCell([5, $fila])->setValue(round(floatval($r['nSaldo']), 2));
                $ws->getCell([6, $fila])->setValue($r['sLeyenda']);

                if ($bCreditoProcesado == '1') {
                    $ws->getCell([4, $filaProcesada])->setValue($sumAbonos);
                    $importe = $sumAbonos;
                } else {
                    $ws->getCell([4, $filaProcesada])->setValue('');
                }

                if ($r['conCredito'] == '0') {
                    $importe = round(floatval($r['nTotal']), 2);
                    $this->agregaAcumuladoPorFecha($aAcum, $r, $importe, $aTiposPagos, $r['dtAlta']);
                } else {
                    if ($r['dtPago'] != '') {
                        $cFecha = $r['dtPago'];
                        $importe = round(floatval($r['pagoCredito']), 2);
                        $this->agregaAcumuladoPorFecha($aAcum, $r, $importe, $aTiposPagos, $cFecha);
                    }
                }

                $idVentaProcesado = $r['nIdVentas'];
                $bCreditoProcesado = $r['conCredito'];
                $filaProcesada = $fila;
                $sumAbonos = round(floatval($r['pagoCredito']), 2);
            } else {
                $importe = round(floatval($r['pagoCredito']), 2);
                $sumAbonos += $importe;
                $this->agregaAcumuladoPorFecha($aAcum, $r, $importe, $aTiposPagos, $r['dtPago']);
            }
        }
        $ws->getStyle([1, 4, 1, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([2, 4, 2, $fila])->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');
        $ws->getStyle([3, 4, 5, $fila])->getNumberFormat()->setFormatCode('#,##0.00');

        ksort($aTiposPagos);
        $nCont = 0;
        foreach ($aTiposPagos as $k => $v) $aTiposPagos[$k][0] = $nCont++;

        $fila = 5;
        $ws->getCell([13, $fila])->setValue('Fecha');
        $col = 14;
        foreach ($aTiposPagos as $r) {
            $ws->getCell([$col++, $fila])->setValue($r[1]);
        }
        $col--;
        $estilo = $ws->getStyle([13, $fila, $col, $fila]);
        $estilo->getBorders()->getOutline()->setBorderStyle('medium');
        $estilo->getFont()->setBold(true);
        $estilo->getAlignment()->setHorizontal('center');

        $fila++;
        foreach ($aAcum as $k => $r) {
            $fecha = $objFecha->PHPToExcel(
                date(
                    "Y/m/d",
                    strtotime(substr($k, 0, 4) . '-' . substr($k, 4, 2) . '-' . substr($k, 6, 2))
                )
            );

            $ws->getCell([13, $fila])->setValue($fecha);
            foreach ($r as $kk => $v) {
                $ws->getCell([14 + intval($aTiposPagos[$kk][0]), $fila])->setValue($v);
            }

            $fila++;
        }
        $ws->getStyle([13, 6, 13, $fila])->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');
        $ws->getStyle([14, 6, $col, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        for ($c = 14; $c <= $col; $c++) {
            $columna = $ws->getCell([$c, $fila])->getColumn();
            $formula = '=SUM(' . $columna . '6:' . $columna . strval($fila - 1) . ')';
            $ws->getCell([$c, $fila])->setValue($formula);
        }
        // $ws->setSelectedCells([14, $fila, $col, $fila])->setValue();

        $ws->getColumnDimensionByColumn(1)->setWidth(15);
        for ($i = 2; $i <= 6; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        for ($i = 13; $i <= $col; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);

        $xlsx = new Xlsx($wb);
        $nomarch = 'assets/xlsresven' . $this->nIdSucursal . '.xlsx';
        $xlsx->save($nomarch);

        $wb->disconnectWorksheets();
        unset($wb);

        return $this->response->download($nomarch, null);
    }
*/
    public function resumenVentasXLS()
    {
        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'), 'M');
        $usu = $this->request->getVar('lstUsu') ?? '0';

        $mdlCorteCaja = new CorteCajaMdl();
        $regs = $mdlCorteCaja->repCorteVentas($f[0], $f[1], $this->nIdSucursal, false, ($usu == '0' ? false : $usu));

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();

        $aAcum = [];
        $aTiposPagos = [];
        $aVtFec = [];
        $dFecAux = new DateTime();

        $columnaTrabajo = 1;
        $fila = 1;
        $filaInicialCorte = $fila;
        $numCajaActual = '';
        $numCorteActual = '';
        $acumCorte = 0;
        $acumCorteNC = 0;
        $FolioRemisionActual = '';
        foreach ($regs as $r) {
            //if ($r['conCredito'] == '1') continue;
            if ($r['nIdCorte'] != $numCorteActual) {
                // total del corte, estilo del corte
                if ($numCorteActual != '') $this->pieCorte($acumCorte, $acumCorteNC, $columnaTrabajo, $fila, $ws, $filaInicialCorte);
                if ($r['nNumCaja'] != $numCajaActual) {
                    $ws->getColumnDimensionByColumn($columnaTrabajo)->setWidth(5);
                    $ws->getColumnDimensionByColumn($columnaTrabajo + 1)->setWidth(14);
                    $ws->getColumnDimensionByColumn($columnaTrabajo + 2)->setWidth(18);
                    $ws->getColumnDimensionByColumn($columnaTrabajo + 3)->setWidth(14);
                    $ws->getColumnDimensionByColumn($columnaTrabajo + 4)->setWidth(14);
                    $ws->getColumnDimensionByColumn($columnaTrabajo + 5)->setWidth(15);
                    $ws->getColumnDimensionByColumn($columnaTrabajo + 6)->setWidth(15);
                    $ws->getColumnDimensionByColumn($columnaTrabajo + 7)->setWidth(5);
                    if ($numCajaActual != '') {
                        $columnaTrabajo += 9;
                    }
                    $fila = 1;
                    $numCajaActual = $r['nNumCaja'];
                } else {
                    $fila += 3;
                }
                $acumCorte = 0.0;
                $acumCorteNC = 0.0;
                $filaInicialCorte = $fila;
                $numCorteActual = $r['nIdCorte'];
                $this->tituloCorte($r, $columnaTrabajo, $fila, $ws);
                $fila++;
            }
            $importe = round(floatval($r['nTotal']), 2);
            $importePagado = round(floatval($r['nImportePagado']), 2);
            $importeNC = round(floatval($r['nImporteNC']), 2);

            if ($FolioRemisionActual !== $r['nFolioRemision']) {
                $acumCorte += $importe;
                $acumCorteNC += $importeNC;
                $FolioRemisionActual = $r['nFolioRemision'];
                if ($importeNC === 0.0) $importeNC = false;
            } else {
                $importe = false;
                $importeNC = false;
            }
            $ws->getCell([$columnaTrabajo + 1, $fila])->setValue($r['nFolioRemision']);
            $ws->getCell([$columnaTrabajo + 2, $fila])->setValue($r['sNombre']);
            $ws->getCell([$columnaTrabajo + 3, $fila])->setValue($importe === false ? '' : $importe);
            $ws->getCell([$columnaTrabajo + 4, $fila])->setValue($r['sLeyenda']);
            $ws->getCell([$columnaTrabajo + 5, $fila])->setValue($importePagado);
            $ws->getCell([$columnaTrabajo + 6, $fila++])->setValue($importeNC === false ? '' : $importeNC);
            // detalle de corte
            $this->agregaAcumuladoPorFecha($aAcum, $r, $importePagado, ($importeNC === false ? 0 : $importeNC), $aTiposPagos, $r['dtApertura']);
            $dFecAuxInd = $dFecAux->modify($r['dtAlta'])->format('Ymd');
            if (!isset($aVtFec[$dFecAuxInd])) $aVtFec[$dFecAuxInd] = [];
            $aVtFec[$dFecAuxInd][] = [$r['nIdVentas'], $r['nFolioRemision'], $r['tipopago']];
        }
        if ($numCorteActual != '') {
            $this->pieCorte($acumCorte, $acumCorteNC, $columnaTrabajo, $fila, $ws, $filaInicialCorte);
            $ws->getColumnDimensionByColumn($columnaTrabajo)->setWidth(5);
            $ws->getColumnDimensionByColumn($columnaTrabajo + 1)->setWidth(14);
            $ws->getColumnDimensionByColumn($columnaTrabajo + 2)->setWidth(14);
            $ws->getColumnDimensionByColumn($columnaTrabajo + 3)->setWidth(14);
            $ws->getColumnDimensionByColumn($columnaTrabajo + 4)->setWidth(14);
            $ws->getColumnDimensionByColumn($columnaTrabajo + 5)->setWidth(15);
            $ws->getColumnDimensionByColumn($columnaTrabajo + 6)->setWidth(15);
            $ws->getColumnDimensionByColumn($columnaTrabajo + 7)->setWidth(5);
        }
        $columnaTrabajo += 9;
        $this->tablaResumenPorTipoPago($aAcum, $aTiposPagos, $columnaTrabajo, $ws);
        $columnaTrabajo += 4;
        $this->tablaResumenPorRemision($aVtFec, $ws, $columnaTrabajo);

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repmm');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('reporteresven' . $this->nIdSucursal . '.xlsx', $text);
    }

    private function tituloCorte(&$r, $col, &$fil, Worksheet &$ws)
    {
        $aDias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $aMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $fec = new DateTime($r['dtApertura']);
        $sF = $fec->format('dmYw');
        $sH = $fec->format('h:i');
        $fecha = $aDias[intval(substr($sF, 8))] . ', ' .
            substr($sF, 0, 2) . ' de ' . $aMeses[intval(substr($sF, 2, 2)) - 1] .
            ' del ' . substr($sF, 4, 4) . '. (' . $sH . ')';

        $ws->getCell([$col, $fil++])->setValue('Caja ' . $r['nNumCaja'] . '(' . $r['nomUsuario'] . ')');
        $ws->getCell([$col, $fil])->setValue($fecha);
        $ws->mergeCells([$col, $fil - 1, $col + 7, $fil - 1]);
        $ws->mergeCells([$col, $fil, $col + 7, $fil]);
        $estilo = $ws->getStyle([$col, $fil - 1, $col + 6, $fil]);
        $estilo->getFont()->setBold(true)->setSize(14);
        $estilo->getAlignment()->setHorizontal('center');
        $fil++;
        $ws->getCell([$col + 1, $fil])->setValue('Remision');
        $ws->getCell([$col + 2, $fil])->setValue('Cliente');
        $ws->getCell([$col + 3, $fil])->setValue('Total');
        $ws->getCell([$col + 4, $fil])->setValue('Tipo Pago');
        $ws->getCell([$col + 5, $fil])->setValue('Importe Tipo');
        $ws->getCell([$col + 6, $fil])->setValue('Nota de Cred.');
        $estilo = $ws->getStyle([$col, $fil, $col + 7, $fil]);
        $estilo->getFont()->setBold(true)->setSize(14);
        $estilo->getAlignment()->setHorizontal('center');
        $estilo->getBorders()->getAllBorders()->setBorderStyle('thin');
    }

    private function pieCorte($acumCorte, $acumCorteNC, $col, &$fil, Worksheet &$ws, $filaInicialCorte)
    {
        $ws->getCell([$col + 1, $fil])->setValue('Total');
        $ws->getCell([$col + 3, $fil])->setValue($acumCorte - $acumCorteNC);
        $ws->getStyle([$col + 1, $fil, $col + 1, $fil])->getFill()->setFillType('solid')->getStartColor()->setARGB('FF00B0F0');
        // $ws->me$ws->getStyle([$col, $fil, $col + 3, $fil]);rgeCells([$col, $fil, $col + 3, $fil]);
        $estilo = $ws->getStyle([$col, $fil, $col + 7, $fil]);
        $estilo->getFont()->setBold(true);
        // $estilo->getAlignment()->setHorizontal('center');
        $estilo->getBorders()->getAllBorders()->setBorderStyle('thin');
        $estilo = $ws->getStyle([$col, $filaInicialCorte, $col + 7, $fil]);
        $estilo->getBorders()->getOutline()->setBorderStyle('thin');
        $estilo = $ws->getStyle([$col + 1, $filaInicialCorte, $col + 1, $fil]);
        $estilo->getAlignment()->setHorizontal('center');
        $estilo = $ws->getStyle([$col + 3, $filaInicialCorte, $col + 3, $fil]);
        $estilo->getNumberFormat()->setFormatCode('$* #,##0.00');  // _-$* #,##0.00_-;-$* #,##0.00_-;_-$* "-"??_-;_-@_-
        $estilo = $ws->getStyle([$col + 5, $filaInicialCorte, $col + 6, $fil]);
        $estilo->getNumberFormat()->setFormatCode('$* #,##0.00');  // _-$* #,##0.00_-;-$* #,##0.00_-;_-$* "-"??_-;_-@_-
    }

    private function tablaResumenPorTipoPago(&$aAcum, &$aTiposPagos, &$columna, Worksheet &$ws)
    {
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();
        ksort($aTiposPagos);
        $nCont = 0;
        foreach ($aTiposPagos as $k => $v) $aTiposPagos[$k][0] = $nCont++;

        $fila = 5;
        $ws->getCell([$columna, $fila])->setValue('Fecha');
        $col = $columna + 1;
        $colImportes = $col;
        foreach ($aTiposPagos as $r) {
            $ws->getCell([$col++, $fila])->setValue($r[1]);
        }
        $ws->getCell([$col, $fila])->setValue('Total');
        // $col--;
        $estilo = $ws->getStyle([$columna, $fila, $col, $fila]);
        $estilo->getBorders()->getOutline()->setBorderStyle('medium');
        $estilo->getFont()->setBold(true);
        $estilo->getAlignment()->setHorizontal('center');

        $fila++;
        $granTotal = 0;
        foreach ($aAcum as $k => $r) {
            $fecha = $objFecha->PHPToExcel(
                date(
                    "Y/m/d",
                    strtotime(substr($k, 0, 4) . '-' . substr($k, 4, 2) . '-' . substr($k, 6, 2))
                )
            );

            $ws->getCell([$columna, $fila])->setValue($fecha);
            $sumaFecha = 0;
            foreach ($r as $kk => $v) {
                $ws->getCell([$colImportes + intval($aTiposPagos[$kk][0]), $fila])->setValue($v);
                if ($kk == '999')
                    $sumaFecha -= $v;
                else
                    $sumaFecha += $v;
            }
            $ws->getCell([$col, $fila])->setValue($sumaFecha);
            $granTotal += $sumaFecha;
            $fila++;
        }
        $ws->getStyle([$columna, 6, $columna, $fila])->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');
        $ws->getStyle([$colImportes, 6, $col, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        for ($c = $colImportes; $c <= $col; $c++) {
            $scolumna = $ws->getCell([$c, $fila])->getColumn();
            $formula = '=SUM(' . $scolumna . '6:' . $scolumna . strval($fila - 1) . ')';
            $ws->getCell([$c, $fila])->setValue($formula);
        }
        for ($i = $columna; $i <= $col; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        $ws->getStyle([$columna, 6, $col, $fila])->getBorders()->getAllBorders()->setBorderStyle('thin');
        $ws->getStyle([$columna, $fila, $col, $fila])->getBorders()->getOutline()->setBorderStyle('thick');
        $ws->getStyle([$col, 6, $col, $fila])->getFont()->setBold(true);
        $columna = $col;
    }

    private function agregaAcumuladoPorFecha(&$aAcum, &$r, $importe, $importeNC, &$aTipoPag, $dfec)
    {
        $fecha = date("Ymd", strtotime($dfec));
        if (!isset($aAcum[$fecha])) $aAcum[$fecha] = [];
        if (!isset($aAcum[$fecha][$r['tipopago']])) {
            $aAcum[$fecha][$r['tipopago']] = 0;
            if (!isset($aTipoPag[$r['tipopago']])) $aTipoPag[$r['tipopago']] = [count($aTipoPag), $r['sLeyenda']];
        }
        $aAcum[$fecha][$r['tipopago']] += $importe;
        if ($importeNC > 0) {
            if (!isset($aAcum[$fecha]['999'])) {
                $aAcum[$fecha]['999'] = 0;
                if (!isset($aTipoPag['999'])) $aTipoPag['999'] = [count($aTipoPag), 'NC'];
            }
            $aAcum[$fecha]['999'] += $importeNC;
        }
    }

    private function tablaResumenPorRemision(&$aV, Worksheet &$ws, &$columna)
    {
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();
        $mdlVentasD = new VentasDetMdl();
        $ws->getCell([$columna, 3])->setValue('RESUMEN DE CANTIDADES QUE ENTRAN EN EL CORTE');
        $ws->mergeCells([$columna, 3, $columna + 4, 3]);

        $estilo = $ws->getStyle([$columna, 3, $columna + 4, 3]);
        $estilo->getFont()->setBold(true)->setSize(12);
        $estilo->getAlignment()->setHorizontal('center');

        $ws->getCell([$columna, 4])->setValue('Fecha Remision');
        $ws->getCell([$columna + 1, 4])->setValue('Remision');
        $ws->getCell([$columna + 2, 4])->setValue('ID articulo');
        $ws->getCell([$columna + 3, 4])->setValue('Descripcion');
        $ws->getCell([$columna + 4, 4])->setValue('Cantidad');
        $ws->getCell([$columna + 5, 4])->setValue('Precio');
        $ws->getCell([$columna + 6, 4])->setValue('Importe');
        $fila = 5;
        ksort($aV);
        foreach ($aV as $k => $v) {
            $idvant = '0';
            $idtippag = '0';
            foreach ($v as $kk => $vv) {
                if ($idvant != $vv[0]) {
                    $idvant = $vv[0];
                    $idtippag = $vv[2];
                } else {
                    if ($idtippag != $vv[2]) continue;
                }
                $regs = $mdlVentasD
                    ->select('vtventasdet.nCant, vtventasdet.nPrecio, ' .
                        'IFNULL(vdx.nImpComisionTotal - vdx.nImpDescuentoProd - vdx.nImpDescuentoGral, 0) AS nImporteAux, ' .
                        'a.nIdArticulo, a.sDescripcion, v.nFolioRemision, v.dtAlta')
                    ->join('vtventas v', 'vtventasdet.nIdVentas = v.nIdVentas', 'inner')
                    ->join('alarticulo a', 'vtventasdet.nIdArticulo = a.nIdArticulo', 'inner')
                    ->join('vtventasdetaux vdx', 'vtventasdet.nIdVentasDet = vdx.nIdVentasDet', 'left')
                    ->where('vtventasdet.nIdVentas', $vv[0])
                    ->findAll();
                $filaIni = $fila;
                foreach ($regs as $r) {
                    $fecha = $objFecha->PHPToExcel($r['dtAlta']);
                    $nImporte = ($r['nCant'] * $r['nPrecio']) + $r['nImporteAux'];
                    if($r['nCant'] > 0) {
                        $nPrecioUnitario = $nImporte / $r['nCant'];
                    } else {
                        $nPrecioUnitario = 0;
                    }
                    
                    $ws->getCell([$columna, $fila])->setValue($fecha);
                    $ws->getCell([$columna + 1, $fila])->setValue($r['nFolioRemision']);
                    $ws->getCell([$columna + 2, $fila])->setValue($r['nIdArticulo']);
                    $ws->getCell([$columna + 3, $fila])->setValue($r['sDescripcion']);
                    $ws->getCell([$columna + 4, $fila])->setValue($r['nCant']);
                    $ws->getCell([$columna + 5, $fila])->setValue($nPrecioUnitario);
                    $ws->getCell([$columna + 6, $fila++])->setValue($nImporte);
                }
                $ws->getStyle([$columna, $filaIni, $columna + 6, $fila - 1])
                    ->getBorders()->getOutline()->setBorderStyle('thin');
            }
        }
        $ws->getStyle([$columna, 5, $columna, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy;@');

        $ws->getColumnDimensionByColumn($columna)->setWidth(12);
        $ws->getColumnDimensionByColumn($columna + 1)->setWidth(10);
        $ws->getColumnDimensionByColumn($columna + 2)->setWidth(10);
        $ws->getColumnDimensionByColumn($columna + 3)->setWidth(40);
        $ws->getColumnDimensionByColumn($columna + 4)->setWidth(10);
        $ws->getColumnDimensionByColumn($columna + 5)->setWidth(10);
        $ws->getColumnDimensionByColumn($columna + 6)->setWidth(12);
    }
}
