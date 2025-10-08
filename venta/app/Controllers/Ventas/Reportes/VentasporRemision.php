<?php

namespace App\Controllers\Ventas\Reportes;

use App\Controllers\BaseController;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Ventas\VentasMdl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VentasporRemision extends BaseController
{
    public function reporte()
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
        echo view('ventas/reportes/ventasporremision', $data);
        echo view('templates/footer', $this->dataMenu);
    }
    public function exportaXLS()
    {
        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'), 'M');
        $idProd = $this->request->getVar('idProducto') ?? '0';



        $mdlVentas = new VentasMdl();
        $regs = $mdlVentas->repVentasProductoPorRemision($idProd, $f[0], $f[1], $this->nIdSucursal);
        $mdlArticulo = new ArticuloMdl();
        $rArt = $mdlArticulo->getRegistros($idProd);

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();

        $ws->getCell([1, 1])->setValue(strtoupper('Reporte de ventas por remision del Articulo:'));
        $ws->getCell([1, 2])->setValue($rArt['sDescripcion']);
        $ws->getCell([1, 3])->setValue('Remision');
        $ws->getCell([2, 3])->setValue('Fecha');
        $ws->getCell([3, 3])->setValue('Cliente');
        $ws->getCell([4, 3])->setValue('Cantidad Pagada');
        $ws->getCell([5, 3])->setValue('Cantidad Entregada/Asignada');
        $wsEstilo = $ws->getStyle([1, 3, 5, 3]);
        $wsEstilo->getBorders()->getAllBorders()->setBorderStyle('medium');
        $wsEstilo->getFont()->setBold(true);
        $wsEstilo->getAlignment()->setHorizontal('center');
        // 'vtventas.nFolioRemision, vtventas.dtAlta, c.sNombre, b.nCant, b.nPorEntregar, b.nEntregado'
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $fila = 4;
        foreach ($regs as $r) {
            $fecha = $objFecha->PHPToExcel(
                date(
                    "Y/m/d",
                    strtotime(substr($r['dtAlta'], 0, 10))
                )
            );
            $ws->getCell([1, $fila])->setValue($r['nFolioRemision']);
            $ws->getCell([2, $fila])->setValue($fecha);
            $ws->getCell([3, $fila])->setValue($r['sNombre']);
            $ws->getCell([4, $fila])->setValue($r['nCant']);
            $ws->getCell([5, $fila])->setValue($r['nEntregado']);
            $fila++;
        }
        $ws->getStyle([1, 4, 2, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([4, 4, 5, $fila])->getAlignment()->setHorizontal('center');

        $ws->getStyle([2, 4, 2, $fila])->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');
        $ws->getStyle([4, 4, 5, $fila])->getNumberFormat()->setFormatCode('#,##0');
        for ($i = 1; $i <= 5; $i++) $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(1)->setAutoSize(false);
        $ws->getColumnDimensionByColumn(1)->setWidth(15);
        $ws->getColumnDimensionByColumn(3)->setAutoSize(false);
        $ws->getColumnDimensionByColumn(3)->setWidth(45);
        $ws->mergeCells([1,1,5,1]);
        $ws->mergeCells([1,2,5,2]);

        $wsEstilo = $ws->getStyle([1, 1, 5, 2]);
        $wsEstilo->getAlignment()->setHorizontal('center');
        $wsEstilo->getFont()->setBold(true);
        $wsEstilo->getFont()->setSize(14);
        

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

        return $this->response->download('repoVentasPorRemision' . $this->nIdSucursal . '.xlsx', $text);
    }
}
