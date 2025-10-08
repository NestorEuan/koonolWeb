<?php

namespace App\Controllers\Almacen;

use App\Controllers\BaseController;
use App\Models\Almacen\EntradaDetalleMdl;
use App\Models\Almacen\EntradaMdl;
use App\Models\Almacen\MovimientoDetalleMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Almacen\SalidaDetalleMdl;
use App\Models\Catalogos\ConfiguracionMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Cardex extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $fi = $this->request->getVar('dFecIni');
        $ff = $this->request->getVar('dFecFin');
        $f = initFechasFiltro($fi, $ff, '30');
        $data = [
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d')
        ];

        // se validan todos los datos
        $id = $this->request->getVar('idProducto') ?? '';
        if ($id != '') {
            $mdlMovto = new MovimientoMdl();
            $data['registros'] = $mdlMovto->getMovtosArticulo($id, $this->nIdSucursal, $f[0], $f[1]);
        } else {
            $data['registros'] = [];
        }

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/cardex', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function exportaXLS()
    {
        $this->validaSesion();
        $fi = $this->request->getVar('dFecIni');
        $ff = $this->request->getVar('dFecFin');
        $f = initFechasFiltro($fi, $ff, '30');
        $data = [
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d')
        ];

        // se validan todos los datos
        $id = $this->request->getVar('idProducto') ?? '';

        if ($id != '') {
            $mdlMovto = new MovimientoMdl();
            $registros = $mdlMovto->getMovtosArticulo($id, $this->nIdSucursal, $f[0], $f[1]);
        } else {
            return;
        }
        $sProducto = $this->request->getVar('sProducto');


        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $fi = $f[0];
        $ff = $f[1];
        // se genera el listado para el reporte

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $ws->getCell([1, 1])->setValue('Reporte de Cardex')
            ->getStyle([1, 1, 11, 1])
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 18
                ]
            ]);
        $ws->getRowDimension(1)->setRowHeight(26);
        $ws->getCell([1, 2])->setValue($sProducto);
        $ws->getCell([1, 3])->setValue(
            'Período: Del ' . $fi->format('d-m-Y') .
                ' al ' . $ff->format('d-m-Y')
        );
        // $ws->getCell([1, 3])->setValue(
        //     'Ordenado por cantidad ' .
        //         ($this->request->getVar('nOrden') == '1' ? 'Vendida' : 'Facturada')
        // );
        $ws->mergeCells([1, 1, 11, 1])->mergeCells([1, 2, 11, 2])->mergeCells([1, 3, 11, 3]);
        $ws->getStyle([1, 1, 11, 3])->applyFromArray([
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
        $ws->getCell([$columna++, $filaTitulo])->setValue('Tipo Movto');
        $ws->getCell([$columna++, $filaTitulo])->setValue('ID Docto');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Remision Origen Entrega');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Origen del Envio');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Remision/ ID Traspaso');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Sucursal');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Con Factura');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Cliente/ Suc. Origen');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Cantidad');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Saldo');
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

        $fila = 5;
        $saldo = 0;
        foreach ($registros as $row) {
            if ($fila == 5) {
                $saldo = round(floatval($row['fSaldoInicial']), 3);
                $ws->getCell([1, $fila])->setValue($objFecha->PHPToExcel(date("Y/m/d", strtotime($row['dtAlta']))));
                $ws->getCell([2, $fila])->setValue('Saldo Inicial');
                $ws->getCell([11, $fila])->setValue($saldo);
                $fila++;
            }
            $saldo += round(floatval($row['fCantidad']), 3);
            $ws->getCell([1, $fila])->setValue($objFecha->PHPToExcel(date("Y/m/d", strtotime($row['dtAlta']))));
            $ws->getCell([2, $fila])->setValue($row['cOrigen']);
            $ws->getCell([3, $fila])->setValue($row['nIdOrigen']);
            if ($row['cOrigen'] == 'entrega') :
                $ws->getCell([4, $fila])->setValue($row['nFolioRemisionEntrega']);
                // $ws->getCell([5, $fila])->setValue($row['nTotal']);
                // $ws->getCell([6, $fila])->setValue($row['nIdArticulo']);
                $ws->getCell([7, $fila])->setValue($row['sucOriEntrega']);
                $ws->getCell([8, $fila])->setValue(intval($row['tieneFacturaEntrega']) > 0 ? 'Si' : '');
                $ws->getCell([9, $fila])->setValue($row['sClienteVEntrega']);
            elseif ($row['cOrigen'] == 'envio') :
                if ($row['nFolioRemisionEnvio'] != '0') :
                    // $ws->getCell([4, $fila])->setValue($row['nFolioRemisionEntrega']);
                    $ws->getCell([5, $fila])->setValue('Venta');
                    $ws->getCell([6, $fila])->setValue($row['nFolioRemisionEnvio']);
                    $ws->getCell([7, $fila])->setValue($row['sucOriEnvio']);
                    $ws->getCell([8, $fila])->setValue(intval($row['tieneFacturaEnvio']) > 0 ? 'Si' : '');
                    $ws->getCell([9, $fila])->setValue($row['sClienteVEnvio']);
                elseif ($row['nIdTraspaso'] != '0') :
                    // $ws->getCell([4, $fila])->setValue($row['nFolioRemisionEntrega']);
                    $ws->getCell([5, $fila])->setValue('Traspaso');
                    $ws->getCell([6, $fila])->setValue($row['nIdTraspaso']);
                    $ws->getCell([7, $fila])->setValue($row['sucOriTraspaso']);
                endif;
            endif;
            $ws->getCell([10, $fila])->setValue(round(floatval($row['fCantidad']), 3));
            $ws->getCell([11, $fila])->setValue($saldo);
            $fila++;
        }
        $ws->getStyle([1, 5, 6, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([8, 5, 8, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([1, 5, 1, $fila])->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');
        $ws->getStyle([10, 5, 11, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([10, 5, 11, $fila])->getAlignment()->setHorizontal('right');

        $ws->getStyle([1, 5, 11, $fila])->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        $ws->getColumnDimensionByColumn(1)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(2)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(3)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(4)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(5)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(6)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(7)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(8)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(9)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(10)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(11)->setAutoSize(true);

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repca');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('repoCardex.xlsx', $text);
    }

    private function corrigesaldos($idMovtoDet)
    {
        $this->validaSesion();
        // movimientos que la cantidad esta al limite y el saldo inicial es mayor a cero
        // saldoInicial > 0 y cantidad = 99999999.99 
        /*
        $mdlMovtoD = new MovimientoDetalleMdl();
        $regsMD = $mdlMovtoD->where('fCantidad', 99999999.99)
            ->where('fSaldoInicial  > ', 0)
            ->findAll();
        foreach ($regsMD as $rmd) {
            $this->corrigesaldosentrada($rmd['nIdArticulo']);
            $this->corrigesaldos2($rmd['nIdSucursal'], $rmd['nIdArticulo']);
            // return;
        }
        */

        // movimientos a partir de un nIdMovimiento
        $this->corrigeApartirMovtoDet($idMovtoDet);
        return json_encode(['ok' => '1', 'msj' => 'Finalizado']);
    }

    private function corrigesaldosedo()
    {
        $mdlConfig = new ConfiguracionMdl();
        $reg = $mdlConfig->where('nIdConfiguracion', 8)->first();
        if ($reg == null) return json_encode(['ok' => '0', 'msj' => 'sin edo']);
        return json_encode(['ok' => '1', 'msj' => ($reg['sValor'] ?? '')]);
    }

    private function corrigeApartirMovtoDet($idMovtoDet)
    {
        $mdlConfig = new ConfiguracionMdl();
        $mdlMovtoD = new MovimientoDetalleMdl();
        $regFiltro = $mdlMovtoD->where('nIdMovimientoDetalle', $idMovtoDet)->findAll();
        $nsaldoini = round(floatval($regFiltro[0]['fSaldoInicial']) + floatval($regFiltro[0]['fCantidad']), 2);
        $regsMD = $mdlMovtoD
            ->where('nIdMovimientoDetalle >', $idMovtoDet)
            ->where('nIdSucursal', $regFiltro[0]['nIdSucursal'])
            ->where('nIdArticulo', $regFiltro[0]['nIdArticulo'])
            ->orderBy('nIdMovimientoDetalle', 'asc')
            ->findAll();
        $ntotal = count($regsMD);
        $ctotal = ' de ' . $ntotal;
        foreach ($regsMD as $k => $rmd) {
            $ncant = round(floatval($rmd['fCantidad']), 2);
            $this->corrigesaldosdetalle($rmd['nIdMovimientoDetalle'], $ncant, $nsaldoini);
            // echo $rmd['nIdMovimiento'] . ' - ' . $rmd['nIdSucursal'] . ' - ' . $rmd['nIdArticulo'] . ' - ' . $ncant . ' - ' . round(floatval($nsaldoini), 3) . '<br>';
            $nsaldoini = $nsaldoini + $ncant;
            // $_SESSION['procesoCorrigeSaldos'] = ($k + 1) . $ctotal;
            // $mdlConfig->where('nIdConfiguracion', 8)
            //     ->update(null, ['sValor' => ($k + 1) . $ctotal]);
        }

        // $mdlConfig->where('nIdConfiguracion', 8)
        //         ->update(null, ['sValor' => 'termino']);
    }

    private function corrigesaldos2($idsucursal, $idarticulo)
    {
        $mdlMovtoD = new MovimientoDetalleMdl();
        $regsMD = $mdlMovtoD->where('nIdSucursal', $idsucursal)
            ->where('nIdArticulo', $idarticulo)
            ->orderBy('nIdMovimiento', 'asc')
            ->findAll();
        $nsaldoini = 0;
        foreach ($regsMD as $rmd) {
            $ncant = round(floatval($rmd['fCantidad']), 2);
            if ($ncant == 99999999.99) $ncant = 1000000;
            if ($ncant == -99999999.99) {
                $ncant = -1000000;
                $this->corrigesaldossalida($rmd['nIdArticulo'], $rmd['nIdMovimiento']);
            }
            $nsaldonew = $ncant + $nsaldoini;
            $this->corrigesaldosdetalle($rmd['nIdMovimientoDetalle'], $ncant, $nsaldoini);
            echo $rmd['nIdMovimiento'] . ' - ' . $rmd['nIdSucursal'] . ' - ' . $rmd['nIdArticulo'] . ' - ' . $ncant . ' - ' . round(floatval($nsaldoini), 3) . '<br>';
            $nsaldoini = $nsaldonew;
        }
        echo 'saldo ' . round(floatval($nsaldoini), 2) . '<br><br><hr><br><br>';
    }

    private function corrigesaldosdetalle($iddetmov, $nCant, $nSaldoIni)
    {
        $mdlMovtoD = new MovimientoDetalleMdl();
        $mdlMovtoD->where('nIdMovimientoDetalle', $iddetmov)
            ->update(null, [
                'fCantidad' => $nCant,
                'fSaldoInicial' => $nSaldoIni,
            ]);
    }
    private function corrigesaldosentrada($idarticulo)
    {
        $mdlEntradaD = new EntradaDetalleMdl();
        $mdlEntradaD->where('nIdEntrada', 1023)
            ->where('nIdArticulo', $idarticulo)
            ->update(null, [
                'fCantidad' => 1000000,
                'fRecibido' => 1000000,
            ]);
    }
    private function corrigesaldossalida($idarticulo, $idmov)
    {
        $mdlMovto = new MovimientoMdl();
        $r = $mdlMovto->where('nIdMovimiento', $idmov)->first();
        if ($r == null) return;
        $mdlSalidaD = new SalidaDetalleMdl();
        $mdlSalidaD->where('nIdSalida', $r['nIdOrigen'])
            ->where('nIdArticulo', $idarticulo)
            ->update(null, [
                'fCantidad' => 1000000,
                'fRecibido' => 1000000,
            ]);
    }
}
