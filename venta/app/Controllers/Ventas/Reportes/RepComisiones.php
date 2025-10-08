<?php

namespace App\Controllers\Ventas\Reportes;

use App\Controllers\BaseController;
use App\Models\Catalogos\AgenteVentasMdl;
use DateInterval;
use DateTime;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RepComisiones extends BaseController
{

    public function index()
    {
        $this->validaSesion();
        $fi = $this->request->getVar('dFecIni');
        $ff = $this->request->getVar('dFecFin');
        $f = initFechasFiltro($fi, $ff, '30');
        $mdlAgente = new AgenteVentasMdl();

        $data = [
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d'),
            'lstAgentes' => $mdlAgente->getRegistros(false, false, 1),
            'registro' => [
                'idAgente' => 0
            ]
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/reportes/repcomisiones', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function exportaXLS()
    {
        $session = session();
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $fi = new DateTime($this->request->getVar('dFecIni') ?? '');
        $ff = new DateTime($this->request->getVar('dFecFin') ?? '');
        $sfi = $fi->format('Y-m-d');
        $sff = $ff->add((new DateInterval('P1D')))->format('Y-m-d');
        $idAgente = $this->request->getVar('idAgente') ?? '1';
        $mdlAgente = new AgenteVentasMdl();
        $regAgente = $mdlAgente->getRegistros($idAgente);
        // se genera el listado para el reporte
        // siendo complejo, se arma de un query

        $db = \Config\Database::connect();
        $registros = $db->query(
            'SELECT t.*, v.dtAlta, v.nFolioRemision,  ' .
                'vd.nIdArticulo, vd.nPrecio, vd.nCant, a.sDescripcion AS nomProducto, cla.*, ' .
                'IFNULL(vdx.nImpComisionTotal - vdx.nImpDescuentoProd - vdx.nImpDescuentoGral, 0) AS nDescuentoTotal, ' .
                ' ((vd.nPrecio * vd.nCant) - IFNULL(vdx.nImpComisionTotal - vdx.nImpDescuentoProd - vdx.nImpDescuentoGral, 0) ) AS nImporte, ' .
                ' cli.sNombre AS nomCliente, s.sDescripcion AS nomSucursal ' .
                'FROM ( ' .
                'SELECT v.nIdVentas, vca.nIdAgenteVentas, v.dtAlta AS dFecAplica ' .
                'FROM vtventas v ' .
                'INNER JOIN vtventasconagente vca ON v.nIdVentas = vca.nIdVentas  ' .
                'LEFT JOIN vtventassaldo vs ON v.nIdVentas = vs.nIdVentas ' .
                'WHERE v.dtAlta >= \'' . $sfi . '\' AND v.dtAlta < \'' . $sff . '\' ' .
                'AND vs.nIdVentas IS NULL ' .
                'union ' .
                'SELECT vs.nIdVentas, vca.nIdAgenteVentas, vs.dFecSaldado AS dFecAplica ' .
                'FROM vtventassaldo vs ' .
                'INNER JOIN vtventas v ON vs.nIdVentas = v.nIdVentas ' .
                'INNER JOIN vtventasconagente vca ON v.nIdVentas = vca.nIdVentas  ' .
                'WHERE vs.dFecSaldado >= \'' . $sfi . '\' AND vs.dFecSaldado < \'' . $sff . '\' ' .
                ') t ' .
                'INNER JOIN vtventas v ON t.nIdVentas = v.nIdVentas ' .
                'INNER JOIN vtventasdet vd ON t.nIdVentas = vd.nIdVentas ' .
                'LEFT JOIN vtventasdetaux vdx ON vd.nIdVentasDet = vdx.nIdVentasDet ' .
                'INNER JOIN alarticulo a ON vd.nIdArticulo = a.nIdArticulo ' .
                'INNER JOIN vtcliente cli ON v.nIdCliente = cli.nIdCliente ' .
                'INNER JOIN alartclasificacion cla ON a.nIdArtClasificacion = cla.nIdArtClasificacion ' .
                'INNER JOIN alsucursal s ON v.nIdSucursal = s.nIdSucursal ' .
                'WHERE v.dtBaja IS NULL AND v.cEdo <> \'5\'  ' .
                'AND t.nIdAgenteVentas = ' . $idAgente .
                ' AND cla.nPorcentajeComision > 0 ' .
                ' ORDER BY t.dFecAplica ASC, t.nIdVentas ASC, cla.nIdArtClasificacion ASC'
        )->getResultArray();

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $ws->getCell([1, 1])->setValue('Reporte de Comisiones')
            ->getStyle([1, 1, 10, 1])
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 18
                ]
            ]);
        $ws->getRowDimension(1)->setRowHeight(26);
        $ws->getCell([1, 2])->setValue(
            'Período: Del ' . $fi->format('d-m-Y') .
                ' al ' . $ff->format('d-m-Y')
        );
        $ws->getCell([1, 3])->setValue(
            'Agente de Venta ' . $regAgente['sNombre']
        );
        $ws->mergeCells([1, 1, 10, 1])->mergeCells([1, 2, 10, 2])->mergeCells([1, 3, 10, 3]);
        $ws->getStyle([1, 1, 10, 3])->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => 'medium',
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true
            ]
        ]);

        $columna = 1;
        $filaTitulo = 4;
        $ws->getCell([$columna++, $filaTitulo])->setValue('Fecha');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Sucursal');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Remision');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Fecha Remision');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Cliente');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Cantidad');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Descripcion');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Precio');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Importe');
        $ws->getStyle([1, 4, $columna - 1, 4])->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 12
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true
            ]
        ]);
        $ws->getColumnDimensionByColumn(1)->setWidth(12);
        $ws->getColumnDimensionByColumn(2)->setWidth(40);
        $ws->getColumnDimensionByColumn(3)->setWidth(12);
        $ws->getColumnDimensionByColumn(4)->setWidth(12);
        $ws->getColumnDimensionByColumn(5)->setWidth(40);
        $ws->getColumnDimensionByColumn(6)->setWidth(12);
        $ws->getColumnDimensionByColumn(7)->setWidth(40);
        $ws->getColumnDimensionByColumn(8)->setWidth(12);
        $ws->getColumnDimensionByColumn(9)->setWidth(12);

        $aAcum = [];
        $aTiposCla = [];

        $fila = 5;
        $idVentaActual = '';
        $filaIniVenta = 5;
        foreach ($registros as $row) {
            if ($idVentaActual != $row['nIdVentas']) {
                $columDet = 1;
                if ($fila > 5) {
                    $ws->getStyle([1, $filaIniVenta, 9, $fila - 1])->getBorders()->getOutline()->applyFromArray([
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => '000000'],
                    ]);
                    // $ws->getStyle([1, $filaIniVenta, 9, $fila - 1])->getBorders()->getOutline()->applyFromArray([
                    //     'borders' => [
                    //         'allBorders' => [
                    //             'borderStyle' => 'thick',
                    //             'color' => ['rgb' => '000000']
                    //         ]
                    //     ]
                    // ]);    
                }
                $idVentaActual = $row['nIdVentas'];
                $filaIniVenta = $fila;
                $ws->getCell([$columDet++, $fila])->setValue($objFecha->PHPToExcel(date("Y/m/d", strtotime($row['dFecAplica']))));
                $ws->getCell([$columDet++, $fila])->setValue($row['nomSucursal']);
                $ws->getCell([$columDet++, $fila])->setValue($row['nFolioRemision']);
                $ws->getCell([$columDet++, $fila])->setValue($objFecha->PHPToExcel(date("Y/m/d", strtotime($row['dtAlta']))));
                $ws->getCell([$columDet++, $fila])->setValue($row['nomCliente']);
                $ws->getStyle([1, $fila, 4, $fila])->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '000000']
                        // 'size' => 12
                    ]
                ]);
            } else {
                $columDet = 6;
            }
            $ws->getCell([$columDet++, $fila])->setValue($row['nCant']);
            $ws->getCell([$columDet++, $fila])->setValue($row['nomProducto']);
            $ws->getCell([$columDet++, $fila])->setValue($row['nPrecio']);
            $val = round(floatval($row['nImporte']), 2);
            $ws->getCell([$columDet++, $fila])->setValue($val);
            $ws->getStyle([6, $fila, 9, $fila])->applyFromArray([
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => substr($row['sColor'], 1)],
                    'endColor' => ['argb' => substr($row['sColor'], 1)]
                    // 'size' => 12
                ]
            ]);
            $this->agregaAcumuladoPorFecha($aAcum, $row, $val, 0, $aTiposCla, $row['dFecAplica']);
            $fila++;
        }
        $ws->getStyle([1, 5, 4, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([1, 5, 1, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy;@');
        $ws->getStyle([4, 5, 4, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy;@');
        $ws->getStyle([6, 5, 6, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([8, 5, 9, $fila])->getNumberFormat()->setFormatCode('#,##0.00');

        $this->tablaResumenPorTipoPago($aAcum, $aTiposCla, 12, $ws);

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repcomi');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('repoComisiones.xlsx', $text);
    }

    private function agregaAcumuladoPorFecha(&$aAcum, &$r, $importe, $importeNC, &$aTipoPag, $dfec)
    {
        $fecha = date("Ymd", strtotime($dfec));
        if (!isset($aAcum[$fecha])) $aAcum[$fecha] = [];
        if (!isset($aAcum[$fecha][$r['nIdArtClasificacion']])) {
            $aAcum[$fecha][$r['nIdArtClasificacion']] = 0;
            if (!isset($aTipoPag[$r['nIdArtClasificacion']])) $aTipoPag[$r['nIdArtClasificacion']] = [count($aTipoPag), $r['sClasificacion'], $r['nPorcentajeComision']];
        }
        $aAcum[$fecha][$r['nIdArtClasificacion']] += $importe;
        if ($importeNC > 0) {
            if (!isset($aAcum[$fecha]['999'])) {
                $aAcum[$fecha]['999'] = 0;
                if (!isset($aTipoPag['999'])) $aTipoPag['999'] = [count($aTipoPag), 'NC'];
            }
            $aAcum[$fecha]['999'] += $importeNC;
        }
    }

    private function tablaResumenPorTipoPago(&$aAcum, &$aTiposPagos, $columna, Worksheet &$ws)
    {
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();
        ksort($aTiposPagos);
        $nCont = 0;
        foreach ($aTiposPagos as $k => $v) {
            $aTiposPagos[$k][0] = $nCont++;
        }
        $fila = 5;
        $ws->getCell([$columna, $fila])->setValue('Fecha');
        $col = $columna + 1;
        $colImportes = $col;
        $aTiposPagosPorcentaje = [];
        foreach ($aTiposPagos as $r) {
            $ws->getCell([$col++, $fila])->setValue($r[1]);
            $aTiposPagosPorcentaje[] = $r[2];
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
        $ws->getStyle([$columna, 6, $columna, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy;@');
        $ws->getStyle([$colImportes, 6, $col, $fila + 2])->getNumberFormat()->setFormatCode('#,##0.00');
        $nContCol = 0;
        for ($c = $colImportes; $c <= $col; $c++) {
            $scolumna = $ws->getCell([$c, $fila])->getColumn();
            $formula = '=SUM(' . $scolumna . '6:' . $scolumna . strval($fila - 1) . ')';
            $ws->getCell([$c, $fila])->setValue($formula);
            if($c < $col) $ws->getCell([$c, $fila + 1])->setValue($formula . '*' . round(floatval($aTiposPagosPorcentaje[$nContCol++]) / 100, 4) );
        }

        $scolumnaini = $ws->getCell([$colImportes, $fila])->getColumn();
        $scolumnafin = $ws->getCell([$col - 1, $fila])->getColumn();
        $filacad = $fila + 1;
        $formula = '=SUM(' . $scolumnaini . $filacad . ':' . $scolumnafin . $filacad . ')';
        $ws->getCell([$columna, $fila + 1])->setValue('Comisiones')->getStyle()->getFont()->setBold(true);
        // $ws->getStyle([$columna, 6, $col, $fila])->getFont()->setBold(true);
        $ws->getCell([$col, $fila + 1])->setValue($formula);

        for ($i = $columna; $i <= $col; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        $ws->getStyle([$columna, 6, $col, $fila])->getBorders()->getAllBorders()->setBorderStyle('thin');
        $ws->getStyle([$columna, $fila, $col, $fila])->getBorders()->getOutline()->setBorderStyle('thick');
        $ws->getStyle([$columna, $fila + 1, $col, $fila + 1])->getBorders()->getOutline()->setBorderStyle('thick');
        $ws->getStyle([$col, 6, $col, $fila])->getFont()->setBold(true);
    }
}
