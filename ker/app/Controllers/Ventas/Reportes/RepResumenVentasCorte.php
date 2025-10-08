<?php

namespace App\Controllers\Ventas\Reportes;

use App\Controllers\BaseController;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Ventas\CorteCajaMdl;
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


        echo view('templates/header', $this->dataMenu);
        echo view('ventas/reportes/resumenCortes');
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function initResumenVentasXLS()
    {
        $nom = tempnam('assets', 'repedo');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.txt';
        return json_encode([
            'arch' => $ainfo['filename'],
        ]);
    }

    protected $acumulaCortes = [];
    protected $tiposPagos = [];
    protected $total = 0;
    protected $actual = 0;
    protected $nombreSucursal = '';
    protected $nomArchEdo = '';

    public function resumenVentasXLS()
    {
        $todasLasSucursales = isset($this->aPermiso['oVerTodasSuc']);
        $sucursalMdl = new SucursalMdl();
        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'), 'M');
        $wb = new Spreadsheet();
        $this->nomArchEdo = 'assets/' . $this->request->getVar('nomArch') . '.txt';
        $nomArchEdo = $this->request->getVar('nomArch');

        if ($todasLasSucursales) {
            $aSucursales = $sucursalMdl->getRegistros();
            $this->total = count($aSucursales);
            $nCont = 0;
            foreach ($aSucursales as $r) {
                $ws = $wb->createSheet();
                $nCont++;
                $this->actual = $nCont;
                $this->resumenVentasXLS1($r, $f, $ws);
            }
            $textoArchivo = 'Generando Acumulado|1|1|1|1';
            file_put_contents($this->nomArchEdo, $textoArchivo);
            $ws = $wb->setActiveSheetIndex(0);
            $this->resumenVentasXLSGeneral($ws);
            $textoArchivo = 'Exportando|1|1|1|1';
            file_put_contents($this->nomArchEdo, $textoArchivo);
        } else {
            $this->total = 1;
            $this->actual = 1;
            $aSucursales = $sucursalMdl->getRegistros($this->nIdSucursal);
            $ws = $wb->getActiveSheet();
            $this->resumenVentasXLS1($aSucursales, $f, $ws);
        }

        $wb->setActiveSheetIndex(0);
        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repmm');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

       
        unlink($nom);

        return json_encode([
            'ok' => 1,
            'arch' => $ainfo['filename'],
            'archedo' => $nomArchEdo,
        ]);

    }

    public function resumenVentasXLS1(&$aSucursal, &$f, Worksheet &$ws)
    {
        $this->nombreSucursal = $aSucursal['sDescripcion'];
        $textoArchivo = 'Generando sucursal... ' . $this->nombreSucursal . '|' .
        $this->total . '|' . $this->actual; 
        $mdlCorteCaja = new CorteCajaMdl();
        $regs = $mdlCorteCaja->repCorteVentas($f[0], $f[1], $aSucursal['nIdSucursal']);

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
        $total = count($regs);
        $nCont = 0;
        foreach ($regs as $r) {
            $nCont++;
            $textoArchivo2 = $textoArchivo . '|' .$total . '|' . $nCont;
            file_put_contents($this->nomArchEdo, $textoArchivo2);
            //if ($r['conCredito'] == '1') continue;
            if ($r['nIdCorte'] != $numCorteActual) {
                // total del corte, estilo del corte
                // if ($numCorteActual != '') $this->pieCorte($acumCorte, $acumCorteNC, $columnaTrabajo, $fila, $ws, $filaInicialCorte);
                if ($r['nNumCaja'] != $numCajaActual) {
                    // $ws->getColumnDimensionByColumn($columnaTrabajo)->setWidth(5);
                    // $ws->getColumnDimensionByColumn($columnaTrabajo + 1)->setWidth(14);
                    // $ws->getColumnDimensionByColumn($columnaTrabajo + 2)->setWidth(14);
                    // $ws->getColumnDimensionByColumn($columnaTrabajo + 3)->setWidth(14);
                    // $ws->getColumnDimensionByColumn($columnaTrabajo + 4)->setWidth(15);
                    // $ws->getColumnDimensionByColumn($columnaTrabajo + 5)->setWidth(15);
                    // $ws->getColumnDimensionByColumn($columnaTrabajo + 6)->setWidth(5);
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
                // $this->tituloCorte($r, $columnaTrabajo, $fila, $ws);
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
            // $ws->getCell([$columnaTrabajo + 1, $fila])->setValue($r['nFolioRemision']);
            // $ws->getCell([$columnaTrabajo + 2, $fila])->setValue($importe === false ? '' : $importe);
            // $ws->getCell([$columnaTrabajo + 3, $fila])->setValue($r['sLeyenda']);
            // $ws->getCell([$columnaTrabajo + 4, $fila])->setValue($importePagado);
            // $ws->getCell([$columnaTrabajo + 5, $fila++])->setValue($importeNC === false ? '' : $importeNC);
            // detalle de corte
            $this->agregaAcumuladoPorFecha($aAcum, $r, $importePagado, ($importeNC === false ? 0 : $importeNC), $aTiposPagos, $r['dtApertura']);
            $dFecAuxInd = $dFecAux->modify($r['dtAlta'])->format('Ymd');
            if (!isset($aVtFec[$dFecAuxInd])) $aVtFec[$dFecAuxInd] = [];
            $aVtFec[$dFecAuxInd][] = [$r['nIdVentas'], $r['nFolioRemision'], $r['tipopago']];
        }
        if ($numCorteActual != '') {
            // $this->pieCorte($acumCorte, $acumCorteNC, $columnaTrabajo, $fila, $ws, $filaInicialCorte);
            // $ws->getColumnDimensionByColumn($columnaTrabajo)->setWidth(5);
            // $ws->getColumnDimensionByColumn($columnaTrabajo + 1)->setWidth(14);
            // $ws->getColumnDimensionByColumn($columnaTrabajo + 2)->setWidth(14);
            // $ws->getColumnDimensionByColumn($columnaTrabajo + 3)->setWidth(14);
            // $ws->getColumnDimensionByColumn($columnaTrabajo + 4)->setWidth(15);
            // $ws->getColumnDimensionByColumn($columnaTrabajo + 5)->setWidth(15);
            // $ws->getColumnDimensionByColumn($columnaTrabajo + 6)->setWidth(5);
        }
        // $columnaTrabajo += 8;
        $columnaTrabajo = 2;
        $this->tablaResumenPorTipoPago($aAcum, $aTiposPagos, $columnaTrabajo, $ws);
        $ws->setTitle($aSucursal['sClave']);
        // $columnaTrabajo = 4;
        // $this->tablaResumenPorRemision($aVtFec, $wb, $columnaTrabajo);
        $ws->getCell([2, 1])->setValue($aSucursal['sDescripcion']);

    }

    public function resumenVentasXLSGeneral(Worksheet &$ws)
    {
        $columnaTrabajo = 2;
        $this->tablaResumenPorTipoPago($this->acumulaCortes, $this->tiposPagos, $columnaTrabajo, $ws);
        $ws->setTitle('Acumulado');
    }

    public function descargaVentasXLS() {
        $arch = $this->request->getVar('archivo');
        $arch = 'assets/' . $arch . '.xlsx';
        $archedo = $this->request->getVar('nomArchEdo');
        $archedo1 = 'assets/' . $archedo . '.txt';
        $archedo2 = 'assets/' . $archedo . '.tmp';


        
        $text = file_get_contents($arch);
        error_reporting(E_ALL & ~E_WARNING);

        unlink($arch);
        unlink($archedo1);
        unlink($archedo2);

        return $this->response->download('reporteresventas.xlsx', $text);
        
    }

    public function consultaVentasXLS() {
        error_reporting(E_ALL & ~E_WARNING);
        $arch = $this->request->getVar('nomArch');
        $arch = 'assets/' . $arch . '.txt';

        $text = file_get_contents($arch);
        if($text === false) {
            return json_encode([
                'ok' => 1,
                'msj' => 'Finalizado',
                'tot' => 1,
                'act' => 0,
                'tot2' => 1,
                'act2' => 0,
            ]);

        } else {
            if($text == '') {
                return json_encode([
                    'ok' => 0,
                    'msj' => '',
                ]);
            }

            $a = explode('|', $text);
            return json_encode([
                'ok' => 1,
                'msj' => $a[0],
                'tot' => $a[1],
                'act' =>  $a[2],
                'tot2' => $a[3],
                'act2' => $a[4],
            ]);
        }

        
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
        if (!isset($this->acumulaCortes[$fecha])) $this->acumulaCortes[$fecha] = [];
        if (!isset($this->acumulaCortes[$fecha][$r['tipopago']])) {
            $this->acumulaCortes[$fecha][$r['tipopago']] = 0;
            if (!isset($this->tiposPagos[$r['tipopago']])) $this->tiposPagos[$r['tipopago']] = [count($this->tiposPagos), $r['sLeyenda']];
        }
        $this->acumulaCortes[$fecha][$r['tipopago']] += $importe;
        if ($importeNC > 0) {
            if (!isset($this->acumulaCortes[$fecha]['999'])) {
                $this->acumulaCortes[$fecha]['999'] = 0;
                if (!isset($this->tiposPagos['999'])) $this->tiposPagos['999'] = [count($this->tiposPagos), 'NC'];
            }
            $this->acumulaCortes[$fecha]['999'] += $importeNC;
        }

        
    }
}
