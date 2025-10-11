<?php

/******
 * ***
 * Compra: Sucursal que solicita,Proveedor a solicitar
 * Traspaso: Sucursal que solicita, Sucursal solicitante
 * Entrada
 * Salida
 * ***

 - Cambiar a session los valores de data en las pantallas de capturas
 - Capturar por código las claves de sucursal, proveedor
 - Aplicar los cambios de foco al presionar la tecla ENTER
 - Terminar la parte de recepción
 ******/

namespace App\Controllers\Almacen;

use App\Controllers\BaseController;
use App\Models\Compras\ComprasMdl;
use App\Models\Compras\CompraDetalleMdl;
use App\Models\Compras\CompraDetalleSaldoMdl;
use App\Models\Almacen\EntregaMdl;
use App\Models\Almacen\EntregaDetalleMdl;
use App\Models\Almacen\EntradaMdl;
use App\Models\Almacen\EntradaDetalleMdl;
use App\Models\Almacen\TraspasoMdl;
use App\Models\Almacen\TraspasoDetalleMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Almacen\MovimientoDetalleMdl;
use App\Models\Almacen\SalidaMdl;
use App\Models\Almacen\SalidaDetalleMdl;
use App\Models\Almacen\InventarioCapturaMdl;
use App\Models\Almacen\InventarioCapturaDetalleMdl;
use App\Models\Envios\EnvioMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Almacen\EntradaCancelMdl;
use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Catalogos\ProveedorMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Viajes\ViajeEnvioMdl;

class Movimiento extends BaseController
{
    // Variables de configuración del módulo
    private $bModuloLogistica = true;           // false para Ker
    private $bModuloCapturaInv = true;          // true para ambos
    private $bUsaPermisosSucursal = true;       // true para Ker con permisos
    private $bUsaModeloSaldoCompra = true;      // true para Venta
    private $bUsaCancelacionEntradas = true;    // true para Ker
    private $bGuardaUsuarioMovimiento = true;   // true para Venta
    private $bValidaSinExistencia = true;       // true para Venta
    private $bUsaHistorialNavegacion = true;    // true para Venta
    private $bPantallaImpresionExtendida = true; // true para Venta

    public function index($operacion = 'compra', $modocompra = false)
    {
        $this->validaSesion(false, "movimiento/$operacion");

        $aKeys = [
            'compra' => 'Cotizaciones de compra',
            'entrega' => 'Surtir productos',
            'entrada' => 'Entradas especiales',
            'traspaso' => 'Traspaso almacén',
            'salida' => 'Salidas especiales',
        ];

        // Agregar módulo de captura de inventario si está habilitado
        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = 'Captura modo inventario';
        }

        $aWhere = [
            'Edo' => $this->request->getVar('cEstado') ?? '0',
            'dIni' => $this->request->getVar('dIni'),
            'dFin' => $this->request->getVar('dFin'),
            'nIdSucursal' => $this->request->getVar('nIdSucursal') ?? '0',
            'nIdArticulo' => $this->request->getVar('nIdArticulo') ?? '0',
        ];

        if (($this->request->getVar('page') ?? false) === false) {
            $aWhere['pagina'] = $this->request->getVar('pagina') ?? '1';
        } else {
            $aWhere['pagina'] = $this->request->getVar('page');
        }

        $titulo = $aKeys[$operacion];

        // Determinar sucursal según permisos
        $id_Suc = false;
        if ($this->bUsaPermisosSucursal) {
            if ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) {
                if ($aWhere['nIdSucursal'] != '0') $id_Suc = $aWhere['nIdSucursal'];
            } else {
                if ($aWhere['nIdSucursal'] != '0') {
                    $id_Suc = $aWhere['nIdSucursal'];
                } else {
                    $id_Suc = $this->nIdSucursal;
                }
            }
        } else {
            // Para Venta: siempre usa la sucursal del usuario, excepto para compras
            $id_Suc = ($operacion == 'compra' ? false : $this->nIdSucursal);
        }

        // Cargar sucursales si es necesario
        $aSucursales = [];
        $bMostrarSelectorSucursal = in_array($operacion, array('entrada', 'salida', 'traspaso'));

        if ($bMostrarSelectorSucursal && $this->bUsaPermisosSucursal) {
            $mdlSucursal = new SucursalMdl();
            $aSucursales = $mdlSucursal->getRegistros();
            $regSucursal = $aSucursales[0];
            $regSucursal['nIdSucursal'] = '0';
            $regSucursal['sDescripcion'] = 'Todas las sucursales';
            array_unshift($aSucursales, $regSucursal);
        }

        $modelo = $this->getMovimientos($operacion, $id_Suc, 0, 8, $aWhere);

        $data = [
            'registros' =>  $modelo['regs'],
            'pager' => $modelo['pager'],
            'titulo' => $titulo,
            'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
            'operacion' => $operacion,
            'modocompra' => $modocompra,
            'aWhere' => $aWhere,
            'aSucursales' => $aSucursales,
            'nIdSucursal' => $this->request->getVar('nIdSucursal') ?? '0',
            'pagina' => $aWhere['pagina'],
        ];

        if ($bMostrarSelectorSucursal && $this->bUsaPermisosSucursal) {
            $data['lstSucursales'] = $aSucursales;
        }
        // Manejar mensajes de error de sesión
        if (isset($_SESSION['cMsjError'])) {
            $data['cMsjError'] = $_SESSION['cMsjError'];
        }

        unset($_SESSION[$operacion], $_SESSION['cMsjError']);

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/movimientos', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function buscar($operacion = 'compra')
    {
        session();
        $this->validaSesion();

        $aKeys = [
            'salida'   => ['nIdSalida',   'Salidas especiales', 'Salida especial'],
            'entrega'  => ['nIdEntrega',  'Entrega de mercancía', 'Entrega de mercancía'],
            'compra'   => ['nIdCompra',   'Recepción de compra', 'Cotización de compra'],
            'entrada'  => ['nIdEntrada',  'Recepción de entradas especiales', 'Entrada especial'],
            'traspaso' => ['nIdTraspaso', 'Recepción de traspaso de almacén', 'Traspaso almacén'],
        ];

        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = ['idInventarioCaptura', 'Aplicar captura modo inventario', 'Captura modo inventario'];
        }

        $masterkey = $aKeys[$operacion][0];
        $titulo = $aKeys[$operacion][1];
        $label = $aKeys[$operacion][2];

        $data = [
            'operacion' => $operacion,
            'titulo' => $titulo,
            'label' => $label,
        ];

        unset($_SESSION[$operacion], $_SESSION['cMsjError']);

        if ($this->request->getMethod() === 'post') {
            $a = $this->validaBusqueda();
            if ($a === false) {
                // Determinar sucursal según configuración
                if ($this->bUsaPermisosSucursal) {
                    $id_Suc = ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal;
                } else {
                    $id_Suc = ($operacion == 'compra' ? false : $this->nIdSucursal);
                }

                $modelo = $this->getMovimientos($operacion, $id_Suc, $this->request->getVar('nIdCompra'));
                $r = $modelo['regs'];
                if ($r !== null)
                    return redirect()->to(base_url("movimiento/$operacion/r/" . $r[$masterkey]));
                $data['error'] = ['nIdCompra' => 'No existe el folio solicitado'];
            } else {
                $data['error'] = $a['amsj'];
            }
        }

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/movimientobuscar', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    private function initVarSesion($operacion, $masterkey)
    {
        $_SESSION[$operacion] = [
            'total' => 0.0,
            'pagoCompleto' => false,
            'lstArt' => [],
            'foc' => 'art',
            'proveedor' => [
                'nIdProveedor' => 0,
                'nIdCliente' => 0,
                'nIdDirEntrega' => 0,
                'sNombre' => '',
                'email' => '',
                'sDireccion' => ''
            ],
            'registro' => [
                $masterkey => 0,
                'nIdSucursal' => $this->nIdSucursal,
                'sDescripcion' => $this->sSucursal,
                'nIdProveedor' => 0,
                'sNombre' => '',
                'sObservacion' => '',
                'fTotal' => 0,
                'cTipoSalida' => '',
            ],
        ];
    }

    public function limpiaOperacion($operacion, $tipoaccion, $idoperacion = 0)
    {
        session();
        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];

        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'];
        }

        $masterkey = $aKeys[$operacion][0];

        // Guardar historial si está habilitado
        $urlPrevio = null;
        if ($this->bUsaHistorialNavegacion && isset($_SESSION[$operacion]['hisBack'])) {
            $urlPrevio = $_SESSION[$operacion]['hisBack'];
        }

        unset($_SESSION[$operacion]);
        $this->initVarSesion($operacion, $masterkey);

        // Restaurar historial si estaba guardado
        if ($urlPrevio !== null) {
            $_SESSION[$operacion]['hisBack'] = $urlPrevio;
        }

        return redirect()->to(base_url("/movimiento/$operacion/" . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function recepcion($Xoperacion, $id = 0)
    {
        $this->validaSesion();

        $movto = new MovimientoMdl();
        $movotdetail = new MovimientoDetalleMdl();
        $inventario = new InventarioMdl();

        $operacion = $Xoperacion;
        if ($Xoperacion === 'entregaC')
            $operacion = 'entrega';

        // Manejo especial para entregaC (cancelación de entrega)
        if ($Xoperacion === 'entregaC') {
            $idOrigen = $this->request->getVar('nIdOrigen');
            $idProveedor = $this->request->getVar('nIdProveedor');
            $arrRecibido = $this->request->getVar($operacion);
            $nIdSucursal = $this->request->getVar('nIdSucursal');

            foreach ($arrRecibido  as $idArt => $fRecibido) {
                $ventadetail = new VentasDetMdl();
                $ventadetail->actualizaSaldo($idOrigen, $idArt, $fRecibido, '+');
                $inventario->updtArticuloSucursalInventario($nIdSucursal, $idArt, 0, -1 * $fRecibido);
            }

            $master = new EntregaMdl();
            $detail = new EntregaDetalleMdl();
            $master->delete($id);
            $detail->delete('nIdEntrega', $id);

            return json_encode(['ok' => '1']);
        }

        $aKeys = [
            'entrega' => ['nIdEntrega', 'dEntrega',],
            'salida' => ['nIdSalida', 'dSalida',],
            'compra' => ['nIdCompra', 'dcompra',],
            'entrada' => ['nIdEntrada', 'dEntrada',],
            'traspaso' => ['nIdTraspaso', 'dTraspaso',],
        ];

        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = ['idInventarioCaptura', 'dAplicacion',];
        }

        $masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];

        $dt = $this->request->getVar($datefld);
        $obs = $this->request->getVar('sObservacion') ?? '';
        $idOrigen = $this->request->getVar($masterkey);

        $r = [
            'nIdOrigen' => $idOrigen,
            'cOrigen' => $operacion,
            'dMovimiento' => $dt,
            'sObservacion' => $obs,
        ];

        // Guardar usuario si está configurado
        if ($this->bGuardaUsuarioMovimiento) {
            $r['nIdUsuario'] = $this->nIdUsuario;
        }

        $movto->save($r);
        $inserted = $movto->insertID();

        $nIdSucursal = $this->nIdSucursal;
        $nIdFuente = 0;

        // Determinar modelo master según operación
        switch ($operacion) {
            case 'entrega':
                $master = new EntregaMdl();
                $nIdFuente = intval($this->request->getVar('nIdOrigen'));
                break;
            case 'compra':
                $master = new ComprasMdl();
                break;
            case 'salida':
                $master = new SalidaMdl();
                break;
            case 'entrada':
                $master = new EntradaMdl();
                break;
            case 'traspaso':
                $nIdFuente = intval($this->request->getVar('nIdProveedor'));
                $master = new TraspasoMdl();
                break;
            case 'capturainv':
                if ($this->bModuloCapturaInv) {
                    $master = new InventarioCapturaMdl();
                }
                break;
        }

        $master->updateFecha($this->nIdSucursal, $idOrigen, $dt);
        $mdlArticulo = new ArticuloMdl();
        $arrRecibido = $this->request->getVar($operacion);
        foreach ($arrRecibido  as $idArt => $fRecibido) {
            if ($fRecibido === '')
                $fRecibido = 0;

            $det = [];

            // Determinar signo según operación
            // Ker usa -1 para traspaso, Venta solo para entrega y salida
            $nSigno = in_array($operacion, array('entrega', 'salida')) ? -1 : 1;

            $ar2d2 = $inventario->getArticuloSucursal($idArt, $nIdSucursal);
            $saldoIni = $ar2d2['fExistencia'] ?? 0;

            $det = [
                'nIdMovimiento' => $inserted,
                'nIdArticulo' => $idArt,
                'nIdSucursal' => $nIdSucursal,
                'fCantidad' => $nSigno * $fRecibido,
                'fSaldoInicial' => $saldoIni,
            ];
            $movotdetail->save($det);

            // Determinar modelo de detalle según operación
            switch ($operacion) {
                case 'entrega':
                    $origen = new EntregaDetalleMdl();
                    $ventadet = new VentasDetMdl();
                    $venta = new VentasMdl();
                    $ventadet->updtRecibidos($nIdFuente, $idArt, $fRecibido);
                    break;
                case 'compra':
                    $origen = new CompraDetalleMdl();
                    break;
                case 'salida':
                    $origen = new SalidaDetalleMdl();
                    break;
                case 'entrada':
                    $origen = new EntradaDetalleMdl();
                    break;
                case 'traspaso':
                    $origen = new TraspasoDetalleMdl();
                    break;
                case 'capturainv':
                    if ($this->bModuloCapturaInv) {
                        $origen = new InventarioCapturaDetalleMdl();
                    }
                    break;
            }

            $origen->updtRecibidos($idOrigen, $idArt, $fRecibido);

            // Actualizar inventario considerando validación de existencia
            if ($this->bValidaSinExistencia) {
                $regArt = $mdlArticulo->select('cSinExistencia')->where('nIdArticulo', $idArt)->first();
                if ($regArt['cSinExistencia'] == '0') {
                    $inventario->updtArticuloSucursalInventario(
                        $nIdSucursal,
                        $idArt,
                        $nSigno * $fRecibido,
                        $operacion === 'entrega'
                    );
                }
            } else {
                // Versión Ker: siempre actualiza inventario
                $inventario->updtArticuloSucursalInventario(
                    $nIdSucursal,
                    $idArt,
                    $nSigno * $fRecibido,
                    $operacion === 'entrega' ? $nSigno * $fRecibido : 0
                );
            }

            // Actualizar inventario de sucursal fuente en traspasos
            if ($operacion === 'traspaso') {
                $inventario->updtArticuloSucursalInventario($nIdFuente, $idArt, $fRecibido);
            }
        }

        $master->updateEdo($this->nIdSucursal, $idOrigen);

        if ($operacion === 'entrega')
            $venta->updateEdoSurtido($this->nIdSucursal, $nIdFuente, 'Entrega');

        return json_encode(['ok' => '1']);
    }

    public function recepcioncompra($operacion, $id = 0)
    {
        $this->validaSesion();

        $articulo = new ArticuloMdl();
        $proveedor = new ProveedorMdl();

        $aKeys = [
            'entrega' => ['nIdEntrega', 'dEntrega',],
            'salida' => ['nIdSalida', 'dSalida',],
            'compra' => ['nIdCompra', 'dcompra',],
            'entrada' => ['nIdEntrada', 'dEntrada',],
            'traspaso' => ['nIdTraspaso', 'dTraspaso',],
        ];
        $masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];

        $dt = $this->request->getVar($datefld);
        $obs = $this->request->getVar('sObservacion');
        $idOrigen = $this->request->getVar($masterkey);

        $r = [
            'nIdOrigen' => $idOrigen,
            'cOrigen' => $operacion,
            'dMovimiento' => $dt,
            'sObservacion' => $obs,
        ];

        // Guardar usuario si está configurado
        if ($this->bGuardaUsuarioMovimiento) {
            $r['nIdUsuario'] = $this->nIdUsuario;
        }

        $nIdSucursal = $this->request->getVar('nIdSucursal');

        switch ($operacion) {
            case 'entrega':
                $master = new EntregaMdl();
                break;
            case 'compra':
                $master = new ComprasMdl();
                break;
            case 'salida':
                $master = new SalidaMdl();
                break;
            case 'entrada':
                $master = new EntradaMdl();
                break;
            case 'traspaso':
                $master = new TraspasoMdl();
                break;
        }

        $master->updateFecha($this->nIdSucursal, $idOrigen, $dt);

        $arrRecibido = $this->request->getVar($operacion);
        foreach ($arrRecibido  as $idArt => $fRecibido) {

            switch ($operacion) {
                case 'entrega':
                    $origen = new EntregaDetalleMdl();
                    break;
                case 'compra':
                    $origen = new CompraDetalleMdl();
                    break;
                case 'salida':
                    $origen = new SalidaDetalleMdl();
                    break;
                case 'entrada':
                    $origen = new EntradaDetalleMdl();
                    break;
                case 'traspaso':
                    $origen = new TraspasoDetalleMdl();
                    break;
            }

            // Venta usa updtPrecio, Ker usa updtCosto - unificamos a updtPrecio
            $origen->updtPrecio($idOrigen, $idArt, $fRecibido);
            $articulo->updtPrecio($idArt, $fRecibido);
        }

        $master->updateSaldo($this->nIdSucursal, $idOrigen, $this->request->getVar('fTotal'));
        $proveedor->updtSaldo($this->request->getVar('nIdProveedor'), $this->request->getVar('fTotal'));

        return json_encode(['ok' => '1']);
    }

    public function getMovimientos($operacion = 'entrada', $idsuc = false, $id = 0, $pages = 0, $aWhere = null)
    {
        $this->validaSesion();

        $rcancel = null;

        switch ($operacion) {
            case 'traspaso':
                $model = new TraspasoMdl();
                break;
            case 'entrega':
                $model = new EntregaMdl();
                break;
            case 'entrada':
                $model = new EntradaMdl();
                if ($this->bUsaCancelacionEntradas) {
                    $cancel = new EntradaCancelMdl();
                    $rcancel = $cancel->getRegistrosByField('nIdEntrada', $id);
                }
                break;
            case 'compra':
                $model = new ComprasMdl();
                break;
            case 'salida':
                $model = new SalidaMdl();
                break;
            case 'capturainv':
                if ($this->bModuloCapturaInv) {
                    $model = new InventarioCapturaMdl();
                }
                break;
        }

        $resp = [
            'regs' => $model->getRegistros($idsuc, $id, $pages, $aWhere),
            'pager' => $model->pager,
        ];

        if ($rcancel !== null) {
            $resp['cancel'] = $rcancel;
        }

        return $resp;
    }

    public function delMovimiento($operacion = 'entrada', $idsuc = false, $id = 0, $pages = 0)
    {
        $this->validaSesion();

        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];

        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'];
        }

        $masterkey = $aKeys[$operacion][0];

        switch ($operacion) {
            case 'salida':
                $master = new SalidaMdl();
                $detalle = new SalidaDetalleMdl();
                break;
            case 'compra':
                $master = new ComprasMdl();
                $detalle = new CompraDetalleMdl();
                break;
            case 'entrega':
                $master = new EntregaMdl();
                $detalle = new EntregaDetalleMdl();
                break;
            case 'entrada':
                $master = new EntradaMdl();
                $detalle = new EntradaDetalleMdl();
                break;
            case 'traspaso':
                $master = new TraspasoMdl();
                $detalle = new TraspasoDetalleMdl();
                if ($this->bModuloLogistica) {
                    $this->updEnvioTraspaso($id);
                }
                break;
            case 'capturainv':
                if ($this->bModuloCapturaInv) {
                    $master = new InventarioCapturaMdl();
                    $detalle = new InventarioCapturaDetalleMdl();
                }
                break;
        }

        $master->delete($id);
        $detalle->where($masterkey, $id)->delete();

        return;
    }

    private function updEnvioTraspaso($id)
    {
        if (!$this->bModuloLogistica) return;

        $envio = new EnvioMdl();
        $enviod = new EnvioDetalleMdl();
        $rEnv = $envio->where(['nIdOrigen' => $id, 'cOrigen' => 'traspaso'])->first();

        if ($rEnv !== null) {
            // eliminamos el detalle del envio
            $enviod->where('nIdEnvio', $rEnv['nIdEnvio'])->delete();
            // eliminamos el envio
            $envio->where('nIdEnvio', $rEnv['nIdEnvio'])->delete();
        }
    }

    public function cancelaMovimiento($operacion = 'entrada', $id = 0, $idsuc = false, $pages = 0)
    {
        if (!$this->bUsaCancelacionEntradas) {
            return redirect()->to(base_url('movimiento/' . $operacion));
        }

        $this->validaSesion();

        $aKeys = [
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
        ];

        $masterkey = $aKeys[$operacion][0];

        switch ($operacion) {
            case 'entrada':
                $master = new EntradaMdl();
                $detalle = new EntradaDetalleMdl();
                $cancel = new EntradaCancelMdl();
                break;
            default:
                return;
        }

        $master->set('cEdoEntrega', '9')->where('nIdEntrada', $id)->update();
        $detalle->set('fPorRecibir', 0)->where('nIdEntrada', $id)->update();

        $rcancel = [
            'nIdEntrada' => $id,
            'sObservacion' => $this->request->getVar('sObservacion'),
        ];
        $cancel->save($rcancel);

        return;
    }

    public function getMovimientosDetalle($operacion = 'entrada', $id = 0, $pages = 0)
    {
        $this->validaSesion();

        switch ($operacion) {
            case 'traspaso':
                $model = new TraspasoDetalleMdl();
                break;
            case 'entrega':
                $model = new EntregaDetalleMdl();
                break;
            case 'entrada':
                $model = new EntradaDetalleMdl();
                break;
            case 'compra':
                $model = new CompraDetalleMdl();
                break;
            case 'salida':
                $model = new SalidaDetalleMdl();
                break;
            case 'capturainv':
                if ($this->bModuloCapturaInv) {
                    $model = new InventarioCapturaDetalleMdl();
                }
                break;
        }

        if ($operacion === 'entrega')
            $resp = [
                'regs' => $model->getRegistros($id, $pages, $this->nIdSucursal),
                'pager' => $model->pager,
            ];
        else
            $resp = [
                'regs' => $model->getRegistros($id, $pages),
                'pager' => $model->pager,
            ];

        return $resp;
    }

    public function saveMovimiento($operacion = 'entrada', $id = 0)
    {
        $this->validaSesion();

        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];

        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'];
        }

        $masterkey = $aKeys[$operacion][0];

        $r = [
            'dSolicitud' => $_SESSION[$operacion]['registro']['dSolicitud'],
            'nIdSucursal' => $_SESSION[$operacion]['registro']['nIdSucursal'],
            'nIdProveedor' => $_SESSION[$operacion]['registro']['nIdProveedor'],
            'fTotal' => 0,
            'nProductos' => count($_SESSION[$operacion]['lstArt']),
            'sObservacion' => $this->request->getVar('sObservacion') ?? '',
            'cTipoSalida' => $this->request->getVar('cTipoSalida') ?? '0',
        ];

        $fSaldo = 0;
        $detallesaldo = null;

        switch ($operacion) {
            case 'salida':
                $master = new SalidaMdl();
                $detalle = new SalidaDetalleMdl();
                break;
            case 'compra':
                $master = new ComprasMdl();
                $detalle = new CompraDetalleMdl();

                if ($this->bUsaModeloSaldoCompra) {
                    $detallesaldo = new CompraDetalleSaldoMdl();
                    $fSaldo = $this->request->getVar('fTotal2');
                    $r['fTotal'] = $fSaldo;
                    $r['fSaldo'] = $fSaldo;
                    $r['dcompra'] = $r['dSolicitud'];
                }
                break;
            case 'entrega':
                $master = new EntregaMdl();
                $detalle = new EntregaDetalleMdl();
                break;
            case 'entrada':
                $master = new EntradaMdl();
                $detalle = new EntradaDetalleMdl();
                break;
            case 'traspaso':
                $master = new TraspasoMdl();
                $detalle = new TraspasoDetalleMdl();
                break;
            case 'capturainv':
                if ($this->bModuloCapturaInv) {
                    $master = new InventarioCapturaMdl();
                    $detalle = new InventarioCapturaDetalleMdl();
                }
                break;
        }

        if ($id > 0) {
            $r[$masterkey] = $_SESSION[$operacion]['registro']["$masterkey"];
        }

        $master->save($r);

        if ($id > 0) {
            $inserted = $id;
            $detalle->where($masterkey, $inserted)->delete(null, true);
            if ($operacion == 'compra' && $this->bUsaModeloSaldoCompra && $detallesaldo !== null) {
                $detallesaldo->where("nIdCompra", $inserted)->delete(true);
            }
        } else {
            $inserted = $master->insertID();
        }

        foreach ($_SESSION[$operacion]['lstArt'] as $rd) {
            $det = [
                $masterkey => $inserted,
                'nIdArticulo' => $rd['nIdArticulo'],
                'fCantidad' => $rd['fCantidad'],
                'fRecibido' => 0,
                'fPorRecibir' => $rd['fCantidad'],
                'fImporte' => $rd['fImporte'],
            ];

            // Campos específicos para captura de inventario
            if ($operacion === 'capturainv' && $this->bModuloCapturaInv) {
                $det['fExistencia'] = $rd['fExistencia'];
            }

            // Campos específicos para compras con logística
            if ($operacion == 'compra' && $this->bModuloLogistica) {
                $det['nIdViajeEnvio'] = $rd['nIdViajeEnvio'] ?? 0;
                $det['fIVA'] = $rd['fIVA'] ?? 0;
                if (isset($rd['nIdViajeEnvio']) && $rd['nIdViajeEnvio'] > 0) {
                    $det['fRecibido'] = $rd['fCantidad'];
                    $det['fPorRecibir'] = 0;
                }
            }

            $detalle->save($det);

            // Actualizar precio del artículo si cambió
            if ($operacion === 'compra' || $operacion === 'entrada') {
                if ($rd['nCosto'] <> $rd['fImporte']) {
                    $articulo = new ArticuloMdl();
                    $articulo->updtPrecio(
                        $rd['nIdArticulo'],
                        $rd['fImporte']
                    );
                }
            }
        }

        // Crear envío para traspasos si módulo de logística está habilitado
        if ($operacion === 'traspaso' && $this->bModuloLogistica) {
            $envio = new EnvioMdl();
            $enviodet = new EnvioDetalleMdl();

            $e = [
                'nIdOrigen' => $inserted,
                'cOrigen' => $operacion,
                'nIdSucursal' => $_SESSION[$operacion]['proveedor']['nIdProveedor'],
                'dSolicitud' =>  $_SESSION[$operacion]['registro']['dSolicitud'],
                'nIdCliente' => $this->nIdSucursalCliente,
                'nIdDirEntrega' => $this->nIdDirEntrega,
                'cEstatus' => '1',
            ];
            $envio->save($e);
            $envioinserted = $envio->insertID();

            foreach ($_SESSION[$operacion]['lstArt'] as $rd) {
                $edet = [
                    'nIdEnvio' => $envioinserted,
                    'nIdArticulo' => $rd['nIdArticulo'],
                    'fCantidad' => $rd['fCantidad'],
                    'fRecibido' => 0.0,
                    'fPorRecibir' => $rd['fCantidad'],
                ];
                $enviodet->save($edet);
            }
        }

        $_SESSION[$operacion]['registro']["$masterkey"] = $inserted;
        return;
    }

    public function imprime($operacion = 'entrada', $id = 0)
    {
        $this->validaSesion(false, "imprime/$operacion");

        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];

        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'];
        }

        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];

        // Determinar sucursal según configuración
        $idSuc = $this->bUsaPermisosSucursal ? false : $this->nIdSucursal;

        $master = $this->getMovimientos($operacion, $idSuc, intval($id));
        $registro =  $master['regs'];
        $timestamp = strtotime($registro[$datefld] ?? date("d-m-Y"));
        $registro[$datefld] = date("Y-m-d", $timestamp);

        $modetalle = $this->getMovimientosDetalle($operacion, intval($id));
        $arts = $modetalle['regs'];

        $prov = [
            'nIdProveedor' => $registro['nIdProveedor'],
            'sNombre' => $registro['sNombre'],
            'email' => '',
            'sDireccion' => ''
        ];

        $registro['fTotal'] = number_format(floatval($registro['fTotal']), 2);

        $data = [
            'titulo' => $stitulooperacion,
            'operacion' => $operacion,
            'modo' => strtoupper('p'),
            'id' => $id,
            'registros' => $arts,
            'registro' => $registro,
            'proveedor' => $prov,
        ];

        // Si usa impresión extendida (Venta), incluir historial de recepciones
        if ($this->bPantallaImpresionExtendida) {
            $aRegis = [];
            $aRegis[0] = ['Captura', $master['regs'],  $modetalle['regs']];

            $mdlMovto = new MovimientoMdl();
            $mdlMovtoDet = new  MovimientoDetalleMdl();

            $regs = $mdlMovto
                ->select('almovimiento.*, IFNULL(u.sNombre, \'\') AS nomUsu')
                ->join('sgusuario u', 'almovimiento.nIdUsuario = u.nIdUsuario', 'left')
                ->where('cOrigen', $operacion)->where('nIdOrigen', $id)
                ->findAll();

            foreach ($regs as $k => $row) {
                $regsD = $mdlMovtoDet
                    ->select('almovimientodetalle.nIdArticulo, almovimientodetalle.fCantidad, a.sDescripcion')
                    ->join('alarticulo a', 'almovimientodetalle.nIdArticulo = a.nIdArticulo', 'inner')
                    ->where('nIdMovimiento', $row['nIdMovimiento'])
                    ->where('fCantidad <>', 0)
                    ->findAll();
                $aRegis[] = ['Recepcion', $row, $regsD];
            }

            $data['aRegLis'] = $aRegis;
            $data['aInfoSis'] = $this->dataMenu['aInfoSis'];
            $data['frmURListado'] = base_url('movimiento/' . $operacion);

            echo view('almacen/movimientoimpresion', $data);
        } else {
            // Versión simple (Ker)
            $data['tipoaccion'] = 'p';
            $data['frmURL'] = 'movimiento/' . $operacion . '/p' . ($id > 0 ? '/' . $id : '');
            $data['enfoque'] = 'art';
            $data['sSucursal'] = $this->sSucursal;
            $data['printerMode'] = true;

            echo view('templates/header', $this->dataMenu);
            echo view('almacen/movimientomtto', $data);
            echo view('templates/footer', $this->dataMenu);
        }

        unset($_SESSION[$operacion]);
        return;
    }

    public function accion($operacion, $tipoaccion, $id = 0)
    {
        $this->validaSesion(false, "movimiento/$operacion/$tipoaccion");

        $aTitulo = ['a' => 'Agregar', 'b' => 'Cancelar', 'x' => 'Cancelar', 'e' => 'Editar', 'r' => 'Recepción', 'p' => 'Cotización', 'v' => 'Consultar'];
        $stituloevento = $aTitulo[$tipoaccion] ?? 'Ver';

        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];

        if ($this->bModuloCapturaInv) {
            $aKeys['capturainv'] = ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'];
        }

        $masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];
        $sufijoURL = ($operacion . '/' . $tipoaccion . ($id > 0 ? '/' . $id : ''));

        // Cargar sucursales si es necesario para Ker
        $mdlSucursal = new SucursalMdl();
        $rgSucursales = $mdlSucursal->getRegistros();

        if (!isset($_SESSION[$operacion])) {
            $this->initVarSesion($operacion, $masterkey);
        }

        // Manejar historial de navegación
        if ($this->bUsaHistorialNavegacion) {
            if (!isset($_SESSION[$operacion]['hisBack'])) {
                $_SESSION[$operacion]['hisBack'] = previous_url();
                if (strpos($_SESSION[$operacion]['hisBack'], '/imprime/') !== false) {
                    $_SESSION[$operacion]['hisBack'] = '0';
                }
            }
        }

        $titulo = $stituloevento . ' ' . $stitulooperacion .
            (in_array($tipoaccion, ['a', 'p']) ? '' : ' con folio ' . ($_SESSION[$operacion]['editandoregistro'] ?? $id));

        $data = [
            'titulo' => $titulo,
            'operacion' => $operacion,
            'tipoaccion' => $tipoaccion,
            'sufijoURL' => $sufijoURL,
            'frmURL' => ('movimiento/' . $sufijoURL),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
            'lstSucursales' => $rgSucursales,
        ];

        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'r')
                $id = $this->request->getVar($masterkey);

            // Tipoacción 'b' = cancelar/borrar movimiento
            if ($tipoaccion === 'b') {
                // Validaciones específicas según operación
                if ($operacion == 'traspaso' && $this->bModuloLogistica) {
                    $mdlTraspaso = new TraspasoMdl();
                    $reVal = $mdlTraspaso->getRegistros(false, $id, 0);
                    if (isset($reVal['contEnviosViajes']) && $reVal['contEnviosViajes'] > 0) {
                        $_SESSION['cMsjError'] = 'El envio ya se asigno a un viaje.';
                        return redirect()->to(base_url('movimiento/' . $operacion));
                    }
                }

                if ($operacion == 'compra' && $this->bUsaModeloSaldoCompra) {
                    $mdlCompraDetSal = new CompraDetalleSaldoMdl();
                    $mdlCompraDetSal->where("nIdCompra", $id)->delete(true);
                }

                $this->delMovimiento($operacion, false, $id);
                $this->initVarSesion($operacion, $masterkey);
                return redirect()->to(base_url('movimiento/' . $operacion));
            }

            // Tipoacción 'x' = cancelar entrada (Ker)
            if ($tipoaccion === 'x' && $this->bUsaCancelacionEntradas) {
                $this->cancelaMovimiento($operacion, $id);
                $this->initVarSesion($operacion, $masterkey);
                return redirect()->to(base_url('movimiento/' . $operacion));
            }

            $a = $this->validaCampos($operacion);
            if (!$a) {
                // guardar datos
                $this->saveMovimiento($operacion, $id);
                $this->initVarSesion($operacion, $masterkey);
                return redirect()->to(base_url('movimiento/' . $operacion));
            } else {
                $data = [
                    'error' => $a['amsj'],
                    'registros' => $_SESSION[$operacion]['lstArt'] ?? [],
                    'registro' => $_SESSION[$operacion]['registro'],
                    'proveedor' => $_SESSION[$operacion]['proveedor'],
                    'tipoaccion' => $tipoaccion,
                    'sufijoURL' => $sufijoURL,
                    'frmURL' => ('movimiento/' . $sufijoURL),
                    'modo' => strtoupper($tipoaccion),
                    'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
                    'lstSucursales' => $rgSucursales,
                    'id' => $id,
                    'enfoque' => $_SESSION[$operacion]['foc'] ?? 'art'
                ];
            }
        } else {
            if (!in_array($tipoaccion, ['a', 'e'])) {
                $this->initVarSesion($operacion, $masterkey);

                if ($this->bUsaHistorialNavegacion) {
                    $_SESSION[$operacion]['hisBack'] = previous_url();
                    if (strpos($_SESSION[$operacion]['hisBack'], '/imprime/') !== false) {
                        $_SESSION[$operacion]['hisBack'] = '0';
                    }
                }

                // Determinar sucursal según configuración
                $idSucConsulta = false;
                if ($this->bUsaPermisosSucursal) {
                    $idSucConsulta = ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal;
                } else {
                    $idSucConsulta = ($operacion == 'compra' ? false : $this->nIdSucursal);
                }

                $master = $this->getMovimientos($operacion, $idSucConsulta, intval($id));
                $registro =  $master['regs'];

                if (empty($registro))
                    return redirect()->to(base_url('movimiento/' . $operacion));

                $timestamp = strtotime($registro['dSolicitud'] ?? date("d-m-Y"));
                $registro['dSolicitud'] = date("Y-m-d", $timestamp);

                $modetalle = $this->getMovimientosDetalle($operacion, intval($id));
                $arts = $modetalle['regs'];

                $prov = [
                    'nIdProveedor' => $registro['nIdProveedor'],
                    'sNombre' => $registro['sNombre'],
                    'email' => '',
                    'sDireccion' => ''
                ];

                $frmURL = 'movimiento/' .  $sufijoURL;
                $recepcioncompra = '';

                if ($operacion === 'compra' && $tipoaccion === 'n')
                    $recepcioncompra = 'compra';

                if ($tipoaccion === 'r' || $tipoaccion === 'n') {
                    $timestamp = strtotime($registro[$datefld] ?? date("d-m-Y"));
                    $registro[$datefld] = date("Y-m-d", $timestamp);
                    $frmURL = ('movimiento/recepcion' . $recepcioncompra . '/' . $operacion . '/' .  $id);

                    // Preparar datos para compras con logística
                    if ($operacion === 'compra' && $this->bModuloLogistica) {
                        foreach ($arts as &$rg) {
                            if (!array_key_exists('cModoEnv', $rg))
                                $rg['cModoEnv'] = '0';
                        }
                    }
                }

                $data = [
                    'titulo' => $titulo,
                    'operacion' => $operacion,
                    'tipoaccion' => $tipoaccion,
                    'sufijoURL' => $sufijoURL,
                    'frmURL' => $frmURL,
                    'modo' => strtoupper($tipoaccion),
                    'id' => $id,
                    'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
                    'lstSucursales' => $rgSucursales,
                    'registros' => $arts,
                    'enfoque' => 'art',
                    'registro' => $registro,
                    'proveedor' => $prov,
                ];

                // Agregar datos de cancelación si existen
                if ($this->bUsaCancelacionEntradas && isset($master['cancel'])) {
                    $data['regcancel'] = $master['cancel'];
                }
            } else {
                // Modo agregar o editar

                // Para edición de compras, cargar datos previos
                if ($operacion == 'compra' && !isset($_SESSION[$operacion]['editandoregistro']) && $tipoaccion != 'a') {
                    $idSucConsulta = $this->bUsaPermisosSucursal ?
                        (($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal) :
                        $this->nIdSucursal;

                    $master = $this->getMovimientos($operacion, $idSucConsulta, intval($id));
                    $registro =  $master['regs'];

                    $timestamp = strtotime($registro['dSolicitud'] ?? date("d-m-Y"));

                    $_SESSION[$operacion]['registro'] = [
                        $masterkey => $id,
                        'nIdSucursal' => $this->nIdSucursal,
                        'sDescripcion' => $this->sSucursal,
                        'nIdProveedor' => $registro['nIdProveedor'],
                        'sNombre' => $registro['sNombre'],
                        'sObservacion' => $registro['sObservacion'],
                        'fTotal' => $registro['fTotal'],
                        'dSolicitud' => date("Y-m-d", $timestamp),
                    ];

                    $modetalle = $this->getMovimientosDetalle($operacion, intval($id));
                    $arts = $modetalle['regs'];

                    $_SESSION[$operacion]['proveedor'] = [
                        'nIdProveedor' => $registro['nIdProveedor'],
                        'sNombre' => $registro['sNombre'],
                        'email' => '',
                        'sDireccion' => ''
                    ];

                    if ($tipoaccion === 'e') {
                        $_SESSION[$operacion]['lstArt'] = [];
                        foreach ($arts as $r) {
                            $artData = [
                                'nIdArticulo' => $r['nIdArticulo'],
                                'sDescripcion' => $r['sDescripcion'],
                                'nCosto' => $r['nCosto'],
                                'fImporte' => $r['fImporte'],
                                'fCantidad' => $r['fCantidad'],
                                'fPorRecibir' => $r['fPorRecibir'],
                            ];

                            // Agregar campos de logística si están disponibles
                            if ($this->bModuloLogistica) {
                                $artData['nIdEnvio'] = $r['nIdEnvio'] ?? 0;
                                $artData['nIdViaje'] = $r['nIdViaje'] ?? 0;
                                $artData['nIdViajeEnvio'] = $r['nIdViajeEnvio'] ?? 0;
                                $artData['fIVA'] = $r['fIVA'] ?? 0;
                            }

                            $_SESSION[$operacion]['lstArt'][] = $artData;
                        }
                    }
                    $_SESSION[$operacion]['editandoregistro'] = intval($id);
                }

                // Preparar fecha si el proveedor no está seleccionado
                if ($_SESSION[$operacion]['proveedor']['nIdProveedor'] == 0) {
                    $timestamp = strtotime($_SESSION[$operacion]['registro']['dSolicitud'] ?? date("d-m-Y"));
                    $_SESSION[$operacion]['registro']['dSolicitud'] = date("Y-m-d", $timestamp);
                }

                $data = [
                    'titulo' => $titulo,
                    'operacion' => $operacion,
                    'tipoaccion' => $tipoaccion,
                    'sufijoURL' => $sufijoURL,
                    'frmURL' => ('movimiento/' . $sufijoURL),
                    'modo' => strtoupper($tipoaccion),
                    'id' => $id,
                    'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
                    'lstSucursales' => $rgSucursales,
                    'registros' => $_SESSION[$operacion]['lstArt'] ?? [],
                    'registro' => $_SESSION[$operacion]['registro'],
                    'enfoque' => $_SESSION[$operacion]['foc'] ?? 'art',
                    'proveedor' => $_SESSION[$operacion]['proveedor'],
                ];
            }

            // Agregar URL de listado si está configurado
            if ($this->bUsaHistorialNavegacion) {
                $data['frmURListado'] = base_url('movimiento/' . $operacion);
                $data['hisBack'] = $_SESSION[$operacion]['hisBack'];
            }
        }

        $data['titulo'] = $titulo;
        $data['operacion'] = $operacion;
        $data['sSucursal'] = $this->sSucursal;

        // Determinar pantalla a mostrar
        $pantalla = ($tipoaccion === 'r' || $tipoaccion === 'n' ? 'recepcion' : 'movimiento');

        // Pantallas específicas para compras si está habilitado
        if ($operacion === 'compra' && $this->bModuloLogistica) {
            $pantalla .= 'compra';
        }

        $pantalla .= 'mtto';

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/' . $pantalla, $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function agregaArticulo($operacion, $tipoaccion, $idoperacion = 0)
    {
        $this->validaSesion();

        if (!isset($_SESSION[$operacion]['lstArt'])) {
            $_SESSION[$operacion]['lstArt'] = array();
        }

        $nPos = -1;
        $nIdKey = 'nIdArticulo';
        $key = $this->request->getVar("$nIdKey");

        foreach ($_SESSION[$operacion]['lstArt'] as $k => $v) {
            if ($v[$nIdKey] == $key) {
                $nPos = $k;
                break;
            }
        }

        $artData = [
            $nIdKey => $this->request->getVar("$nIdKey"),
            'sDescripcion' => $this->request->getVar('dlArticulos0'),
            'nCosto' => $this->request->getVar('nCosto'),
            'fImporte' => $this->request->getVar('fImporte'),
            'fCantidad' => $this->request->getVar('nCant'),
            'fPorRecibir' => $this->request->getVar('nCant'),
        ];

        // Agregar existencia si está presente (para capturainv)
        if ($this->request->getVar('fExistencia') !== null) {
            $artData['fExistencia'] = $this->request->getVar('fExistencia');
        }

        if ($nPos == -1)
            $_SESSION[$operacion]['lstArt'][] = $artData;
        else
            $_SESSION[$operacion]['lstArt'][$nPos] = $artData;

        $_SESSION[$operacion]['foc'] = 'art';
        $datefld = 'dSolicitud';
        $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
        $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");

        // Para Ker: también actualiza nIdSucursal si está presente
        if ($this->bUsaPermisosSucursal && $this->request->getVar('nIdSucursal') !== null) {
            $_SESSION[$operacion]['registro']['nIdSucursal'] = $this->request->getVar('nIdSucursal');
        }

        return redirect()->to(base_url('movimiento/' . $operacion . '/' . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function agregaArticuloCompra($operacion, $tipoaccion, $idoperacion = 0)
    {
        if (!$this->bModuloLogistica) {
            return $this->agregaArticulo($operacion, $tipoaccion, $idoperacion);
        }

        $this->validaSesion();

        if (!isset($_SESSION[$operacion]['lstArt'])) {
            $_SESSION[$operacion]['lstArt'] = array();
        }

        $nPos = -1;
        $nIdKey = 'nIdArticulo';
        $key = $this->request->getVar("$nIdKey");
        $idEnv = $this->request->getVar("nIdEnvio") ?? '';
        $idEnv = $idEnv == '' ? '0' : $idEnv;
        $idVia = $this->request->getVar("nIdViaje") ?? '';
        $idVia = $idVia == '' ? '0' : $idVia;
        $idViaEnv = $this->request->getVar("nIdViajeEnvio") ?? '';
        $idViaEnv = $idViaEnv == '' ? '0' : $idViaEnv;

        foreach ($_SESSION[$operacion]['lstArt'] as $k => $v) {
            if ($v[$nIdKey] == $key and $v['nIdEnvio'] == $idEnv) {
                $nPos = $k;
                break;
            }
        }

        $artData = [
            $nIdKey => $this->request->getVar("$nIdKey"),
            'sDescripcion' => $this->request->getVar('dlArticulos0'),
            'nCosto' => $this->request->getVar('nCosto'),
            'fImporte' => $this->request->getVar('fImporte'),
            'fCantidad' => $this->request->getVar('nCant'),
            'fPorRecibir' => $this->request->getVar('nCant'),
            'nIdEnvio' => $idEnv,
            'nIdViaje' => $idVia,
            'nIdViajeEnvio' => $idViaEnv,
            'fIVA' => $this->request->getVar('fIVA'),
        ];

        if ($nPos == -1)
            $_SESSION[$operacion]['lstArt'][] = $artData;
        else
            $_SESSION[$operacion]['lstArt'][$nPos] = $artData;

        $_SESSION[$operacion]['foc'] = 'art';
        $datefld = 'dSolicitud';
        $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
        $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");

        return redirect()->to(base_url('movimiento/' . $operacion . '/' . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function agregaProveedor($operacion, $tipoaccion, $idoperacion = 0)
    {
        session();

        $datefld = 'dSolicitud';

        // Validación solo si es necesario (comentado en ambas versiones originales)
        // $reglas = [
        //     'nIdProveedor' => 'required|decimal|greater_than[0]',
        // ];
        // $reglasMsj = [
        //     'nIdProveedor' => [
        //         'required'   => 'Se requiere proveedor',
        //         'greater_than' => 'Clave proveedor no válida'
        //     ],
        // ];
        // $this->validate($reglas, $reglasMsj);

        $_SESSION[$operacion]['foc'] = 'art';

        if ($tipoaccion == 'a' || $tipoaccion == 'e') {
            $_SESSION[$operacion]['proveedor'] = [
                'nIdProveedor' => $this->request->getVar('nIdProveedor'),
                'nIdCliente' => $this->request->getVar('nIdCliente'),
                'sNombre' => $this->request->getVar('dlProveedores0'),
                'email' => '',
                'sDireccion' => ''
            ];

            $_SESSION[$operacion]['nIdProveedor'] = $this->request->getVar('nIdProveedor');
            $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
            $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");
            $_SESSION[$operacion]['registro']['nIdProveedor'] = $this->request->getVar('nIdProveedor');
            $_SESSION[$operacion]['registro']['sNombre'] = $this->request->getVar('dlProveedores0');

            // Para Ker con permisos: también actualiza nIdSucursal
            if ($this->bUsaPermisosSucursal && $this->request->getVar('nIdSucursal') !== null) {
                $_SESSION[$operacion]['registro']['nIdSucursal'] = $this->request->getVar('nIdSucursal');
            }
        }

        return redirect()->to(base_url('movimiento/' . $operacion . '/' . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function borraArticulo($id, $operacion, $tipoaccion, $idoperacion = 0)
    {
        $session = session();
        $a = [];

        foreach ($_SESSION[$operacion]['lstArt'] as $r) {
            if ($r['nIdArticulo'] != $id) {
                $a[] = $r;
            }
        }

        $_SESSION[$operacion]['lstArt'] = $a;
        $_SESSION[$operacion]['foc'] = 'art';

        return redirect()->to(base_url('movimiento/' . $operacion . '/' . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function borraArticuloCompra($idArticulo, $idEnvio, $operacion, $tipoaccion, $idoperacion = 0)
    {
        if (!$this->bModuloLogistica) {
            return $this->borraArticulo($idArticulo, $operacion, $tipoaccion, $idoperacion);
        }

        $session = session();
        $a = [];

        foreach ($_SESSION[$operacion]['lstArt'] as $r) {
            if (($r['nIdArticulo'] == $idArticulo && $r['nIdEnvio'] == $idEnvio)) continue;
            $a[] = $r;
        }

        $_SESSION[$operacion]['lstArt'] = $a;
        $_SESSION[$operacion]['foc'] = 'art';

        return redirect()->to(base_url('movimiento/' . $operacion . '/' . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function validaCampos($operacion)
    {
        $reglasMsj = [];

        if ($this->nIdSucursal == 0)
            $reglasMsj['nIdSucursal'] = 'Se requiere seleccionar una sucursal';

        if ($operacion === 'compra') {
            if ($_SESSION[$operacion]['proveedor']['nIdProveedor'] === 0 || $_SESSION[$operacion]['proveedor']['nIdProveedor'] === '')
                $reglasMsj['nIdProveedor'] = 'Se requiere seleccionar un proveedor';
        }

        if (!empty($reglasMsj)) {
            return ['amsj' => $reglasMsj];
        }

        return false;
    }

    public function validaBusqueda()
    {
        $reglas = [
            'nIdCompra' => 'required',
        ];
        $reglasMsj = [
            'nIdCompra' => [
                'required'   => 'Falta folio de orden de compra',
            ],
        ];

        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }

        return false;
    }
}
