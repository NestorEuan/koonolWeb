<?php

namespace App\Controllers\Ventas\Reportes;

use App\Controllers\BaseController;
use App\Models\Ventas\VentasMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RepFacturasEspeciales extends BaseController
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

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/reportes/repfacesp', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function exportaXLS()
        {
            $session = session();
            $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

            $fi = new DateTime($this->request->getVar('dFecIni') ?? '');
            $ff = (new DateTime($this->request->getVar('dFecFin') ?? ''));
            // se genera el listado para el reporte
            $mdlVenta = new VentasMdl();
            $registros = $mdlVenta->repFacturasEspeciales($fi, $ff);

            $wb = new Spreadsheet();
            $ws = $wb->getActiveSheet();
            $ws->getCell([1, 1])->setValue('Reporte de Facturas Especiales')
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
            // $ws->getCell([1, 3])->setValue(
            //     'Ordenado por cantidad ' .
            //         ($this->request->getVar('nOrden') == '1' ? 'Vendida' : 'Facturada')
            // );
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
            $ws->getCell([$columna++, $filaTitulo])->setValue('Remision');
            $ws->getCell([$columna++, $filaTitulo])->setValue('Factura');
            $ws->getCell([$columna++, $filaTitulo])->setValue('Cliente');
            $ws->getCell([$columna++, $filaTitulo])->setValue('Importe Facturado');
            $ws->getCell([$columna++, $filaTitulo])->setValue('Id Prod');
            $ws->getCell([$columna++, $filaTitulo])->setValue('Descripcion');
            $ws->getCell([$columna++, $filaTitulo])->setValue('Cantidad');
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
            $ws->getColumnDimensionByColumn(2)->setWidth(12);
            $ws->getColumnDimensionByColumn(3)->setWidth(12);
            $ws->getColumnDimensionByColumn(4)->setWidth(40);
            $ws->getColumnDimensionByColumn(5)->setWidth(19);
            $ws->getColumnDimensionByColumn(6)->setWidth(6);
            $ws->getColumnDimensionByColumn(7)->setWidth(40);
            $ws->getColumnDimensionByColumn(8)->setWidth(10);
            $ws->getColumnDimensionByColumn(9)->setWidth(10);
            $ws->getColumnDimensionByColumn(10)->setWidth(10);
    
            $fila = 5;
            $idVentaActual = '';
            foreach ($registros as $row) {
                if($idVentaActual != $row['nIdVentas']) {
                    $idVentaActual = $row['nIdVentas'];
                    $ws->getCell([1, $fila])->setValue($objFecha->PHPToExcel(date("Y/m/d", strtotime($row['fecha']))));
                    $ws->getCell([2, $fila])->setValue($row['nFolioRemision']);
                    $ws->getCell([3, $fila])->setValue($row['sSerie'] . ' - ' . $row['nFolioFactura']);
                    $ws->getCell([4, $fila])->setValue($row['sNombre']);
                    $ws->getCell([5, $fila])->setValue($row['nTotal']);
                    $ws->getStyle([1, $fila, 10, $fila])->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000']
                            // 'size' => 12
                        ]
                    ]);
                }
                $ws->getCell([6, $fila])->setValue($row['nIdArticulo']);
                $ws->getCell([7, $fila])->setValue($row['sDescripcionArt']);
                $ws->getCell([8, $fila])->setValue($row['nCant']);
                $ws->getCell([9, $fila])->setValue($row['nPrecio']);
                $val = round(floatval($row['nCant']) * floatval($row['nPrecio']), 2);
                $ws->getCell([10, $fila])->setValue($val);
                $fila++;
            }
            $ws->getStyle([1, 5, 1, $fila])->getAlignment()->setHorizontal('center');
            $ws->getStyle([1, 5, 1, $fila])->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');
            $ws->getStyle([5, 5, 5, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->getStyle([5, 5, 5, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->getStyle([6, 5, 6, $fila])->getAlignment()->setHorizontal('center');
            $ws->getStyle([8, 5, 10, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
    
            $xlsx = new Xlsx($wb);
            $nom = tempnam('assets', 'repfe');
            $ainfo = pathinfo($nom);
            $arch = 'assets/' . $ainfo['filename'] . '.xlsx';
    
            $xlsx->save($arch);
    
            $wb->disconnectWorksheets();
            unset($wb);
    
            $text = file_get_contents($arch);
            unlink($arch);
            unlink($nom);
    
            return $this->response->download('repoFacturaEspecial.xlsx', $text);
        }
}
