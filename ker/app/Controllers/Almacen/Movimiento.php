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

class Movimiento extends BaseController
{

    public function index($operacion = 'compra', $modocompra = false)
    {
        $this->validaSesion(false, "movimiento/$operacion");
        $aKeys = [
            'compra' => 'Cotizaciones de compra',
            'entrega' => 'Surtir productos',
            'entrada' => 'Entradas especiales',
            'traspaso' => 'Traspaso almacén',
            'salida' => 'Salidas especiales',
            'capturainv' => 'Captura modo inventario',
        ];

        $aWhere = [
            'Edo' => $this->request->getVar('cEstado') ?? '0',
            'dIni' => $this->request->getVar('dIni'),
            'dFin' => $this->request->getVar('dFin'),
            'nIdSucursal' => $this->request->getVar('nIdSucursal') ?? '0',
            'nIdArticulo' => $this->request->getVar('nIdArticulo') ?? '0',
        ];
        if(($this->request->getVar('page') ?? false) === false) {
            $aWhere['pagina'] = $this->request->getVar('pagina') ?? '1';
        } else {
            $aWhere['pagina'] = $this->request->getVar('page');
        }
        $titulo = $aKeys[$operacion];

        $id_Suc = ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal;

        $id_Suc = false;
        if ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) {
            if ($aWhere['nIdSucursal'] != '0') $id_Suc = $aWhere['nIdSucursal'];
        } else {
            if ($aWhere['nIdSucursal'] != '0') {
                $id_Suc = $aWhere['nIdSucursal'];
            } else {
                $id_Suc = $this->nIdSucursal;
            }
        }

        if (in_array($operacion, array('entrada', 'salida', 'traspaso'))) {
            $mdlSucursal = new SucursalMdl();
            $aSucursales = $mdlSucursal->getRegistros();
            $regSucursal = $aSucursales[0];
            $regSucursal['nIdSucursal'] = '0';
            $regSucursal['sDescripcion'] = 'Todas las sucursales';
            array_unshift($aSucursales, $regSucursal);
        } else {
            $aSucursales = [];
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
            'capturainv' => ['idInventarioCaptura', 'Aplicar captura modo inventario', 'Captura modo inventario'],
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
        if ($this->request->getMethod() === 'post') {
            $a = $this->validaBusqueda();
            if ($a === false) {
                $id_Suc = ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal;
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
            ],
            /*
            'pago' => [
                'lst' => [],
                'sub' => 0.0,
                'iva' => 0.0,
                'des' => 0.0,
                'tot' => 0.0,
                'acum' => 0.0
            ]
            */
        ];
        //var_dump($_SESSION[$operacion]['proveedor']);
    }

    public function limpiaOperacion($operacion, $masterkey)
    {
        session();
        unset($_SESSION[$operacion]);
        $this->initVarSesion($operacion, $masterkey);
        return redirect()->to(base_url("/movimiento/$operacion/a"));
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
            'capturainv' => ['idInventarioCaptura', 'dAplicacion',],
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
        ];

        $movto->save($r);
        $inserted = $movto->insertID();

        $nIdSucursal = $this->request->getVar('nIdSucursal');
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
            case 'capturainv':
                $master = new InventarioCapturaMdl();
                break;
            case 'traspaso':
                $nIdFuente = intval($this->request->getVar('nIdProveedor'));
                $master = new TraspasoMdl();
                break;
        }

        $master->updateFecha($nIdSucursal, $idOrigen, $dt);
        $mdlArticulo = new ArticuloMdl();

        $arrRecibido = $this->request->getVar($operacion);
        foreach ($arrRecibido  as $idArt => $fRecibido) {
            if ($fRecibido === '')
                $fRecibido = 0;
            $det = [];
            $nSigno = in_array($operacion, array('entrega', 'salida', 'traspaso')) ? -1 : 1;

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
                case 'capturainv':
                    $origen = new InventarioCapturaDetalleMdl();
                    break;
            }

            $origen->updtRecibidos($idOrigen, $idArt, $fRecibido);
            $inventario->updtArticuloSucursalInventario(
                $nIdSucursal,
                $idArt,
                $nSigno * $fRecibido,
                $operacion === 'entrega' ? $nSigno * $fRecibido : 0
            );


            if ($operacion === 'traspaso') {
                $inventario->updtArticuloSucursalInventario($nIdFuente, $idArt, $fRecibido);
            }
            if ($operacion === 'entrega') {
                //$venta->builder()->update('cEstado'  );
            }
        }

        $master->updateEdo($nIdSucursal, $idOrigen);
        if ($operacion === 'entrega')
            $venta->updateEdoSurtido($nIdSucursal, $nIdFuente, 'Entrega');
        //return redirect()->to( base_url('movimiento/buscar/' . $operacion) );
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

            $origen->updtCosto($idOrigen, $idArt, $fRecibido);

            $articulo->updtCosto(
                $idArt,
                $fRecibido
            );
        }
        $master->updateSaldo($this->nIdSucursal, $idOrigen, $this->request->getVar('fTotal'));
        $proveedor->updtSaldo($this->request->getVar('nIdProveedor'), $this->request->getVar('fTotal'));
        //return redirect()->to( base_url('movimiento/buscar/' . $operacion) );
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
                $cancel = new EntradaCancelMdl();
                $rcancel = $cancel->getRegistrosByField('nIdEntrada', $id);
                break;
            case 'compra':
                $model = new ComprasMdl();
                break;
            case 'salida':
                $model = new SalidaMdl();
                break;
            case 'capturainv':
                $model = new InventarioCapturaMdl();
                break;
        }

        $resp = [
            'regs' => $model->getRegistros($idsuc, $id, $pages, $aWhere),
            'pager' => $model->pager,
            'cancel' => $rcancel,
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
            'capturainv' => ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'],
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
                break;
            case 'capturainv':
                $master = new InventarioCapturaMdl();
                $detalle = new InventarioCapturaDetalleMdl();
                break;
        }
        $master->delete($id);
        $detalle->where($masterkey, $id)->delete();

        return;
    }

    public function cancelaMovimiento($operacion = 'entrada', $id = 0, $idsuc = false, $pages = 0)
    {
        $this->validaSesion();
        $aKeys = [
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            /*
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
            'capturainv' => ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'],
            */
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
                $cancel = new EntradaCancelMdl();
                break;
            case 'traspaso':
                $master = new TraspasoMdl();
                $detalle = new TraspasoDetalleMdl();
                break;
            case 'capturainv':
                $master = new InventarioCapturaMdl();
                $detalle = new InventarioCapturaDetalleMdl();
                break;
        }
        $master->set('cEdoEntrega', '9')->where('nIdEntrada', $id)->update();
        $detalle->set('fPorRecibir', 0)->where('nIdEntrada', $id)->update();
        $rcancel =
            [
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
                $model = new InventarioCapturaDetalleMdl();
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

    public function saveMovimiento($operacion = 'entrada', $idsuc = false, $id = 0, $pages = 0)
    {
        $this->validaSesion();
        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
            'capturainv' => ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'],
        ];
        $masterkey = $aKeys[$operacion][0];
        $datefld = 'dSolicitud'; // $aKeys[$operacion][1];

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
                break;
            case 'capturainv':
                $master = new InventarioCapturaMdl();
                $detalle = new InventarioCapturaDetalleMdl();
                break;
        }

        $r = [
            "$datefld" => $_SESSION[$operacion]['registro']["$datefld"],
            'nIdSucursal' => $_SESSION[$operacion]['registro']['nIdSucursal'], //$this->request->getVar('fTotal'), //
            'nIdProveedor' => $_SESSION[$operacion]['registro']['nIdProveedor'],
            'fTotal' => 0, //$this->request->getVar('fTotal'),
            'nProductos' => count($_SESSION[$operacion]['lstArt']), //$this->request->getVar('nProductos'),
            'sObservacion' => $this->request->getVar('sObservacion') ?? ''
        ];
        if ($id > 0) {
            $r[$masterkey] = $_SESSION[$operacion]['registro']["$masterkey"];
        }
        $master->save($r);
        $inserted = $master->insertID();

        foreach ($_SESSION[$operacion]['lstArt'] as $rd) {
            $det = [
                $masterkey => $inserted,
                'nIdArticulo' => $rd['nIdArticulo'],
                'fCantidad' => $rd['fCantidad'],
                'fRecibido' => 0,
                'fPorRecibir' => $rd['fCantidad'],
                'fImporte' => $rd['fImporte'],
            ];
            if ($operacion === 'capturainv')
                $det['fExistencia'] = $rd['fExistencia'];
            $detalle->save($det);
            if ($operacion == 'compra' || $operacion == 'entrada')
                if ($rd['nCosto'] <> $rd['fImporte']) {
                    $articulo = new ArticuloMdl();
                    $articulo->updtPrecio(
                        $rd['nIdArticulo'],
                        $rd['fImporte']
                    );
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
        $this->validaSesion(false, "imprime/$operacion");

        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
        ];
        //$masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];

        $master = $this->getMovimientos($operacion, false, intval($id));
        $registro =  $master['regs'];
        $timestamp = strtotime($registro[$datefld] ?? date("d-m-Y"));
        $registro[$datefld] = date("Y-m-d", $timestamp);

        $modetalle = $this->getMovimientosDetalle($operacion, intval($id));
        $arts = $modetalle['regs'];
        $prov =
            [
                'nIdProveedor' => $registro['nIdProveedor'],
                'sNombre' => $registro['sNombre'],
                'email' => '',
                'sDireccion' => ''
            ];
        $data = [
            'titulo' => $stitulooperacion,
            'operacion' => $operacion,
            'tipoaccion' => 'p',
            'frmURL' => 'movimiento/' . $operacion . '/p' . ($id > 0 ? '/' . $id : ''),
            'modo' => strtoupper('p'),
            'id' => $id,
            'registros' => $arts,
            'enfoque' => 'art',
            'registro' => $registro,
            'proveedor' => $prov,
            'sSucursal' => $this->sSucursal,
            'printerMode' => true,

        ];

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/movimientomtto', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($operacion, $tipoaccion, $id = 0)
    {
        $this->validaSesion(false, "movimiento/$operacion/$tipoaccion");

        $aTitulo = ['a' => 'Agregar', 'b' => 'Borrar', 'c' => 'Cancelar', 'e' => 'Editar', 'r' => 'Recepción', 'p' => 'Cotización'];
        $stituloevento = $aTitulo[$tipoaccion] ?? 'Ver';
        $aKeys = [
            'salida'  => ['nIdSalida', 'dSalida', 'Salidas especiales'],
            'entrega'  => ['nIdEntrega', 'dEntrega', 'Entrega de mercancías'],
            'compra'  => ['nIdCompra', 'dcompra', 'Cotizaciones de compra'],
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'],
            'traspaso' => ['nIdTraspaso', 'dTraspaso', 'Traspaso almacén'],
            'capturainv' => ['idInventarioCaptura', 'dAplicacion', 'Captura modo inventario'],
        ];
        $masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];

        $mdlSucursal = new SucursalMdl();
        $rgSucursales = $mdlSucursal->getRegistros();

        if (!isset($_SESSION[$operacion]) && $tipoaccion === 'a') {
            $this->initVarSesion($operacion, $masterkey);
        }

        $titulo = $stituloevento . ' ' . $stitulooperacion;
        $data = [
            'titulo' => $titulo,
            'operacion' => $operacion,
            'frmURL' => ('movimiento/' . $operacion . '/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
            'lstSucursales' => $rgSucursales,
        ];
        //unset($_SESSION['lstArt']);

        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'r')
                $id = $this->request->getVar($masterkey);
            if ($tipoaccion === 'b') {
                $this->delMovimiento($operacion, $id);
                echo 'ok';
                return;
            }
            if ($tipoaccion === 'c') {
                $this->cancelaMovimiento($operacion, $id);
                $this->initVarSesion($operacion, $masterkey);
                return redirect()->to(base_url('movimiento/' . $operacion));
                // echo 'ok';
                // return;
            }
            $a = $this->validaCampos($operacion);
            if (!$a) {
                // guardar datos
                $this->saveMovimiento($operacion);
                $this->initVarSesion($operacion, $masterkey);
                return redirect()->to(base_url('movimiento/' . $operacion));
            } else {
                $data = [
                    'error' => $a['amsj'],
                    'registros' => $_SESSION[$operacion]['lstArt'] ?? [],
                    'registro' => $_SESSION[$operacion]['registro'],
                    'proveedor' => $_SESSION[$operacion]['proveedor'],
                    'tipoaccion' => $tipoaccion,
                    'frmURL' => ('movimiento/' . $operacion . '/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
                    'modo' => strtoupper($tipoaccion),
                    'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
                    'lstSucursales' => $rgSucursales,
                    'id' => $id,
                    'enfoque' => $_SESSION[$operacion]['foc'] ?? 'art'
                ];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $this->initVarSesion($operacion, $masterkey);
                //$id_Suc = ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal;
                $master = $this->getMovimientos($operacion, false, intval($id));
                $registro =  $master['regs'];
                $regcancel = $master['cancel'];

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

                $frmURL = 'movimiento/' .  $operacion . '/' . $tipoaccion . ($id > 0 ? '/' . $id : '');
                $recepcioncompra = '';
                if ($operacion === 'compra' && $tipoaccion === 'n')
                    $recepcioncompra = 'compra';

                if ($tipoaccion === 'r' || $tipoaccion === 'n') {
                    $timestamp = strtotime($registro[$datefld] ?? date("d-m-Y"));
                    $registro[$datefld] = date("Y-m-d", $timestamp);
                    $frmURL = ('movimiento/recepcion' . $recepcioncompra . '/' . $operacion . '/' .  $id);
                }

                $data = [
                    'titulo' => $titulo,
                    'operacion' => $operacion,
                    'tipoaccion' => $tipoaccion,
                    'frmURL' => $frmURL,
                    'modo' => strtoupper($tipoaccion),
                    'id' => $id,
                    'bVerTodasSuc' => $this->aPermiso['oVerTodasSuc'] ?? false,
                    'lstSucursales' => $rgSucursales,
                    //'sql' => $sql,
                    'registros' => $arts, //
                    'enfoque' => 'art',
                    'registro' => $registro,
                    'proveedor' => $prov,
                    'regcancel' => $regcancel,
                ];
            } else {
                if ($_SESSION[$operacion]['proveedor']['nIdProveedor'] == 0) {
                    $timestamp = strtotime($_SESSION[$operacion]['registro']['dSolicitud'] ?? date("d-m-Y"));
                    $_SESSION[$operacion]['registro']['dSolicitud'] = date("Y-m-d", $timestamp);
                }
                $data = [
                    'titulo' => $titulo,
                    'operacion' => $operacion,
                    'tipoaccion' => $tipoaccion,
                    'frmURL' => ('movimiento/' . $operacion . '/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
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
        }
        $data['titulo'] = $titulo;
        $data['operacion'] = $operacion;
        $data['sSucursal'] = $this->sSucursal;

        $pantalla = ($tipoaccion === 'r' || $tipoaccion === 'n' ? 'recepcion' : 'movimiento') . 'mtto';
        // $pantalla = ($tipoaccion === 'r' || $tipoaccion === 'n' ? 'recepcion' : 'movimiento') . ($operacion === 'capturainv' ? 'modoinv' : '') . 'mtto';

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/' . $pantalla, $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function agregaArticulo($operacion, $tipoaccion)
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
                'fExistencia' => $this->request->getVar('fExistencia'),
                'fCantidad' => $this->request->getVar('nCant'),
                'fPorRecibir' => $this->request->getVar('nCant'),
            );
        else
            $_SESSION[$operacion]['lstArt'][$nPos] = array(
                $nIdKey => $this->request->getVar("$nIdKey"),
                'sDescripcion' => $this->request->getVar('dlArticulos0'),
                'nCosto' => $this->request->getVar('nCosto'),
                'fImporte' => $this->request->getVar('fImporte'),
                'fExistencia' => $this->request->getVar('fExistencia'),
                'fCantidad' => $this->request->getVar('nCant'),
                'fPorRecibir' => $this->request->getVar('nCant'),
            );

        $_SESSION[$operacion]['foc'] = 'art';
        $datefld = 'dSolicitud'; // $aKeys[$operacion][1];
        $_SESSION[$operacion]['registro']['nIdSucursal'] = $this->request->getVar('nIdSucursal');
        $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
        $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");
        $this->accion($operacion, $tipoaccion);
        return;
        //return redirect()->to(base_url('movimiento/' . $operacion . '/a'));
    }

    public function agregaProveedor($operacion, $tipoaccion)
    {
        session();

        /*
        $aKeys = [
            'salida'  => ['nIdSalida','dSalida','Salidas especiales'], 
            'compra'  => ['nIdCompra','dcompra','Cotizaciones de compra'], 
            'entrada' => ['nIdEntrada', 'dEntrada', 'Entradas especiales'], 
            'traspaso' => ['nIdTraspaso','dTraspaso', 'Traspaso almacén'],
        ];
        */
        $datefld = 'dSolicitud'; // $aKeys[$operacion][1];

        $reglas = [
            'nIdProveedor' => 'required|decimal|greater_than[0]',
        ];
        $reglasMsj = [
            'nIdProveedor' => [
                'required'   => 'Se requiere proveedor',
                'greater_than' => 'Clave proveedor no válida'
            ],
        ];
        $this->request->getMethod();
        $this->validate($reglas, $reglasMsj);

        $_SESSION[$operacion]['foc'] = 'art';
        if ($tipoaccion == 'a') {
            $_SESSION[$operacion]['proveedor'] =
                [
                    'nIdProveedor' => $this->request->getVar('nIdProveedor'),
                    'nIdCliente' => $this->request->getVar('nIdCliente'),
                    'sNombre' => $this->request->getVar('dlProveedores0'),
                    'email' => '',
                    'sDireccion' => ''
                ];
            //echo 'UNO ';
            //var_dump($_SESSION[$operacion]['registro']);

            $_SESSION[$operacion]['nIdProveedor'] = $this->request->getVar('nIdProveedor');
            $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
            $_SESSION[$operacion]['registro']["nIdSucursal"] = $this->request->getVar("nIdSucursal");
            $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");
            $_SESSION[$operacion]['registro']['nIdProveedor'] = $this->request->getVar('nIdProveedor');
            $_SESSION[$operacion]['registro']['sNombre'] = $this->request->getVar('dlProveedores0');

            //echo '<BR> DOS';
            //var_dump($_SESSION[$operacion]['registro']);

        }
        return redirect()->to(base_url('movimiento/' . $operacion . '/a'));
    }

    public function borraArticulo($operacion, $id)
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
        return redirect()->to(base_url('movimiento/' . $operacion . '/a'));
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
