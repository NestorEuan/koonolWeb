<?php

namespace App\Controllers\Envio;

use App\Controllers\BaseController;
use App\Models\Envios\ViajeMdl;
use App\Models\Envios\ViajeEnvioMdl;
use App\Models\Envios\ViajeEnvioDetalleMdl;
use App\Models\Envios\ViajeEnvioDevolucionMdl;
use App\Models\Envios\EnvioMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Catalogos\ChoferMdl;

use App\Models\Almacen\InventarioMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Almacen\MovimientoDetalleMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Almacen\TraspasoMdl;
use App\Models\Almacen\TraspasoDetalleMdl;

class ViajeCtrl extends BaseController
{

    public function index()
    {
        $this->validaSesion();
        $operacion = 'viajectrl';
        $aKeys = [
            'viajectrl' => 'Viajes',
        ];
        $titulo = $aKeys[$operacion];

        switch ($operacion) {
            case 'viajectrl':
                $model = new ViajeMdl();
                break;
        }

        $aWhere = [
            'Edo' => $this->request->getVar('cEstado') ?? '1',
            'dIni' => $this->request->getVar('dIni'),
            'dFin' => $this->request->getVar('dFin'),
        ];

        $regs = $model->getRegistros($this->nIdSucursal, 0, 8, $aWhere);

        $data = [
            'registros' =>  $regs,
            'pager' => $model->pager,
            'titulo' => $titulo,
            'operacion' => $operacion,
            'aWhere' => $aWhere,
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('envios/viajesctrl', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    /**** 
     * 
     * REVISAR QUE ACCIONES SE PUEDEN HACER CON EL VIAJE
     * EN EL MODULO DE CONTROL DE VIAJE
     * 1.- VISUALIZAR
     * 2.- INICIAR CARGA
     * 3.- CERRAR VIAJE
     * 4.- RECHAZAR VIAJE
     * 5.- CAMBIAR DE CHOFER
     * 6.- DEVOLUCION DE VIAJE
     * *****/
    public function accion($tipoaccion, $id = 0)
    {
        session();
        $this->validaSesion();
        $operacion = 'viajectrl';
        $aTitulo = ['a' => 'Agregar', 'b' => 'Borrar', 'e' => 'Editar', 'c' => 'Carga'];
        $stituloevento = $aTitulo[$tipoaccion] ?? 'Ver';
        $aKeys = [
            'viajectrl'  => ['nIdViaje', 'dViaje', 'Asignar viajes'],
        ];
        $masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];
        $titulo = $stituloevento . ' ' . $stitulooperacion;

        $master = new ViajeMdl();
        $chofer = new ChoferMdl();

        $mdlViajeEnvio = new ViajeEnvioMdl();

        if ($tipoaccion === 'a') {
            if (!isset($_SESSION[$operacion]) || $_SESSION[$operacion]['registro'][$masterkey] != 0) {
                $this->initVarSesion($operacion, $masterkey);
            }
            $registro = $_SESSION[$operacion]['registro'];
            $registros  = $_SESSION[$operacion]['envios'];
        } else {
            //$this->initVarSesion($operacion, $masterkey);
            $registro = $master->getRegistros(false, intval($id));
            $timestamp = strtotime($registro[$datefld] ?? date("d-m-Y"));
            $registro[$datefld] = date("Y-m-d", $timestamp);
            $_SESSION[$operacion]['registro'] = $registro;
        }
        if (!isset($_SESSION[$operacion]['regChofer'])) {
            $regChofer = $chofer->getRegistros();
            $_SESSION[$operacion]['regChofer'] = $regChofer;
        }
        $registro = $_SESSION[$operacion]['registro'];
        $regChofer = $_SESSION[$operacion]['regChofer'];

        $frmURL = "viajectrl/$tipoaccion" . ($id > 0 ? '/' . $id : '');

        switch ($tipoaccion) {
            case 'i': //Editar, Ver (Consultar)
                if (strtoupper($this->request->getMethod()) === 'POST') {
                    //Aqui es cuando se va a cerrar la asignación de envios
                    $inventario = new InventarioMdl(); //Modelo para actualizar inventarios
                    $movto = new MovimientoMdl();
                    $movtodetail = new MovimientoDetalleMdl();
                    //$enviodetalle = new EnvioDetalleMdl();

                    $mdlViajeEnvioDet = new ViajeEnvioDetalleMdl();
                    $artenviados = $mdlViajeEnvioDet->getArtsEnViaje(0, $id);

                    //$artenviados = $enviodetalle->getRegsExistenciaYEnviados($id, $this->nIdSucursal);
                    $id_origen = -1;
                    $c_origen = 'no';
                    $fTotSolicitado = 0;
                    $fTotEnviado = 0;
                    $n_OldEnvio = $artenviados[0]['nIdEnvio'];
                    //Actualizar detalle de envio (Sumar los que se entregan, revisar cuantos quedan para actualizar estado de envio )
                    foreach ($artenviados as $art2Updt) {
                        //Actualizar estatus de envio
                        if ($n_OldEnvio !== $art2Updt['nIdEnvio']) {
                            $env = [
                                'nIdEnvio' => $n_OldEnvio,
                                'cEstatus' => $fTotEnviado <> $fTotSolicitado ? '4' : '5',
                            ];
                            $envMdl = new EnvioMdl();
                            $envMdl->set('cEstatus', $env['cEstatus'])
                                ->where('nIdEnvio', $env['nIdEnvio'])
                                ->update();
                            $n_OldEnvio = $art2Updt['nIdEnvio'];
                        }
                        if ($art2Updt['nIdArticulo'] === null) {
                            //Aquí se acumularán los totales del envio para ver si se actualizar el estado.
                            $fTotSolicitado += $art2Updt['fCantidad'];
                            $fTotEnviado += $art2Updt['fRecibido'];
                            //ver de que forma guardar el idOrigen en un arreglo para 
                            continue;
                        }
                        $fTotSolicitado += $art2Updt['fCantidad'];
                        $fTotEnviado += $art2Updt['fRecibido'];

                        if ($art2Updt['cModoEnv'] === '1')  continue; // Si es MODO ENV no se actualizan existencias

                        if ($id_origen !== $art2Updt['nIdOrigen'] || $c_origen !== $art2Updt['cOrigen']) {
                            $r = [
                                'nIdOrigen' => $art2Updt['nIdOrigen'],
                                'cOrigen' => $art2Updt['cOrigen'],
                                'dMovimiento' => date("Y-m-d", strtotime(date("d-m-Y"))),
                                //'sObservacion' => $obs,
                            ];

                            $movto->save($r);
                            $idmovto = $movto->insertID();
                            $id_origen = $art2Updt['nIdOrigen'];
                            $c_origen = $art2Updt['cOrigen'];
                            if ($c_origen === 'traspaso')
                                $origenMdl = new TraspasoDetalleMdl();
                            else
                                $origenMdl = new VentasDetMdl();
                        }
                        //Actualizar inventario de sucursal (descontar de inventario y comprometidos)
                        $ar2d2 = $inventario->getArticuloSucursal($art2Updt['nIdArticulo'], $art2Updt['nIdSucursal']);
                        $saldoIni = $ar2d2['fExistencia'] ?? 0;

                        $inventario->updtArticuloSucursalInventario($art2Updt['nIdSucursal'], $art2Updt['nIdArticulo'], -1 * $art2Updt['fXRecibir'], -1 * $art2Updt['fXRecibir']);
                        if ($c_origen === 'ventas')
                            $origenMdl->updtRecibidos($art2Updt['nIdOrigen'], $art2Updt['nIdArticulo'], $art2Updt['fXRecibir']);

                        $rd = [
                            'nIdMovimiento' => $idmovto,
                            'nIdArticulo' => $art2Updt['nIdArticulo'],
                            'nIdSucursal' => $art2Updt['nIdSucursal'],
                            'fCantidad' => -1 * $art2Updt['fXRecibir'],
                            'fSaldoInicial' => $saldoIni,
                        ];
                        //Agregar detalle de movimiento
                        $movtodetail->save($rd);
                        //Actualizar detalle de origen venta/traspaso (Por entregar)
                        //Actualizar estado de origen
                    }
                    //Actualizar viaje
                    $mstr = [
                        'nIdViaje' => $id,
                        'cEstatus' => '2',
                    ];
                    $master->save($mstr);
                    //Actualizar estatus de envio
                    if (true) //$art2Updt['nIdArticulo'] !== null)
                    {
                        $env = [
                            'nIdEnvio' => $n_OldEnvio,
                            'cEstatus' => $fTotEnviado <> $fTotSolicitado ? '4' : '5',
                        ];
                        $envMdl = new EnvioMdl();
                        //$envMdl->save($env);

                        $envMdl->set('cEstatus', $env['cEstatus'])
                            ->where('nIdEnvio', $env['nIdEnvio'])
                            ->update();

                        $n_OldEnvio = $art2Updt['nIdEnvio'];
                    }
                    return redirect()->to(base_url("../"));
                }
                $frmURL = "viajectrl/i" . ($id > 0 ? '/' . $id : '');
                $registros = $mdlViajeEnvio->getViajeEnvios($id);
                foreach ($registros as &$rg) {
                    if (!array_key_exists('selected', $rg))
                        $rg['selected'] = $rg['nIdViajeEnvio'] == null || $rg['nIdViaje'] != $id ? '-1' : '0';
                }
                $chofer = new ChoferMdl();
                //$regChofer = $chofer->getRegistros(false);
                //$registro = $_SESSION[$operacion]['registro'];
                break;
            case 'v':
            case 'b':
            case 'c':
                $frmURL = '';
                $frmURL = "viajectrl/s" . ($id > 0 ? '/' . $id : '');
                $chofer = new ChoferMdl();
                $regChofer = $chofer->getRegistros(false);
                $registros = $mdlViajeEnvio->getViajeEnvios($id);
                foreach ($registros as &$rg) {
                    if (!array_key_exists('selected', $rg))
                        $rg['selected'] = $rg['nIdViajeEnvio'] == null ? '-1' : '0';
                }
                break;
            case 's':
                $mstr = [
                    'nIdViaje' => $id,
                    'cEstatus' => '3',
                ];
                $master->save($mstr);
                return redirect()->to(base_url("viajectrl"));
                break;
            case 'c':
                break;
        }
        //$registros = $_SESSION[$operacion]['envios'];
        $_SESSION[$operacion]['envios'] = $registros;
        $data =
            [
                'id' => $id,
                'titulo' => $titulo,
                'operacion' => $operacion,
                'tipoaccion' => $tipoaccion,
                'modo' => strtoupper($tipoaccion),
                'sSucursal' => $this->sSucursal,
                'frmURL' => $frmURL, //('viaje/' . $operacion . '/'. $tipoaccion . ($id > 0 ? '/'. $id : '') ),
                'registro' => $registro,
                'registros' => $registros,
                'regChofer' => $regChofer,
            ];

        echo view('templates/header', $this->dataMenu);
        echo view('envios/viajectrlmtto', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function envioctrl($tipoaccion, $idenvio = 0, $idviaje = 0, $idviajeenvio = 0)
    {
        session();
        $operacion = 'envioctrl';
        $aTitulo = ['a' => 'Cargar', 'b' => 'Borrar', 'e' => 'Editar', 'c' => 'Cargar', 'd' => 'Devolver'];
        $stituloevento = $aTitulo[$tipoaccion] ?? 'Ver';
        $aKeys = [
            'envioctrl' => ['nIdEnvio', 'dEntrega', 'Envio'],
        ];
        $masterkey = $aKeys[$operacion][0];
        //$datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];
        $titulo = $stituloevento . ' ' . $stitulooperacion;
        /*abevrc*/
        $mdlViaje = new ViajeMdl();
        $mdlViajeEnvio = new ViajeEnvioMdl();
        $mdlViajeEnvioDet = new ViajeEnvioDetalleMdl();

        $envio = new EnvioMdl();
        $enviodetalle = new EnvioDetalleMdl();

        $mdlEnvioDevolucion = new ViajeEnvioDevolucionMdl();

        $inventario = new InventarioMdl(); //Modelo para actualizar inventarios
        $movto = new MovimientoMdl();
        $movtodetail = new MovimientoDetalleMdl();

        if (strtoupper($this->request->getMethod()) === 'POST') {
            switch ($tipoaccion) {
                case 'd': {
                        /* Obtener el enviajeenvio para saber de que enenvio viene la operación */
                        $rViajEnvio = $mdlViajeEnvio->getRegistros($idviajeenvio);
                        /* Actualizar y regresar los valores de fPorRecibir y fRecibido del envio y del origen del envio*/
                        $rViajEnvDet = $mdlViajeEnvioDet->getRegistros($idviajeenvio);
                        $arrPorDevolver = $this->request->getVar($operacion);
                        $fTotEnviado = 0;
                        $fTotPeso = 0;
                        $nIdMovto = -1;
                        foreach ($arrPorDevolver as $idXDev => $fXDev) {
                            if ($fXDev === '' || floatval($fXDev) == 0) continue;
                            //$fXDev = 0;
                            $fOldXEnv = 0;
                            $needle = array_search($idXDev, array_column($_SESSION[$operacion]['registros'], 'nIdArticulo'));
                            $fTotEnviado += $fXDev ?? 0;
                            if (floatval($fXDev) == 0 && $_SESSION[$operacion]['registros'][$needle]['nIdViajeEnvioDetalle'] === null)  continue;
                            $rd = [
                                'nIdViajeEnvioDetalle' => $_SESSION[$operacion]['registros'][$needle]['nIdViajeEnvioDetalle'],
                                'nIdViajeEnvio' => $idviajeenvio,
                                'nIdArticulo' => $idXDev,
                                'fDevolver' => $fXDev,
                                'fPeso' => $fXDev * $_SESSION[$operacion]['registros'][$needle]['fPeso'],
                            ];
                            $fTotPeso += $rd['fPeso'];
                            if ($_SESSION[$operacion]['registros'][$needle]['nIdViajeEnvioDetalle'] !== null) {
                                $rd['nIdViajeEnvioDetalle'] = $_SESSION[$operacion]['registros'][$needle]['nIdViajeEnvioDetalle'];
                                $fOldXEnv = -1 * $_SESSION[$operacion]['registros'][$needle]['fXRecibir'];
                            }
                            $mdlEnvioDevolucion->save($rd);

                            $renvdet = $enviodetalle->getRegByArray(['nIdEnvio' => $rViajEnvio['nIdEnvio'], 'nIdArticulo' => $idXDev]);
                            $idEnvioDetalle = $renvdet[0]['nIdEnvioDetalle'];

                            $enviodetalle->updtRecibidos($idEnvioDetalle, -1 * $fXDev);

                            if ($_SESSION[$operacion]['registros'][$needle]['cModoEnv'] === '1') continue;
                            if ($nIdMovto === -1) {
                                $r = [
                                    'nIdOrigen' => $_SESSION[$operacion]['registro']['nIdViaje'],
                                    'cOrigen' => $_SESSION[$operacion]['registro']['cOrigen'],
                                    'dMovimiento' => date("Y-m-d", strtotime(date("d-m-Y"))),
                                ];
                                $movto->save($r);
                                $nIdMovto = $movto->insertID();
                                if ($_SESSION[$operacion]['registro']['cOrigen'] === 'ventas')
                                    $origenMdl = new TraspasoDetalleMdl();
                                else
                                    $origenMdl = new VentasDetMdl();
                            }
                            //Actualizar inventario de sucursal (descontar de inventario y comprometidos)
                            $ar2d2 = $inventario->getArticuloSucursal(
                                $_SESSION[$operacion]['registros'][$needle]['nIdArticulo'],
                                $_SESSION[$operacion]['registro']['nIdSucursal']
                            );
                            $saldoIni = $ar2d2['fExistencia'] ?? 0;

                            if ($nIdMovto > 0) {
                                $rd = [
                                    'nIdMovimiento' => $nIdMovto,
                                    'nIdArticulo' => $_SESSION[$operacion]['registros'][$needle]['nIdArticulo'],
                                    'nIdSucursal' => $_SESSION[$operacion]['registro']['nIdSucursal'],
                                    'fCantidad' => $_SESSION[$operacion]['registros'][$needle]['fXRecibir'],
                                    'fSaldoInicial' => $saldoIni,
                                ];
                                $movtodetail->save($rd);
                            }
                        }
                        return 'oK';
                        break;
                    }
                case 'b':
                    /* Obtener el enviajeenvio para saber de que enenvio viene la operación */
                    $rViajEnvio = $mdlViajeEnvio->getRegistros($idviajeenvio);
                    /* Actualizar y regresar los valores de fPorRecibir y fRecibido del envio y del origen del envio*/
                    $rViajEnvDet = $mdlViajeEnvioDet->getRegistros($idviajeenvio);
                    $fTotEnviado = 0;
                    $fTotPeso = 0;
                    foreach ($rViajEnvDet as $rvedXBorrar) {
                        $fOldXEnv = $rvedXBorrar['fPorRecibir'];
                        $fTotEnviado += $fOldXEnv;
                        $fTotPeso += $rvedXBorrar['fPeso'];
                        $renvdet = $enviodetalle->getRegByArray(['nIdEnvio' => $rViajEnvio['nIdEnvio'], 'nIdArticulo' => $rvedXBorrar['nIdArticulo']]);
                        $idEnvioDetalle = $renvdet[0]['nIdEnvioDetalle'];

                        $idViajeEnvioDetalle = $rvedXBorrar['nIdViajeEnvioDetalle'];

                        $enviodetalle->updtRecibidos($idEnvioDetalle, -1 * $rvedXBorrar['fPorRecibir']);
                        $mdlViajeEnvioDet->delete($idViajeEnvioDetalle);
                    }
                    $mdlViajeEnvio->delete($idviajeenvio);
                    $mdlViaje->updatePeso($_SESSION['viajectrl']['registro']['nIdViaje'], -1 * $rViajEnvio['fPeso']);
                    break;
                case 'i':
                    $needleEnvio = array_search($this->request->getVar('nIdEnvio'), array_column($_SESSION['viajectrl']['envios'], 'nIdEnvio'));
                    $_SESSION['viajectrl']['envios'][$needleEnvio]['selected'] = '0';
                    $_SESSION['viajectrl']['envios'][$needleEnvio]['sObservacion'] = $this->request->getVar('sObservacion');
                    $_SESSION['viajectrl']['envios'][$needleEnvio]['fPeso'] = $this->request->getVar('fPesoTotal');
                    $_SESSION['viajectrl']['envios'][$needleEnvio]['cEstatus'] = 'Proceso de carga';

                    $arrPorEnviar = $this->request->getVar($operacion);
                    $fTotEnviado = 0;
                    $fTotPeso = 0;
                    foreach ($arrPorEnviar as $idXEnv => &$fXEnv) {
                        if ($fXEnv === '')
                            $fXEnv = 0;
                        $fOldXEnv = 0;
                        $needle = array_search($idXEnv, array_column($_SESSION[$operacion]['registros'], 'nIdArticulo'));
                        $fTotEnviado += $fXEnv ?? 0;
                        if (floatval($fXEnv) == 0 && $_SESSION[$operacion]['registros'][$needle]['nIdViajeEnvioDetalle'] === null)  continue;
                        $rd = [
                            'nIdViajeEnvio' => $idviajeenvio,
                            'nIdArticulo' => $idXEnv,
                            'fPorRecibir' => $fXEnv,
                            'fPeso' => $fXEnv * $_SESSION[$operacion]['registros'][$needle]['fPeso'],
                        ];
                        $fTotPeso += $rd['fPeso'];
                        $_SESSION[$operacion]['registros'][$needle]['fXRecibir'] = $fXEnv;
                        if ($_SESSION[$operacion]['registros'][$needle]['nIdViajeEnvioDetalle'] !== null) {
                            $rd['nIdViajeEnvioDetalle'] = $_SESSION[$operacion]['registros'][$needle]['nIdViajeEnvioDetalle'];
                            $fOldXEnv = -1 * $_SESSION[$operacion]['registros'][$needle]['fXRecibir'];
                        }
                    }
                    $_SESSION['viajectrl']['envios'][$needleEnvio]['articulos'] = $_SESSION[$operacion]['registros'];

                    break;
            }
            return 'oK';
        }

        $frmURL = ("viajectrl/envioctrl/$tipoaccion/$idenvio/$idviaje/$idviajeenvio");

        $needleEnvio = array_search($idenvio, array_column($_SESSION['viajectrl']['envios'], 'nIdEnvio'));
        $myaccion = $tipoaccion;
        if ($_SESSION['viajectrl']['envios'][$needleEnvio]['selected'] === '0') {
            if (isset($_SESSION['viajectrl']['envios'][$needleEnvio]['articulos'])) {
                $rd = $_SESSION['viajectrl']['envios'][$needleEnvio]['articulos'];
                $r = $_SESSION['viajectrl']['envios'][$needleEnvio];
                $myaccion = 'nohagasnada';
            }            /*&& $_SESSION['viaje']['envios'][$needleEnvio]['nIdViaje'] == $idviaje*/
        } else {
            if ($tipoaccion === 'e') $myaccion = 'a';
        }
        switch ($myaccion) {
            case 'd':
                //$rd = $mdlViajeEnvio->getRegsEnviados(intval($idenvio), $this->nIdSucursal);
                $rd = $mdlViajeEnvio->getRegsDevueltos($idviajeenvio, $this->nIdSucursal);
                break;
            case 'c':
                break;
            case 'i':
            case 'v':
            case 'b':
                $rd = $mdlViajeEnvio->getRegsEnviados($idviajeenvio, $this->nIdSucursal);
                break;
        }
        $r = $envio->getViajePendiente(false, $idenvio);
        $cl =
            [
                'nIdCliente' => $r['nIdCliente'],
                'sNombre' => $r['sEnvEntrega'],
                'sReferencia' => $r['sEnvReferencia'],
                'sDireccion' => $r['sEnvDireccion'],
            ];
        $_SESSION[$operacion]['registro'] = $r;
        $_SESSION[$operacion]['registro']['totalarts'] = array_sum(array_column($rd, 'fCantidad'));
        $_SESSION[$operacion]['registros'] = $rd;
        $_SESSION[$operacion]['cliente'] = $cl;

        $data =
            [
                'titulo' => $titulo,
                'operacion' => $operacion,
                'tipoaccion' => $tipoaccion,
                'sSucursal' => $this->sSucursal,
                'modo' => strtoupper($tipoaccion),
                'frmURL' => $frmURL,
                'id' => $idenvio,
                'registro' => $r,
                'registros' => $rd,
                'cliente' => $cl,
            ];
        if ($tipoaccion === 'd')
            echo view('envios/devolucionctrlmtto', $data);
        else
            echo view('envios/cargactrlmtto', $data);
    }

    private function initVarSesion($operacion, $masterkey)
    {
        $timestamp = strtotime(date("d-m-Y"));
        //$_SESSION[$operacion]['registro'][$datefld] = date("Y-m-d", $timestamp);

        $_SESSION[$operacion] = [
            'chofer' => [
                'nIdChofer' => 0,
                'sChofer' => '',
                'sCelular' => '',
                'sLicenciaNo' => ''
            ],
            'registro' => [
                $masterkey => 0,
                'dViaje' => date("Y-m-d", $timestamp),
                'nIdChofer' => 0,
                'sObservacion' => '',
                'sChofer' => '',
                'cEstatus' => '',
                'nIdSucursal' => $this->nIdSucursal,
                'sDescripcion' => $this->sSucursal,
                'nViaje' => '',
                'fPeso' => 0,
            ],
            'envios' => [],
            'cliente' => [],
        ];
        $timestamp = strtotime($registro['dViaje'] ?? date("d-m-Y"));
        $_SESSION[$operacion]['registro']['dViaje'] = date("Y-m-d", $timestamp);
        //var_dump($_SESSION[$operacion]['chofer']);
    }

    public function limpiaOperacion($operacion, $masterkey)
    {
        session();
        unset($_SESSION[$operacion]);
        $this->initVarSesion($operacion, $masterkey);
        return redirect()->to(base_url("/viajectrl/$operacion/a"));
    }

    public function imprime($id)
    {
        $mdl = new ViajeMdl();
        $regs = $mdl->getImpresion($id);
        $arts = [];
        $clientes = [];
        $nomChofer = '';
        foreach ($regs as $reg) {
            if ($nomChofer == '') $nomChofer = $reg['sChofer'];
            $idx = array_search($reg['nIdArticulo'], array_column($arts, 'nIdArticulo'));
            if ($idx === false)
                $arts[] =
                    [
                        'nIdArticulo' => $reg['nIdArticulo'],
                        'sArticulo' => $reg['sArticulo'],
                        'nTotales' => $reg['fPorRecibir'],
                    ];
            else
                $arts[$idx]['nTotales'] += $reg['fPorRecibir'];
            $clientedx = array_search($reg['nIdCliente'] . '-' . $reg['nIdDirEntrega'], array_column($clientes, 'nIdCliente'));
            if ($clientedx === false)
                $clientes[] =
                    [
                        'nIdCliente' => $reg['nIdCliente'] . '-' . $reg['nIdDirEntrega'],
                        'sEnvEntrega' => $reg['sEnvEntrega'],
                        'sEnvDireccion' => $reg['sEnvDireccion'],
                        'sEnvColonia' => $reg['sEnvColonia'],
                        'sEnvTelefono' => $reg['sEnvTelefono'],
                        'sEnvReferencia' => $reg['sEnvReferencia'],
                    ];
        }
        $data['printerMode'] = 'true';
        $data['nIdViaje'] = $id;
        $data['nomChofer'] = $nomChofer;
        $data['titulo'] = '';
        $data['registros'] = $regs;
        $data['articulos'] = $arts;
        $data['clientes'] = $clientes;
        echo view('templates/header', $this->dataMenu);
        echo view('envios/viajectrlimprimir', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }
}
