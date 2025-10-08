<?php

namespace App\Controllers\Cuentas;

use App\Controllers\BaseController;

use App\Models\Catalogos\TipoPagoMdl;
use App\Models\Compras\ComprasMdl;
use App\Models\Compras\CompraDetalleMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasDetMdl;

use App\Models\Ventas\VentasSaldoMdl;
use App\Models\Cuentas\CompraPagoMdl;
use App\Models\Cuentas\CreditoPagoMdl;
use App\Models\Ventas\FacturasMdl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Cuentas extends BaseController
{
    public function index($operacion, $id = 0)
    {
        $this->validaSesion();
        $aKeys = [
            'pagar' => 'Cuentas por pagar',
            'cobrar' => 'Cuentas por cobrar',
        ];

        $aWhere = [
            'Edo' => $this->request->getVar('cEstado') ?? '0',
            'dIni' => $this->request->getVar('dIni'),
            'dFin' => $this->request->getVar('dFin'),
            'idCliente' => $this->request->getVar('idCliente') ?? ''
        ];
        $titulo = $aKeys[$operacion];

        $modelo = $this->getMovimientos(
            $operacion,
            ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal,
            $id,
            $aWhere,
            8
        );

        $data = [
            'registros' =>  $modelo['regs'],
            'pager' => $modelo['pager'],
            'titulo' => $titulo,
            'operacion' => $operacion,
            'aWhere' => $aWhere,
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('cuentas/cuentas', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function getMovimientos($operacion = 'compra', $idsuc = false, $id = 0, $aWhere = null, $pages = 0, $conDetalle = false)
    {
        $this->validaSesion();
        $resp = [];

        switch ($operacion) {
            case 'pagar':
                $model = new ComprasMdl();
                $resp = [
                    'regs' => $model->getRegs($idsuc, $id, $aWhere, $pages, $conDetalle),
                    'pager' => $model->pager,
                ];
                break;
            case 'cobrar':
                $mdlCreditoPago = new CreditoPagoMdl();
                if ($aWhere !== null && $aWhere['dIni'] != null) {
                    $model = new VentasMdl();
                } else {
                    $model = new VentasSaldoMdl();
                }
                $resp['regs'] = $model->getRegs($idsuc, $id, $aWhere, $pages, $conDetalle);
                //if($conDetalle) $resp['regsp'] = $mdlCreditoPago->getLstRegs(0, $idsuc, $aWhere);
                $resp['pager'] = $model->pager;
                break;
        }
        return $resp;
    }

    public function updtSaldo($operacion, $id, $importe)
    {
        $this->validaSesion();

        switch ($operacion) {
            case 'pagar':
                $model = new ComprasMdl();
                break;
            case 'cobrar':
                $model = new VentasSaldoMdl();
                break;
        }

        $model->updateSaldo($this->nIdSucursal, $id, -1 * $importe);
    }

    public function accion($operacion, $tipoaccion, $id = 0)
    {

        $this->validaSesion();

        $sKey = 'nIdCompra';
        switch ($operacion) {
            case 'pagar':
                $model = new CompraPagoMdl();
                $sKey = 'nIdCompra';
                break;
            case 'cobrar':
                $model = new CreditoPagoMdl();
                $sKey = 'nIdVentas';
                break;
        }

        $aTitulo = ['p' => 'Recepción', 'v' => 'Consulta', 'c' => 'Cancelar', 'd' => 'Detalle'];
        $stitulo = $aTitulo[$tipoaccion] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' pago',
            'operacion' => $operacion,
            'frmURL' => base_url('cuentas/' . $operacion . '/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'c') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $r = [
                    $sKey => $this->request->getVar($sKey),
                    'fPago' => $this->request->getVar('fPago'),
                    'dtPago' => $this->request->getVar('dtPago'),
                    'nIdTipoPago' => $this->request->getVar('nIdTipoPago')
                ];
                if ($tipoaccion == 'v') {
                    $r['nIdCompraPago'] = $id;
                }
                $this->updtSaldo($operacion, $id, $r['fPago']);
                $model->save($r);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion === 'd') {
                $data['regs'] = $model->getLstRegs($id);
                echo view('cuentas/cuentaLstPagos', $data);
                return;
            }
        }

        $modelo = $this->getMovimientos($operacion, false, $id);
        $timestamp = strtotime($modelo['regs']['dtPago'] ?? date("d-m-Y"));
        $modelo['regs']['dtPago'] = date("Y-m-d", $timestamp);
        $modelo['regs']['fSaldo2'] = number_format(floatval($modelo['regs']['fSaldo']), 2);
        $data['registro'] =  $modelo['regs'];
        $mdlTipoPago = new TipoPagoMdl();
        $data['regTipoPago'] = $mdlTipoPago->getRegistros();
        echo view('cuentas/cuentapagomtto', $data);
    }

    public function validaCampos()
    {
        $reglas = [
            'fPago' => 'required|greater_than[0]|less_than_equal_to[{fSaldo}]',
            'nIdTipoPago' => 'required|greater_than[0]'
        ];
        $reglasMsj = [
            'fPago' => [
                'required'   => 'Pago requerido',
                'greater_than' => 'Pago debe ser mayor a cero',
                'less_than_equal_to' => 'Pago debe ser menor al saldo'
            ],
            'nIdTipoPago' => [
                'required' => 'Falta tipo de pago',
                'greater_than' => 'Falta tipo de pago'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function exporta($operacion, $id = 0)
    {
        $this->validaSesion();

        $aKeys = [
            'pagar' => 'Cuentas por pagar',
            'cobrar' => 'Cuentas por cobrar',
        ];

        $aWhere = [
            'Edo' => $this->request->getVar('cEstado') ?? '0',
            'dIni' => $this->request->getVar('dIni'),
            'dFin' => $this->request->getVar('dFin'),
            'idCliente' => $this->request->getVar('idCliente') ?? '',
            'sCliente' => $this->request->getVar('sCliente') ?? ''
        ];
        $titulo = $aKeys[$operacion];

        $modelo = $this->getMovimientos(
            $operacion,
            ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal,
            $id,
            $aWhere,
            0,
            true
        );

        $data = [
            'registros' =>  $modelo['regs'],
            'pager' => $modelo['pager'],
            'titulo' => $titulo,
            'operacion' => $operacion,
            'aWhere' => $aWhere,
        ];
        return $this->generaXls($data);
    }

    private function generaXls($data)
    {
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $operacion = $data['operacion'];
        $aKeys = [
            'pagar'  => ['nIdCompra', 'dcompra', 'F.Compra', 'fSaldo', 'dSolicitud', 'compra', 'nIdCompra'],
            'cobrar' => ['nFolioRemision', 'dtAlta', 'F.Venta', 'fSaldo', 'dtAlta', 'compra', 'nIdVentas'],
        ];
        $sKey = $aKeys[$operacion][0];
        $sMov = $aKeys[$operacion][1];
        $fMov = $aKeys[$operacion][2];
        $nTotal = $aKeys[$operacion][3];
        $fSolicitud = $aKeys[$operacion][4];
        $bOcultaRecepcion = $operacion === "compra";
        $consultar = $aKeys[$operacion][5];
        $sKeyInterno = $aKeys[$operacion][6];

        $mdlCreditoPago = new CreditoPagoMdl();
        $mdlFactura = new FacturasMdl();
        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();

        $ws->getCell([1, 1])->setValue('Reporte de Saldos ' . $data['titulo'])->getStyle()
            ->getFont()->setBold(true)->setSize(20);
        $fila = 2;
        $ws->getCell([1, 2])->setValue('Filtro:')
            ->getStyle()->getFont()->setBold(true);
        $aedo = [
            '0' => 'Con Saldo',
            '1' => 'Pagados',
            '2' => 'Todos'
        ];
        $ws->getCell([2, $fila])->setValue('Estado ->')->getStyle()
            ->getFont()->setItalic(true);
        $ws->getCell([3, $fila++])->setValue($aedo[$data['aWhere']['Edo']]);

        $fecini = $data['aWhere']['dIni'];
        $fecfin = $data['aWhere']['dFin'];
        if ($fecini != '' && $fecfin != '') {
            $f = initFechasFiltro($fecini, $fecfin);
            $ws->getCell([2, $fila])->setValue('Periodo de Fechas ->')->getStyle()
                ->getFont()->setItalic(true);
            $ws->getCell([3, $fila++])->setValue($f[0]->format('d-m-Y') . ' al ' . $f[1]->format('d-m-Y'));
        }

        if ($data['aWhere']['idCliente'] != '') {
            $ws->getCell([2, $fila])->setValue('Cliente ->')->getStyle()
                ->getFont()->setItalic(true);
            $ws->getCell([3, $fila++])->setValue($data['aWhere']['sCliente']);
        }

        $fila += 2;
        $col = 1;
        // titulo
        if ($operacion === 'pagar') {
            $ws->getCell([$col++, $fila])->setValue('#');
        } else {
            $ws->getCell([$col++, $fila])->setValue('Folio Remision');
            $ws->getCell([$col++, $fila])->setValue('Factura');
        }
        $ws->getCell([$col++, $fila])->setValue($fMov)->getStyle()->getAlignment()->setHorizontal('center');
        // $ws->getStyle([$col - 1, $fila, $col - 1, $fila])->getAlignment()->setHorizontal('center');
        $ws->getCell([$col++, $fila])->setValue('Sucursal');
        if ($operacion === 'pagar') {
            $ws->getCell([$col++, $fila])->setValue('Proveedor');
        } else {
            $ws->getCell([$col++, $fila])->setValue('Cliente');
            $ws->getCell([$col++, $fila])->setValue('Importe Venta');
        }
        $ws->getCell([$col++, $fila])->setValue('Saldo');
        if ($operacion === 'pagar') {
            $ws->getCell([$col++, $fila])->setValue('F. Solicitud');
        }
        $ws->getCell([$col++, $fila])->setValue('Estado');
        $ws->getCell([$col + 1, $fila - 1])->setValue('Pagos')->getStyle()->getFont()->setSize(14)->setBold(true);
        $ws->getCell([$col++, $fila])->setValue('Fecha Pago');
        $ws->getCell([$col++, $fila])->setValue('Tipo');
        $ws->getCell([$col++, $fila])->setValue('Importe Pago');
        $wsestilo = $ws->getStyle([$col - 4, $fila, $col - 1, $fila]);
        $wsestilo->getAlignment()->setHorizontal('center');

        $wsestilo = $ws->getStyle([1, $fila, $col - 1, $fila]);
        $wsestilo->getFont()->setBold(true)->setSize(12);
        $wsestilo->getBorders()->getBottom()->setBorderStyle('double');
        $fila++;
        $FolioActual = '';
        $filaNueva = 0;
        $sumaAdeudo = 0;
        $sumaPagado = 0;
        foreach ($data['registros'] as $r) {
            if ($FolioActual != $r[$sKey]) {
                $FolioActual = $r[$sKey];
                if ($fila < $filaNueva) $fila = $filaNueva;
                $col = 1;
                $ws->getCell([$col++, $fila])->setValue($r[$sKey]);
                $regFac = $mdlFactura->select('nFolioFactura')->where('nIdVentas', $r[$sKeyInterno])->first();
                $valFac = $regFac == null ? '' : $regFac['nFolioFactura'];
                $ws->getCell([$col++, $fila])->setValue($valFac);
                $ws->getCell([$col++, $fila])->setValue(
                    $r[$sMov] === null ? '' : $objFecha->PHPToExcel(date("Y/m/d", strtotime($r[$sMov])))
                )->getStyle()->getAlignment()->setHorizontal('center');
                $ws->getCell([$col++, $fila])->setValue($r['sDescripcion']);
                $ws->getCell([$col++, $fila])->setValue($r['sNombre']);
                if ($operacion !== 'pagar') {
                    $ws->getCell([$col++, $fila])->setValue(round(floatval($r['nTotalVenta']), 2));
                    $sumaAdeudo += round(floatval($r['nTotalVenta']), 2);
                }
                $ws->getCell([$col++, $fila])->setValue(round(floatval($r[$nTotal]), 2));
                if ($operacion === 'pagar') {
                    $ws->getCell([$col++, $fila])->setValue(
                        $r[$fSolicitud] === null ? '' : date("Y/m/d", strtotime($r[$fSolicitud]))
                    );
                }
                switch ($r['cEdoEntrega']) {
                    case '2':
                        $cEdo = 'Surtido parcial';
                        break;
                    case '3':
                        $cEdo = 'Surtido total';
                        break;
                    default:
                        $cEdo = 'Pendiente';
                } // 'rgb' => 'FFFF00' #FABF8F
                $ws->getCell([$col, $fila])->setValue($cEdo);
                $ws->getStyle([1, $fila, $col, $fila])->getFill()->applyFromArray([
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'FABF8F']
                ]);
                $rd = $mdlCreditoPago->getLstRegs($r[$sKeyInterno]);
                $filaNueva = $fila;
                foreach ($rd as $rr) {
                    $colNueva = $col + 1;
                    $estilo = $ws->getCell([$colNueva++, $filaNueva])->setValue(
                        $rr['dtPago'] === null ? '' : $objFecha->PHPToExcel(date("Y/m/d", strtotime($rr['dtPago'])))
                    )->getStyle();
                    $estilo->getAlignment()->setHorizontal('center');
                    $estilo->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');
                    $ws->getCell([$colNueva++, $filaNueva])->setValue($rr['sLeyenda']);
                    $ws->getCell([$colNueva++, $filaNueva++])->setValue(round(floatval($rr['fPago']), 2))
                        ->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
                    $sumaPagado += round(floatval($rr['fPago']), 2);
                }
                $fila++;
            }
            $colDet = 4;
            $ws->getCell([$colDet++, $fila])->setValue(round(floatval($r['nCant']), 3));
            $ws->getCell([$colDet++, $fila])->setValue($r['sDescripcionArt']);
            $precioUni = round(floatval($r['nPrecio']) - floatval($r['nDescuentoTotal']), 3);
            $ws->getCell([$colDet++, $fila])->setValue($precioUni);
            $ws->getCell([$colDet++, $fila])->setValue($precioUni * round(floatval($r['nCant']), 3));
            $fila++;
        }
        $fila += 3;
        $ws->getCell([4, $fila])->setValue('TOTAL IMPORTE:');
        $ws->getCell([5, $fila++])->setValue($sumaAdeudo);
        $ws->getCell([4, $fila])->setValue('TOTAL PAGADO:')->getStyle()->getBorders()->getBottom()->setBorderStyle('double');
        $ws->getCell([5, $fila++])->setValue($sumaPagado)->getStyle()->getBorders()->getBottom()->setBorderStyle('double');
        $ws->getCell([4, $fila])->setValue('SALDO:');
        $ws->getCell([5, $fila])->setValue($sumaAdeudo - $sumaPagado);
        $ws->getStyle([5, $fila - 3, 5, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([4, $fila - 3, 5, $fila])->getFont()->setBold(true)->setSize(14);
      
        $ws->getStyle([5, 5, 5, $fila - 1])
            ->getNumberFormat()->setFormatCode('#,##0.00');
        if ($operacion !== 'pagar') {
            $ws->getStyle([6, 5, 7, $fila - 1])
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $ws->getStyle([3, 5, 3, $fila - 1])
            ->getNumberFormat()->setFormatCode('yyyy/mm/dd;@');

        $ws->getColumnDimensionByColumn(1)->setWidth(15);
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
        $ws->getColumnDimensionByColumn(12)->setAutoSize(true);

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'cuentas');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('cuentas.xlsx', $text);
    }
}
