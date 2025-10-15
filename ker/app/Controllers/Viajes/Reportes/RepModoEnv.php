<?php

namespace App\Controllers\Viajes\Reportes;

use App\Controllers\BaseController;
use App\Models\Viajes\ViajeEnvioMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RepModoEnv extends BaseController
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
        //  $id = $this->request->getVar('idProducto') ?? '';
        echo view('templates/header', $this->dataMenu);
        echo view('Viajes/Reportes/repmodoenv', $data);
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
        // si el id del producto esta vacio, enlisto todos los de la sucursal
        $mdlMovto = new ViajeEnvioMdl();

        $registros = $mdlMovto->getEnviosConModoENV($id, $this->nIdSucursal, $f[0], $f[1]);

        $sProducto = $id == '' ? $this->request->getVar('sProducto') : '';


        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $fi = $f[0];
        $ff = $f[1];
        // se genera el listado para el reporte

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $ws->getCell([1, 1])->setValue('Reporte de Envios con Producto en modo ENV')
            ->getStyle([1, 1, 8, 1])
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
        $ws->mergeCells([1, 1, 8, 1])->mergeCells([1, 2, 8, 2])->mergeCells([1, 3, 11, 3]);
        $ws->getStyle([1, 1, 8, 3])->applyFromArray([
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
        $ws->getCell([$columna++, $filaTitulo])->setValue('Viaje');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Envio');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Remision');
        $ws->getCell([$columna++, $filaTitulo])->setValue('traspaso');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Id Art');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Articulo');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Cantidad ENV');
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
            $ws->getCell([1, $fila])->setValue($objFecha->PHPToExcel(date("Y/m/d", strtotime($row['dtAlta']))));
            $ws->getCell([2, $fila])->setValue($row['nIdViaje']);
            $ws->getCell([3, $fila])->setValue($row['nIdEnvio']);
            if ($row['oriDoc'] == 'ventas') {
                $ws->getCell([4, $fila])->setValue($row['folioDoc']);
            } else {
                $ws->getCell([5, $fila])->setValue($row['folioDoc']);
            }
            $ws->getCell([6, $fila])->setValue($row['nIdArticulo']);
            $ws->getCell([7, $fila])->setValue($row['nomArt']);
            $ws->getCell([8, $fila])->setValue($row['fPorRecibir']);
            $fila++;
        }
        $ws->getStyle([1, 5, 6, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([8, 5, 8, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([1, 5, 1, $fila])->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');

        $ws->getStyle([1, 5, 8, $fila])->applyFromArray([
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

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repcaenv');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('repoEnviosENV.xlsx', $text);
    }

}
