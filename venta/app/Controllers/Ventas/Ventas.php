<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Almacen\EntregaDetalleMdl;
use App\Models\Almacen\EntregaMdl;
use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\AgenteVentasMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Catalogos\ClienteMdl;
use App\Models\Catalogos\ConfiguracionMdl;
use App\Models\Ventas\FacturasMdl;
use App\Models\Ventas\CotizacionesDetMdl;
use App\Models\Ventas\CotizacionesMdl;
use App\Models\Catalogos\PrecioArticuloMdl;
use App\Models\Catalogos\RazonSocialMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Catalogos\TipoListaMdl;
use App\Models\Catalogos\TipoPagoMdl;
use App\Models\Catalogos\UsoCfdiMdl;
use App\Models\Ventas\VentasConAgenteMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Envios\EnvioMdl;
use App\Models\Seguridad\UsuarioMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\DirEntregaMdl;
use App\Models\Ventas\MovPagAdelMdl;
use App\Models\Ventas\SaldoPagAdelMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\VentasImpuestosMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasNomEntregaMdl;
use App\Models\Ventas\VentasPagoMdl;

use App\Models\Ventas\PagAdelMdl;
use App\Models\Ventas\VentasDetAuxMdl;
use App\Models\Ventas\VentasDetDescripcionMdl;
use App\Models\Ventas\VentasDetRelMdl;
use App\Models\Ventas\VentasSaldoMdl;
use App\Models\Viajes\EnvioDireccionMdl;
use DateTime;
use DateInterval;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class Ventas extends BaseController
{

    private function initVarSesion()
    {

        // NOTA: En esta pantalla de entrega el saldo 
        //   por surtir es 15 (saldo restante)
        //      
        /* campos en ['Ventas']['lstArt']:
            tiene como key el idarticulo
            00 - idArticulo
            01 - descripcion articulo
            02 - precio unitario
            03 - cantidad (en remisiones seria [6] cant a entregar)
            04 - existencia disponible en sucursal
            05 - existencia disponible en otros almacenes
            06 - cant a entregar en sucursal
            07 - cant a enviar al cliente
            08 - IVA
            09 - descuento por producto (%)
            10 - importe descuento producto
            11 - importe descuento gral
            12 - Saldo Por Surtir (campo nPorEntregar) (se inicia con 03- cantidad)
            13 - Producto se carga con el proveedor (modo ENV)
            14 - Existencia en sucursal del producto
            15 - Detalle de productos con acumulados (vigas de diferentes medidas por ejemplo)
                 esto pasa cuando se hace una venta anticipada, donde el cliente compra por ejemplo en ML
                 y se entrega por medidas (las vigas y vigetas).
                 Campos en ['Ventas']['lstArt'][15] tiene como key el idarticulo

                 00 - idArticulo con relacion al padre
                 01 - Descripcion Medida a mostrar
                 02 - Medida a usar como factor
                 03 - Existencia
                 04 - Disponible
                 05 - Otros almacenes
                 06 - cant a entregar en sucursal
                 07 - cant a enviar al cliente

            16 - Indica si se permite ampliar la descripcion del producto, 
                    si es asi se guarda la descripcion original a 
                    partir de la posicion 2 de la cadena
            17 - Peso del articulo (si tiene)
            18 - importe comision bancaria
            19 - precio tapado usado en factura.

           campos en ['Ventas']['lstArtCot']:
            tiene como key el idarticulo
            00 - precio unitario en el que se ofrecio
            01 - indica si el precio no se altera con los descuentos y comisiones
        */

        $_SESSION['Ventas'] = [
            'lstArt' => [],
            'lstArtCot' => [],   // listado de precios originales de la cotizacion o precios manuales (porque se toma de este)
            'lstTipoListas' => [],  // tipos de listas de precios que se puede seleccionar en cotizacion
            'idTipoLista' => '0',     // id del tipo de lista seleccionado
            'chkEnvio' => '',
            'foc' => 'cli',
            'msjErr' => '',
            'descGral' => 0.0,  // porcentaje
            'idCot' => 0,    // folio de cotizacion si la venta viienen de ahi
            'permisoDescuentos' => false,
            'permisoCambioPrecio' => false,
            'permisoCredito' => false,
            'sucEnvio' => 0,
            'idAgente' => 0,
            'nombreAgente' => '',
            'cli' => [
                'id' => '',
                'nom' => '',
                'nomManual' => '',  // para captura manual de nombre
                'email' => '',
                'dire' => '',
                'saldo' => 0.0,
                'envEnt' => '',
                'envDir' => '',
                'envCol' => '',
                'envTel' => '',
                'envRef' => '',
                'tipoLis' => 0,
                'nomTipoLis' => '',
                'tipoCli' => '',
                'idDirEnt' => 0
            ],
            'docu' => ['modoDocu' => 'R', 'variasFacturas' => '0', 'sinMovtoInv' => '1'],
            'pago' => [
                'tipos' => [],
                'lst' => [],
                'sub' => 0.0,
                'des' => 0.0,
                'iva' => 0.0,
                'tot' => 0.0,
                'porcom' => 0.0,
                'selcom' => 0.0,
                'acum' => 0.0,
                'idEfe' => '',
                'idEfeAnti' => '',
                'idCredito' => '',
                'efec' => 0.0,
                'efecReal' => 0.0,
                'cambio' => 0.0,
                'completar' => false,
                'comision' => 0,
                'msjErr' => '',
                'totpagos' => 0,
                'tipoSeleccionado' => ''
            ],
            'factura' => [
                'usoCFDI' => '',
                'metPago' => '',
                'impuestoIVA' => [
                    'id' => '002',
                    'tipo' => 'T',
                    'tipoVal' => 'T',
                    'valor' => 0.16
                ]
            ]
        ];
        $_SESSION['bQuienRecogeEntrega'] = '0';
        $mdlConfig = new ConfiguracionMdl();
        $regConfig = $mdlConfig->getRegistrosByName('PAGOSVEN', true);
        $aTiposAceptados = explode(',', $regConfig[0]['sValor']);
        $mdlPagos = new TipoPagoMdl();
        $r = $mdlPagos->getRegistros();
        foreach ($r as $a) {
            if (!in_array($a['nIdTipoPago'], $aTiposAceptados)) continue;
            $_SESSION['Ventas']['pago']['tipos'][$a['nIdTipoPago']] = [
                $a['sTipoSAT'], round(floatval($a['nComision']) / 100, 4),
                $a['sLeyenda'], round(floatval($a['nComision']), 2),
                $a['cTipo']
            ];
            if ($a['sTipoSAT'] == '01' || $a['sTipoSAT'] == '30') {
                if ($a['cTipo'] == '0') {
                    $_SESSION['Ventas']['pago']['idEfe'] = $a['nIdTipoPago'];
                } elseif ($a['cTipo'] == '1') {
                    $_SESSION['Ventas']['pago']['idEfeAnti'] = $a['nIdTipoPago'];
                } elseif ($a['cTipo'] == '2') {
                    $_SESSION['Ventas']['pago']['idCredito'] = $a['nIdTipoPago'];
                }
            }
        };
    }

    public function index()
    {
        $this->validaSesion();
        $this->validaTurnoCorte();

        if (!isset($_SESSION['Ventas'])) {
            $this->initVarSesion();
        }
        $mdlUsuario = new UsuarioMdl();
        $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        $_SESSION['Ventas']['permisoDescuentos'] = $regUsu['bDaDescuento'];
        $_SESSION['Ventas']['permisoCambioPrecio'] = $regUsu['bDaPrecio'];
        $data = [
            'registros' => $_SESSION['Ventas']['lstArt'],
            'enfoque' => $_SESSION['Ventas']['foc'],
            'cliente' => $_SESSION['Ventas']['cli'],
            'docu' => $_SESSION['Ventas']['docu'],
            'pago' => $_SESSION['Ventas']['pago'],
            'permisoDescuentos' => $_SESSION['Ventas']['permisoDescuentos'],
            'permisoCapturaImporte' => $_SESSION['Ventas']['permisoCambioPrecio'],
            'permisofacturaSinMovtoExistencia' => false,  //isset($this->aPermiso['oFacSinMovtoExis'])
            'nomAgente' =>  intval($_SESSION['Ventas']['idAgente'] ?? '0') > 0 ?  $_SESSION['Ventas']['nombreAgente'] : ''
        ];
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'C') {
            // se hace listado de lista de precios
            $mdlListasPrecio = new TipoListaMdl();
            $regListasPrecio = $mdlListasPrecio->getRegistros(false, false, false, 1);
            $_SESSION['Ventas']['lstTipoListas'] = $regListasPrecio;
            $lst = [];
            foreach ($regListasPrecio as $v) {
                $lst[] = [
                    'nIdTipoLista' => 'R' . $v['nIdTipoLista'],
                    'cNombreTipo' => $v['cNombreTipo'] . ' Rem.'
                ];
                $lst[] = [
                    'nIdTipoLista' => 'F' . $v['nIdTipoLista'],
                    'cNombreTipo' => $v['cNombreTipo'] . ' Fac.'
                ];
            }
            $data['lstTipoListas'] = $lst;
            $data['idTipoLista'] = $_SESSION['Ventas']['idTipoLista'];
        }


        $data['lastDoc'] = isset($_SESSION['lastDoc']);
        $data['esMobil'] = $this->request->getUserAgent()->isMobile();

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/ventas', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    protected PrecioArticuloMdl $mdlPrecio;
    protected function calcularPrecios($idProdACalcular = false)
    {
        $cp = $this->initCalculoPrecio();
        $asum = [
            'tipoListaPrecios' => $cp['idTipoLista'],
            'campoPrecio' => $cp['campoPrecio'],
            'porcenComisionBancaria' => $cp['porcenComiBanc'],
            'descGral' => round($_SESSION['Ventas']['descGral'] / 100, 2),
            'bndPrecioEnCero' => false
        ];
        $this->mdlPrecio = new PrecioArticuloMdl();
        if ($idProdACalcular) {
            $this->calcularPrecio(
                $idProdACalcular,
                $_SESSION['Ventas']['lstArt'][$idProdACalcular],
                $asum
            );
        } else {
            foreach ($_SESSION['Ventas']['lstArt'] as $k => $r) {
                $this->calcularPrecio($k, $r, $asum);
            }
        }
        $this->calcularTotales();
        if ($asum['bndPrecioEnCero']) {
            $_SESSION['Ventas']['pago']['msjErr'] = 'No puede haber precios unitarios en cero.';
            $_SESSION['Ventas']['pago']['completar'] = false;
        } else {
            if ($_SESSION['Ventas']['docu']['modoDocu'] == 'C') {
                $_SESSION['Ventas']['pago']['completar'] = true;
            }
        }
    }

    protected function calcularPrecio($k, $r, &$asum)
    {
        $noAplicarComiDesc = false;
        $nImporteTapadoEnFactura = 0;
        if (isset($_SESSION['Ventas']['lstArtCot'][$k])) {
            $nPrecio = $_SESSION['Ventas']['lstArtCot'][$k][0];
            if ($_SESSION['Ventas']['docu']['modoDocu'] == 'F') {
                $tipoListaAusar =  isset($asum['tipoListaPrecios']) ? $asum['tipoListaPrecios'] : 1; 
                $p = $this->mdlPrecio->buscaPrecio(
                    $this->nIdSucursal,
                    $tipoListaAusar,
                    $k,
                    $r[3]
                );
                if ($p != null) {
                    if (
                        $_SESSION['Ventas']['docu']['modoDocu'] == 'F' &&
                        $p['cPrecioTapadoFactura'] == '1'
                    ) {
                        $nImporteTapadoEnFactura = round(floatval($p['fPrecioTapado']), 2);
                        if($nPrecio >= $nImporteTapadoEnFactura) $nImporteTapadoEnFactura = $nPrecio;
                        // if ($nImporteTapadoEnFactura == 0) $nImporteTapadoEnFactura = round(floatval($nPrecio) * 1.20, 2);
                    }
                }
            }
            if (isset($_SESSION['Ventas']['lstArtCot'][$k][1])) {
                $noAplicarComiDesc = $_SESSION['Ventas']['lstArtCot'][$k][1] == '1';
            }
        } else {
            $p = $this->mdlPrecio->buscaPrecio(
                $this->nIdSucursal,
                $asum['tipoListaPrecios'],
                $k,
                $r[3]
            );
            if ($p == null) {
                $nPrecio = 0;
            } else {
                $nPrecio = $p[$asum['campoPrecio']];
                if (
                    $_SESSION['Ventas']['docu']['modoDocu'] == 'F' &&
                    $p['cPrecioTapadoFactura'] == '1'
                ) {
                    $nImporteTapadoEnFactura = round(floatval($p['fPrecioTapado']), 2);
                    if ($nImporteTapadoEnFactura == 0) $nImporteTapadoEnFactura = round(floatval($nPrecio) * 1.20, 2);
                }
            }
        }


        if ($noAplicarComiDesc === false) {
            $nPrecio = round(floatval($nPrecio), 4);
            $nImp = round($r[3] * $nPrecio, 4);

            $importeComisionBancaria = round($nImp * $asum['porcenComisionBancaria'], 4);
            $nImp0 = $nImp + $importeComisionBancaria;

            $descProd = round($_SESSION['Ventas']['lstArt'][$k][9] / 100, 4);
            $nDescP = round($nImp0 * $descProd, 4);
            $nImp1 = $nImp0 - $nDescP;

            $nDescG = round($nImp1 * $asum['descGral'], 4);
            //$nIva = round(($nImp1 - $nDescG) * $factorIva, 2);
            $nIva = 0;
            $nImporteTapadoEnFactura = round($r[3] * $nImporteTapadoEnFactura, 4);
            $nImporteTapadoEnFactura = $nImporteTapadoEnFactura + round($nImporteTapadoEnFactura * $asum['porcenComisionBancaria'], 4);
        } else {
            $nPrecio = round(floatval($nPrecio), 4);
            $nImp = round($r[3] * $nPrecio, 4);
            $nIva = 0;
            $nDescP = 0;
            $nDescG = 0;
            $importeComisionBancaria = 0;
        }

        if ($nImp == 0) $asum['bndPrecioEnCero'] = true;
        $_SESSION['Ventas']['lstArt'][$k][2] = $nPrecio;
        $_SESSION['Ventas']['lstArt'][$k][8] =  $nIva;
        $_SESSION['Ventas']['lstArt'][$k][10] = $nDescP;
        $_SESSION['Ventas']['lstArt'][$k][11] = $nDescG;
        $_SESSION['Ventas']['lstArt'][$k][18] = $importeComisionBancaria;
        $_SESSION['Ventas']['lstArt'][$k][19] = $nImporteTapadoEnFactura;
    }

    protected function calcularTotales()
    {
        $nSumSub = 0.0;
        $nSumComisionBancaria = 0.0;
        $nSumDescG = 0.0;
        $nSumIva = 0.0;
        foreach ($_SESSION['Ventas']['lstArt'] as $r) {
            $nImp = round($r[2] * $r[3], 2);
            $nSumSub += $nImp + $r[18] - $r[10];
            $nSumComisionBancaria += $r[18];
            $nSumDescG += $r[11];
            $nSumIva += $r[8];
        }
        $_SESSION['Ventas']['pago']['comision'] = $nSumComisionBancaria;
        $_SESSION['Ventas']['pago']['sub'] = $nSumSub;
        $_SESSION['Ventas']['pago']['des'] = $nSumDescG;
        $_SESSION['Ventas']['pago']['iva'] = $nSumIva;
        $_SESSION['Ventas']['pago']['tot'] = round(
            $_SESSION['Ventas']['pago']['sub']
                - $_SESSION['Ventas']['pago']['des']
                + $_SESSION['Ventas']['pago']['iva'],
            2
        );
    }

    protected function initPagos()
    {
        $_SESSION['Ventas']['pago']['msjErr'] = '';
        $_SESSION['Ventas']['pago']['lst'] = [];
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'C') return;
        // $_SESSION['Ventas']['pago']['completar'] = false;
        $mdlCliente = new ClienteMdl();
        $r = $mdlCliente->getRegistros($_SESSION['Ventas']['cli']['id']);
        $s = round(floatval($r['nSaldo'] ?? 0), 2);

        $idPagoAnticipado = $_SESSION['Ventas']['pago']['idEfeAnti'];
        $tot = $_SESSION['Ventas']['pago']['tot'];
        if ($s > 0 && $tot > 0) {
            $_SESSION['Ventas']['pago']['lst'][$idPagoAnticipado] = [
                'id' => $idPagoAnticipado,
                'des' => $_SESSION['Ventas']['pago']['tipos'][$idPagoAnticipado][2],
                'imp' => $s >= $tot ? $tot : $s,
                'com' => 0,
                'porcom' => 0
            ];
            // $_SESSION['Ventas']['pago']['completar'] = $s >= $tot;
        }
    }

    protected function calcularPagos(array $aLstPagos, $initValores = false)
    {
        $_SESSION['Ventas']['pago']['msjErr'] = '';
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'C') return;
        if ($initValores) {
            $_SESSION['Ventas']['pago']['efec'] = 0.0;
            $_SESSION['Ventas']['pago']['efecReal'] = 0.0;
            $_SESSION['Ventas']['pago']['acum'] = 0.0;
            $_SESSION['Ventas']['pago']['cambio'] = 0.0;
            $_SESSION['Ventas']['pago']['completar'] = false;
        }
        $tot = $_SESSION['Ventas']['pago']['tot'];
        $b = [
            'totPagado' => 0.0,
            'efec' => 0.0,
            'efecReal' => 0.0,
            'cambio' => 0.0,
            'err' => '',
            'enableBtnCompletar' => false
        ];
        $idEfectivo = $_SESSION['Ventas']['pago']['idEfe'];
        $idPagoAnticipado = $_SESSION['Ventas']['pago']['idEfeAnti'];
        foreach ($aLstPagos as $k => $v) {
            $imp = $v['imp'];
            if ($v['id'] == $idEfectivo) {
                $b['efec'] = $v['imp'];
            } elseif ($v['id'] == $idPagoAnticipado) {
                $mdlCliente = new ClienteMdl();
                $r = $mdlCliente->getRegistros($_SESSION['Ventas']['cli']['id']);
                $s = round(floatval($r['nSaldo'] ?? 0), 2);
                if ($v['imp'] > $s) {
                    if ($s > 0) {
                        $aLstPagos[$k]['imp'] = $s;
                        $imp = $s;
                    } else {
                        $_SESSION['Ventas']['pago']['msjErr'] = 'El importe del pago anticipado(' .
                            number_format($v['imp'], 2) . ') NO ' .
                            'puede ser mayor que el importe del saldo del cliente (' .
                            number_format($s, 2) . ')';
                        return false;
                    }
                }
            }
            $b['totPagado'] += $imp;
        }

        $b['totPagado'] = round($b['totPagado'], 2);
        if (round($b['totPagado'] - $b['efec'], 2) <= $tot) {
            $b['cambio'] = $b['totPagado'] - $_SESSION['Ventas']['pago']['tot'];
            if ($b['totPagado'] >= $tot) {
                $b['enableBtnCompletar'] = true;
                if ($b['totPagado'] > $tot) {
                    $b['efecReal'] = round($tot - $b['totPagado'] + $b['efec'], 2);
                }
            }

            if (($_SESSION['Ventas']['cli']['id'] == '0' || $_SESSION['Ventas']['cli']['id'] == '') && $tot > 0) {
                $_SESSION['Ventas']['pago']['completar'] = false;
                $_SESSION['Ventas']['pago']['msjErr'] = 'Recuerde seleccionar al cliente a vender, antes de completar el pago.';
                return false;
            }

            $_SESSION['Ventas']['pago']['efec'] = $b['efec'];
            $_SESSION['Ventas']['pago']['efecReal'] = $b['efecReal'];
            $_SESSION['Ventas']['pago']['acum'] = $b['totPagado'];
            $_SESSION['Ventas']['pago']['cambio'] = $b['cambio'];
            $_SESSION['Ventas']['pago']['completar'] = $b['enableBtnCompletar'];
        } else {
            $_SESSION['Ventas']['pago']['msjErr'] = 'No se puede exceder el pago del total a pagar (' .
                number_format($_SESSION['Ventas']['pago']['tot'], 2) . ')';
            return false;
        }
        return true;
    }

    // tipoMov : P indica que se recalcula al agregar/modificar/borrar pagos
    //           A indica que se recalcula al agregar/modificar/borrar articulos/precios
    private function recalculaPagos($a, $tipoMov = 'P')
    {
        $tot = round($_SESSION['Ventas']['pago']['tot'], 2);
        $b = [
            'acum' => 0.0,
            'efec' => 0.0,
            'efecReal' => 0.0,
            'cambio' => 0.0,
            'err' => '',
            'completar' => false
        ];

        $mdlCliente = new ClienteMdl();
        $r = $mdlCliente->getRegistros($_SESSION['Ventas']['cli']['id']);
        $s = round(floatval($r['nSaldo'] ?? 0), 2);
        $idPago = $_SESSION['Ventas']['pago']['idEfeAnti'];
        if ($tipoMov == 'A') {
            $_SESSION['Ventas']['pago']['lst'] = [];
            $a = [];
            if ($s > 0) {
                // si tiene saldo, se va en automatico al pago efectivo aplicado con deposito
                if ($tot > 0) {
                    $_SESSION['Ventas']['pago']['lst'][$idPago] = [
                        'id' => $idPago,
                        'des' => $_SESSION['Ventas']['pago']['tipos'][$idPago][2],
                        'imp' => $s >= $tot ? $tot : $s
                    ];
                    $a = $_SESSION['Ventas']['pago']['lst'];
                }
            }
        } else {
            if (array_key_exists($idPago, $a)) {
                if ($a[$idPago]['imp'] > $s) {
                    if (isset($_SESSION['Ventas']['pago']['lst'][$idPago])) {
                        if ($s == 0) {
                            unset($_SESSION['Ventas']['pago']['lst'][$idPago]);
                        } else {
                            $_SESSION['Ventas']['pago']['lst'][$idPago] = $s >= $tot ? $tot : $s;
                        }
                    }
                    if ($s == 0) {
                        unset($a[$idPago]);
                    } else {
                        $a[$idPago]['imp'] = $s >= $tot ? $tot : $s;
                    }
                    $b['err'] = 'El importe del pago anticipado(' .
                        number_format($a[$idPago]['imp'], 2) . ') NO ' .
                        'puede ser mayor que el importe del saldo del cliente (' .
                        number_format($s, 2) . ')';
                }
            }
        }

        $id = $_SESSION['Ventas']['pago']['idEfe'];
        foreach ($a as $v) {
            $b['acum'] += $v['imp'];
            if ($v['id'] == $id) $b['efec'] = $v['imp'];
        }
        if (round($b['acum'] - $b['efec'], 2) <= $tot) {
            $b['cambio'] = $b['acum'] - $_SESSION['Ventas']['pago']['tot'];
            if (round($b['acum'], 2) >= $tot) $b['completar'] = true;
            if (round($b['acum'], 2) > $tot) {
                $b['efecReal'] = round($tot - $b['acum'] + $b['efec'], 2);
            }

            $_SESSION['Ventas']['pago']['msjErr'] = '';
            $_SESSION['Ventas']['pago']['efec'] = $b['efec'];
            $_SESSION['Ventas']['pago']['efecReal'] = $b['efecReal'];
            $_SESSION['Ventas']['pago']['acum'] = $b['acum'];
            $_SESSION['Ventas']['pago']['cambio'] = $b['cambio'];
        } else {
            $b['err'] = 'No se puede exceder el pago del total a pagar (' .
                number_format($_SESSION['Ventas']['pago']['tot'], 2) . ')';
            if ($tipoMov == 'P') {
                if (round($b['acum'], 2) >= $tot) $b['completar'] = true;
            }
        }

        $_SESSION['Ventas']['pago']['completar'] = $b['completar'];
        return $b;
    }


    public function agregaCliente()
    {
        function conCotizacion()
        {
            $_SESSION['Ventas']['cli']['nomManual'] = $_POST['nIdCliente'];
            $_SESSION['Ventas']['cli']['tipoCli'] = 'N';
            if ($_SESSION['Ventas']['idTipoLista'] == '0') {
                $_SESSION['Ventas']['foc'] = 'tiplis';
            } else {
                $_SESSION['Ventas']['foc'] = 'art';
            }
        };

        function otrosFormatos($idSucursal)
        {
            $model = new ClienteMdl();
            $datos =  $model->getRegistros($_POST['nIdCliente']);
            $_SESSION['Ventas']['cli']['id'] = $datos['nIdCliente'];
            $_SESSION['Ventas']['cli']['nom'] = $datos['sNombre'];
            $_SESSION['Ventas']['cli']['email'] = $datos['email'];
            $_SESSION['Ventas']['cli']['dire'] = $datos['sDireccion'];
            $_SESSION['Ventas']['cli']['saldo'] = round(floatval($datos['nSaldo']), 2);
            $_SESSION['Ventas']['cli']['tipoLis'] = $datos['nIdTipoLista'];
            $_SESSION['Ventas']['cli']['nomTipoLis'] = $datos['nomTipoLista'];
            $_SESSION['Ventas']['cli']['tipoCli'] = $datos['cTipoCliente'];

            $_SESSION['Ventas']['foc'] = 'art';
        };

        $this->validaSesion();
        $session = session();
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'C') {
            conCotizacion();
        } else {
            otrosFormatos($this->nIdSucursal);
        }

        $this->calcularPrecios();
        $this->initPagos();
        $this->index();
    }

    public function agregaArticulo()
    {
        $this->validaSesion();
        $mdlArticulo = new ArticuloMdl();
        //validamos si existe otro registro y lo sumamos ahi
        // se valida si hay en existencia primero se suma el registro
        $idArticulo = $this->request->getVar('nIdArticulo');
        $regArt = $mdlArticulo->getRegistros($idArticulo);
        $nCant = round(floatval($this->request->getVar('nCant')), 3);
        if ($this->request->getVar('valImporte') !== null) {
            $_SESSION['Ventas']['lstArtCot'][$idArticulo][0] = round(floatval($this->request->getVar('valImporte')), 2);
        }
        if ($regArt['cConDescripcionAdicional'] == '1') {
            $regArt['cConDescripcionAdicional'] .= trim($regArt['sDescripcion']);
        }
        $_SESSION['Ventas']['lstArt'][$idArticulo] = [
            $idArticulo,
            $this->request->getVar('dlArticulos0'),
            0,      // precio
            $nCant,
            0, 0,   // exist. disponible, exist. en otros almacenes, 
            0, 0,   // Cant. a entregar, Cant a enviar
            0, 0.0, // IVA, descuento prod.
            0, 0, 0, // Imp. Desc. prod., Imp. Desc. Gral., Saldo por Surtir
            '0', 0, false,       // articulo en modo ENV, exis. fisica sucursal.
            $regArt['cConDescripcionAdicional'],
            round(floatval($regArt['fPeso']), 2),   // peso del producto por unidad
            0, 0   // Imp. comision bancaria, Imp. precio tapado factura
        ];
        $this->calcularPrecios($idArticulo);
        $this->initPagos();
        $this->calcularPagos($_SESSION['Ventas']['pago']['lst'], true);

        $_SESSION['Ventas']['foc'] = 'art';

        $this->index();
    }

    public function borraArticulo($id)
    {
        $this->validaSesion();
        unset($_SESSION['Ventas']['lstArt'][$id]);
        unset($_SESSION['Ventas']['lstArtCot'][$id]);
        $this->calcularTotales();
        $this->initPagos();
        $this->calcularPagos($_SESSION['Ventas']['pago']['lst'], true);

        $_SESSION['Ventas']['foc'] = 'art';
        $this->index();
    }

    public function modoDocu()
    {
        $this->validaSesion();
        $_SESSION['Ventas']['docu']['modoDocu'] = $this->request->getVar('sModoDoc');
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'F') {
            if ($this->request->getVar('chkVariasFacturas') !== null) {
                $_SESSION['Ventas']['docu']['variasFacturas'] = $this->request->getVar('chkVariasFacturas') == 'on' ? '1' : '0';
            } else {
                $_SESSION['Ventas']['docu']['variasFacturas'] = '0';
            }
        }
        if (
            $_SESSION['Ventas']['docu']['modoDocu'] == 'R' ||
            $_SESSION['Ventas']['docu']['modoDocu'] == 'C' ||
            $_SESSION['Ventas']['docu']['modoDocu'] == 'F'
        ) {
            $this->calcularPrecios();
            $this->initPagos();
            $this->calcularPagos($_SESSION['Ventas']['pago']['lst'], true);
            $_SESSION['Ventas']['pago']['msjErr'] = '';
        }
        if (
            $_SESSION['Ventas']['docu']['modoDocu'] == 'CB' ||
            $_SESSION['Ventas']['docu']['modoDocu'] == 'RB'
        ) {
            $_SESSION['Ventas']['foc'] = 'art1';
        } else {
            $_SESSION['Ventas']['foc'] = 'art';
        }

        $this->index();
    }

    public function agregaPago()
    {
        $this->validaSesion();
        $idTipoPago = $this->request->getVar('frm03Tipo');
        $importe = round(floatval($this->request->getVar('frm03Pago')), 2);

        if ($idTipoPago == $_SESSION['Ventas']['pago']['idCredito']) {
            if ($_SESSION['Ventas']['cli']['tipoCli'] == 'P') {
                $_SESSION['Ventas']['pago']['msjErr'] = 'No se puede asignar un credito a un cliente de publico gral.';
                $this->index();
                return;
            }
            if ($_SESSION['Ventas']['cli']['tipoCli'] == 'N') {
                $mdlUsuario = new UsuarioMdl();
                $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
                if ($regUsu['bPermitirCredito'] == '0') {
                    $mdlVentasSaldo = new VentasSaldoMdl();
                    $reg = $mdlVentasSaldo->getSaldoCliente($_SESSION['Ventas']['cli']['id']);
                    if (count($reg) > 0) {
                        $_SESSION['Ventas']['pago']['msjErr'] = 'El cliente tiene ' . $reg[0]['nCont'] . ' creditos activos por un total de ' . number_format(floatval($reg[0]['nSum']), 2);
                        $this->index();
                        return;
                    }
                }
            }
        }

        $a = $_SESSION['Ventas']['pago']['lst'];
        $a[$idTipoPago] = [
            'id' => $idTipoPago,
            'des' => $_SESSION['Ventas']['pago']['tipos'][$idTipoPago][2],
            'imp' => $importe,
            'com' => $_SESSION['Ventas']['pago']['tipos'][$idTipoPago][3],
            'porcom' => $_SESSION['Ventas']['pago']['tipos'][$idTipoPago][1]
        ];
        $this->calcularPagos($a);
        if ($_SESSION['Ventas']['pago']['msjErr'] == '') {
            $_SESSION['Ventas']['pago']['lst'] = $a;
        }

        $_SESSION['Ventas']['foc'] = 'pag';
        $this->index();
    }

    public function borraPago($id, $idPagoActual)
    {
        $this->validaSesion();

        $_SESSION['Ventas']['pago']['msjErr'] = '';
        unset($_SESSION['Ventas']['pago']['lst'][$id]);
        $comisionAnterior = $_SESSION['Ventas']['pago']['selcom'];
        $_SESSION['Ventas']['pago']['selcom'] = $_SESSION['Ventas']['pago']['tipos'][$idPagoActual][1];
        foreach ($_SESSION['Ventas']['pago']['lst'] as $r) {
            if ($_SESSION['Ventas']['pago']['selcom'] < $r['porcom']) {
                $_SESSION['Ventas']['pago']['selcom'] = $r['porcom'];
            }
        }
        if ($comisionAnterior != $_SESSION['Ventas']['pago']['selcom']) {
            $this->calcularPrecios();
        }

        $this->calcularPagos($_SESSION['Ventas']['pago']['lst'], true);

        $_SESSION['Ventas']['foc'] = 'pag';
        $this->index();
    }

    public function nVenta()
    {
        $this->validaSesion();
        unset($_SESSION['Ventas']);
        $this->index();
    }

    public function entrega()
    {
        $this->validaSesion();
        $idSucursal = $this->request->getVar('nIdSucursalEntrega') ?? $this->nIdSucursal;
        $model = new InventarioMdl();
        $mdlArticulo = new ArticuloMdl();
        $_SESSION['Ventas']['lstArtRelacionados'] = [];
        $nSumEntregar = 0;
        $porSurtir = 0;
        foreach ($_SESSION['Ventas']['lstArt'] as $k => $v) {
            $_SESSION['Ventas']['lstArt'][$k][12] = $_SESSION['Ventas']['lstArt'][$k][3];
            $_SESSION['Ventas']['lstArt'][$k][15] = false;
            $_SESSION['Ventas']['lstArt'][$k][7] = 0;
            $_SESSION['Ventas']['lstArt'][$k][6] = 0;
            $r = $model->getRegistrosInv($v[0], $idSucursal);
            if ($r === null) {
                $_SESSION['Ventas']['lstArt'][$k][4] = 0;
                $_SESSION['Ventas']['lstArt'][$k][5] = 0;
                $_SESSION['Ventas']['lstArt'][$k][14] = 0;
            } else {
                $regArticulo = $mdlArticulo->getRegistros($v[0]);
                if ($regArticulo['cConArticuloRelacionado'] == '1') {
                    // busco todos los articulos relacionados
                    $mdlInven = new InventarioMdl();
                    $regArticulos = $mdlArticulo->getRegistrosRelacionados($v[0]);
                    $arrMedidas = [];
                    foreach ($regArticulos as $m) {
                        $regInv = $mdlInven->getRegistrosInv($m['nIdArticulo'], $idSucursal);
                        $exiSuc = round(floatval($regInv['exiSuc'] ?? 0), 3);
                        $exiOtros = round(floatval($regInv['exiOtros'] ?? 0), 3);
                        $exiFisic = round(floatval($regInv['exiFis'] ?? 0), 3);
                        $arrMedidas[] = [
                            number_format(floatval($m['nMedida']), 2, '.', ''),
                            $m['nIdArticulo'],
                            $exiFisic,
                            $exiSuc,
                            $exiOtros
                        ];
                    }
                    $_SESSION['Ventas']['lstArtRelacionados'][strval($v[0])] = $arrMedidas;
                    $_SESSION['Ventas']['lstArt'][$k][4] = 0;
                    $_SESSION['Ventas']['lstArt'][$k][5] = 0;
                    $_SESSION['Ventas']['lstArt'][$k][14] = 0;
                    $_SESSION['Ventas']['lstArt'][$k][15] = [];
                } else {
                    $exiSuc = round(floatval($r['exiSuc'] ?? 0), 3);
                    $exiOtros = round(floatval($r['exiOtros'] ?? 0), 3);
                    $exiFisic = round(floatval($r['exiFis'] ?? 0), 3);
                    $_SESSION['Ventas']['lstArt'][$k][4] = $exiSuc;
                    $_SESSION['Ventas']['lstArt'][$k][5] = $exiOtros;
                    $_SESSION['Ventas']['lstArt'][$k][14] = $exiFisic;
                    if (round(floatval($_SESSION['Ventas']['lstArt'][$k][3]), 3) <= $exiSuc) {
                        $_SESSION['Ventas']['lstArt'][$k][6] =
                            $_SESSION['Ventas']['lstArt'][$k][3];
                    } else {
                        $_SESSION['Ventas']['lstArt'][$k][6] = $exiSuc;
                    }
                }
                $_SESSION['Ventas']['lstArt'][$k][12] =
                    $_SESSION['Ventas']['lstArt'][$k][3] -
                    $_SESSION['Ventas']['lstArt'][$k][6];
                $porSurtir += $_SESSION['Ventas']['lstArt'][$k][12];
                $nSumEntregar += $_SESSION['Ventas']['lstArt'][$k][6];
            }
        }
        $bEntrega = round($nSumEntregar, 3) > 0 ? '1' : '0';
        $this->assignaDatosEnvio('0', $bEntrega);
    }

    public function entregaTodo()
    {
        session();
        $nSumEntrega = 0;
        foreach ($_SESSION['Ventas']['lstArt'] as $k => $v) {
            if ($v[15] !== false) {
                $sumConcentrado = 0;
                foreach ($v[15] as $kk => $vv) {
                    // si tengo en envio, se lo sumo a la entrega
                    $_SESSION['Ventas']['lstArt'][$k][15][$kk][6] +=
                        $_SESSION['Ventas']['lstArt'][$k][15][$kk][7];
                    $_SESSION['Ventas']['lstArt'][$k][15][$kk][7] = 0;
                    $sumConcentrado += round($vv[2] * $_SESSION['Ventas']['lstArt'][$k][15][$kk][6], 3);
                }
                $_SESSION['Ventas']['lstArt'][$k][6] = $sumConcentrado;
                $_SESSION['Ventas']['lstArt'][$k][7] = 0;
                $nSumEntrega += $_SESSION['Ventas']['lstArt'][$k][6];
                continue;
            }
            $mdlArticulo = new ArticuloMdl();
            $bndValExistencia = ($mdlArticulo->getRegistros($k))['cSinExistencia'] == '0';
            if ($bndValExistencia === false) {
                $_SESSION['Ventas']['lstArt'][$k][6] =
                    round(floatval($_SESSION['Ventas']['lstArt'][$k][3]), 3);
            } elseif (round(floatval($_SESSION['Ventas']['lstArt'][$k][3]), 3) <= round(floatval($v[14]), 3)) {
                $_SESSION['Ventas']['lstArt'][$k][6] =
                    round(floatval($_SESSION['Ventas']['lstArt'][$k][3]), 3);
            } else {
                $_SESSION['Ventas']['lstArt'][$k][6] = round(floatval($v[14]), 3);
            }
            $_SESSION['Ventas']['lstArt'][$k][7] = 0;
            $_SESSION['Ventas']['lstArt'][$k][12] =
                $_SESSION['Ventas']['lstArt'][$k][3] -
                $_SESSION['Ventas']['lstArt'][$k][6] -
                $_SESSION['Ventas']['lstArt'][$k][7];
            $nSumEntrega += $_SESSION['Ventas']['lstArt'][$k][6];
        }
        $bEntrega = round($nSumEntrega, 3) > 0 ? '1' : '0';
        $this->assignaDatosEnvio('0', $bEntrega);
    }

    public function enviaTodo()
    {
        session();
        $nSumEnvio = 0;
        foreach ($_SESSION['Ventas']['lstArt'] as $k => $v) {
            if ($v[15] !== false) {
                $sumConcentrado = 0;
                foreach ($v[15] as $kk => $vv) {
                    // si tengo en entrega, se lo sumo a la envio
                    $_SESSION['Ventas']['lstArt'][$k][15][$kk][7] +=
                        $_SESSION['Ventas']['lstArt'][$k][15][$kk][6];
                    $_SESSION['Ventas']['lstArt'][$k][15][$kk][6] = 0;
                    $sumConcentrado += round($vv[2] * $_SESSION['Ventas']['lstArt'][$k][15][$kk][7], 3);
                }
                $_SESSION['Ventas']['lstArt'][$k][7] = $sumConcentrado;
                $_SESSION['Ventas']['lstArt'][$k][6] = 0;
                $nSumEnvio += $_SESSION['Ventas']['lstArt'][$k][7];
                continue;
            }

            $mdlArticulo = new ArticuloMdl();
            $bndValExistencia = ($mdlArticulo->getRegistros($k))['cSinExistencia'] == '0' && false; // por ser envio no se valida
            if ($bndValExistencia === false) {
                $_SESSION['Ventas']['lstArt'][$k][7] =
                    round(floatval($_SESSION['Ventas']['lstArt'][$k][3]), 3);
            } elseif (round(floatval($_SESSION['Ventas']['lstArt'][$k][3]), 3) <= round(floatval($v[4]), 3)) {
                $_SESSION['Ventas']['lstArt'][$k][7] =
                    round(floatval($_SESSION['Ventas']['lstArt'][$k][3]), 3);
            } else {
                $_SESSION['Ventas']['lstArt'][$k][7] = round(floatval($v[4]), 3);
            }
            $_SESSION['Ventas']['lstArt'][$k][6] = 0;
            $_SESSION['Ventas']['lstArt'][$k][12] =
                $_SESSION['Ventas']['lstArt'][$k][3] -
                $_SESSION['Ventas']['lstArt'][$k][6] -
                $_SESSION['Ventas']['lstArt'][$k][7];
            $nSumEnvio += $_SESSION['Ventas']['lstArt'][$k][7];
        }
        $bEnvio = round($nSumEnvio, 3) > 0 ? '1' : '0';
        $this->assignaDatosEnvio($bEnvio, '0');
    }

    private function assignaDatosEnvio($bEnvio, $bEntrega)
    {
        if ($this->request->getVar('sEnvEntrega') != null) {
            $_SESSION['Ventas']['cli']['envDir'] = $this->request->getVar('sEnvDireccion') ?? $_SESSION['Ventas']['cli']['envDir'];
            $_SESSION['Ventas']['cli']['envCol'] = $this->request->getVar('sEnvColonia') ?? $_SESSION['Ventas']['cli']['envCol'];
            $_SESSION['Ventas']['cli']['envTel'] = $this->request->getVar('sEnvTelefono') ?? $_SESSION['Ventas']['cli']['envTel'];
            $_SESSION['Ventas']['cli']['envEnt'] = $this->request->getVar('sEnvEntrega');
            $_SESSION['Ventas']['cli']['envRef'] = $this->request->getVar('sEnvReferencia');
        }
        $mdlCliente = new ClienteMdl();
        $regCli = $mdlCliente->getRegistros($_SESSION['Ventas']['cli']['id']);
        if (strlen(trim($regCli['sRFC'])) == 13) {
            $bEsPersonaFisica = true;
            $bEsPersonaMoral = null;
        } else {
            $bEsPersonaFisica = null;
            $bEsPersonaMoral = true;
        }
        $mdlUsoCfdi = new UsoCfdiMdl();
        $regUso = $mdlUsoCfdi->getRegistros(false, false, $bEsPersonaFisica, $bEsPersonaMoral);
        //array_splice($regUso, 0, 0, [['nIdSucursal' => $this->nIdSucursal, 'sDescripcion' => 'Esta bodega']]);
        $data['usocfdi'] = $regUso;
        $data['nIdUsoCFDI'] = $this->request->getVar('nIdUsoCFDI') ?? $regCli['cIdUsoCfdi'];
        $mdlSucursal = new SucursalMdl();
        $regSuc = $mdlSucursal->getRegistros(false, false, $this->nIdSucursal);
        array_splice($regSuc, 0, 0, [['nIdSucursal' => $this->nIdSucursal, 'sDescripcion' => 'Esta bodega']]);
        $data['regSucursales'] = $regSuc;
        $data['direcciones'] = [];
        if ($_SESSION['Ventas']['cli']['tipoCli'] == 'P') {
            $lstDir = [];
        } else {
            $mdlDirEntrega = new DirEntregaMdl();
            $lstDir = $mdlDirEntrega->listaDireccionesCliente($_SESSION['Ventas']['cli']['id']);
        }
        $data['direcciones'] = $lstDir;
        $_SESSION['Ventas']['cli']['idDirEnt'] = $this->request->getVar('nIdDirEntrega') ?? '0';

        $data['registros'] = $_SESSION['Ventas']['lstArt'];
        $data['cliente'] = $_SESSION['Ventas']['cli'];
        $data['docu'] = $_SESSION['Ventas']['docu'];
        $data['hayEnvio'] = $bEnvio;
        $data['hayEntrega'] = $bEntrega;
        $data['hayEntregaPublico'] = $bEntrega == '1' && $_SESSION['Ventas']['cli']['tipoCli'] == 'P' ? '1' : '0';
        $data['nIdSucursalActual'] = $this->request->getVar('nIdSucursalEntrega') ?? $this->nIdSucursal;
        $data['cIdMetodoPago'] = $this->request->getVar('cIdMetodoPago') ?? '0';
        $data['artRelacionados'] = $_SESSION['Ventas']['lstArtRelacionados'];
        $data['esMobil'] = $this->request->getUserAgent()->isMobile();
        echo view('ventas/entrega', $data);
    }

    public function entregaArt($id, $tipoMov, $cant)
    {
        $this->validaSesion();
        $msjErr = '';
        $cant = round(floatval($cant), 3);
        $cantPorEntregar = $_SESSION['Ventas']['lstArt'][$id][3];
        $cantActual = $tipoMov == 'entrega' ? $_SESSION['Ventas']['lstArt'][$id][6] : $_SESSION['Ventas']['lstArt'][$id][7];
        $mdlArticulo = new ArticuloMdl();
        $bndValExistencia = ($mdlArticulo->getRegistros($id))['cSinExistencia'] == '0' && $tipoMov == 'entrega';
        $mdlUsuario = new UsuarioMdl();

        // $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        $bndValidaEntregaConDiponibles = false;
        // otros posibles condiciones
        // $bndValidaEntregaConDiponibles = $regUsu['bDaVtaSinDisponibles'] == '1';          // con permiso temporal
        // $bndValidaEntregaConDiponibles = isset(this->aPermiso['oEntregaSinDisponibles']); // con permiso del perfil
        if ($_SESSION['Ventas']['lstArt'][$id][13] == '1') {
            // en modo env no se valida existencia, solo debe validarse con la cantidad
            // y debe ser el movto de tipo envio no de entrega
            if ($tipoMov == 'entrega') {
                // $msjErr = 'No se puede realizar entregas en modo ENV, desmárquelo';
            } else {
                $_SESSION['Ventas']['lstArt'][$id][6] = 0;
                $nval = $cantPorEntregar - $cant;
                if (round($nval, 3) < 0) {
                    $cant = $cantPorEntregar;
                }
                $_SESSION['Ventas']['lstArt'][$id][7] = round($cant, 3);
                $_SESSION['Ventas']['lstArt'][$id][12] = round(
                    $cantPorEntregar -
                        $_SESSION['Ventas']['lstArt'][$id][6] -
                        $_SESSION['Ventas']['lstArt'][$id][7],
                    3
                );
            }
        } elseif (($bndValExistencia && $bndValidaEntregaConDiponibles && ($cant > $_SESSION['Ventas']['lstArt'][$id][4]))) {
            $msjErr = 'No hay suficientes productos disponibles en almacén';
        } elseif (($bndValExistencia && ($cant > $_SESSION['Ventas']['lstArt'][$id][14]))) {
            $msjErr = 'No hay suficientes existencias fisicas del articulo en almacén';
        } elseif ($tipoMov == 'envio' && ($cant > $cantPorEntregar)) {
            $msjErr = 'La cantidad a enviar no puede ser mayor que la cantidad comprada';
        } else {
            // segun lo capturado se hace el ajuste automatico sobre la cantidad capturada
            $nEntrega = $_SESSION['Ventas']['lstArt'][$id][6];
            $nEnvio = $_SESSION['Ventas']['lstArt'][$id][7];
            if ($tipoMov == 'entrega') {
                $nval = $cantPorEntregar - ($cant + $nEnvio);
                if (round($nval, 3) < 0) {
                    $cant = $cantPorEntregar - $nEnvio;
                }
                $_SESSION['Ventas']['lstArt'][$id][6] = round($cant, 3);
            } else {
                $nval = $cantPorEntregar - ($nEntrega + $cant);
                if (round($nval, 3) < 0) {
                    $cant = $cantPorEntregar - $nEntrega;
                }
                $_SESSION['Ventas']['lstArt'][$id][7] = round($cant, 3);
            }
            $_SESSION['Ventas']['lstArt'][$id][12] = round(
                $_SESSION['Ventas']['lstArt'][$id][3] -
                    $_SESSION['Ventas']['lstArt'][$id][6] -
                    $_SESSION['Ventas']['lstArt'][$id][7],
                3
            );
        }
        if ($msjErr == '') {
            return $this->retornaEntregaArt($id, []);
        }
        return json_encode(['ok' => '0', 'msj' => $msjErr, 'cantActual' => $cantActual]);
    }

    public function entregaArtD($id)
    {
        $this->validaSesion();
        $nIdArticulo = intval($this->request->getVar('nIdArticulo'));
        $nEntrega = round(floatval($this->request->getVar('nEntrega') ?? 0), 2);
        $nEnvio = round(floatval($this->request->getVar('nEnvio') ?? 0), 2);
        $nCantidadComprado = $_SESSION['Ventas']['lstArt'][$id][3];
        // valida los datos
        $msjErr = '';
        if ($nIdArticulo == 0) $msjErr = 'Falta seleccionar la medida.';
        if ($nEntrega == 0 && $nEnvio == 0) $msjErr .= ($msjErr == '' ? '' : '<br>') . 'Falta capturar la cantidad.';
        if ($msjErr != '') return json_encode(['ok' => '0', 'msj' => $msjErr]);

        $mdlArticulo = new ArticuloMdl();
        $regArt = $mdlArticulo->getRegistros($nIdArticulo);
        $mdlInventario = new InventarioMdl();
        $idSucursal = $this->request->getVar('nIdSucursalEntrega') ?? $this->nIdSucursal;
        $regInv = $mdlInventario->getRegistrosInv($nIdArticulo, $idSucursal);
        $mdlUsuario = new UsuarioMdl();
        // $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        $bndValidaEntregaConDiponibles = false;

        $nMedida = round(floatval($regArt['nMedida']), 2);
        $nCantidad = $nEntrega + $nEnvio;
        // sumar los registros para calcular el total
        $nSumMedidaEntrega = $nMedida * $nEntrega;
        $nSumMedidaEnvio = $nMedida * $nEnvio;
        foreach ($_SESSION['Ventas']['lstArt'][$id][15] as $k => $v) {
            if ($k == $nIdArticulo) continue;
            $nSumMedidaEntrega += $v[2] * $v[6];
            $nSumMedidaEnvio   += $v[2] * $v[7];
        }
        if ($nCantidadComprado < round($nSumMedidaEntrega + $nSumMedidaEnvio, 2)) {
            $msjErr = 'La cantidad a entregar no puede ser mayor que la cantidad comprada';
        } elseif ($bndValidaEntregaConDiponibles && (round(floatval($regInv['exiSuc']), 3) < $nEntrega)) {
            $msjErr = 'No hay suficientes productos disponibles en almacén';
        } elseif ($nEntrega > round(floatval($regInv['exiFis']), 3)) {
            $msjErr = 'No hay suficientes existencias en almacén';
        } else {
            $_SESSION['Ventas']['lstArt'][$id][15][$nIdArticulo] = [
                $nIdArticulo,
                number_format($nMedida, 2),
                $nMedida,
                round(floatval($regInv['exiFis']), 3),
                round(floatval($regInv['exiSuc']), 3),
                round(floatval($regInv['exiOtros']), 3),
                $nEntrega,
                $nEnvio
            ];
            $_SESSION['Ventas']['lstArt'][$id][6] = round($nSumMedidaEntrega, 3);
            $_SESSION['Ventas']['lstArt'][$id][7] = round($nSumMedidaEnvio, 3);

            $_SESSION['Ventas']['lstArt'][$id][12] = round(
                $_SESSION['Ventas']['lstArt'][$id][3] -
                    $_SESSION['Ventas']['lstArt'][$id][6] -
                    $_SESSION['Ventas']['lstArt'][$id][7],
                3
            );
        }
        if ($msjErr == '') {
            return $this->retornaEntregaArt($id, [
                'idm' => $id,
                'nIdArticulo' => $nIdArticulo,
                'medida' => strval($nMedida),
                'exis' => round(floatval($regInv['exiFis']), 3),
                'dis' => round(floatval($regInv['exiSuc']), 3),
                'otr' => round(floatval($regInv['exiOtros']), 3),
                'nEnt' => $nEntrega,
                'nEnv' => $nEnvio
            ]);
        }
        return json_encode(['ok' => '0', 'msj' => $msjErr]);
    }

    public function borraArtD($idm, $idArticulo)
    {
        session();
        unset($_SESSION['Ventas']['lstArt'][$idm][15][$idArticulo]);
        $nSumMedidaEntrega = 0;
        $nSumMedidaEnvio = 0;
        foreach ($_SESSION['Ventas']['lstArt'][$idm][15] as $k => $v) {
            $nSumMedidaEntrega += $v[2] * $v[6];
            $nSumMedidaEnvio   += $v[2] * $v[7];
        }
        $_SESSION['Ventas']['lstArt'][$idm][6] = round($nSumMedidaEntrega, 3);
        $_SESSION['Ventas']['lstArt'][$idm][7] = round($nSumMedidaEnvio, 3);
        return $this->retornaEntregaArt($idm, []);
    }

    private function retornaEntregaArt($id, $aValores)
    {
        $sumEntrega = 0;
        $sumEnvio = 0;
        foreach ($_SESSION['Ventas']['lstArt'] as $v) {
            $sumEnvio += $v[7];
            $sumEntrega += $v[6];
        }
        $nPorSurtir = $_SESSION['Ventas']['lstArt'][$id][3] -
            $_SESSION['Ventas']['lstArt'][$id][6] -
            $_SESSION['Ventas']['lstArt'][$id][7];

        return json_encode([
            'ok' => '1',
            'id' => $id,
            'hayEntrega' => ($sumEntrega == 0 ? '0' : '1'),
            'hayEnvio' => ($sumEnvio == 0 ? '0' : '1'),
            'hayEntregaPublico' => ($_SESSION['Ventas']['cli']['tipoCli'] == 'P' && $sumEntrega > 0) ? '1' : '0',
            'nEntrega' => $_SESSION['Ventas']['lstArt'][$id][6],
            'nEnvio' => $_SESSION['Ventas']['lstArt'][$id][7],
            'porSurtir' => round($nPorSurtir, 2),
            'bndModoEnv' => $_SESSION['Ventas']['lstArt'][$id][13],
            'valores' => $aValores
        ]);
    }

    public function agregarDireccion()
    {
        $session = session();
        $dir = str_replace(
            [chr(13) . chr(10), chr(9), chr(13)],
            [chr(10), ' ', chr(10)],
            $this->request->getVar('sEnvDireccion')
        );
        $ref = str_replace(
            [chr(13) . chr(10), chr(9), chr(13)],
            [chr(10), ' ', chr(10)],
            $this->request->getVar('sEnvReferencia')
        );

        $mdlDirEntrega = new DirEntregaMdl();
        $id = $mdlDirEntrega->insert([
            'nIdCliente' => $_SESSION['Ventas']['cli']['id'],
            'sEnvEntrega' => $this->request->getVar('sEnvEntrega'),
            'sEnvDireccion' => $dir,
            'sEnvColonia' => $this->request->getVar('sEnvColonia'),
            'sEnvTelefono' => $this->request->getVar('sEnvTelefono'),
            'sEnvReferencia' => $ref
        ]);
        $_SESSION['Ventas']['cli']['idDirEnt'] = $id;
        $reg = $mdlDirEntrega->listaDireccionesCliente(
            $_SESSION['Ventas']['cli']['id']
        );
        return json_encode(['ok' => '1', 'id' => $id, 'reg' => $reg]);
    }

    public function initCalculoPrecio()
    {
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'C') {
            $tipoListaPrecios = substr($_SESSION['Ventas']['idTipoLista'], 1);
            if (substr($_SESSION['Ventas']['idTipoLista'], 0, 1) == 'F') {
                $campoPrecio = 'fPrecioFactura';
            } else {
                $campoPrecio = 'fPrecio';
            }
            $porcenComisionBancaria = 0;
        } else {
            $tipoListaPrecios = $_SESSION['Ventas']['cli']['tipoLis'];
            if ($_SESSION['Ventas']['docu']['modoDocu'] == 'R') {
                $campoPrecio = 'fPrecio';
            } else {
                $campoPrecio = 'fPrecioFactura';
            }
            $porcenComisionBancaria = round($_SESSION['Ventas']['pago']['selcom'], 4);
        }
        return [
            'idTipoLista' => $tipoListaPrecios,
            'campoPrecio' => $campoPrecio,
            'porcenComiBanc' => $porcenComisionBancaria
        ];
    }

    public function valExistencia($id, $cant)
    {
        $session = session();
        // se verifica si se controla existencia
        $mdlArticulo = new ArticuloMdl();
        $rArt = $mdlArticulo->getRegistros($id);
        $cp = $this->initCalculoPrecio();
        $idSucursal = $this->nIdSucursal;
        // se valida el precio
        $mdlPrecio = new PrecioArticuloMdl();
        $r = $mdlPrecio->buscaPrecio(
            $idSucursal,
            $cp['idTipoLista'],
            $id,
            1
        );
        $nPrecio = round(floatval($r == null ? 0 : $r['fPrecio']), 2);
        if ($nPrecio == 0 && $rArt['cImporteManual'] == '0') {
            echo 'oKmsjEl articulo no tiene precio';
            return;
        }
        if ($rArt['cSinExistencia'] == '1') {
            if ($rArt['cImporteManual'] == '1') {
                $data['nomProd'] = $rArt['sDescripcion'];
                echo view('ventas/capturaPrecio', $data);
            } else {
                echo 'oK';
            }
            return;
        }
        $model = new InventarioMdl();
        $r = $model->getRegistros($id, false);

        $disponible = 0.0;
        $total = 0.0;
        foreach ($r as $k => $v) {
            $val = round(floatval($v['fExistencia']) - floatval($v['fComprometido']), 3);
            $r[$k]['fReal'] = $val;
            $total += $val;
            if ($v['nIdArticulo'] == $id && $v['nIdSucursal'] == $idSucursal) {
                $disponible = $val;
            }
        }

        if (round(floatval($cant), 3) <= $disponible) {
            if ($rArt['cImporteManual'] == '1') {
                $data['nomProd'] = $rArt['sDescripcion'];
                echo view('ventas/capturaPrecio', $data);
            } else {
                echo 'oK';
            }
        } else {
            $data['registros'] = $r;
            $data['idSucursal'] = $idSucursal;
            $data['total'] = $total;
            $data['disponible'] = $disponible;
            $data['textoColor'] = (round(floatval($cant), 3) <= $total) ? 'text-primary' : 'text-danger';
            $data['bHasImporte'] = $rArt['cImporteManual'] == '1';
            echo view('ventas/articulosDisponibles', $data);
        }
        return;
    }

    public function reimprimirLastDoc()
    {
        $this->validaSesion();
        $esMobil = $this->request->getUserAgent()->isMobile();
        if (!isset($_SESSION['lastDoc'])) {
            if ($esMobil) {
                return json_encode(['ok' => '0']);
            } else {
                $this->index();
                return;
            }
}

        if ($_SESSION['lastDoc']['tipo'] == 'V') {
            $this->ImprimeRemision($_SESSION['lastDoc']['id'], false);
        } else {
            $this->imprimeCotizacion($_SESSION['lastDoc']['id'], false);
        }
    }

    public function consultaRemision($idVenta, $reiniciaVenta = true, $dest = 'venta', $nIdEntrega = false)
    {
        // para el caso de impresion de entrega en otra bodega $dest = 'entregaOtra'
        $session = session();
        // se toma para el caso de la impresion del cliente, el precio tapado mas alto
        // que es el de la factura. y parece del tipo publico.
        // se leen lo datos de la tabla y se calculan los importes
        // se debe leer los precios tapados
        // no comision bancaria 
        $data = [];
        $mdlVentas = new VentasMdl();
        $mdlVentasDet = new VentasDetMdl();
        $mdlAgenteVenta = new VentasConAgenteMdl();
        $mdlCli = new ClienteMdl();
        $mdlVentasImpuestos = new VentasImpuestosMdl();
        $mdlTipoLista = new TipoListaMdl();
        $mdlPrecio = new PrecioArticuloMdl();
        $mdlTipoPago = new VentasPagoMdl();
        $mdlDirEntrega = new DirEntregaMdl();
        $mdlVentasNomEntrega = new VentasNomEntregaMdl();
        $mdlRazonSocial = new SucursalMdl();
        $mdlEntregad = new EntregaDetalleMdl();
        $mdlEnviod = new EnvioDetalleMdl();
        $detEntrega = [];
        if ($dest == 'entregaos') {
            $detEntrega = $mdlEntregad->where('nIdEntrega', $nIdEntrega)->findAll();
            $IndDetEntrega = array_column($detEntrega, 'nIdArticulo');
        } elseif ($dest == 'reimpenvio') {
            $detEntrega = $mdlEnviod->where('nIdEnvio', $nIdEntrega)->findAll();
            $IndDetEntrega = array_column($detEntrega, 'nIdArticulo');
        } else {
            if ($nIdEntrega === false || $nIdEntrega === '0') $nIdEntrega = $_SESSION['nIdEntregaEnVenta'] ?? 0;
        }
        $nIdEnvio = $_SESSION['nIdEntregaEnEnvio'] ?? 0;
        $rpt = $mdlTipoLista->getRegistros(false, true);

        // $tipoListaTapado = $rpt['nIdTipoLista'];
        $rv = $mdlVentas->getRegistros($idVenta);
        $rvd = $mdlVentasDet->getDetalleVenta($rv['nIdVentas']);
        $rc = $mdlCli->getRegistros($rv['nIdCliente']);
        $tipoListaTapado = $rc['nIdTipoLista'];
        $rrz = $mdlRazonSocial->getRegistroParaFacturar($rv['nIdSucursal']);
        $agv = $mdlAgenteVenta->getRegistroAgente($rv['nIdVentas']);

        $nomUsuario = $rv['nomUsuario'];

        $porcDescGral = round(floatval($rv['nPorcenDescuento']) / 100, 2);
        $porcenComisionBancaria = round(floatval($rv['nPorcenComision']), 2);

        $nSumSub = 0.0;
        $nSumaDescuentos = 0.0;
        $nDescuentoRemision = 0.0;
        $nDescuentoEnProducto = 0.0;
        $nSumIva = 0.0;

        $bSeEntregaTodo = true;
        $bSeEnviaTodo = true;
        $bHayEntrega = false;
        $bHayEnvio = false;
        $entregaConCero = false;
        $envioConCero = false;
        $nSumSubTap = 0.0;
        $nSumComisioBancariaTapado = 0.0;
        $det = [];
        $nIndDet = -1;
        foreach ($rvd as $k => $v) {
            $nCant = round(floatval($v['nCant']), 3);
            $porcDescProd = round(floatval($v['nPorcenDescuento']) / 100, 2);
            if ($rv['cClasificacionVenta'] == '1') {
                $pt = $mdlPrecio->buscaPrecio(
                    $rv['nIdSucursal'],
                    $tipoListaTapado,
                    $v['nIdArticulo'],
                    1
                );
            } elseif ($rv['cClasificacionVenta'] == '2') {
                $pt['fPrecioFactura'] = round(floatval($v['nPrecio']), 2);
            }

            // por ahora es solo el iva
            $factorIva = 0;
            $rIB = $mdlVentasImpuestos->getImpuestoBase($v['nIdVentasDet']);
            if (count($rIB[0]) > 0) {
                $factorIva = round(floatval($rIB[0]['fValor']), 4);
            }

            $nPrecio = round(floatval($v['nPrecio']), 2);
            $nImp = round($nCant * $nPrecio, 2);
            $nSumSub += $nImp;

            $nPrecioT = round(floatval(($pt['fPrecioTapado'] ?? null) == null ? round(floatval($v['nPrecio']) * 1.20, 2) : $pt['fPrecioTapado']), 2);
            $importeComisionBancaria = round($nPrecioT * $porcenComisionBancaria, 2);
            $nSumComisioBancariaTapado += $importeComisionBancaria;
            $nPrecioT = $nPrecioT + $importeComisionBancaria;
            if ($nPrecioT < $nPrecio) $nPrecioT = round($nPrecio * 1.20, 2);
            $nImpT = round($nCant * $nPrecioT, 2);
            $nSumSubTap += $nImpT;

            $impDescProd = round($nImp * $porcDescProd, 2);
            $nDescuentoEnProducto += $impDescProd;
            $nSumaDescuentos += $impDescProd;
            $nTotProd = $nImp - $impDescProd;

            $impDescGral = round($nTotProd * $porcDescGral, 2);
            $nDescuentoRemision += $impDescGral;
            $nSumaDescuentos += $impDescGral;
            // $nIva = round(($nTotProd - $impDescGral) * $factorIva, 2);
            $nIva = 0;
            $nSumIva += $nIva;
            $det[] = [
                $v['nomArt'],
                $nPrecio,
                $nCant,
                round(floatval($v['nEntrega']), 3),
                $nPrecioT,
                $nIva,
                $impDescProd,
                $impDescGral,
                round(floatval($v['nEnvio']), 3),
                null,
                null
            ];
            $nIndDet++;
            if ($dest == 'entregaos') {
                $det[$nIndDet][8] = 0;
                if ($v['cConArticuloRelacionado'] == '0') {
                    $idArti = $v['nIdArticulo'];
                    $av = array_search($idArti, $IndDetEntrega);
                    if ($av === false) {
                        $det[$nIndDet][3] = 0;
                    } else {
                        $det[$nIndDet][3] = round(floatval($detEntrega[$av]['fCantidad']), 3);
                    }
                }
            } elseif ($dest == 'reimpenvio') {
                $det[$nIndDet][3] = 0;
                if ($v['cConArticuloRelacionado'] == '0') {
                    $idArti = $v['nIdArticulo'];
                    $av = array_search($idArti, $IndDetEntrega);
                    if ($av === false) {
                        $det[$nIndDet][8] = 0;
                    } else {
                        $det[$nIndDet][8] = round(floatval($detEntrega[$av]['fCantidad']), 3);
                    }
                }
            }
            if ($v['cConArticuloRelacionado'] == '1') {
                if ($det[$nIndDet][3] > 0) {
                    $regsArtRelacionados = $mdlEntregad->select('alentregadetalle.nIdArticulo, ' .
                        'alentregadetalle.fCantidad, ' .
                        'b.sDescripcion, b.cSinExistencia, b.nMedida, b.fPeso')
                        ->join('alarticulo b', 'alentregadetalle.nIdArticulo = b.nidArticulo')
                        ->where([
                            'alentregadetalle.nIdEntrega' => $nIdEntrega,
                            'b.nIdArticuloAcumulador' => intval($v['nIdArticulo'])
                        ])->findAll();
                    foreach ($regsArtRelacionados as $kk => $vv) {
                        $pt = $mdlPrecio->buscaPrecio(
                            $rv['nIdSucursal'],
                            $tipoListaTapado,
                            $vv['nIdArticulo'],
                            1
                        );
                        $nPrecioT = round(floatval($pt == null ? 0 : $pt['fPrecioTapado']), 2);
                        $importeComisionBancaria = round($nPrecioT * $porcenComisionBancaria, 2);
                        $nPrecioT = $nPrecioT + $importeComisionBancaria;
                        $regsArtRelacionados[$kk]['fPrecio'] = $nPrecioT;
                    }
                    $det[$nIndDet][9] = [$regsArtRelacionados, count($regsArtRelacionados), false];
                }
                if ($det[$nIndDet][8] > 0) {
                    $regsArtRelacionados = $mdlEnviod->select('enenviodetalle.nIdArticulo, ' .
                        'enenviodetalle.fCantidad, ' .
                        'b.sDescripcion, b.cSinExistencia, b.nMedida, b.fPeso')
                        ->join('alarticulo b', 'enenviodetalle.nIdArticulo = b.nidArticulo')
                        ->where([
                            'enenviodetalle.nIdEnvio' => $nIdEnvio,
                            'b.nIdArticuloAcumulador' => intval($v['nIdArticulo'])
                        ])->findAll();
                    foreach ($regsArtRelacionados as $kk => $vv) {
                        $pt = $mdlPrecio->buscaPrecio(
                            $rv['nIdSucursal'],
                            $tipoListaTapado,
                            $vv['nIdArticulo'],
                            1
                        );
                        $nPrecioT = round(floatval($pt == null ? 0 : $pt['fPrecioTapado']), 2);
                        $importeComisionBancaria = round($nPrecioT * $porcenComisionBancaria, 2);
                        $nPrecioT = $nPrecioT + $importeComisionBancaria;
                        $regsArtRelacionados[$kk]['fPrecio'] = $nPrecioT;
                    }
                    $det[$nIndDet][10] = [$regsArtRelacionados, count($regsArtRelacionados), false];
                }
            }
            if ($det[$nIndDet][3] > 0) {
                $bHayEntrega = true;
                if ($det[$nIndDet][3] < $nCant) $bSeEntregaTodo = false;
            } else {
                $entregaConCero = true;
            }
            if ($det[$nIndDet][8] > 0) {
                $bHayEnvio = true;
                if ($det[$nIndDet][8] < $nCant) $bSeEnviaTodo = false;
            } else {
                $envioConCero = true;
            }
        }
        if ($bHayEntrega && $entregaConCero) $bSeEntregaTodo = false;
        if ($bHayEnvio && $envioConCero) $bSeEnviaTodo = false;
        $nDiferencia = round($nSumSubTap - ($nSumSub - $nSumaDescuentos), 2);
        $data = [
            'cli' => $rc,
            'lst' => $det,
            'agev' => ($agv === null ? '' : sprintf('%03d ', intval($agv['nIdAgenteVentas'])) . $agv['sNombre']),
            'nFolio' => $rv['nFolioRemision'],
            'sumaSubTotalReal' => $nSumSub,
            'sumaSubTotalTapado' => $nSumSubTap,
            'sumaDescuentos' => $nSumaDescuentos,
            'sumaComiBancariaTapado' => $nSumComisioBancariaTapado,
            'bonificaciones' => $nDiferencia,
            'sumaIVA' => $nSumIva,
            'impTotal' => $nSumSub - $nSumaDescuentos + $nSumIva,
            'descuentoRemision' => $nDescuentoRemision,
            'descuentoEnProducto' => $nDescuentoEnProducto,
            'nIdEntrega' => $_SESSION['nIdEntregaEnVenta'] ?? 0,
            'nIdEnvio' => $_SESSION['nIdEntregaEnEnvio'] ?? 0,
            'rzLeyenda' => $rrz[0]['sLeyenda'],
            'nomUsu' => ($rv['nIdUsuario'] == '0' ? '' : sprintf('%03d ', intval($rv['nIdUsuario'])) . $rv['nomUsuario']),
            'bParaEnvio' => $_SESSION['bParaEnvio'] ?? '0',
            'bParaEntrega' => $_SESSION['bParaEntrega'] ?? '0',
            'bQuienRecogeEntrega' => $_SESSION['bQuienRecogeEntrega'] ?? '0',
            'bSeEntregaTodo' => $bSeEntregaTodo,
            'bSeEnviaTodo' => $bSeEnviaTodo,
            'bEntregaEnOtraSuc' => '0'
        ];
        if ($dest == 'entregaos') {
            $data['nIdEntrega'] = $nIdEntrega;
            $data['bParaEntrega'] = '1';
            $data['nIdEnvio'] = 0;
            $data['bParaEnvio'] = '0';
            $data['bQuienRecogeEntrega'] = '0';
            $mdlEntrega = new EntregaDetalleMdl();
            $detEntrega = $mdlEntrega->where('nIdEntrega', $nIdEntrega)->findAll();
        } elseif ($dest == 'reimpenvio') {
            $data['nIdEntrega'] = 0;
            $data['bParaEntrega'] = '0';
            $data['nIdEnvio'] = $nIdEntrega;
            $data['bParaEnvio'] = '1';
            $data['bQuienRecogeEntrega'] = '0';
            $rv['bEnvio'] = '1';
            $mdlEntrega = new EnvioDetalleMdl();
            $detEntrega = $mdlEntrega->where('nIdEnvio', $nIdEntrega)->findAll();
        }
        $ln = '<br>';   // chr(10)
        if ($rv['bEnvio'] == '1') {
            $mdlEnvioDireccion = new EnvioDireccionMdl();
            $rTmp = $mdlEnvioDireccion->where([
                'nIdDirEntrega' => $rv['nIdDirEntrega'],
                'nIdEnvio <=' => $data['nIdEnvio']
            ])->orderBy('nIdDirEntrega, nIdEnvio DESC')
                ->limit(1)->first();
            if ($rTmp != null) {
                $tok = strtok($rTmp['sDirecEnvio'], '||');
                $a = '';
                $nContTok = 0;
                while ($tok !== false) {
                    $nContTok++;
                    $a .= $tok;
                    if ($nContTok == 1 || $nContTok == 4)
                        $a .= $ln;
                    else
                        $a .= ' ';
                    if ($nContTok == 3) $a .= '. Tel. ';
                    $tok = strtok('||');
                }
                $data['enviarA'] = $a;
            } else {
                $dir = $mdlDirEntrega->getRegistros($rv['nIdDirEntrega']);
                if ($dir === null) {
                    $data['enviarA'] = '';
                } else {
                    $data['enviarA'] = $dir['sEnvEntrega'] . $ln .
                        $dir['sEnvDireccion'] . ' ' .
                        $dir['sEnvColonia'] . '. Tel. ' .
                        $dir['sEnvTelefono'] . $ln .
                        $dir['sEnvReferencia'];
                }
            }
        } else {
            $cEntrega = $mdlVentasNomEntrega->getNomEntrega($idVenta);
            $data['enviarA'] = 'ENTREGAR EN BODEGA.' . $ln . ($cEntrega['cNomEntrega'] ?? '');
        }
        $data['entregarA'] = '';
        if ($data['bParaEntrega'] != '0') {
            $cEntrega = $mdlVentasNomEntrega->getNomEntrega($idVenta);
            $mdlEntrega = new EntregaMdl();
            $detEntrega = $mdlEntrega->where('nIdEntrega', $data['nIdEntrega'])->first();
            $data['entregarA'] = 'ENTREGAR EN BODEGA';
            if ($rv['nIdSucursal'] != $detEntrega['nIdSucursal']) {
                $mdlSucursal = new SucursalMdl();
                if ($dest == 'entregaos') {
                    $nomSucDest = $mdlSucursal->getRegistros($rv['nIdSucursal']);
                    $data['entregarA'] .= $ln . 'Sucursal de Origen: ' . $nomSucDest['sDescripcion'];
                } else {
                    $nomSucDest = $mdlSucursal->getRegistros($detEntrega['nIdSucursal']);
                    $data['entregarA'] .= $ln . 'Sucursal de Entrega: ' . $nomSucDest['sDescripcion'];
                }
                $data['bEntregaEnOtraSuc'] = '1';
            } else {
                $data['entregarA'] .= '.';
            }
            $data['entregarA'] .= $ln . ($cEntrega['cNomEntrega'] ?? '');
        }
        // $data['modoDocu'] = $_SESSION['Ventas']['docu']['modoDocu'];
        $data['fechaImp'] = new DateTime($rv['dtAlta']);
        $_SESSION['fechaImp'] = new DateTime();
        $modelSuc =  new SucursalMdl();
        $data['suc'] = $modelSuc->getRegistros($this->nIdSucursal);
        $data['reiniciaVenta'] = $reiniciaVenta;
        $data['destino'] = $dest;
        $data['lstpagos'] = $mdlTipoPago->getRegistros($idVenta);

        // $mdlSucursal = new Sucur
        // $pdf = new Fpdf('P', 'mm', 'Letter');
        // $pdf->SetFont('Arial');
        // $data['pdf'] = $pdf;
        // $this->response->setHeader('Content-Type', 'application/pdf');        
        return $data;
    }

    public function imprimeRemision($idVenta, $reiniciaVenta = true, $dest = 'venta', $nIdEntrega = false)
    {
        $this->validaSesion();
        if ($dest == 'back' && intval($_SESSION['lastSurtimiento'] ?? '0') == intval($idVenta)) {
            // se llamo desde la consulta de remisiones
            $dest = 'entrega';
        }
        if ($dest == 'cierra') {
            $data['cierraVentana'] = 'true';
            $dest = 'venta';
        } else {
            $data['cierraVentana'] = 'false';
        }
        $data['dat'] = $this->consultaRemision($idVenta, $reiniciaVenta, $dest, $nIdEntrega);
        $data['dat']['aInfoSis'] = $this->dataMenu['aInfoSis'];
        $data['dat']['esMobil'] = $this->request->getUserAgent()->isMobile();
        unset($_SESSION['ventacancela']);

        // echo view('templates/header', $this->dataMenu);
        echo view('ventas/imprimeremision3', $data);
        // echo view('templates/footer', $this->dataMenu);
    }

    public function generaCotizacion($idCot)
    {
        $data = [
            'idcoti' => $idCot
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/generaCotiVentas', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function consultaRemision2Cancel($idVenta, $reiniciaVenta = true, $dest = 'venta')
    {
        // Calcular cuanto se va a devolver.
        // Se tiene que efectuar la entrega/envio para que se pueda cancelar la venta
        // Si se cancela con entrega/envio pendiente se debe validar que no se pueda cancelar la 
        // entrega/envio 
        // Dejar en ceros el detalle de la venta para que no se pueda programar mas entregas/envios


        $mdlVentas = new VentasMdl();
        $mdlVentasDet = new VentasDetMdl();
        $mdlCli = new ClienteMdl();
        $mdlPorEntregar = new EntregaMdl();

        $rv = $mdlVentas->getRegistros($idVenta);
        $rvd = $mdlVentasDet->getDetalleVenta($rv['nIdVentas']);
        $rc = $mdlCli->getRegistros($rv['nIdCliente']);
        if ($rv['dtCancela'] === null) {
            $timestamp = strtotime(date("d-m-Y"));
            $rv['dtCancela'] = date("Y-m-d", $timestamp);
        }
        $detxentregar = $mdlPorEntregar->articulosporentregar($this->nIdSucursal, $idVenta);

        foreach ($detxentregar as $dxe) {
            $needle = array_search($dxe['nIdArticulo'], array_column($rvd, 'nIdArticulo'));
            if ($needle === false)
                continue;
            $rvd[$needle]['nPorEntregar'] += $dxe['fPorRecibir'];
        };
        $fTotalADevolver = 0;
        foreach ($rvd as $dt) {
            $fTotalADevolver += $dt['nPrecio'] * $dt['nPorEntregar'];
        }
        $data = [
            'registro' => $rv,
            'registros' => $rvd,
            'cliente' => $rc,
            'detxent' => $detxentregar,
        ];
        $data['registro']['fADevolver'] = $fTotalADevolver;
        return $data;
    }

    public function verRemision($idVenta, $reiniciaVenta = true, $dest = 'venta')
    {
        $mdlSaldoPagAdel = new SaldoPagAdelMdl();
        $mdlVentaDet = new VentasDetMdl();

        session();
        if (!isset($_SESSION['ventacancela']['registro'])) {
            $data = $this->consultaRemision2Cancel($idVenta, $reiniciaVenta, $dest);
            $_SESSION['ventacancela']['registro'] = $data['registro'];
            $_SESSION['ventacancela']['registros'] = $data['registros'];
            $_SESSION['ventacancela']['cliente'] = $data['cliente'];
            $_SESSION['ventacancela']['detxent'] = $data['detxent'];
            $timestamp = strtotime(date("d-m-Y"));
            $_SESSION['ventacancela']['registro']['dtCancela'] = date("Y-m-d", $timestamp);
        } else {
            $data['registro'] = $_SESSION['ventacancela']['registro'];
            $data['registros'] = $_SESSION['ventacancela']['registros'];
            $data['cliente'] = $_SESSION['ventacancela']['cliente'];
            $data['detxent'] = $_SESSION['ventacancela']['detxent'];
        }

        $data['titulo'] = 'Cancelar remisión';
        /*
        echo '<BR>REGISTRO <BR>';
        var_dump($data['registro']);
        echo '<BR>REGISTROS <BR>';
        var_dump($data['registros']);
        echo '<BR>FULL <BR>';
        var_dump($data);
        exit;
        */
        if ($this->request->getMethod() === 'post') {

            echo "<BR> POST <BR>";
            var_dump($_POST);

            $a = $this->validacancel();
            if ($a === false) {
                //guardar
                // Calcular cuanto se va a devolver.
                // Se tiene que efectuar la entrega/envio para que se pueda cancelar la venta
                // Si se cancela con entrega/envio pendiente se debe validar que no se pueda cancelar la 
                // entrega/envio 
                // Dejar en ceros el detalle de la venta para que no se pueda programar mas entregas/envios
                $arr2Devolver = $this->request->getVar('devolver');
                echo "<BR>Registros<BR>";
                var_dump($data['registros']);
                foreach ($arr2Devolver as $idXDev => $paDevolver) {
                    $needle = array_search($idXDev, array_column($data['registros'], 'nIdArticulo'));
                    echo "<BR>idx  $idXDev  paDevolv $paDevolver  needle $needle <BR> Pa devolver ";
                    var_dump($data['registros'][$needle]);
                    //$mdlVentaDet->actualizaSaldo($data['registro']['nIdVentas'],$needle,$data['registros'][$needle]['nIdArticulo']);

                }
                exit;
                // Crear el saldo a favor
                $nIdCliente = $_SESSION['ventacancela']['registro']['nIdCliente'];
                $nImporte = $this->request->getVar('fADevolver');
                $r = [
                    'nIdCliente' => $nIdCliente,
                    'nImporte' => $nImporte,
                    'nIdSucursal' => $this->nIdSucursal,
                    'sObservaciones' => 'Cancela Entregas: ' . str_replace(
                        [chr(10), chr(9), chr(13)],
                        ' ',
                        $this->request->getVar('sMotivoCancela')
                    ),
                ];
                if ($nIdCliente > 1) {
                    $pagoAdelaMdl = new PagAdelMdl();
                    $nIdPagAdel = $pagoAdelaMdl->insert($r);

                    $mdlCliente = new ClienteMdl();

                    $mdlCliente->actualizaSaldo(
                        $nIdCliente,
                        $nImporte
                    );

                    $regSaldo = $mdlSaldoPagAdel->getRegVacioSaldoDeposito();
                    $mdlSaldoPagAdel->guardaSaldoDeposito(
                        $nIdPagAdel,
                        $this->request->getVar('nImporte'),
                        $regSaldo['id'] ?? false
                    );
                }
                /*
                $venta = new VentasMdl();
                $venta->set(
                    [
                        'cEdo' => '5',
                        'dtCancela' => $this->request->getVar('dtCancela'),
                        'sMotivoCancela' => $this->request->getVar('sMotivoCancela')
                    ]
                )
                    ->where('nIdVentas', $idVenta)
                    ->update();
                */

                unset($_SESSION['ventacancela']);
                return redirect()->to(base_url('remisiones'));
            } else
                $data['error'] = $a['amsj'];
        }
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/verRemision', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function validacancel()
    {
        $reglas = [
            'sMotivoCancela' => 'required|min_length[5]',
        ];
        $reglasMsj = [
            'sMotivoCancela' => [
                'required'   => 'Falta el motivo de cancelación',
                'min_length' => 'Debe tener 5 caracteres como mínimo'
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function imprimeCotizacion($idcoti, $reiniciaVenta = true)
    {
        $session = session();
        // se leen lo datos de la tabla y se calculan los importes
        // se debe leer los precios tapados
        // no comision bancaria
        $data = [];
        $mdlVentas = new CotizacionesMdl();
        $mdlVentasDet = new CotizacionesDetMdl();
        $mdlPrecio = new PrecioArticuloMdl();

        $mdlTipoLista = new TipoListaMdl();
        $rpt = $mdlTipoLista->getRegistros(false, true);
        $tipoListaTapado = $rpt['nIdTipoLista'];

        $rv = $mdlVentas->getRegistros($idcoti);
        $rvd = $mdlVentasDet->getRegistros($rv['nIdCotizaciones'], true);

        $porcDescGral = round(floatval($rv['nPorcenDescuento']) / 100, 2);

        $nSumSub = 0.0;
        $nSumaDescuentos = 0.0;
        $nDescuentoRemision = 0.0;
        $nDescuentoEnProducto = 0.0;
        $nSumDesc = 0.0;
        $nSumIva = 0.0;

        $nSumSubTap = 0.0;
        $det = [];
        foreach ($rvd as $k => $v) {
            $nCant = round(floatval($v['nCant']), 3);
            $porcDescProd = round(floatval($v['nPorcenDescuento']) / 100, 2);
            $pt = $mdlPrecio->buscaPrecio(
                $rv['nIdSucursal'],
                $tipoListaTapado,
                $v['nIdArticulo'],
                $v['nCant']
            );
            // por ahora es solo el iva
            $factorIva = round(floatval(0.16), 4);

            $nPrecioT = round(floatval($pt == null ? round(floatval($v['nPrecio']) * 1.30, 2) : $pt['fPrecioFactura']), 2);
            $nImpT = round($nCant * $nPrecioT, 2);
            $nSumSubTap += $nImpT;

            $nPrecio = round(floatval($v['nPrecio']), 2);
            $nImp = round($nCant * $nPrecio, 2);
            $nSumSub += $nImp;

            $impDescProd = round($nImp * $porcDescProd, 2);
            $nDescuentoEnProducto += $impDescProd;
            $nSumaDescuentos += $impDescProd;
            $nTotProd = $nImp - $impDescProd;

            $impDescGral = round($nTotProd * $porcDescGral, 2);
            $nDescuentoRemision += $impDescGral;
            $nSumaDescuentos += $impDescGral;
            $nIva = round(($nTotProd - $impDescGral) * $factorIva, 2);
            $nSumIva += $nIva;

            $det[] = [
                $v['nomArt'],
                $nPrecio,
                $nCant,
                0,
                $nPrecioT,
                $nIva,
                $impDescProd,
                $impDescGral
            ];
        }
        $nDiferencia = round($nSumSubTap - ($nSumSub - $nSumaDescuentos), 2);


        $data = [
            'rv' => $rv,
            'lst' => $det,
            'nFolio' => $rv['nFolioCotizacion'],
            'sumaSubTotalReal' => $nSumSub,
            'sumaSubTotalTapado' => $nSumSubTap,
            'sumaDescuentos' => $nSumaDescuentos,
            'bonificaciones' => $nDiferencia,
            'sumaIVA' => $nSumIva,
            'impTotal' => $nSumSub - $nSumaDescuentos + $nSumIva,
            'descuentoRemision' => $nDescuentoRemision,
            'descuentoEnProducto' => $nDescuentoEnProducto
        ];

        $ln = '<br>';   // chr(10)
        $data['fechaImp'] = new DateTime($rv['dtAlta']);
        $data['vigencia'] = new DateTime($rv['dVigencia']);
        $_SESSION['fechaImp'] = new DateTime();

        $data['modoDocu'] = $_SESSION['Ventas']['docu']['modoDocu'];
        $modelSuc =  new SucursalMdl();
        $data['suc'] = $modelSuc->getRegistros($this->nIdSucursal);

        // $mdlSucursal = new Sucur
        // $pdf = new Fpdf('P', 'mm', 'Letter');
        // $pdf->SetFont('Arial');
        // $data['pdf'] = $pdf;
        //$this->response->setHeader('Content-Type', 'application/pdf');

        $data['reiniciaVenta'] = $reiniciaVenta;
        $data['esMobil'] = $this->request->getUserAgent()->isMobile();

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/imprimeCotizacion', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function guardaVenta()
    {
        if ($this->nIdSucursal === null) return redirect()->to(base_url('/'));
        $session = session();
        $esMobil = $this->request->getUserAgent()->isMobile();

        // se valida que no se haga por error retroceso
        if (isset($_SESSION['Ventas'])) {
            if (
                count($_SESSION['Ventas']['lstArt']) == 0 ||
                $_SESSION['Ventas']['cli']['tipoCli'] == ''
            ) {
                if ($esMobil) {
                    return json_encode(['ok' => '0']);
                } else {
                    $this->index();
                    return;
                }
            }
        } else {
            if ($esMobil) {
                return json_encode(['ok' => '0']);
            } else {
                $this->index();
                return;
            }
        }
        $idSuc = intval($this->nIdSucursal);
        $mdlSucursal = new SucursalMdl();

        $mdlSucursal->db->transStart();

        $rSuc = $mdlSucursal->leeFolio($idSuc);

        if ($_SESSION['Ventas']['docu']['modoDocu'] !== 'C') {
            $ret = $this->guardaRemision($rSuc, $mdlSucursal, $idSuc);
            if ($mdlSucursal->db->transStatus() === true) {
                if ($ret['ok'] == '0') {
                    $mdlSucursal->db->transRollback();
                    $this->recalculaPagos($_SESSION['Ventas']['pago']['lst'], 'A');
                    $_SESSION['Ventas']['pago']['msjErr'] = $ret['msj'];
                    if ($esMobil) {
                        return json_encode(['ok' => '0']);
                    } else {
                        $this->index();
                        return;
                    }
                    // return json_encode(['ok' => '0']);
                } else {
                    $mdlSucursal->db->transComplete();
                    $_SESSION['lastDoc'] = [
                        'tipo' => 'V',
                        'id' => $ret['id']
                    ];
                    return json_encode(['ok' => '1', 'id' => $ret['id']]);
                }
            } else {
                $err = $mdlSucursal->db->error();
                log_message('error', 'codigo: ' . ($err['code'] ?? '') . ' mensaje: ' . ($err['message'] ?? ''));
                $_SESSION['Ventas']['pago']['msjErr'] = 'Error en el sistema, contacte al administrador del sistema para su atencion.';
                $this->index();
                return;
            }
        } else {
            $ret = $this->guardaCotizacion($rSuc, $mdlSucursal, $idSuc);
            if ($mdlSucursal->db->transStatus() === true) {
                $mdlSucursal->db->transComplete();
                // $_SESSION['lastDoc'] = [
                //     'tipo' => 'C',
                //     'id' => $ret['id']
                // ];
                $this->generaCotizacion($ret['id']);
                return;
            } else {
                $err = $mdlSucursal->db->error();
                log_message('error', 'codigo: ' . ($err['code'] ?? '') . ' mensaje: ' . ($err['message'] ?? ''));
                $_SESSION['Ventas']['pago']['msjErr'] = 'Error en el sistema, contacte al administrador del sistema para su atencion.';
                $this->index();
                return;
                // $err = $mdlSucursal->db->error();
                // var_dump($err);
                // exit;
            }
        }
    }

    /**
     * Funcion Guarda remision
     * 
     * Guarda las remisiones y si tiene exito, devuelve un arreglo de [ok, id]
     * 
     * @param  int $rSuc
     * @param  SucursalMdl $mdlSucursal
     * @return array   ['ok' => 0/1 ,  'id' => id Ventas ]
     */
    public function guardaRemision($rSuc, SucursalMdl $mdlSucursal, $idSuc)
    {
        $mdlVentas = new VentasMdl();
        $mdlVentasdet = new VentasDetMdl();
        $mdlVentasdetRel = new VentasDetRelMdl();
        $mdlVentasDetDescripcion = new VentasDetDescripcionMdl();
        $mdlVentasDetAux = new VentasDetAuxMdl();
        $mdlVentasImpuestos = new VentasImpuestosMdl();
        $mdlVentasPagos = new VentasPagoMdl();
        $mdlCliente = new ClienteMdl();
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $mdlCoti = new CotizacionesMdl();
        $mdlCorte = new CorteCajaMdl();
        $mdlFactura = new FacturasMdl();
        $mdlDirEntrega = new DirEntregaMdl();
        $mdlUsuario = new UsuarioMdl();
        $mdlVentasNomEntrega = new VentasNomEntregaMdl();
        $mdlSaldoPagAdel = new SaldoPagAdelMdl();
        $mdlMovPagAdel = new MovPagAdelMdl();
        $mdlVentasConAgente = new VentasConAgenteMdl();
        $mdlArticulo = new ArticuloMdl();
        $mdlEnvioDirec = new EnvioDireccionMdl();

        $cNomEntrega = $this->request->getVar('cNomEntrega') ?? '';
        $nIdDirEntrega = 0;
        $bEnvio = $this->request->getVar('hayEnvio');
        $nuevaDireccionEnvio = false;

        if ($bEnvio == '1') {
            $nIdDirEntrega = $this->request->getVar('nIdDirEntrega') ?? '0';
            $nIdDirEntrega = $nIdDirEntrega == '' ? '0' : $nIdDirEntrega;
            $ref = str_replace(
                [chr(13) . chr(10), chr(9), chr(13)],
                [chr(10), ' ', chr(10)],
                trim($this->request->getVar('sEnvReferencia'))
            );
            $dir = str_replace(
                [chr(13) . chr(10), chr(9), chr(13)],
                [chr(10), ' ', chr(10)],
                trim($this->request->getVar('sEnvDireccion'))
            );
            if ($_SESSION['Ventas']['cli']['tipoCli'] == 'P' || $nIdDirEntrega == '0') {
                // si es publico se agrega y lo actualizamos en ventas
                $nIdDirEntrega = $mdlDirEntrega->insert([
                    'nIdCliente' => $_SESSION['Ventas']['cli']['id'],
                    'sEnvEntrega' => $this->request->getVar('sEnvEntrega'),
                    'sEnvDireccion' => $dir,
                    'sEnvColonia' => $this->request->getVar('sEnvColonia'),
                    'sEnvTelefono' => $this->request->getVar('sEnvTelefono'),
                    'sEnvReferencia' => $ref
                ]);
                if ($nIdDirEntrega == '0') $nuevaDireccionEnvio = true;
            } else {
                $regDir = $mdlDirEntrega->where('nIdDirEntrega', $nIdDirEntrega)
                    ->first();
                if (
                    trim($regDir['sEnvEntrega']) != trim($this->request->getVar('sEnvEntrega')) ||
                    trim($regDir['sEnvDireccion']) != $dir ||
                    trim($regDir['sEnvColonia']) != trim($this->request->getVar('sEnvColonia')) ||
                    trim($regDir['sEnvTelefono']) != trim($this->request->getVar('sEnvTelefono')) ||
                    trim($regDir['sEnvReferencia']) != $ref
                ) {
                    $nuevaDireccionEnvio = true;
                }
                if ($nuevaDireccionEnvio) {
                    // si es la primera direccion de envio, guardo la anterior
                    $regEnvDir = $mdlEnvioDirec->where('nIdDirEntrega', $nIdDirEntrega)
                        ->limit(1)->first();
                    if ($regEnvDir == null) {
                        $sDirecEnvioInicial = trim($regDir['sEnvEntrega']) . '||' .
                            trim($regDir['sEnvDireccion']) . '||' .
                            trim($regDir['sEnvColonia']) . '||' .
                            trim($regDir['sEnvTelefono']) . '||' .
                            trim($regDir['sEnvReferencia']);
                        $mdlEnvioDirec->insert([
                            'nIdDirEntrega' => $nIdDirEntrega,
                            'nIdEnvio' => 0,
                            'sDirecEnvio' => $sDirecEnvioInicial
                        ]);
                    }
                }
                $mdlDirEntrega->set([
                    'sEnvEntrega' => $this->request->getVar('sEnvEntrega'),
                    'sEnvDireccion' => $dir,
                    'sEnvColonia' => $this->request->getVar('sEnvColonia'),
                    'sEnvTelefono' => $this->request->getVar('sEnvTelefono'),
                    'sEnvReferencia' => $ref
                ])->where('nIdDirEntrega', $nIdDirEntrega)
                    ->update();
            }
            if ($nuevaDireccionEnvio) {
                $sDirecEnvio = trim($this->request->getVar('sEnvEntrega')) . '||' .
                    $dir . '||' .
                    trim($this->request->getVar('sEnvColonia')) . '||' .
                    trim($this->request->getVar('sEnvTelefono')) . '||' .
                    $ref;
            }
        }
        // si el metodo de pago es PUE se asigna edo '4' pagado
        $idVentas = $mdlVentas->insert([
            'nIdSucursal' => $idSuc,
            'nFolioRemision' => intval($rSuc['nFolioRemision']),
            'nIdCliente' => intval($_SESSION['Ventas']['cli']['id']),
            'cEdo' => '1',
            'nSubTotal' => $_SESSION['Ventas']['pago']['sub'],
            'nImpuestos' => $_SESSION['Ventas']['pago']['iva'],
            'nTotal' => $_SESSION['Ventas']['pago']['tot'],
            'bEnvio' => $bEnvio,
            'nImpDescuento' => $_SESSION['Ventas']['pago']['des'],
            'nImpComision' => $_SESSION['Ventas']['pago']['comision'],
            'nPorcenDescuento' => $_SESSION['Ventas']['descGral'],
            'nPorcenComision' => $_SESSION['Ventas']['pago']['selcom'],
            'nIdDirEntrega' => $nIdDirEntrega,
            'bVariasFacturas' => $_SESSION['Ventas']['docu']['variasFacturas'],
            'nIdUsuario' => $this->nIdUsuario,
            'cClasificacionVenta' => '1'     // '1'-Normal, '2'-pago anticipo, ....
        ]);
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'F') {
            if ($_SESSION['Ventas']['cli']['tipoCli'] == 'P')
                $usoCfdi = 'S01';
            else
                $usoCfdi = $this->request->getVar('nIdUsoCFDI');
            $mdlFactura->insert([
                'nIdVentas' => $idVentas,
                'nFolioFactura' => 0,
                'sSerie' => '',
                'cUsoCFDI' => $usoCfdi,
                'cMetodoPago' => $this->request->getVar('cIdMetodoPago')
            ]);
        }
        if ($cNomEntrega != '') {
            $mdlVentasNomEntrega->insert([
                'nIdVentas' => $idVentas,
                'cNomEntrega' => $cNomEntrega
            ]);
        }
        if (intval($_SESSION['Ventas']['idAgente']) > 0) {
            $mdlVentasConAgente->insert([
                'nIdVentas' => $idVentas,
                'nIdAgenteVentas' => intval($_SESSION['Ventas']['idAgente'])
            ]);
        }
        // se guarda el detalle
        $nSaldo = 0;
        $bndParaEntregar = false;
        $nCantPorEntregar = 0;
        foreach ($_SESSION['Ventas']['lstArt'] as $k => $v) {
            $nSaldo += $v[7];
            $idVentasDet = $mdlVentasdet->insert([
                'nIdVentas' => $idVentas,
                'nIdArticulo' => intval($v[0]),
                'nPrecio' => $v[2],
                'nCant' => $v[3],
                'nPorcenDescuento' => $v[9],
                'nEnvio' => $v[7],
                'nEntrega' => $v[6],
                'nPorEntregar' => round($v[3] - $v[6] - $v[7], 3),
                // 'nDescuentoTotal' => $v[10] + $v[11] - $v[18]
            ], true);
            if (round($v[10] + $v[11] + $v[18] + $v[19], 3) > 0) {
                // indica si alguno tiene un valor
                $mdlVentasDetAux->insert([
                    'nIdVentasDet' => $idVentasDet,
                    'nImpComisionTotal' => $v[18],
                    'nImpDescuentoProd' => $v[10],
                    'nImpDescuentoGral' => $v[11],
                    'nImporteTapadoFactura' => $v[19],
                ]);
            }
            $nCantPorEntregar += round($v[3] - $v[6] - $v[7], 3);
            if ((substr($v[16], 0, 1) == '1') && (substr($v[16], 1) != trim($v[1]))) {
                $mdlVentasDetDescripcion->insert([
                    'nIdVentasDet' => $idVentasDet,
                    'sDescripcion' => trim($v[1])
                ]);
            }
            // ventas impuestos
            $mdlVentasImpuestos->insert([
                'nIdVentasDet' => $idVentasDet,
                'nIdTipoImpuesto' => 1,
                'nBase' => round(($v[2] * $v[3]) - $v[10] - $v[11], 2),
                'nImporte' => $v[8]
            ]);
            if ($v[15] !== false) {
                foreach ($v[15] as $vv) {
                    $mdlVentasdetRel->insert([
                        'nIdVentasDet' => $idVentasDet,
                        'nIdArticulo' => intval($vv[0]),
                        'nCant' => round($vv[7] + $vv[6], 2),
                        'nEnvio' => $vv[7],
                        'nEntrega' => $vv[6]
                    ]);
                }
            }
            if (round($v[6], 3) > 0) $bndParaEntregar = true;
        }
        if ($nCantPorEntregar == 0) $mdlVentas->update($idVentas, ['cEdo' => '2']);
        $idPago = $_SESSION['Ventas']['pago']['idEfeAnti'];
        $idEfec = $_SESSION['Ventas']['pago']['idEfe'];
        $idCred = $_SESSION['Ventas']['pago']['idCredito'];
        $nSumAnticipos = 0; // para descontar del saldo del cliente
        foreach ($_SESSION['Ventas']['pago']['lst'] as $k => $v) {
            if ($idPago == $v['id']) $nSumAnticipos += $v['imp'];
            if ($idEfec == $v['id'] && $_SESSION['Ventas']['pago']['efecReal'] > 0) {
                $mdlVentasPagos->insert([
                    'nIdVentas' => $idVentas,
                    'nIdTipoPago' => $v['id'],
                    'nImporte' => $_SESSION['Ventas']['pago']['efecReal']
                ]);
            } else {
                $mdlVentasPagos->insert([
                    'nIdVentas' => $idVentas,
                    'nIdTipoPago' => $v['id'],
                    'nImporte' => $v['imp']
                ]);

                if ($idCred == $v['id']) {
                    $mdlSaldoVenta = new VentasSaldoMdl();
                    $mdlSaldoVenta->insert([
                        'nIdCliente' => $_SESSION['Ventas']['cli']['id'],
                        'nIdVentas' => $idVentas,
                        'nSaldo' => $v['imp']
                    ]);
                }
            }
        }
        if ($nSumAnticipos > 0) {
            $r = $mdlCliente->getRegistros($_SESSION['Ventas']['cli']['id']);
            $s = round(floatval($r['nSaldo']), 2);
            if ($s >= $nSumAnticipos) {
                // se actualiza el saldo acumulado del cliente
                $mdlCliente->actualizaSaldo(
                    $_SESSION['Ventas']['cli']['id'],
                    $nSumAnticipos,
                    '-'
                );
                // leo los registros del cliente para gastar su saldo
                $nSumAnticiposAcum = round($nSumAnticipos, 2);
                while ($nSumAnticiposAcum > 0) {
                    // lee registro cliente vacio (se debe de encontrar porque todavia tiene saldo) nIdSaldoPagosAdelantados
                    $regCliSaldo = $mdlSaldoPagAdel->getRegSaldoCliente($_SESSION['Ventas']['cli']['id']);
                    $nSaldoPagAdel = round(floatval($regCliSaldo['nSaldo']), 2);
                    if ($nSumAnticiposAcum > $nSaldoPagAdel) {
                        // $mdlSaldoPagAdel->actualizaSaldo($regCliSaldo['nIdSaldoPagosAdelantados'], 0);
                        $nImporteSaldo = 0;
                        $nImporteMovto = $nSaldoPagAdel;
                        $nSumAnticiposAcum = round($nSumAnticiposAcum - $nSaldoPagAdel, 2);
                    } else {
                        $nImporteSaldo = round($nSaldoPagAdel - $nSumAnticiposAcum, 2);
                        $nImporteMovto = $nSumAnticiposAcum;
                        $nSumAnticiposAcum = 0;
                    }

                    $mdlSaldoPagAdel->actualizaSaldo(
                        $regCliSaldo['nIdSaldoPagosAdelantados'],
                        $nImporteSaldo
                    );

                    $mdlMovPagAdel->insert([
                        'nIdCliente' => $_SESSION['Ventas']['cli']['id'],
                        'nIdVentas' => $idVentas,
                        'nIdPagosAdelantados' => $regCliSaldo['nIdPagosAdelantados'],
                        'nSaldoActual' => $nSaldoPagAdel,
                        'nImporte' => $nImporteMovto
                    ]);
                }
            } else {
                return ['ok' => '0', 'msj' => 'Error: Otro proceso a actualizado el saldo del cliente'];
            }
        }
        $mdlInventario = new InventarioMdl();
        $nIdSucursalEntrega = $this->request->getVar('nIdSucursalEntrega');
        // indica en donde (esta, otra sucursal), y que tipo de movimiento (envio, entrega)
        // 0 - no hay movto, 1 - esta sucursal, 2 - otra sucursal. 
        $_SESSION['bParaEnvio'] = '0';
        $_SESSION['bParaEntrega'] = '0';
        $_SESSION['nIdEntregaEnEnvio'] = 0;
        if ($bEnvio == '1') {
            $mdlVentas->set('cEdoEnvio', '1', false)->where('nIdVentas', $idVentas)->update();
            //->update($idVentas, ['cEdoEnvio' => '1']);
            $_SESSION['bParaEnvio'] = (intval($nIdSucursalEntrega) == intval($this->nIdSucursal) ? '1' : '2');
            $idEnvio = $mdlEnvio->insert([
                'nIdOrigen' => $idVentas,
                'cOrigen' => 'ventas',
                'dSolicitud' => (new DateTime())->format('Y-m-d'),
                'nIdSucursal' => $nIdSucursalEntrega,
                'nIdCliente' => intval($_SESSION['Ventas']['cli']['id']),
                'nIdDirEntrega' => $nIdDirEntrega,
                'cEstatus' => '1'
            ], true);
            $_SESSION['nIdEntregaEnEnvio'] = $idEnvio;
            if ($nuevaDireccionEnvio) {
                $mdlEnvioDirec->insert([
                    'nIdDirEntrega' => $nIdDirEntrega,
                    'nIdEnvio' => $idEnvio,
                    'sDirecEnvio' => $sDirecEnvio
                ]);
            }
            foreach ($_SESSION['Ventas']['lstArt'] as $k => $v) {
                if ($v[7] <= 0) continue;
                // $idEnvio, $idArt, $cant, $porrec, $recibido, $modoenv
                if ($v[15] === false) {
                    $mdlEnvioDet->insert([
                        'nIdEnvio' => $idEnvio,
                        'nIdArticulo' => intval($v[0]),
                        'fCantidad' => $v[7],
                        'fPorRecibir' => $v[7],
                        'fRecibido' => '0',
                        'cModoEnv' => $v[13]
                    ]);
                    $retCompromete = $this->comprometeInv($v[0], '1', $nIdSucursalEntrega, $v[1], $v[4], $v[14], $v[7]);
                    if ($retCompromete['ok'] == '0') return $retCompromete;
                } else {
                    foreach ($v[15] as $vv) {
                        if ($vv[7] <= 0) continue;
                        $mdlEnvioDet->insert([
                            'nIdEnvio' => $idEnvio,
                            'nIdArticulo' => intval($vv[0]),
                            'fCantidad' => $vv[7],
                            'fPorRecibir' => $vv[7],
                            'fRecibido' => '0',
                            'cModoEnv' => $v[13]
                        ]);
                        $retCompromete = $this->comprometeInv($vv[0], '1', $nIdSucursalEntrega, $vv[1], $vv[4], $vv[3], $vv[7]);
                        if ($retCompromete['ok'] == '0') return $retCompromete;
                    }
                }
            }
        }
        $_SESSION['nIdEntregaEnVenta'] = 0;
        if ($bndParaEntregar) {
            $mdlVentas->update($idVentas, ['cEdoEntrega' => '1']);
            $_SESSION['bParaEntrega'] = (intval($nIdSucursalEntrega) == intval($this->nIdSucursal) ? '1' : '2');
            $mdlEntrega = new EntregaMdl();
            $mdlEntregaDet = new EntregaDetalleMdl();
            $idEntrega = $mdlEntrega->insert([
                'dEntrega' => (new DateTime())->format('Y-m-d'),
                'nIdSucursal' => $nIdSucursalEntrega,
                'nIdProveedor' => intval($_SESSION['Ventas']['cli']['id']),
                'nIdOrigen' => $idVentas
            ]);
            $_SESSION['nIdEntregaEnVenta'] = $idEntrega;
            foreach ($_SESSION['Ventas']['lstArt'] as $k => $v) {
                if ($v[6] <= 0) continue;
                if ($v[15] === false) {
                    $mdlEntregaDet->insert([
                        'nIdEntrega' => $idEntrega,
                        'nIdArticulo' => intval($v[0]),
                        'fCantidad' => $v[6],
                        'fPorRecibir' => $v[6]
                    ]);
                    $retCompromete = $this->comprometeInv($v[0], '0', $nIdSucursalEntrega, $v[1], $v[4], $v[14], $v[6]);
                    if ($retCompromete['ok'] == '0') return $retCompromete;
                } else {
                    foreach ($v[15] as $vv) {
                        if ($vv[6] <= 0) continue;
                        $mdlEntregaDet->insert([
                            'nIdEntrega' => $idEntrega,
                            'nIdArticulo' => intval($vv[0]),
                            'fCantidad' => $vv[6],
                            'fPorRecibir' => $vv[6]
                        ]);
                        $retCompromete = $this->comprometeInv($vv[0], '0', $nIdSucursalEntrega, $vv[1], $vv[4], $vv[3], $vv[6]);
                        if ($retCompromete['ok'] == '0') return $retCompromete;
                    }
                }
            }
        }
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'D');
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'P');
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'PSC');
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'V');
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'PC');
        $mdlCorte->guardaFolioMovimiento($this->nIdUsuario, $this->nIdSucursal, $idVentas, '1');

        if ($_SESSION['Ventas']['idCot'] > 0) {
            // si proviene de una cotizacion
            $mdlCoti->actualizaFolioVenta(
                $_SESSION['Ventas']['idCot'],
                $idVentas
            );
        }

        $mdlSucursal->addFolioRem($idSuc);

        return ['ok' => '1', 'id' => $idVentas];
    }

    // NOTA: el parametro modoENV me indica ahora que es envio y no se compromete el inventario.
    //       cuando es entrega se pasa '0' para indicar que si se compromete
    private function comprometeInv($idArt, $modoEnv, $idSuc, $cDes, $nDisponible, $nExistencia, $nCant)
    {
        $mdlInventario = new InventarioMdl();
        $mdlArticulo = new ArticuloMdl();
        $rArt = $mdlArticulo->getRegistros($idArt);
        if ($modoEnv == '0' && $rArt['cSinExistencia'] == '0') {  // si no tiene modo ENV  se valida inventario
            $rInv = $mdlInventario->getArticuloSucursal($idArt, $idSuc, true, true);
            if ($rInv == null) {
                return ['ok' => '0', 'msj' => 'Error: El producto ' . $cDes . ' no existe en el inventario, agregelo en el inventario.'];
            }
            $disponibleEnSuc =  round(floatval($rInv['fExistencia']), 3) - round(floatval($rInv['fComprometido']), 3);
            // if ($disponibleEnSuc < $nDisponible) {
            //     return ['ok' => '0', 'msj' => 'Error: Otro proceso a actualizado la existencia del inventario, debe modificar la entrega de producto'];
            // }
            // leemos el inventario
            $nSobreComprometido = round(floatval($rInv['fSobreComprometido']), 3);
            if ($disponibleEnSuc < $nCant) {
                $nComprometido = round(floatval($rInv['fExistencia']), 3);
                $nSobreComprometido += round($nCant - $disponibleEnSuc, 3);
            } else {
                $nComprometido = round(floatval($rInv['fComprometido']), 3) + $nCant;
            }
            // comprometemos el inventario
            $mdlInventario
                ->set('fComprometido', $nComprometido)
                ->set('fSobreComprometido', $nSobreComprometido)
                ->where([
                    'nIdSucursal' => $idSuc,
                    'nIdArticulo' => intval($idArt)
                ])->update();
        }
        return ['ok' => '1'];
    }

    public function guardaCotizacion($rSuc, SucursalMdl $mdlSucursal, $idSuc)
    {

        $a = [
            'sNombre' => $_SESSION['Ventas']['cli']['nomManual'],
            'nPorcenDescuento' => $_SESSION['Ventas']['descGral'],
            'nIdVentas' => 0,
            'cTipoListaPrecios' => $_SESSION['Ventas']['idTipoLista'],
            'nTotal' => $_SESSION['Ventas']['pago']['tot']
        ];
        // $idSuc = intval($_SESSION['sucursal']);
        $mdlCoti = new CotizacionesMdl();
        $mdlCotiDet = new CotizacionesDetMdl();
        if ($_SESSION['Ventas']['idCot'] == 0) {
            $mdlConfig = new ConfiguracionMdl();
            $r = $mdlConfig->getRegistrosByName('VIGCOT', true);
            $d = new DateTime();
            $d->add(new DateInterval('P' . strval(intval($r[0]['fValor'])) . 'D'));
            $a['nIdSucursal'] = $idSuc;
            $a['nFolioCotizacion'] = intval($rSuc['nFolioCotizacion']);
            $a['dVigencia'] = $d->format('Y-m-d');
            $a['nIdUsuario'] = $this->nIdUsuario;
            $idCoti = $mdlCoti->insert($a);
            $mdlSucursal->addFolioCot($idSuc);
            $rDet = [];
        } else {
            $idCoti = $_SESSION['Ventas']['idCot'];
            $mdlCoti->update($idCoti, $a);
            $rDet = $mdlCotiDet->getRegistros($idCoti, false, true);
        }
        $nRegs = count($rDet);
        $nInd = 0;
        foreach ($_SESSION['Ventas']['lstArt'] as $k => $v) {
            $rd = [
                'nIdCotizaciones' => $idCoti,
                'nIdArticulo' => intval($v[0]),
                'nPrecio' => $v[2],
                'nCant' => $v[3],
                'nPorcenDescuento' => $v[9],
                'nDescuentoTotal' => $v[10] + $v[11] - $v[18]
            ];
            if ($nInd < $nRegs) {
                $mdlCotiDet->update($rDet[$nInd]['nIdCotizacionesDet'], $rd);
                $nInd++;
            } else {
                $mdlCotiDet->insert($rd);
            }
        }
        for ($i = $nInd; $i < $nRegs; $i++) $mdlCotiDet->update(
            $rDet[$nInd]['nIdCotizacionesDet'],
            [
                'nIdCotizaciones' => 0,
                'nIdArticulo' => 0,
                'nPrecio' => 0,
                'nCant' => 0
            ]
        );
        $mdlUsuario = new UsuarioMdl();
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'D');
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'P');
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'MPC');

        return ['ok' => '1', 'id' => $idCoti];
    }

    public function descuentoGral()
    {
        $this->validaSesion();
        $data = [
            'descuento' => number_format($_SESSION['Ventas']['descGral']),
            'cTitulo' => 'Remisión',
            'url' => '/ventas/descuentoGral',
            'nomProd' => ''
        ];

        if ($this->request->getMethod() == 'post') {
            $_SESSION['Ventas']['descGral'] = round(floatval($this->request->getVar('valDesc')), 2);
            $this->calcularPrecios();
            $this->initPagos();
            $this->index();
            return;
        }

        echo view('ventas/descuentoGral', $data);
    }

    public function descuentoProd($id)
    {
        $this->validaSesion();

        $data = [
            'idArticulo' => $id,
            'nomProd' => $_SESSION['Ventas']['lstArt'][$id][1],
            'descuento' => number_format($_SESSION['Ventas']['lstArt'][$id][9]),
            'cTitulo' => 'Producto',
            'url' => '/ventas/descuentoProd/' . $id
        ];

        if ($this->request->getMethod() == 'post') {
            $_SESSION['Ventas']['lstArt'][$id][9] = round(floatval($this->request->getVar('valDesc')), 2);
            $this->calcularPrecios($id);
            $this->initPagos();
            $this->index();
        }

        echo view('ventas/descuentoGral', $data);
    }

    public function cambiaPrecio($id)
    {
        $this->validaSesion();
        $nPrecio = $_SESSION['Ventas']['lstArtCot'][$id][0] ?? $_SESSION['Ventas']['lstArt'][$id][2];
        $bNoAplicaComisionesDescuentos = $_SESSION['Ventas']['lstArtCot'][$id][1] ?? '0';
        $mdlUsuario = new UsuarioMdl();
        $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        // $_SESSION['Ventas']['permisoCambioPrecioSinRecal'] = $regUsu['bPrecioSinRecal'];

        $data = [
            'nomProd' => $_SESSION['Ventas']['lstArt'][$id][1],
            'importe' => number_format($nPrecio, 2),
            'bNoAplicaComisionesDescuentos' => $bNoAplicaComisionesDescuentos,
            'url' => '/ventas/cambiaPrecio/' . $id,
            'bNoRecalcular' => $regUsu['bPrecioSinRecal']
        ];

        if ($this->request->getMethod() == 'post') {
            $_SESSION['Ventas']['lstArtCot'][$id] = [
                round(floatval($this->request->getVar('valImporte')), 4),
                $this->request->getVar('chkNoAplicaComisionesDescuentos') == 'on' ? '1' : '0'
            ];

            $this->calcularPrecios($id);
            $this->initPagos();
            $this->calcularPagos($_SESSION['Ventas']['pago']['lst']);
            $this->index();
            return;
        }

        echo view('ventas/cambiaPrecio', $data);
    }

    public function ampliaDescripcion($id)
    {
        $this->validaSesion();

        $data = [
            'nomProd' => $_SESSION['Ventas']['lstArt'][$id][1],
            'url' => '/ventas/ampliaDescripcion/' . $id
        ];

        if ($this->request->getMethod() == 'post') {
            $c = trim($this->request->getVar('nomProd'));
            if (preg_match('/\r\n/', $c, $aMatch) === 1) {
                $c = preg_replace('/(\r\n){2,}/', "\r\n", $c);
            } elseif (preg_match('/\r/', $c, $aMatch) === 1) {
                $c = preg_replace('/(\r){2,}/', "\r", $c);
            } elseif (preg_match('/\n/', $c, $aMatch) === 1) {
                $c = preg_replace('/(\n){2,}/', "\n", $c);
            }
            $_SESSION['Ventas']['lstArt'][$id][1] = str_replace(array("\r\n", "\r", "\n"), " ", $c);
            $this->index();
        }

        echo view('ventas/ampliaDescripcion', $data);
    }

    public function aplicaComision($id)
    {
        $this->validaSesion();
        // si ya esta agregado como pago...
        if (isset($_SESSION['Ventas']['pago']['lst'][$id])) {
            $_SESSION['Ventas']['pago']['tipoSeleccionado'] = strval($id);
            $this->index();
            return;
        }

        $comisionAnterior = $_SESSION['Ventas']['pago']['selcom'];
        $_SESSION['Ventas']['pago']['selcom'] = $_SESSION['Ventas']['pago']['tipos'][$id][1];
        foreach ($_SESSION['Ventas']['pago']['lst'] as $r) {
            if ($_SESSION['Ventas']['pago']['selcom'] < $r['porcom']) {
                $_SESSION['Ventas']['pago']['selcom'] = $r['porcom'];
            }
        }
        if ($comisionAnterior != $_SESSION['Ventas']['pago']['selcom']) {
            $this->calcularPrecios();
        }

        $_SESSION['Ventas']['foc'] = 'selPag';
        $_SESSION['Ventas']['pago']['tipoSeleccionado'] = strval($id);
        $this->calcularPagos($_SESSION['Ventas']['pago']['lst'], true);
        $this->index();
    }

    public function aplicaCotizacion($idCotizacion, $modoDocu)
    {
        $this->validaSesion();
        $mdlCotizacion = new CotizacionesMdl();
        $reg = $mdlCotizacion->getRegistros($idCotizacion);
        $msj = '';
        if ($reg['nIdVentas'] !== '0') {
            $msj = 'La cotizacion ya fue aplicada en una venta.';
        } else {
            $ret = $this->buscaCotizacion($reg['nFolioCotizacion'], $modoDocu, 'array');
            if ($ret['ok'] == '0') $msj = $ret['msj'];
        }
        if ($msj != '') {
            $data = [
                'msjError' => $msj,
                'titulo' => 'AVISO',
                'urlDestino' => 'cotizaciones'
            ];
            echo view('templates/header', $this->dataMenu);
            echo view('templates/mensajemodal', $data);
            echo view('templates/footer', $this->dataMenu);
            return;
        } else {
            if ($ret['bVigencia'] == '0') {
                $mdlUsuario = new UsuarioMdl();
                $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
                if ($regUsu['bMantenerPrecioCotiza'] == '0') {
                    $data = [
                        'msjError' => 'La vigencia de precios ' .
                            'de la cotización está vencida (' . $ret['fVigencia'] . ').<br>' .
                            'Desea actualizar los precios ?',
                        'titulo' => 'CONFIRMAR',
                        'bTipoConfirmar' => '1',
                        'urlSI' => 'ventas/continuaCotizacion/' . $modoDocu,
                        'urlNO' => 'cotizaciones'
                    ];
                    echo view('templates/header', $this->dataMenu);
                    echo view('templates/mensajemodal', $data);
                    echo view('templates/footer', $this->dataMenu);
                    return;
                }
            }
            $_SESSION['Ventas']['foc'] = $modoDocu == 'R' ? 'cli' : 'art';
        }
        $this->index();
    }

    public function buscaCotizacion($idFolioCotizacion, $modoDocu = 'R', $tipoReturn = 'json')
    {
        $this->validaSesion();
        // se leen los datos y see inicializa el registro de ventas
        $mdlArticulo = new ArticuloMdl();
        $mdlCoti = new CotizacionesMdl();
        $mdlCotiDet = new CotizacionesDetMdl();
        $r = $mdlCoti->getCotizacion($this->nIdSucursal, $idFolioCotizacion);
        if ($r == null) {
            $m = ['ok' => '0', 'msj' => 'Folio no encontrado'];
            if ($tipoReturn == 'json')
                return json_encode($m);
            else
                return $m;
        }
        if (intval($r['nIdVentas']) > 0) {
            $m = ['ok' => '0', 'msj' => 'La cotizacion solicitada, ya se aplicó en una venta'];
            if ($tipoReturn == 'json')
                return json_encode($m);
            else
                return $m;
        }
        $this->initVarSesion();

        $_SESSION['Ventas']['descGral'] = $r['nPorcenDescuento'];
        $_SESSION['Ventas']['idCot'] = intval($r['nIdCotizaciones']);
        $_SESSION['Ventas']['cli']['nomManual'] = $r['sNombre'];
        $_SESSION['Ventas']['cli']['tipoCli'] = 'N';
        $_SESSION['Ventas']['pago']['completar'] = $modoDocu == 'C';
        $_SESSION['Ventas']['docu']['modoDocu'] = $modoDocu;
        $_SESSION['Ventas']['idTipoLista'] = $r['cTipoListaPrecios'];

        $rd = $mdlCotiDet->getRegistros($r['nIdCotizaciones'], true);
        $a = [];
        $ac = [];
        foreach ($rd as $k => $v) {
            $regArt = $mdlArticulo->getRegistros($v['nIdArticulo']);
            $a[$v['nIdArticulo']] = [
                $v['nIdArticulo'],
                $v['nomArt'],
                round(floatval($v['nPrecio']), 2),
                round(floatval($v['nCant']), 3),
                0, 0,
                0, 0,
                0, round(floatval($v['nPorcenDescuento']), 2),
                0, 0, 0, // Imp. Desc. prod., Imp. Desc. Gral., Saldo por Surtir
                '0', 0, false,       // articulo en modo ENV, exis. fisica sucursal.
                '',
                round(floatval($regArt['fPeso']), 2),   // peso del producto por unidad
                0, 0   // Imp. comision bancaria, precio tapado en factura
            ];
            $ac[$v['nIdArticulo']] = [round(floatval($v['nPrecio']), 2), '0'];
        }
        $_SESSION['Ventas']['lstArt'] = $a;
        $_SESSION['Ventas']['lstArtCot'] = $ac;
        unset($a, $ac);
        $this->calcularTotales();

        // valido la vigencia
        $fecVigencia = new DateTime($r['dVigencia']);
        $fecActual = new DateTime();
        $fecVigencia->setTime(0, 0, 0, 0);
        $fecActual->setTime(0, 0, 0, 0);

        $m = [
            'ok' => '1',
            'bVigencia' => ($fecActual <= $fecVigencia ? '1' : '0'),
            'fVigencia' => $fecVigencia->format('d/m/Y')
        ];
        if ($tipoReturn == 'json') {
            $mdlUsuario = new UsuarioMdl();
            $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
            if ($regUsu['bMantenerPrecioCotiza'] == '1') $m['bVigencia'] = '1';
            return json_encode($m);
        } else
            return $m;
    }

    public function continuaCotizacion($modoDocu)
    {
        $this->validaSesion();
        $_SESSION['Ventas']['lstArtCot'] = [];
        $_SESSION['Ventas']['docu']['modoDocu'] = 'C';
        $this->calcularPrecios();
        $_SESSION['Ventas']['docu']['modoDocu'] = $modoDocu;
        if ($_SESSION['Ventas']['docu']['modoDocu'] == 'R') {
            $_SESSION['Ventas']['pago']['completar'] = false;
        }

        $_SESSION['Ventas']['foc'] = 'cli';

        // return redirect()->to(base_url('ventas'));
        $this->index();
    }

    public function cambiaLista($id)
    {
        $this->validaSesion();
        $_SESSION['Ventas']['idTipoLista'] = $id;
        $_SESSION['Ventas']['foc'] = 'art';
        $this->calcularPrecios();
        $this->index();

        // return json_encode(['ok' => '1']);
    }

    public function calculadora()
    {
        echo view('ventas/calculadora');
    }

    public function seleccionaagente()
    {
        $this->validaSesion();
        $mdlAgenteVtas = new AgenteVentasMdl();


        $data = [
            'idAgente' => $_SESSION['Ventas']['idAgente'],
            'lstAgentes' => $mdlAgenteVtas->getRegistros(false, false, 1),
            'url' => '/ventas/seleccionaagente'
        ];
        array_splice($data['lstAgentes'], 0, 0, [['idAgente' => '0', 'sDes' => 'Ninguno']]);
        if ($this->request->getMethod() == 'post') {
            $_SESSION['Ventas']['idAgente'] = intval($this->request->getVar('idAgente'));
            $r = $mdlAgenteVtas->getRegistros($_SESSION['Ventas']['idAgente']);
            $_SESSION['Ventas']['nombreAgente'] = ($r['sNombre'] ?? '');
            $this->index();
            return;
        }

        echo view('ventas/seleccionaagente', $data);
    }


    public function imprime($idVenta, $reiniciaVenta = true, $dest = 'venta')
    {
        $this->validaSesion();
        $data['dat'] = $this->consultaRemision($idVenta, $reiniciaVenta, $dest);
        $data['aInfoSis'] = $this->dataMenu['aInfoSis'];
        // $r = $data['dat']['lst'][0];
        // for($i = 0; $i < 9; $i++) $data['dat']['lst'][] = $r;
        echo view('ventas/imprimeremision3', $data);
    }
}
