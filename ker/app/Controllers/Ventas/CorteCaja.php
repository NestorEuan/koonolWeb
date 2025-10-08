<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Seguridad\UsuarioMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\FacturasDetMdl;
use App\Models\Ventas\FacturasMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\VentasEsperaMdl;
use App\Models\Ventas\VentasMdl;
use DateTime;

class CorteCaja extends BaseController
{


    public function index()
    {
        $this->validaSesion();
        $activos = ($this->request->getVar('flchk') ?? '') == '' ? false : true;
        $mdlCorte = new CorteCajaMdl();
        $data = [
            'registros' => $mdlCorte->getRegistros(false, ($this->nIdUsuario == '1' ? false : $this->nIdSucursal), true, $activos),
            'pager' => $mdlCorte->pager,
            'agregaActual' => $_SESSION['nuevoCorteCajero'] ?? false,
            'activaCapturaID' => $_SESSION['nuevoCorteActivaCapID'] ?? '0',
            'esUsuarioAdmin' => ($this->nIdUsuario == '1'), 
            'esEncargado' => ($this->nIdPerfil == '4'),
            'verImprimpirCorte' => $this->aPermiso['oImprimirCorte'] ?? false,
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/cortecaja', $data);
        echo view('templates/footer', $this->dataMenu);
        unset($_SESSION['nuevoCorteActivaCapID']);
        return;
    }

    public function accion($tipoaccion, $id = 0)
    {
        $this->validaSesion();
        $model = new CorteCajaMdl();
        if (isset($_SESSION['nuevoCorteCajero'])) unset($_SESSION['nuevoCorteCajero']);
        // si es agregar, qu no tenga otro abierto en otra caja, debe cerrar primero
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
            'titulo' => $stitulo . ' Corte de Caja',
            'frmURL' => base_url('cortecaja/' . $tipoaccion .
                ($id !== '' ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'nombreUsu' => get_cookie('sNombre'),
            'desdeVentas' => isset($_SESSION['desdeVentas']),
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos($tipoaccion);
            if ($a === false) {
                $r = null;
                if ($tipoaccion == 'a') {
                    $idCorteParaCerrar = $this->request->getVar('idCorteParaCerrar') ?? false;
                    if($idCorteParaCerrar) {
                        $rs = [
                            'nIdCorte' => $idCorteParaCerrar,
                            'sObservaciones' => 'Cierre Directo',
                            'nSaldoFin' => 0,
                            'dtCierre' => (new DateTime())->format('Y-m-d H-i-s')
                        ];
                        $model->save($rs);
                    }
                    // valido el numero de caja antes de guardar
                    $regCajas = $model->getCajasActivas($this->nIdSucursal);
                    if (array_search($this->request->getVar('nNumCaja'), $regCajas) === false) {
                        $r = [
                            'nIdSucursal' => $this->nIdSucursal,
                            'nIdUsuario' => $this->nIdUsuario,
                            'nNumCaja' => $this->request->getVar('nNumCaja'),
                            'nSaldoIni' => round(floatval($this->request->getVar('nSaldoIni')), 2)
                        ];
                    } else {
                        $vista = 'ventas/cortecajamtto';
                        $data['error'] = ['nNumCaja' => 'El numero de caja ya ha sido ocupado'];
                        $regCajas = $model->getCajasActivas($this->nIdSucursal);
                        $data['cajasA'] = $regCajas;
                    }
                } else {
                    $r = [
                        'sObservaciones' => $this->request->getVar('sObservaciones'),
                        'nSaldoFin' => round(floatval($this->request->getVar('nSaldoFin')), 2),
                        'dtCierre' => (new DateTime())->format('Y-m-d H-i-s')
                    ];
                    if ($tipoaccion == 'e') {
                        $r['nIdCorte'] = $id;
                        $mdlVentasEspera = new VentasEsperaMdl();
                        $mdlVentasEspera
                            ->where('nIdUsuario', $this->nIdUsuario)
                            ->where('nIdSucursal', $this->nIdSucursal)
                            ->delete(null, true);
                    }
                }
                if ($r !== null) {
                    $model->save($r);
                    echo 'oK';
                    return;
                }
            } else {
                $data['error'] = $a['amsj'];
                if ($tipoaccion == 'a') {
                    $vista = 'ventas/cortecajamtto';
                    $regCajas = $model->getCajasActivas($this->nIdSucursal);
                    $data['cajasA'] = $regCajas;
                } else {
                    $r = $model->getRegistros($id);
                    $data['numCaja'] = 'Caja ' . $r['nNumCaja'];
                    $data['dtApertura'] = (new DateTime($r['dtApertura']))->format('d-m-Y H-i-s');
                    // echo view('ventas/cortecajamttocierre', $data);
                    // return;
                    $vista = 'ventas/cortecajamttocierre';
                }
            }
        } else {
            if ($tipoaccion == 'a') {
                if($id == '') $id = 0;
                $vista = 'ventas/cortecajamtto';
                $r = $model->getIdCorteActivo($this->nIdUsuario, $this->nIdSucursal);
                if ($r !== null && $id == 0) {
                    if ($r['dtCierre'] == null) {
                        $data['msjError'] = 'El usuario ya tiene un corte abierto.';
                        // echo view('ventas/cortecajaerror', $data);
                        $vista = 'ventas/cortecajaerror';
                        // return;
                    } else {
                        $regCajas = $model->getCajasActivas($this->nIdSucursal);
                        $data['cajasA'] = $regCajas;
                        $data['desdeVentas'] = isset($_SESSION['desdeVentas']);

                    }
                } else {
                    $regCajas = $model->getCajasActivas($this->nIdSucursal);
                    $data['cajasA'] = $regCajas;
                    $data['IdCorteAcerrar'] = $id;
                    $data['desdeVentas'] = isset($_SESSION['desdeVentas']);
                }
            } elseif ($tipoaccion == 'e') {
                $r = $model->getRegistros($id);
                if (intval($r['nIdUsuario']) != intval($this->nIdUsuario)) {
                    $data['msjError'] = 'El usuario actual debe ser el mismo que el <br>' .
                        'mostrado en el registro de corte de caja.';
                    $vista = 'ventas/cortecajaerror';
                    // echo view('ventas/cortecajaerror', $data);
                    // return;
                } else {
                    $data['numCaja'] = 'Caja ' . $r['nNumCaja'];
                    $data['dtApertura'] = (new DateTime($r['dtApertura']))->format('d-m-Y H-i-s');
                    $vista = 'ventas/cortecajamttocierre';
                    // echo view('ventas/cortecajamttocierre', $data);
                    // return;
                }
            }
        }
        echo view($vista, $data);
    }

    public function validaCampos($accion)
    {
        if ($accion == 'a') {
            $reglas = [
                'nNumCaja' => 'required|is_natural_no_zero',
                'nSaldoIni' => 'required|decimal|mayorIgualCero'
            ];
            $reglasMsj = [
                'nNumCaja' => [
                    'required'   => 'Falta el numero de caja',
                    'is_natural_no_zero' => 'Falta el numero de caja'
                ],
                'nSaldoIni' => [
                    'required'   => 'Falta el saldo inicial de caja',
                    'decimal' => 'Debe ser un numero',
                    'mayorIgualCero' => 'El importe no puede ser negativo',

                ]
            ];
        } else {
            $reglas = [
                'nSaldoFin' => 'required|decimal|mayorIgualCero'
            ];
            $reglasMsj = [
                'nSaldoFin' => [
                    'required'   => 'Falta el saldo final en caja',
                    'decimal' => 'Debe ser un numero',
                    'mayorIgualCero' => 'El importe no puede ser negativo',

                ]
            ];
        }
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function imprime($id)
    {
        $mdlCorte = new CorteCajaMdl();
        $data['corte'] = $mdlCorte->getRegistros($id);
        $data['ventas'] = $this->corteLstVentas($data['corte']['dtApertura']);
        $data['corteResumen'] = $this->corteResumenGral($id, $mdlCorte);
        $data['listadoCajero'] = $this->corteLstCajero($id, $mdlCorte);

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/imprimirCorteCaja', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    private function corteResumenGral($id, CorteCajaMdl &$mdlCorte)
    {
        // se genera sobre todos los movimientos de corte de caja
        // los tipos de pagos
        // los movimientos de caja ingresos / egresos
        $data['corte'] = $mdlCorte->getRegistros($id);

        $id = intval($id);

        // $r1 = $mdlCorte->getLstCorteVentas($id, '1'); // Publico
        // $nLim1 = count($r1);
        // $nSum1 = 0;
        $r2 = $mdlCorte->getLstCorteVentas($id, '2'); // remisiones facturadas
        $nLim2 = count($r2);
        $nSum2 = 0;
        $r3 = $mdlCorte->getLstCorteVentas($id, '3'); // remisiones
        $nLim3 = count($r3);
        $nSum3 = 0;
        $bndContinua = ($nLim2 + $nLim3) > 0;
        $v = [];
        $nFila = -1;
        while ($bndContinua) {
            $nFila++;
            $bndContinua = false;
            // if ($nFila < $nLim1) {
            //     $c1 = [$r1[$nFila]['nFolioRemision'], number_format(floatval($r1[$nFila]['nTotal']), 2)];
            //     $nSum1 += round(floatval($r1[$nFila]['nTotal']), 2);
            //     $bndContinua = true;
            // } else {
            //     $c1 = ['', ''];
            // }
            if ($nFila < $nLim2) {
                if (round(floatval($r2[$nFila]['nTotalFactura']), 2) > 0) {
                    $r2[$nFila]['nTotal'] = $r2[$nFila]['nTotalFactura'];
                }
                $c2 = [$r2[$nFila]['nFolioRemision'], $r2[$nFila]['nFolioFactura'], number_format(floatval($r2[$nFila]['nTotal']), 2)];
                $nSum2 += round(floatval($r2[$nFila]['nTotal']), 2);
                $bndContinua = true;
            } else {
                $c2 = ['', '', ''];
            }
            if ($nFila < $nLim3) {
                $c3 = [$r3[$nFila]['nFolioRemision'], $r3[$nFila]['nFolioRemision'], number_format(floatval($r3[$nFila]['nTotal']), 2)];
                $nSum3 += round(floatval($r3[$nFila]['nTotal']), 2);
                $bndContinua = true;
            } else {
                $c3 = ['', '', ''];
            }
            if ($bndContinua) $v[] = [$c2, $c3];
        }
        $data['lstventas'] = $v;
        $data['sumventas'] = [$nSum2, $nSum3];

        $r = $mdlCorte->getLstCorteEScaja($id);
        $data['lstES'] = $r;

        $r = $mdlCorte->getLstCortePagos($id);
        $data['lstPag'] = $r;

        $r = $mdlCorte->getLstCorteDepositos($id);
        $data['lstDep'] = $r;
        return $data;
    }

    private function corteLstCajero($id, CorteCajaMdl &$mdlCorte)
    {
        $regs = $mdlCorte->getLstCorteCajero($id);
        foreach ($regs as $k => $v) {
            $regs[$k]['pagEfec'] = 0;
            $regs[$k]['pagTarj'] = 0;
            $regs[$k]['pagTran'] = 0;
            $regs[$k]['pagCheq'] = 0;
            $regs[$k]['pagCred'] = 0;
            $filasPagos = explode('|', $v['cadPagos']);
            foreach ($filasPagos as $vf) {
                if ($vf === '') continue;
                $r = explode('&', $vf);
                $imp = round(floatval($r[1]), 2);
                switch ($r[0]) {
                    case '1':
                        $ck = 'pagEfec';
                        break;
                    case '2':
                    case '3':
                        $ck = 'pagTarj';
                        break;
                    case '4':
                        $ck = 'pagTran';
                        break;
                    case '5':
                        $ck = 'pagCheq';
                        break;
                    case '7':
                        $ck = 'pagCred';
                        break;
                    default:
                        $ck = '';
                }
                if ($ck != '') $regs[$k][$ck] = $imp;
            }
        }
        return $regs;
    }

    private function corteLstVentas($dFecha)
    {
        $dFini = new DateTime($dFecha);
        $mdlVentas = new VentasMdl();
        $regs = $mdlVentas->getRepoVentasCorte($dFini, $dFini, $this->nIdSucursal);

        foreach ($regs as $k => $v) {
            $regs[$k]['pagTarj'] = 0;
            $regs[$k]['pagCred'] = 0;
            $filasPagos = explode('|', $v['cadPagos']);
            foreach ($filasPagos as $vf) {
                if ($vf === '') continue;
                $r = explode('&', $vf);
                $imp = round(floatval($r[1]), 2);
                $ck = '';
                switch ($r[0]) {
                    case '2':
                    case '3':
                        $ck = 'pagTarj';
                        break;
                    case '7':
                        $ck = 'pagCred';
                        break;
                }
                if ($ck != '') $regs[$k][$ck] = $imp;
            }
            $regs[$k]['nomCliente'] = sprintf('%05d', intval($v['nIdCliente'])) . ' ' . $v['nomCliente'];
        }
        return $regs;
    }
    // se llama desde listado de cortes o desde el listado de remisiones
    public function factura($nId, $tipo)
    {
        $this->validaSesion();
        $mdlCorte = new CorteCajaMdl();
        $tipo = strtoupper($tipo);

        if ($tipo == 'C') { // si es por tipo de corte de caja
            $r = $mdlCorte->getRegistros($nId);
            if (intval($r['nIdUsuario']) != intval($this->nIdUsuario)) {
                $data['msjError'] = 'El usuario actual debe ser el mismo que el <br>' .
                    'mostrado en el registro de corte de caja.';
                $vista = 'ventas/cortecajaerror';
                echo view($vista, $data);
                return;
            }
            // $this->facturaCorte($nId);
        }
        $arrFacturasParaTimbrar = $mdlCorte->getCorteFacturasParaProcesar($nId, $tipo);
        if (count($arrFacturasParaTimbrar) == 0) {
            if ($tipo == 'V')
                $this->indexRemisiones();
            else
                $this->index();
            return;
        }
        $vista = 'ventas/cortecajamttofactura';
        // se busca si existe para dividir
        $nIdVentaParaDividirEnFacturas = false;
        foreach ($arrFacturasParaTimbrar as $arrV) {
            if ($arrV['bVariasFacturas'] == '1') {
                $nIdVentaParaDividirEnFacturas = intval($arrV['nIdVentas']);
                break;
            }
        }
        if ($nIdVentaParaDividirEnFacturas) {
            $mdlVenta = new VentasMdl();
            $_SESSION['divventa'] = [
                'idVenta' => $nIdVentaParaDividirEnFacturas,
                'datos' => $mdlVenta->getRegistros($nIdVentaParaDividirEnFacturas),
                'impEnRemision' => 0,
                'impEnFacturas' => 0,
                'facturas' => [],
                'venta' => [],
                'facturaActiva' => -1
            ];
            $this->calculaImportesVenta($nIdVentaParaDividirEnFacturas);

            $vista = 'ventas/cortecajadivideenfacturas';
            $data = [
                'venta' => $_SESSION['divventa']['datos'],
                'regRemision' => $_SESSION['divventa']['venta'],
                'regFacturas' => $_SESSION['divventa']['facturas'],
                'facturaActiva' => -1,
                'origen' => $tipo,
                'msjError' => ''
            ];
        } else {
            $data = [
                'registros' => $arrFacturasParaTimbrar,
                'frmURL' => base_url('factura/procesacorte/' . $nId . '/' . $tipo),
                'frmURLedo' => base_url('cortecaja/edoProcesoFacturas/' . $nId . '/' . $tipo),
                'sProceso' => 'Procesar ' . strval(count($arrFacturasParaTimbrar) . ' factura(s)')
            ];
        }
        echo view($vista, $data);
        return;
    }

    private function calculaImportesVenta($nIdVentaParaDividirEnFacturas)
    {
        $arrVenta = [];
        $mdlVentaDet = new VentasDetMdl();
        $sumImportes = 0;

        $aRegDet = $mdlVentaDet->getDetalleVenta($nIdVentaParaDividirEnFacturas);
        $nRegistros = count($aRegDet);
        for ($nInd = 0; $nInd < $nRegistros; $nInd++) {
            $nCant = round(floatval($aRegDet[$nInd]['nCant']), 2);
            $nPrecio = round(floatval($aRegDet[$nInd]['nPrecio']), 2);
            $nImporte = round($nCant * $nPrecio, 2);
            $nDescuento = round(floatval($aRegDet[$nInd]['nDescuentoTotal']), 2);      // entra descuento por producto y por remision juntos
            $nImporteFinalProducto = $nImporte - $nDescuento;
            $nPrecioFinalXproducto = round($nImporteFinalProducto / $nCant, 6);
            $aRegDet[$nInd]['precioUniNew'] = $nPrecioFinalXproducto;
            $arrVenta[$aRegDet[$nInd]['nIdArticulo']] = [
                'id' => $aRegDet[$nInd]['nIdArticulo'],
                'des' => $aRegDet[$nInd]['nomArt'],
                'cant' => $nCant,
                'cantori' => $nCant,
                'precio' => $nPrecioFinalXproducto,
                'idVentasDet' => $aRegDet[$nInd]['nIdVentasDet']
            ];
            $sumImportes += round($nPrecioFinalXproducto * $nCant, 2);
        }
        $_SESSION['divventa']['venta'] = $arrVenta;
        $_SESSION['divventa']['impEnRemision'] = $sumImportes;
        return;
    }

    public function edoProcesoFacturas($idCorte, $tipo)
    {
        $mdlCorte = new CorteCajaMdl();
        $arrDatos = $mdlCorte->getAvanceCorteFacturasParaProcesar($idCorte, $tipo);   // proceso de cortedecaja
        $timbrado = intval($arrDatos[0]['timbrados']);
        $total = intval($arrDatos[0]['totales']);
        $cad = 'Procesados ' . $timbrado . ' de ' . $total;

        return json_encode([
            'ok' => '1',
            'msj' => $cad,
            'tot' => $total,
            'act' => $timbrado
        ]);
    }
    // ********** Proceso para la division de facturas
    public function addFactura($tipo)
    {
        $this->validaSesion();
        $bndAgrega = true;
        if ($_SESSION['divventa']['facturaActiva'] >= 0) {
            if (count($_SESSION['divventa']['facturas'][$_SESSION['divventa']['facturaActiva']]) == 0) {
                $bndAgrega = false;
            }
        }
        if ($bndAgrega) {
            $_SESSION['divventa']['facturas'][] = [];
            $_SESSION['divventa']['facturaActiva'] = count($_SESSION['divventa']['facturas']) - 1;
        }
        $data = [
            'venta' => $_SESSION['divventa']['datos'],
            'regRemision' => $_SESSION['divventa']['venta'],
            'regFacturas' => $_SESSION['divventa']['facturas'],
            'origen' => strtoupper($tipo),
            'msjError' => ''
        ];
        echo view('ventas/cortecajadivideenfacturas', $data);
        return;
    }

    public function addFilaFactura($id, $cant, $tipo)
    {
        $this->validaSesion();
        $msjError = '';
        // se valida si existe el id en la remision si no, no se agrega
        if (isset($_SESSION['divventa']['venta'][$id])) {
            if ($_SESSION['divventa']['venta'][$id]['cant'] > 0) {
                if ($_SESSION['divventa']['venta'][$id]['cant'] >= round(floatval($cant), 2)) {
                    if ($_SESSION['divventa']['facturaActiva'] == -1) {
                        $_SESSION['divventa']['facturas'][] = [];
                        $_SESSION['divventa']['facturaActiva'] = 0;
                    }
                    $descripcionRemision = $_SESSION['divventa']['venta'][$id]['des'];
                    $precioRemision = $_SESSION['divventa']['venta'][$id]['precio'];
                    $facturaActiva = $_SESSION['divventa']['facturaActiva'];
                    if (!isset($_SESSION['divventa']['facturas'][$facturaActiva][$id])) {
                        $_SESSION['divventa']['facturas'][$facturaActiva][$id] = [
                            'id' => $id,
                            'des' => $descripcionRemision,
                            'cant' => 0,
                            'precio' => $precioRemision,
                            'idVentasDet' => $_SESSION['divventa']['venta'][$id]['idVentasDet']
                        ];
                    }
                    $_SESSION['divventa']['facturas'][$facturaActiva][$id]['cant'] += round(floatval($cant), 2);
                    $_SESSION['divventa']['venta'][$id]['cant'] -= round(floatval($cant), 2);
                } else {
                    $msjError = 'La cantidad debe ser igual o menor a ' .
                        $_SESSION['divventa']['venta'][$id]['cant'];
                }
            } else {
                $msjError = 'Ya no hay más producto para dividir';
            }
        } else {
            $msjError = 'No existe el ID del articulo en la remisión';
        }
        $data = [
            'venta' => $_SESSION['divventa']['datos'],
            'regRemision' => $_SESSION['divventa']['venta'],
            'regFacturas' => $_SESSION['divventa']['facturas'],
            'origen' => strtoupper($tipo),
            'msjError' => $msjError
        ];
        echo view('ventas/cortecajadivideenfacturas', $data);
        return;
    }

    public function delFactura($id, $tipo)
    {
        $this->validaSesion();
        $id = intval($id);
        foreach ($_SESSION['divventa']['facturas'][$id] as $r) {
            $_SESSION['divventa']['venta'][$r['id']]['cant'] += $r['cant'];
        }
        unset($_SESSION['divventa']['facturas'][$id]);
        $_SESSION['divventa']['facturas'] = array_values($_SESSION['divventa']['facturas']);
        $_SESSION['divventa']['facturaActiva'] = count($_SESSION['divventa']['facturas']) - 1;
        $data = [
            'venta' => $_SESSION['divventa']['datos'],
            'regRemision' => $_SESSION['divventa']['venta'],
            'regFacturas' => $_SESSION['divventa']['facturas'],
            'origen' => strtoupper($tipo),
            'msjError' => ''
        ];
        echo view('ventas/cortecajadivideenfacturas', $data);
        return;
    }

    public function divideFacturas()
    {
        $this->validaSesion();
        $mdlUsuario = new UsuarioMdl();
        $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        if ($regUsu['bDivFacNoValidar'] == '0') {
            // se valida si toda la remision esta facturada
            $cant = 0;
            foreach ($_SESSION['divventa']['venta'] as $v) {
                $cant += round($v['cant'], 3);
            }
            if ($cant > 0) {
                return json_encode(['ok' => '0', 'msj' => 'Se debe de asignar todo el producto en facturas para continuar.']);
            }
        }
        // se modifica la bandera
        // se guarda cada factura ventasSaldo
        // se toma la primera factura y se la agrega el detalle
        // despues se toman las siguientes facturas y se agrega su detalle
        $regFactura = $this->leeFactura($_SESSION['divventa']['idVenta']);
        foreach ($_SESSION['divventa']['facturas'] as $f) {
            $this->guardaFactura($regFactura, $f);
        }
        $mdlVenta = new VentasMdl();
        $mdlVenta->set('bVariasFacturas', '0')
            ->where('nIdVentas', $_SESSION['divventa']['idVenta'])
            ->update();
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'NV');
        return json_encode(['ok' => '1']);
    }

    private function leeFactura($idVenta)
    {
        $mdlFactura = new FacturasMdl();
        $Reg = $mdlFactura
            ->where('nIdVentas', intval($idVenta))
            ->first();
        if ($Reg === null) return false;
        return $Reg;
    }

    private function guardaFactura(&$regFactura, &$detFactura)
    {
        // calculo el total de la factura primero
        $nTotal = 0;
        foreach ($detFactura as $r) {
            $nTotal += $r['cant'] * $r['precio'];
        }
        $mdlFactura = new FacturasMdl();
        if ($regFactura['nIdFacturas'] === false) {
            $idFactura =  $mdlFactura->insert([
                'nIdVentas' => $regFactura['nIdVentas'],
                'nFolioFactura' => 0,
                'sSerie' => '',
                'cUsoCFDI' => $regFactura['cUsoCFDI'],
                'cMetodoPago' => $regFactura['cMetodoPago'],
                'nTotalFactura' => $nTotal
            ]);
        } else {
            $idFactura = $regFactura['nIdFacturas'];
            $regFactura['nIdFacturas'] = false;
            $mdlFactura->set('nTotalFactura', $nTotal)
                ->where('nIdFacturas', $idFactura)->update();
        }
        $mdlFacturaDet = new FacturasDetMdl();
        foreach ($detFactura as $r) {
            $mdlFacturaDet->insert([
                'nIdFacturas' => $idFactura,
                'nIdVentasDet' => $r['idVentasDet'],
                'nCant' => $r['cant']
            ]);
        }
    }

    private function indexRemisiones()
    {
        $this->validaSesion();

        if (isset($_SESSION['vtEntrega'])) unset($_SESSION['vtEntrega']);

        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'));
        $mdlVenta = new VentasMdl();
        $data = [
            'registros' => $mdlVenta->getRegistros(false, $f[0], $f[1], 'DESC', true, $this->nIdSucursal),
            'pager' => $mdlVenta->pager,
            'bndCancela' => $_SESSION['perfilUsuario'] == '-1' ||  $_SESSION['perfilUsuario'] == '4',
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d')
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/remisiones', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }
}
