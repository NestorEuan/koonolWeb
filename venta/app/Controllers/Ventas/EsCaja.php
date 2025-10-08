<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Catalogos\ConfiguracionMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Cuentas\CompraPagoMdl;
use App\Models\Cuentas\CreditoPagoMdl;
use App\Models\Compras\ComprasMdl;
use App\Models\Ventas\VentasSaldoMdl;
use App\Models\Ventas\EsCajaMdl;
use App\Models\Catalogos\TipoPagoMdl;
use App\Models\Ventas\EsCreditoPagoMdl;
use DateTime;
use Luecano\NumeroALetras\NumeroALetras;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EsCaja extends BaseController
{

    public function index()
    {
        $this->validaSesion();
        $this->validaTurnoCorte();

        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'));
        $opcion = $this->request->getVar('inputGroupSelectOpciones') ?? '0';
        $aCond = [
            'opc' => $opcion,
            'dIni' => $f[0]->format('Y-m-d'),
            'dFin' => $f[1]->format('Y-m-d'),
        ];
        $mdlESCaja = new EsCajaMdl();
        $data = [
            'registros' => $mdlESCaja->getRegistros(false, true, $this->nIdSucursal, $aCond),
            'pager' => $mdlESCaja->pager,
            'esMobil' => $this->request->getUserAgent()->isMobile(),
            'cOpciones' => [
                ['Todos', '0', ''],
                ['Ingresos', '1', ''],
                ['Egresos', '2', ''],
            ],
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d'),
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/esCaja', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function accion($tipoaccion, $id = 0)
    {
        session();
        $model = new EsCajaMdl();

        switch ($tipoaccion) {
            case 'a':
                $stitulo = 'Agrega';
                break;
            case 'b':
                $stitulo = 'Borra';
                break;
            case 'e':
                $stitulo = 'Edita';
                break;
            default:
                $stitulo = 'Ver';
        }

        $data = [
            'titulo' => $stitulo . ' Entrada/Salida de Caja',
            'frmURL' => base_url('movtoCajas/' . $tipoaccion .
                ($id !== '' ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id
        ];

        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $r = [
                    'cTipoMov' => $this->request->getVar('cTipoMov'),
                    'sMotivo' => str_replace(
                        [chr(10), chr(9), chr(13)],
                        ' ',
                        $this->request->getVar('sMotivo')
                    ),
                    'nImporte' => $this->request->getVar('nImporte'),
                    'nIdSucursal' => $this->nIdSucursal
                ];
                $timestamp = strtotime(date("d-m-Y"));
                $hoy = date("Y-m-d", $timestamp);

                if ($tipoaccion == 'e') {
                    $r['nIdEScaja'] = $id;
                }
                $abonoPagado = false;
                if ($r['cTipoMov'] == 'C') {
                    $abonoPagado = true;
                    $r['cTipoMov'] = 'E';
                    $idCreditoPagado = $this->recibePago('cobrar', $this->request->getVar('nIdVentas'), $hoy, $r['nImporte'], $this->request->getVar('nIdTipoPago'));
                }
                if ($r['cTipoMov'] == 'P') {
                    $r['cTipoMov'] = 'S';
                    $this->recibePago('pagar', $this->request->getVar('nIdCompra'), $hoy, $r['nImporte'], $this->request->getVar('nIdTipoPago'));
                }
                $model->save($r);
                // en E/S de cajas solo se agregan registros, es seguro usar el insert de corte de caja aquí
                $mdlCorte = new CorteCajaMdl();
                $idFolio = $model->getInsertID();
                $mdlCorte->guardaFolioMovimiento($this->nIdUsuario, $this->nIdSucursal, $idFolio, '2');
                if ($abonoPagado) {
                    // se guarda la relacion EScaja -> CreditoPagado
                    $mdlEScredito = new EsCreditoPagoMdl();
                    $mdlEScredito->insert([
                        'nIdEScaja' => $idFolio,
                        'nIdCreditoPago' => $idCreditoPagado
                    ]);
                }
                $this->preparaImpresion($idFolio);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $model->getRegistros($id);
            }
        }

        $mdlConfig = new ConfiguracionMdl();
        $regConfig = $mdlConfig->getRegistrosByName('PAGOSDEP', true);
        $mdlTipoPagos = new TipoPagoMdl();
        $regTipoPago = $mdlTipoPagos->getRegistros(
            false,
            false,
            $regConfig[0]['sValor']
        );
        $data['regTipoPago'] = $regTipoPago;
        $data['esMobil'] = $this->request->getUserAgent()->isMobile();
        $mdlSucursal = new SucursalMdl();
        $regSuc = $mdlSucursal->getRegistros(false, false, $this->nIdSucursal);
        array_splice($regSuc, 0, 0, [['nIdSucursal' => $this->nIdSucursal, 'sDescripcion' => 'Esta sucursal']]);
        $data['nIdSucursalActual'] = $this->request->getVar('nIdSucursalEntrega') ?? $this->nIdSucursal;

        $data['regSucursales'] = $regSuc;

        echo view('ventas/escajamtto', $data);
    }

    public function recibePago($operacion, $id, $dFecha, $fImporte, $nIdTipoPago)
    {

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
        $r = [
            $sKey => $id,
            'fPago' => $fImporte,
            'dtPago' => $dFecha,
            'nIdTipoPago' => $nIdTipoPago
        ];
        /*
        if ($tipoaccion == 'v') {
            $r['nIdCompraPago'] = $id;
        }
        */
        $this->updtSaldo($operacion, $id, $r['fPago']);
        $model->save($r);
        $idFolio = $model->getInsertID();   // id del creddito pagado
        return $idFolio;
    }

    public function updtSaldo($operacion, $id, $importe)
    {

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



    public function validaCampos()
    {
        $reglas = [
            'sMotivo' => 'required',
            'nImporte' => 'required|decimal|mayorDeCero',
            'sPersona' => 'required'
        ];
        $reglasMsj = [
            'sMotivo' => [
                'required'   => 'Falta el motivo del movimiento'
            ],
            'nImporte' => [
                'required'   => 'Falta el importe',
                'decimal' => 'Debe ser un numero',
                'mayorDeCero' => 'El importe debe ser mayor de cero',

            ],
            'sPersona' => [
                'required' => 'Falta el nombre de la persona quien recibe o entrega dinero'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    private function preparaImpresion($id)
    {
        $mdlEScredito = new EsCreditoPagoMdl();
        $rPagoVenta = $mdlEScredito
            ->select('v.nFolioRemision, c.sNombre')
            ->join('vtcreditopago b', 'vtescreditopago.nIdCreditoPago = b.nIdCreditoPago', 'left')
            ->join('vtventas v', 'b.nIdVentas = v.nIdVentas', 'left')
            ->join('vtcliente c', 'v.nIdCliente = c.nIdCliente', 'left')
            ->where('vtescreditopago.nIdEScaja', $id)
            ->first();;
        $obsAdicional = '';
        if ($rPagoVenta != null) $obsAdicional = 'Pago Remision ' . $rPagoVenta['nFolioRemision'] .
            ', Cliente: ' .  $rPagoVenta['sNombre'] . '<br>';
        $formato = new NumeroALetras();
        $fec = new DateTime();
        $nImp = round(floatval($this->request->getVar('nImporte')), 2);
        $formato->conector = 'PESOS CON';
        $sImp = '(** ' . $formato->toInvoice($nImp, 2, 'M.N.') . ' **)';
        if (in_array($this->request->getVar('cTipoMov'), ['E', 'C'])) {
            $tipoDoc = 'Recibo de otros ingresos';
            $recibi = $this->request->getVar('sPersona');
            $firma1 = 'E N T R E G A';
            $firma2 = 'R E C I B E';
        } else {
            $tipoDoc = 'Retiro efectivo de caja';
            $recibi = $this->sSucursal;
            $firma1 = 'R E C I B E';
            $firma2 = 'E N T R E G A';
        }
        $data = [
            'folio' => sprintf('%06d', intval($id)),
            'Usuario' => $this->request->getVar('sUsuario') ?? (sprintf('%03d  ', intval($this->nIdUsuario)) . $this->sNombreUsuario),
            'fechahora' => $fec->format('d/m/Y H:i'),
            'tipoDoc' => $tipoDoc,
            'importe' => number_format($nImp, 2),
            'importeletras' => strtoupper($sImp),
            'motivo' => $obsAdicional . str_replace(
                [chr(10), chr(9), chr(13)],
                ' ',
                $this->request->getVar('sMotivo')
            ),
            'persona' => $this->request->getVar('sPersona'),
            'sucursal' => $this->sSucursal,
            'recibi' => $recibi,
            'firma1' => $firma1,
            'firma2' => $firma2,
            'urlret' => $this->request->getVar('urlRet') ?? base_url('movtoCajas')

        ];
        $_SESSION['impresionRECCAJA'] = $data;
    }

    public function imprimeRecibo()
    {
        session();
        $data = $_SESSION['impresionRECCAJA'];
        $data['esMobil'] = $this->request->getUserAgent()->isMobile();
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/imprimirReciboCaja', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function reimprimir($id)
    {
        $this->validaSesion();
        // abro la pantalla de captura del movto. y cargamos los datos.
        // agrego los campos de usuario (para rellenar la info, lo busco de corte de caja.)
        if ($this->request->getMethod() == 'post') {
            $this->preparaImpresion($id);
            echo 'oK';
            return;
        } else {
            // se lee la info
            $mdlES = new EsCajaMdl();
            $r = $mdlES->getRegistros($id, false, false, false, true);
            if ($r['nIdUsuario'] == '0')
                $sUsuario = sprintf('%03d  ', intval($this->nIdUsuario)) . $this->sNombreUsuario;
            else
                $sUsuario = sprintf('%03d  ', intval($r['nIdUsuario'])) . $r['nomUsuario'];

            $data = [
                'registro' => $r,
                'sUsuario' => $sUsuario,
                'frmURL' => base_url('movtoCajas/reimprimir/' . $id),
                'urlRet' => previous_url(),
                'modo' => 'a'
            ];
        }
        $data['esMobil'] = $this->request->getUserAgent()->isMobile();
        echo view('ventas/escajareimprimir', $data);
    }

    public function exportaXLS()
    {
        $this->validaSesion();

        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'));
        $opcion = $this->request->getVar('inputGroupSelectOpciones') ?? '0';
        $aCond = [
            'opc' => $opcion,
            'dIni' => $f[0]->format('Y-m-d'),
            'dFin' => $f[1]->format('Y-m-d'),
        ];
        $mdlESCaja = new EsCajaMdl();
        $registros = $mdlESCaja->getRegistros(false, false, $this->nIdSucursal, $aCond);
        $arrOpc = [
            '0' => 'Todos',
            '1' => 'Ingresos',
            '2' => 'Egresos',
        ];

        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();

        $fi = $f[0];
        $ff = $f[1];
        // se genera el listado para el reporte

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $ws->getCell([1, 1])->setValue('Reporte de Entradas y Salidas de Caja')
            ->getStyle([1, 1, 11, 1])
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 18
                ]
            ]);
        $ws->getRowDimension(1)->setRowHeight(26);
        $ws->getCell([1, 2])->setValue('Tipo Movto: ' . $arrOpc[$opcion]);
        $ws->getCell([1, 3])->setValue(
            'Período: Del ' . $fi->format('d-m-Y') .
                ' al ' . $ff->format('d-m-Y')
        );
        // $ws->getCell([1, 3])->setValue(
        //     'Ordenado por cantidad ' .
        //         ($this->request->getVar('nOrden') == '1' ? 'Vendida' : 'Facturada')
        // );
        $ws->mergeCells([1, 1, 5, 1])->mergeCells([1, 2, 5, 2])->mergeCells([1, 3, 5, 3]);
        $ws->getStyle([1, 1, 5, 3])->applyFromArray([
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
        $ws->getCell([$columna++, $filaTitulo])->setValue('ID Movto');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Fecha');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Tipo Movto.');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Motivo');
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

        $fila = 5;
        foreach ($registros as $row) {
            $ws->getCell([1, $fila])->setValue($row['nIdEScaja']);
            $ws->getCell([2, $fila])->setValue($objFecha->PHPToExcel(date("Y/m/d", strtotime($row['dtAlta']))));
            $ws->getCell([3, $fila])->setValue($row['cTipMov']);
            if ($row['nFolioRemision']  != '') {
                $motivo = 'Remision: ' . $row['nFolioRemision'] .
                    ' Cliente: ' . $row['nomCliente'] .
                    '. ' . $row['sMotivo'];
            } else {
                $motivo = $row['sMotivo'];
            }
            $ws->getCell([4, $fila])->setValue($motivo);
            $ws->getCell([5, $fila])->setValue(round(floatval($row['nImporte']), 3));
            $fila++;
        }
        $ws->getStyle([1, 5, 3, $fila])->getAlignment()->setHorizontal('center');
        $ws->getStyle([2, 5, 2, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy;@');
        $ws->getStyle([5, 5, 5, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([5, 5, 5, $fila])->getAlignment()->setHorizontal('right');

        $ws->getStyle([1, 5, 5, $fila])->applyFromArray([
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

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repescaj');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('repoEScajas.xlsx', $text);
    }
}
