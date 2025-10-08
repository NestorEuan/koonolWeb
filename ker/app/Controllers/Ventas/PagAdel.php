<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Catalogos\ClienteMdl;
use App\Models\Catalogos\ConfiguracionMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Catalogos\TipoPagoMdl;
use App\Models\Catalogos\UsoCfdiMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\FacturasMdl;
use App\Models\Ventas\MovPagAdelMdl;
use App\Models\Ventas\PagAdelMdl;
use App\Models\Ventas\SaldoPagAdelMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\VentasImpuestosMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasPagoMdl;
use DateTime;
use Luecano\NumeroALetras\NumeroALetras;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PagAdel extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        // $this->validaTurnoCorte();
        if (($l = $this->validaTurnoCorte(true))) return $l;
        $model = new PagAdelMdl();
        $data = [
            'registros' => $model->getRegistros(false, true, $this->nIdSucursal),
            'pager' => $model->pager
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/pagadel', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function accion($tipoaccion, $id = 0)
    {
        session();
        $model = new PagAdelMdl();
        $mdlCliente = new ClienteMdl();
        $mdlSaldoPagAdel = new SaldoPagAdelMdl();

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
            'titulo' => $stitulo . ' Depósito de Cliente',
            'frmURL' => base_url('pagoAdel/' . $tipoaccion .
                ($id !== '' ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                if ($this->request->getVar('chkFactura') != null) {
                    $ret = $this->guardaFactura($model, $mdlCliente, $mdlSaldoPagAdel);
                } else {
                    $ret = $this->guardaDeposito($model, $mdlCliente, $mdlSaldoPagAdel);
                }
                if ($ret[0] === true) {
                    $this->preparaImpresion($ret[1]);
                    echo 'oK';
                    return;
                } else {
                    $data['errorGral'] = $ret[1];
                }
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $model->getRegistros($id);
            }
        }
        $mdlUsoFDI = new UsoCfdiMdl();
        $data['regUsoCfdi'] = $mdlUsoFDI->getRegistros();
        $mdlConfig = new ConfiguracionMdl();
        $regConfig = $mdlConfig->getRegistrosByName('PAGOSDEP', true);
        $mdlTipoPagos = new TipoPagoMdl();
        $regTipoPago = $mdlTipoPagos->getRegistros(
            false,
            false,
            $regConfig[0]['sValor']
        );
        $data['regTipoPago'] = $regTipoPago;

        echo view('ventas/pagadelmtto', $data);
    }

    public function validaCampos()
    {
        $reglas = [
            'nIdCliente' => 'required',
            'sObservaciones' => 'required',
            'nImporte' => 'required|decimal|mayorDeCero'
        ];
        $reglasMsj = [
            'sObservaciones' => [
                'required'   => 'Falta las observaciones del deposito'
            ],
            'nImporte' => [
                'required'   => 'Falta el importe',
                'decimal' => 'Debe ser un numero',
                'mayorDeCero' => 'El importe debe de ser mayor de cero'
            ]
        ];
        $chkFactura = $this->request->getVar('chkFactura');
        if ($chkFactura !== null) {
            $mdl = new UsoCfdiMdl();
            $r = $mdl->getRegistros(false, true);
            $reglas['cIdUsoCfdi'] = 'in_list[' . $r[0]['lst'] . ']';
            $reglasMsj['cIdUsoCfdi'] = ['in_list'   => 'Seleccione una opción'];
            $mdl = new ConfiguracionMdl();
            $r = $mdl->getRegistrosByName('PAGOSDEP', true);
            $reglas['nIdTipoPago'] = 'in_list[' . $r[0]['sValor'] . ']';
            $reglasMsj['nIdTipoPago'] = ['in_list'   => 'Seleccione una opción'];
        }

        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    private function guardaFactura($model, $mdlCliente, SaldoPagAdelMdl $mdlSaldo)
    {
        $nImporte = floatval($this->request->getVar('nImporte'));
        $nIdCliente = intval($this->request->getVar('nIdCliente'));
        $nomCli = $this->request->getVar('sCliNom');
        $r = [
            'nIdCliente' => $nIdCliente,
            'nImporte' => $nImporte,
            'nIdSucursal' => $this->nIdSucursal,
            'sObservaciones' => str_replace(
                [chr(10), chr(9), chr(13)],
                ' ',
                $this->request->getVar('sObservaciones')
            ),
        ];
        $mdlSucursal = new SucursalMdl();
        $nIdSuc = intval($this->nIdSucursal);
        $mdlSucursal->db->transStart(); // inicia transaccion
        $rSuc = $mdlSucursal->leeFolio($nIdSuc);

        $mdlVentas = new VentasMdl();
        $mdlVentasdet = new VentasDetMdl();
        $mdlVentasImpuestos = new VentasImpuestosMdl();
        $mdlVentasPagos = new VentasPagoMdl();
        $mdlFactura = new FacturasMdl();

        $mdlConfig = new ConfiguracionMdl();
        $regCfg = $mdlConfig->getRegistrosByName('IDDEPCLI', true);

        $aImpuesto = [
            'id' => '002',
            'tipo' => 'T',
            'tipoVal' => 'T',
            'valor' => 0.16
        ];
        $nSubTotal = round($nImporte / (1 + $aImpuesto['valor']), 2);
        $nIva = $nImporte - $nSubTotal;


        $idVentas = $mdlVentas->insert([
            'nIdSucursal' => $this->nIdSucursal,
            'nFolioRemision' => intval($rSuc['nFolioRemision']),
            'nIdCliente' => $nIdCliente,
            'cEdo' => '7',
            'nSubTotal' => $nSubTotal,
            'nImpuestos' => $nIva,
            'nTotal' => $nImporte,
            'bEnvio' => '0',
            'nImpDescuento' => 0,
            'nImpComision' => 0,
            'nPorcenDescuento' => 0,
            'nPorcenComision' => 0,
            'nIdDirEntrega' => 0,
            'cClasificacionVenta' => '2'     // '1'-Normal, '2'-pago anticipo, ....
        ]);
        $idVentasDet = $mdlVentasdet->insert([
            'nIdVentas' => $idVentas,
            'nIdArticulo' => intval($regCfg[0]['fValor']),
            'nPrecio' => $nSubTotal,
            'nCant' => 1,
            'nPorcenDescuento' => 0,
            'nEnvio' => 0,
            'nEntrega' => 0,
            'nPorEntregar' => 0,
            'nEntregado' => 1
        ]);
        // ventas impuestos
        $mdlVentasImpuestos->insert([
            'nIdVentasDet' => $idVentasDet,
            'nIdTipoImpuesto' => 1,
            'nBase' => $nSubTotal,
            'nImporte' => $nIva
        ]);
        $mdlVentasPagos->insert([
            'nIdVentas' => $idVentas,
            'nIdTipoPago' => $this->request->getVar('nIdTipoPago'),
            'nImporte' => $nImporte
        ]);
        $mdlFactura->insert([
            'nIdVentas' => $idVentas,
            'nFolioFactura' => intval($rSuc['nFolioFactura']),
            'sSerie' => $rSuc['sSerie'],
            'cUsoCFDI' => $this->request->getVar('cIdUsoCfdi'),
            'cMetodoPago' => 'PUE'
        ]);
        $mdlSucursal->addFolioRem($this->nIdSucursal);
        $mdlSucursal->addFolioFac($this->nIdSucursal);

        $r['nIdVentaFacturada'] = $idVentas;

        $nIdPagAdel = $model->insert($r);

        $mdlCliente->actualizaSaldo(
            $this->request->getVar('nIdCliente'),
            $this->request->getVar('nImporte')
        );

        $idSaldo = $mdlSaldo->getIdVacioSaldoDeposito();
        $mdlSaldo->guardaSaldoDeposito(
            $nIdPagAdel,
            $this->request->getVar('nImporte'),
            $idSaldo
        );

        $mdlCorte = new CorteCajaMdl();
        $mdlCorte->guardaFolioMovimiento($this->nIdUsuario, $this->nIdSucursal, $nIdPagAdel, '3');
        $mdlSucursal->db->transComplete();
        if ($mdlSucursal->db->transStatus() === false) {
            // generate an error... or use the log_message() function to log your error
            return [false, 'No se pudo concluir la transacción, intente de nuevo'];
        }
        $r['nomCli'] = $nomCli;
        $r['idPagAdel'] = $nIdPagAdel;

        return [true, $r];
    }

    private function guardaDeposito(PagAdelMdl $model, $mdlCliente, SaldoPagAdelMdl $mdlSaldo)
    {
        $nImporte = floatval($this->request->getVar('nImporte'));
        $nIdCliente = intval($this->request->getVar('nIdCliente'));
        $nomCli = $this->request->getVar('sCliNom');
        $r = [
            'nIdCliente' => $nIdCliente,
            'nImporte' => $nImporte,
            'nIdSucursal' => $this->nIdSucursal,
            'sObservaciones' => str_replace(
                [chr(10), chr(9), chr(13)],
                ' ',
                $this->request->getVar('sObservaciones')
            ),
        ];
        $model->db->transStart();
        $nIdPagAdel = $model->insert($r);

        $mdlCliente->actualizaSaldo(
            $this->request->getVar('nIdCliente'),
            $this->request->getVar('nImporte')
        );

        $idSaldo = $mdlSaldo->getIdVacioSaldoDeposito();
        $mdlSaldo->guardaSaldoDeposito(
            $nIdPagAdel,
            $this->request->getVar('nImporte'),
            $idSaldo
        );

        $mdlCorte = new CorteCajaMdl();
        $mdlCorte->guardaFolioMovimiento($this->nIdUsuario, $this->nIdSucursal, $nIdPagAdel, '3');
        $model->db->transComplete();
        if ($model->db->transStatus() === false) {
            // generate an error... or use the log_message() function to log your error
            return [false, 'No se pudo concluir la transacción, intente de nuevo'];
        }
        $r['nomCli'] = $nomCli;
        $r['idPagAdel'] = $nIdPagAdel;


        return [true, $r];
    }

    public function generaDesglosePagoSaldo($id)
    {
        $mdlMovPagAdel = new MovPagAdelMdl();
        $registros = $mdlMovPagAdel->getMovtos($id);
        if (count($registros) > 0) {
            $nomCli = $registros[0]['sNombre'];
        } else {
            $nomCli = '';
        }

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $ws->getCell([1, 1])->setValue('Desglose de Movimientos del Depósito')
            ->getStyle([1, 1, 4, 1])
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 18
                ]
            ]);
        $ws->getRowDimension(1)->setRowHeight(26);
        $ws->getCell([1, 2])->setValue($nomCli);
        // $ws->getCell([1, 3])->setValue(
        //     'Ordenado por cantidad ' .
        //         ($this->request->getVar('nOrden') == '1' ? 'Vendida' : 'Facturada')
        // );
        $ws->mergeCells([1, 1, 4, 1])->mergeCells([1, 2, 4, 2]); //->mergeCells([1, 3, 4, 3]);
        $ws->getStyle([1, 1, 4, 2])->applyFromArray([
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
        $filaTitulo = 3;
        $ws->getCell([$columna++, $filaTitulo])->setValue('Folio Remision');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Saldo');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Importe Movto.');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Saldo Nuevo');
        $ws->getStyle([1, 3, 4, 3])->applyFromArray([
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
        $ws->getColumnDimensionByColumn(1)->setWidth(19);
        $ws->getColumnDimensionByColumn(2)->setWidth(19);
        $ws->getColumnDimensionByColumn(3)->setWidth(19);
        $ws->getColumnDimensionByColumn(4)->setWidth(19);

        $fila = 4;
        foreach ($registros as $row) {
            $saldoNuevo = round(floatval($row['nSaldoActual']) - floatval($row['nImporte']), 2);
            $ws->getCell([1, $fila])->setValue($row['nFolioRemision']);
            $ws->getCell([2, $fila])->setValue($row['nSaldoActual']);
            $ws->getCell([3, $fila])->setValue($row['nImporte']);
            $ws->getCell([4, $fila])->setValue($saldoNuevo);
            $fila++;
        }

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'desglose');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('desglosePagos.xlsx', $text);
    }

    private function preparaImpresion(&$r)
    {
        $formato = new NumeroALetras();
        $fec = new DateTime();
        $nImp = round(floatval($r['nImporte']), 2);
        $formato->conector = 'PESOS CON';
        $sImp = '(** ' . $formato->toInvoice($nImp, 2, 'M.N.') . ' **)';
        $tipoDoc = 'Recibo Deposito Cliente';
        $recibi = $r['nomCli'];
        $firma1 = 'E N T R E G A';
        $firma2 = 'R E C I B E';
        $data = [
            'folio' => sprintf('%06d', intval($r['idPagAdel'])),
            'Usuario' => sprintf('%03d  ', intval($this->nIdUsuario)) . $this->sNombreUsuario,
            'fechahora' => $fec->format('d/m/Y H:i'),
            'tipoDoc' => $tipoDoc,
            'importe' => number_format($nImp, 2),
            'importeletras' => strtoupper($sImp),
            'motivo' => str_replace(
                [chr(10), chr(9), chr(13)],
                ' ',
                $r['sObservaciones']
            ),
            'persona' => $r['nomCli'],
            'sucursal' => $this->sSucursal,
            'recibi' => $recibi,
            'firma1' => $firma1,
            'firma2' => $firma2,
            'urlret' => base_url('pagoAdel')
        ];
        $_SESSION['impresionRECCAJA'] = $data;
    }
}
