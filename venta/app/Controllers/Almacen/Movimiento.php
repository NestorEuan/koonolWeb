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
use App\Models\Envios\EnvioMdl;
use App\Models\Envios\EnvioDetalleMdl;

use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Catalogos\ProveedorMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Viajes\ViajeEnvioMdl;

class Movimiento extends BaseController
{

    public function index($operacion = 'compra', $modocompra = false)
    {
        $this->validaSesion();
        $aKeys = [
            'compra' => 'Cotizaciones de compra',
            'entrega' => 'Surtir productos',
            'entrada' => 'Entradas especiales',
            'traspaso' => 'Traspaso almacén',
            'salida' => 'Salidas especiales',
        ];

        $aWhere = [
            'Edo' => $this->request->getVar('cEstado') ?? '0',
            'dIni' => $this->request->getVar('dIni'),
            'dFin' => $this->request->getVar('dFin')
        ];
        $titulo = $aKeys[$operacion];

        $modelo = $this->getMovimientos($operacion, $this->nIdSucursal, 0, 8, $aWhere);

        $data = [
            'registros' =>  $modelo['regs'],
            'pager' => $modelo['pager'],
            'titulo' => $titulo,
            'operacion' => $operacion,
            'modocompra' => $modocompra,
            'aWhere' => $aWhere,
        ];
        if (isset($_SESSION['cMsjError'])) $data['cMsjError'] = $_SESSION['cMsjError'];
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
        $masterkey = $aKeys[$operacion][0];
        $titulo = $aKeys[$operacion][1];
        $label = $aKeys[$operacion][2];

        $data =
            [
                'operacion' => $operacion,
                'titulo' => $titulo,
                'label' => $label,
            ];
        unset($_SESSION[$operacion], $_SESSION['cMsjError']);
        if ($this->request->getMethod() === 'post') {
            $a = $this->validaBusqueda();
            if ($a === false) {
                $modelo = $this->getMovimientos($operacion, ($operacion == 'compra' ? false : $this->nIdSucursal), $this->request->getVar('nIdCompra'));
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
        $masterkey = $aKeys[$operacion][0];

        $urlPrevio = $_SESSION[$operacion]['hisBack'];
        unset($_SESSION[$operacion]);
        $this->initVarSesion($operacion, $masterkey);
        $_SESSION[$operacion]['hisBack'] = $urlPrevio;

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
            'nIdUsuario' => $this->nIdUsuario,
        ];

        $movto->save($r);
        $inserted = $movto->insertID();

        $nIdSucursal = $this->nIdSucursal;
        $nIdFuente = 0;

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
        }

        $master->updateFecha($this->nIdSucursal, $idOrigen, $dt);
        $mdlArticulo = new ArticuloMdl();

        $arrRecibido = $this->request->getVar($operacion);
        foreach ($arrRecibido  as $idArt => $fRecibido) {
            if ($fRecibido === '')
                $fRecibido = 0;
            $det = [];
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
            }

            $origen->updtRecibidos($idOrigen, $idArt, $fRecibido);
            $regArt = $mdlArticulo->select('cSinExistencia')->where('nIdArticulo', $idArt)->first();
            if ($regArt['cSinExistencia'] == '0') $inventario->updtArticuloSucursalInventario(
                $nIdSucursal,
                $idArt,
                $nSigno * $fRecibido,
                $operacion === 'entrega'
            );
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
            'nIdUsuario' => $this->nIdUsuario,
        ];


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

            $origen->updtPrecio($idOrigen, $idArt, $fRecibido);

            $articulo->updtPrecio(
                $idArt,
                $fRecibido
            );
        }
        $master->updateSaldo($this->nIdSucursal, $idOrigen, $this->request->getVar('fTotal'));
        $proveedor->updtSaldo($this->request->getVar('nIdProveedor'), $this->request->getVar('fTotal'));
        return json_encode(['ok' => '1']);
    }

    public function getMovimientos($operacion = 'entrada', $idsuc = false, $id = 0, $pages = 0, $aWhere = null)
    {
        $this->validaSesion();

        switch ($operacion) {
            case 'traspaso':
                $model = new TraspasoMdl();
                break;
            case 'entrega':
                $model = new EntregaMdl();
                break;
            case 'entrada':
                $model = new EntradaMdl();
                break;
            case 'compra':
                $model = new ComprasMdl();
                break;
            case 'salida':
                $model = new SalidaMdl();
                break;
        }

        $resp = [
            'regs' => $model->getRegistros($idsuc, $id, $pages, $aWhere),
            'pager' => $model->pager,
        ];

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
                $this->updEnvioTraspaso($id);
                break;
        }
        $master->delete($id);
        $detalle->where($masterkey, $id)->delete();

        return;
    }

    private function updEnvioTraspaso($id)
    {
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
        $masterkey = $aKeys[$operacion][0];

        $r = [
            'dSolicitud' => $_SESSION[$operacion]['registro']['dSolicitud'],
            'nIdSucursal' => $_SESSION[$operacion]['registro']['nIdSucursal'],
            'nIdProveedor' => $_SESSION[$operacion]['registro']['nIdProveedor'],
            'fTotal' => 0, //$this->request->getVar('fTotal'),
            'nProductos' => count($_SESSION[$operacion]['lstArt']), //$this->request->getVar('nProductos'),
            'sObservacion' => $this->request->getVar('sObservacion') ?? '',
            'cTipoSalida' => $this->request->getVar('cTipoSalida') ?? '0',
        ];

        $fSaldo = 0;
        switch ($operacion) {
            case 'salida':
                $master = new SalidaMdl();
                $detalle = new SalidaDetalleMdl();
                break;
            case 'compra':
                $master = new ComprasMdl();
                $detalle = new CompraDetalleMdl();
                $detallesaldo = new CompraDetalleSaldoMdl();

                $fSaldo = $this->request->getVar('fTotal2');
                $r['fTotal'] = $fSaldo;
                $r['fSaldo'] = $fSaldo;
                $r['dcompra'] = $r['dSolicitud'];
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
        }

        if ($id > 0) {
            $r[$masterkey] = $_SESSION[$operacion]['registro']["$masterkey"];
        }
        $master->save($r);
        if ($id > 0) {
            $inserted = $id;
            $detalle->where($masterkey, $inserted)->delete(null, true);
            if ($operacion == 'compra') {
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
            if ($operacion == 'compra') {
                $det['nIdViajeEnvio'] = $rd['nIdViajeEnvio'];
                $det['fIVA'] = $rd['fIVA'];
                if ($rd['nIdViajeEnvio'] > 0) {
                    $det['fRecibido'] = $rd['fCantidad'];
                    $det['fPorRecibir'] = 0;
                }
            }
            $detalle->save($det);
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
        if ($operacion === 'traspaso') {
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
        $this->validaSesion();

        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];
        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];
        $aRegis = [];
        $master = $this->getMovimientos($operacion, $this->nIdSucursal, intval($id));
        $registro =  $master['regs'];
        $timestamp = strtotime($registro[$datefld] ?? date("d-m-Y"));
        $registro[$datefld] = date("Y-m-d", $timestamp);

        $modetalle = $this->getMovimientosDetalle($operacion, intval($id));
        $arts = $modetalle['regs'];

        $aRegis[0] = ['Captura', $master['regs'],  $modetalle['regs']];

        $mdlMovto = new MovimientoMdl();
        $mdlMovtoDet = new  MovimientoDetalleMdl();

        $aJoins = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];

        $regs = $mdlMovto
            ->select('almovimiento.*, IFNULL(u.sNombre, \'\') AS nomUsu')
            ->join('sgusuario u', 'almovimiento.nIdUsuario = u.nIdUsuario', 'left')
            ->where('cOrigen', $operacion)->where('nIdOrigen', $id)
            ->findAll();
        foreach ($regs as $k => $row) {
            $regsD = $mdlMovtoDet
                ->select('almovimientodetalle.nIdArticulo, almovimientodetalle.fCantidad, a.sDescripcion')
                // ->join('almovimiento m', 'almovimientodetalle.nIdMovimiento = m.nIdMovimiento', 'inner')
                ->join('alarticulo a', 'almovimientodetalle.nIdArticulo = a.nIdArticulo', 'inner')
                ->where('nIdMovimiento', $row['nIdMovimiento'])
                ->where('fCantidad <>', 0)
                // ->builder()->getCompiledSelect(false);
                ->findAll();
            $aRegis[] = ['Recepcion', $row, $regsD];
        }


        $prov =
            [
                'nIdProveedor' => $registro['nIdProveedor'],
                'sNombre' => $registro['sNombre'],
                'email' => '',
                'sDireccion' => ''
            ];
        $registro['fTotal'] = number_format(floatval($registro['fTotal']), 2);
        $data = [
            'titulo' => $stitulooperacion,
            'operacion' => $operacion,
            // 'tipoaccion' => 'p',
            // 'frmURL' => 'movimiento/' . $operacion . '/p' . ($id > 0 ? '/' . $id : ''),
            'modo' => strtoupper('p'),
            'id' => $id,
            'registros' => $arts,
            'aRegLis' =>  $aRegis,
            // 'enfoque' => 'art',
            'registro' => $registro,
            'proveedor' => $prov,
            // 'sSucursal' => $this->sSucursal,
            // 'printerMode' => true,

        ];
        $data['frmURListado'] = base_url('movimiento/' . $operacion);

        // se muestra los formatos a imprimir.
        // esto es, en la posicion 0 se muestra la solicitud y los siguientes son las recepciones.
        $data['aInfoSis'] = $this->dataMenu['aInfoSis'];
        echo view('almacen/movimientoimpresion', $data);
        unset($_SESSION[$operacion]);
        return;
    }

    public function accion($operacion, $tipoaccion, $id = 0)
    {
        $this->validaSesion();

        $aTitulo = ['a' => 'Agregar', 'b' => 'Cancelar', 'e' => 'Editar', 'r' => 'Recepción', 'p' => 'Cotización', 'c' => 'Consultar'];
        $stituloevento = $aTitulo[$tipoaccion] ?? 'Ver';
        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];
        $masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];
        $sufijoURL = ($operacion . '/' . $tipoaccion . ($id > 0 ? '/' . $id : ''));

        if (!isset($_SESSION[$operacion])) {
            $this->initVarSesion($operacion, $masterkey);
        }
        if (!isset($_SESSION[$operacion]['hisBack'])) {
            $_SESSION[$operacion]['hisBack'] = previous_url();
            if (strpos($_SESSION[$operacion]['hisBack'], '/imprime/') !== false) $_SESSION[$operacion]['hisBack'] = '0';
        }

        $titulo = $stituloevento . ' ' . $stitulooperacion . (in_array($tipoaccion, ['a', 'p']) ? '' : ' con folio ' . ($_SESSION[$operacion]['editandoregistro'] ?? $id));
        $data = [
            'titulo' => $titulo,
            'operacion' => $operacion,
            'tipoaccion' => $tipoaccion,
            'sufijoURL' => $sufijoURL,
            'frmURL' => ('movimiento/' . $sufijoURL),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];

        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'r')
                $id = $this->request->getVar($masterkey);
            if ($tipoaccion === 'b') {
                // el tipoaccion 'b' solo es usado en traspasos donde se pueden cancelar los movtos.
                // se valida si el envio del traspaso a cancelar, no esta asignado a un viaje.
                if ($operacion == 'traspaso') {
                    $mdlTraspaso = new TraspasoMdl();
                    $reVal = $mdlTraspaso->getRegistros(false, $id, 0);
                    if ($reVal['contEnviosViajes'] > 0) {
                        $_SESSION['cMsjError'] = 'El envio ya se asigno a un viaje.';
                        return redirect()->to(base_url('movimiento/' . $operacion));
                    }
                }
                if ($operacion == 'compra') {
                    $mdlCompraDetSal = new CompraDetalleSaldoMdl();
                    $mdlCompraDetSal->where("nIdCompra", $id)->delete(true);
                }
                $this->delMovimiento($operacion, false, $id);
                $this->initVarSesion($operacion, $masterkey);
                return redirect()->to(base_url('movimiento/' . $operacion));
            }
            $a = $this->validaCampos($operacion);
            if (!$a) {
                // guardar datos
                $this->saveMovimiento($operacion,  $id);
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
                    'id' => $id,
                    'enfoque' => $_SESSION[$operacion]['foc'] ?? 'art'
                ];
            }
        } else {
            if (!in_array($tipoaccion, ['a', 'e'])) {
                $this->initVarSesion($operacion, $masterkey);
                $_SESSION[$operacion]['hisBack'] = previous_url();
                if (strpos($_SESSION[$operacion]['hisBack'], '/imprime/') !== false) $_SESSION[$operacion]['hisBack'] = '0';
                // si es compra, no tomo en cuenta la sucursal.
                $master = $this->getMovimientos($operacion, ($operacion == 'compra' ? false : $this->nIdSucursal), intval($id));
                $registro =  $master['regs'];

                if (empty($registro))
                    return redirect()->to(base_url('movimiento/' . $operacion));

                $timestamp = strtotime($registro['dSolicitud'] ?? date("d-m-Y"));
                $registro['dSolicitud'] = date("Y-m-d", $timestamp);

                $modetalle = $this->getMovimientosDetalle($operacion, intval($id));
                $arts = $modetalle['regs'];

                $prov =
                    [
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
                    if ($operacion === 'compra') {
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
                    //'sql' => $sql,
                    'registros' => $arts, //
                    'enfoque' => 'art',
                    'registro' => $registro,
                    'proveedor' => $prov,
                ];
            } else {
                if ($operacion == 'compra' && !isset($_SESSION[$operacion]['editandoregistro']) && $tipoaccion != 'a') {
                    $master = $this->getMovimientos($operacion, $this->nIdSucursal, intval($id));
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
                            $_SESSION[$operacion]['lstArt'][] = [
                                'nIdArticulo' => $r['nIdArticulo'],
                                'sDescripcion' => $r['sDescripcion'],
                                'nCosto' => $r['nCosto'],
                                'fImporte' => $r['fImporte'],
                                'fCantidad' => $r['fCantidad'],
                                'fPorRecibir' => $r['fPorRecibir'],
                                'nIdEnvio' => $r['nIdEnvio'],
                                'nIdViaje' => $r['nIdViaje'],
                                'nIdViajeEnvio' => $r['nIdViajeEnvio'],
                                'fIVA' => $r['fIVA'],
                            ];
                        }
                    }
                    $_SESSION[$operacion]['editandoregistro'] = intval($id);
                }
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
                    'registros' => $_SESSION[$operacion]['lstArt'] ?? [],
                    'registro' => $_SESSION[$operacion]['registro'],
                    'enfoque' => $_SESSION[$operacion]['foc'] ?? 'art',
                    'proveedor' => $_SESSION[$operacion]['proveedor'],
                ];
            }
            $data['frmURListado'] = base_url('movimiento/' . $operacion);
        }
        $data['titulo'] = $titulo;
        $data['operacion'] = $operacion;
        $data['sSucursal'] = $this->sSucursal;
        $data['hisBack'] = $_SESSION[$operacion]['hisBack'];

        $pantalla = ($tipoaccion === 'r' || $tipoaccion === 'n' ? 'recepcion' : 'movimiento') . 'mtto';
        $pantalla = ($tipoaccion === 'r' || $tipoaccion === 'n' ? 'recepcion' : 'movimiento') . ($operacion === 'compra' ? $operacion : '') . 'mtto';

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
        if ($nPos == -1)
            $_SESSION[$operacion]['lstArt'][] = array(
                $nIdKey => $this->request->getVar("$nIdKey"),
                'sDescripcion' => $this->request->getVar('dlArticulos0'),
                'nCosto' => $this->request->getVar('nCosto'),
                'fImporte' => $this->request->getVar('fImporte'),
                'fCantidad' => $this->request->getVar('nCant'),
                'fPorRecibir' => $this->request->getVar('nCant'),
            );
        else
            $_SESSION[$operacion]['lstArt'][$nPos] = array(
                $nIdKey => $this->request->getVar("$nIdKey"),
                'sDescripcion' => $this->request->getVar('dlArticulos0'),
                'nCosto' => $this->request->getVar('nCosto'),
                'fImporte' => $this->request->getVar('fImporte'),
                'fCantidad' => $this->request->getVar('nCant'),
                'fPorRecibir' => $this->request->getVar('nCant'),
            );

        $_SESSION[$operacion]['foc'] = 'art';
        $datefld = 'dSolicitud'; // $aKeys[$operacion][1];
        $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
        $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");
        return redirect()->to(base_url('movimiento/' . $operacion . '/' . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function agregaArticuloCompra($operacion, $tipoaccion, $idoperacion = 0)
    {
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
        if ($nPos == -1)
            $_SESSION[$operacion]['lstArt'][] = array(
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
            );
        else
            $_SESSION[$operacion]['lstArt'][$nPos] = array(
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
            );

        $_SESSION[$operacion]['foc'] = 'art';
        $datefld = 'dSolicitud'; // $aKeys[$operacion][1];
        $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
        $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");
        return redirect()->to(base_url('movimiento/' . $operacion . '/' . $tipoaccion . ($idoperacion > 0 ? '/' . $idoperacion : '')));
    }

    public function agregaProveedor($operacion, $tipoaccion, $idoperacion = 0)
    {
        session();

        $datefld = 'dSolicitud'; // $aKeys[$operacion][1];

        $_SESSION[$operacion]['foc'] = 'art';
        if ($tipoaccion == 'a' || $tipoaccion == 'e') {
            $_SESSION[$operacion]['proveedor'] =
                [
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
