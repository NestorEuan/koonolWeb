<?php

namespace App\Controllers\Viajes;

use App\Controllers\Viajes\ViajeBase;
use App\Models\Almacen\InventarioMdl;
use App\Models\Almacen\MovimientoDetalleMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Catalogos\ChoferMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Seguridad\UsuarioMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Viajes\EnvioDetalleMdl;
use App\Models\Viajes\EnvioMdl;
use App\Models\Viajes\ViajeEnvioDetalleMdl;
use App\Models\Viajes\ViajeEnvioDevolucionMdl;
use App\Models\Viajes\ViajeEnvioMdl;
use App\Models\Viajes\ViajeMdl;
use DateTime;

class ViajeCtrl extends ViajeBase
{
    public function index()
    {
        $this->validaSesion();

        $data = [
            'cOpciones' => [
                ['Viajes Asignados', '1', ''],
                ['Viajes En Transito', '2', ''],
                ['Folio de Viaje', '4', 'Viaje'],
                ['Folio de Envio', '6', 'Envio'],
                ['Todos', '7', '']
            ],
            'registros' => [],
            'pager' => '',
            'fecIni' => '',
            'fecFin' => '',
            'esMobil' => $this->request->getUserAgent()->isMobile()
        ];
        $this->leeRegistrosFiltrados($data['registros'], $data['pager'], '1');

        echo view('templates/header', $this->dataMenu);
        echo view('Viajes/ctrlviajes', $data);
        echo view('templates/footer', $this->dataMenu);
        return;
    }

    public function accion($tipoaccion, $id)
    {
        $this->validaSesion();
        $data = [
            'modoAccionEnEnvio' => 'c',  // modo consulta
            'tipoAccion' => $tipoaccion,
            'cNomChofer' => '',
            'fechaProg' => '',
            'sObservacionViaje' => '',
            'baseURL' => base_url('viajectrl/' . $tipoaccion . '/' . $id),
            'esMobil' => $this->request->getUserAgent()->isMobile()
        ];

        $aComplementoTitulo = ['s' => 'Salida', 'f' => 'Finaliza', 'c' => 'Consulta'];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion == 's') $this->actualizaCargaViaje($id);
            if ($tipoaccion == 'f') $this->actualizaFinalizaViaje($id);


            return json_encode([
                'nuevo' => ($tipoaccion == 'a' ? '1' : '0'),
                'url' => base_url('viaje')
            ]);
        } else {
            /*
                Estructura 'envios':
                array[idEnvio] = [
                    observaciones
                    peso total del envio
                    marca: indica si el envio tiene producto capturado para enviar.
                    'lstArt' => []
                ]
                donde 'lstArt' es:
                array[idArt] = [
                    00 - idArticulo
                    01 - cantidad a surtir
                    02 - cant capturada o a enviar en el viaje
                    03 - pesoProducto
                    04 - modoENV
                    05 - cant. comprometida (cuando se modifica el viaje)
                    06 - modifica inventario (cSinExistencia)
                    07 - id del articulo (nIdViajeEnvioDetalle)
                    08 - cant capturada para devolucion del viaje
                ]
            */
            $_SESSION['vViaje'] = [
                'idViaje' => '',
                'obs' => '',
                'fecProgramada' => '',
                'nomChof' => 'Pendiente',
                'tipoAccion' => $tipoaccion,
                'enviosOri' => [],
                'envios' => []
            ];
            $data['titulo'] = $aComplementoTitulo[$tipoaccion] . ' Viaje';

            $data['titulo'] .= ' :  ' . $id;
            $mdlViaje = new ViajeMdl();
            $mdlChofer = new ChoferMdl();
            $regViaje = ($mdlViaje->getRegistros($id))[0];
            $data['idViaje'] = $id;
            $data['cNomChofer'] = $regViaje['nomChofer'];
            $data['nIdChofer'] = $regViaje['nIdChofer'];
            $data['listChofer'] = $mdlChofer->getRegistros();
            $data['fechaProg'] = (new DateTime($regViaje['dViaje']))->format('Y-m-d');
            $data['sObservacionViaje'] = $regViaje['sObservacion'];
            $this->cargaEnArrayEnviosDelViaje($id);
            $cadEnvios = '';
            if ($tipoaccion == 's') $cadEnvios = $this->validaInventarioViaje($id);
            // se cargan los envios de un viaje
            $_SESSION['vViaje']['idViaje'] = $id;
            $data['registros'] = $this->initArrayEnvios($id, true);
            $data['modoAccionEnEnvio'] = $tipoaccion == 'f' ? 'e' : 'c';
            $data['inventarioIncompleto'] = $cadEnvios;
            $data['permisoCapDevoluciones'] = isset($this->aPermiso['oPermitirDevol']);
        }

        echo view('templates/header', $this->dataMenu);
        echo view('Viajes/ctrlviajemtto', $data);
        echo view('templates/footer', $this->dataMenu);
        return;
    }

    public function devolucion($tipoaccion, $idEnvio, $idViaje = false)
    {
        $this->validaSesion();

        $aComplementoTitulo = ['e' => 'Procesa', 'c' => 'Consulta'];

        $data = [
            'baseURL' => base_url('viajectrl/devolucion/' . $tipoaccion . '/' . $idEnvio . ($idViaje ? '/' . $idViaje : ''))
        ];
        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion == 'e') {
                // buscon envios si existe
                // agrego los que no estan sustituyo los otros
                // updateVViajeEnvios
                $nIdEnvio = $this->request->getVar('idEnvio');
                $arrDet = $this->request->getVar('det');
                foreach ($arrDet as $r) {
                    $_SESSION['vViaje']['envios'][$nIdEnvio]['lstArt'][$r['idArt']][8] = $r['capturada'];
                }
                $registros = $this->initArrayEnvios($idViaje, true);
                echo view('Viajes/viajemttodetalle', [
                    'registros' => $registros,
                    'esCtrl' => '1',
                    'modoAccionEnEnvio' => $tipoaccion,
                    'tipoAccion' => $_SESSION['vViaje']['tipoAccion'],
                    'permisoCapDevoluciones' => isset($this->aPermiso['oPermitirDevol']),
                    'idViaje' => $idViaje,
                    'esMobil' => $this->request->getUserAgent()->isMobile()
                ]);
                return;
            }
        } else {
            $mdlEnvio = new EnvioMdl();
            $data['titulo'] = $aComplementoTitulo[$tipoaccion] . ' Devolucion del Envio : ' . $idEnvio;
            $data['idEnvio'] = $idEnvio;
            $data['idViaje'] = $idViaje;
            $data['tipoAccion'] = $tipoaccion;
            $data['sObservacionEnvio'] = $_SESSION['vViaje']['envios'][$idEnvio]['observacion'] ?? '';
            $data['regsDet'] = $this->preparaDetalleCapturaDevolucion($idEnvio, $idViaje);
            $data['regEnv'] = $mdlEnvio->getEnviosPendientesParaViaje($idEnvio);
            if ($data['regEnv']['cOrigen'] == 'traspaso') {
                $data['fechaSol'] = substr($data['regEnv']['dSolTraspaso'], 8, 2) . '-' .
                    substr($data['regEnv']['dSolTraspaso'], 5, 2) . '-' .
                    substr($data['regEnv']['dSolTraspaso'], 0, 4);
                $data['fechaAlta'] = substr($data['regEnv']['dAltaTraspaso'], 8, 2) . '-' .
                    substr($data['regEnv']['dAltaTraspaso'], 5, 2) . '-' .
                    substr($data['regEnv']['dAltaTraspaso'], 0, 4);
                $mdlSucursal = new SucursalMdl();
                $regSuc = $mdlSucursal->getRegistros($data['regEnv']['idSucursalDest']);
                $data['sucursalDestino'] = $regSuc['sDescripcion'];
            } else {
                $data['fechaSol'] = '';
                $data['fechaAlta'] = substr($data['regEnv']['dAltaVenta'], 8, 2) . '-' .
                    substr($data['regEnv']['dAltaVenta'], 5, 2) . '-' .
                    substr($data['regEnv']['dAltaVenta'], 0, 4);
                $data['sucursalDestino'] = '';
            }
        }
        echo view('Viajes/viajedevolmtto', $data);
        return;
    }

    private function actualizaCargaViaje($idViaje)
    {
        // el detalle de envio no se va a modificar, se modifica el estatusa del viaje
        // en el detalle del envio se modifica la cantidad de fPorRecibir a fRecibido
        // en el inventario de modifica de comprometido a SALIDA DE EINVENTARIO FISICO.
        $mdlInventario = new InventarioMdl();
        $mdlViaje = new ViajeMdl();
        $mdlMovimiento = new MovimientoMdl();
        $mdlMovimientoDet = new MovimientoDetalleMdl();
        $mdlVentasDet = new VentasDetMdl();

        $regViaje = $mdlViaje->getRegistros($idViaje)[0];
        $registros = $this->initArrayEnvios($idViaje, true);
        $mdlViaje->update($idViaje, [
            'nIdChofer' => $this->request->getVar('nIdChofer'),
            'cEstatus' => '2'
        ]);

        // se actualiza los inventarios de comprometidos a descargados de inventario

        foreach ($_SESSION['vViaje']['envios'] as $k => $v) {
            if ($v['marca'] == '0') continue;
            $idMovto = $mdlMovimiento->insert([
                'nIdOrigen' => $k,
                'cOrigen' => 'envio',
                'dMovimiento' => $regViaje['dViaje'],
                'sObservacion' => $idViaje
            ]);
            // ahora se guarda el detalle del envioViaje
            foreach ($v['lstArt'] as $kd => $vd) {
                //  contiene [idArt, cantAsurtir, cantCapturada, pesoProducto, modoenv, comprometido, cSinExistencia]
                if (round($vd[2], 3) == 0) continue;

                $nCant = round($vd[2], 3);

                if ($registros[$k]['cOrigen'] == 'ventas') {
                    $mdlVentasDet->builder()->set(
                        [
                            'nEntregado' => 'vtventasdet.nEntregado + ' . $nCant
                        ],
                        '',
                        false
                    )->where([
                        'nIdVentas' => $registros[$k]['nIdOrigen'],
                        'nIdArticulo' => $kd
                    ])->update();
                }

                if ($vd[6] == '1' || $vd[4] == '1') continue;    // no hay movto de inventario
                // se realiza la microtransaccion del inventario
                // $this->comprometeInventario($mdlInventario, $kd, round($vd[2], 3));
                $mdlInventario->db->transStart();

                $rInv = $mdlInventario->getArticuloSucursal($kd, $this->nIdSucursal, true, true);
                $fExistencia = round(floatval($rInv['fExistencia']), 3);
                $fExistenciaOri = $fExistencia;
                $nComprometido = round(floatval($rInv['fComprometido']), 3);
                if ($nCant > $nComprometido)
                    $nComprometido = 0;
                else
                    $nComprometido -= $nCant;

                if ($nCant > $fExistencia)
                    $fExistencia = 0;
                else
                    $fExistencia -= $nCant;

                $mdlInventario->set('fComprometido', $nComprometido)
                    ->set('fExistencia', $fExistencia);
                $mdlInventario
                    ->where([
                        'nIdSucursal' => $this->nIdSucursal,
                        'nIdArticulo' => intval($kd)
                    ])->update();

                $mdlMovimientoDet->insert([
                    'nIdMovimiento' => $idMovto,
                    'nIdArticulo' => $kd,
                    'nIdSucursal' => $this->nIdSucursal,
                    'fCantidad' => $nCant * -1,
                    'fSaldoInicial' => $fExistenciaOri
                ]);

                $mdlInventario->db->transComplete();
            }
        }
    }

    private function actualizaFinalizaViaje($idViaje)
    {
        // el detalle de envio no se va a modificar, se modifica el estatus del viaje
        // en el detalle del envio se modifica la cantidad de fPorRecibir a fRecibido
        // en el inventario de modifica de comprometido a SALIDA DE EINVENTARIO FISICO.
        $mdlInventario = new InventarioMdl();
        $mdlEnvioDevolucion = new ViajeEnvioDevolucionMdl();
        $mdlEnvioDetalle = new EnvioDetalleMdl();
        $mdlVentasDet = new VentasDetMdl();

        $mdlEnvio = new EnvioMdl();
        $mdlViaje = new ViajeMdl();

        $mdlMovimiento = new MovimientoMdl();
        $mdlMovimientoDet = new MovimientoDetalleMdl();

        $mdlViaje->update($idViaje, ['cEstatus' => '3']);

        $registros = $this->initArrayEnvios($idViaje, true);

        // se actualiza la devolucion de producto,
        // los inventarios y el detalle del envio
        $fecMov = (new DateTime())->format('Y-m-d');

        foreach ($_SESSION['vViaje']['envios'] as $k => $v) {
            if ($v['marca'] == '0') continue;
            $nIdViajeEnvio = $v['nIdViajeEnvio'];
            // se verifica que tenga devolucion
            $nSumDevol = 0;
            foreach ($v['lstArt'] as $kd => $vd) $nSumDevol += round($vd[8], 3);
            // ahora se guarda el detalle del envioViaje
            if ($nSumDevol > 0) {
                $idMovto = $mdlMovimiento->insert([
                    'nIdOrigen' => $k,
                    'cOrigen' => 'enviodev',
                    'dMovimiento' => $fecMov,
                    'sObservacion' => $idViaje
                ]);
                foreach ($v['lstArt'] as $kd => $vd) {
                    //  contiene [idArt, cantAsurtir, cantCapturada, pesoProducto, modoenv, comprometido, cSinExistencia, nIdViajeEnvioDetalle]
                    if (round($vd[8], 3) == 0) continue;

                    $nCant = round($vd[8], 3);

                    if ($registros[$k]['cOrigen'] == 'ventas') {
                        $mdlVentasDet->builder()->set(
                            [
                                'nEntregado' => 'vtventasdet.nEntregado - ' . $nCant
                            ],
                            '',
                            false
                        )->where([
                            'nIdVentas' => $registros[$k]['nIdOrigen'],
                            'nIdArticulo' => $kd
                        ])->update();
                    }
                    // se realiza la microtransaccion del inventario
                    if ($vd[6] == '0') {
                        $mdlInventario->db->transStart();

                        $rInv = $mdlInventario->getArticuloSucursal($kd, $this->nIdSucursal, true, true);
                        $fExistencia = round(floatval($rInv['fExistencia']), 3);
                        $fExistenciaOri = $fExistencia;
                        $nSobreComprometido = round(floatval($rInv['fSobreComprometido']), 3);
                        if ($nSobreComprometido > 0) {
                            if ($nCant >= $nSobreComprometido)
                                $nSobreComprometido = 0;
                            else
                                $nSobreComprometido -= $nCant;

                            $mdlInventario->set('fSobreComprometido', $nSobreComprometido);
                        }
                        $fExistencia += $nCant;
                        $mdlInventario->set('fExistencia', $fExistencia);
                        $mdlInventario
                            ->where([
                                'nIdSucursal' => $this->nIdSucursal,
                                'nIdArticulo' => intval($kd)
                            ])->update();

                        // se agrega el movto de producto
                        $mdlMovimientoDet->insert([
                            'nIdMovimiento' => $idMovto,
                            'nIdArticulo' => $kd,
                            'nIdSucursal' => $this->nIdSucursal,
                            'fCantidad' => $nCant,
                            'fSaldoInicial' => $fExistenciaOri
                        ]);

                        $mdlInventario->db->transComplete();
                    }
                    // se actualiza el detalle del envio
                    $regEnvDet = $mdlEnvioDetalle->where([
                        'nIdEnvio' => $k,
                        'nIdArticulo' => $kd
                    ])->first();
                    if ($regEnvDet != null) {
                        $mdlEnvioDetalle->builder()->set(
                            [
                                'fRecibido' => 'enenviodetalle.fRecibido - ' . $nCant,
                                'fPorRecibir' => 'enenviodetalle.fPorRecibir + ' . $nCant
                            ],
                            '',
                            false
                        )->where(['nIdEnvioDetalle' => $regEnvDet['nIdEnvioDetalle']])
                            ->update();
                    }
                    // se agrega la devolucion
                    $mdlEnvioDevolucion->insert([
                        'nIdViajeEnvioDetalle' => $vd[7],
                        'nIdViajeEnvio' => $nIdViajeEnvio,
                        'nIdArticulo' => $kd,
                        'fDevolver' => $nCant,
                        'fPeso' => 0
                    ]);
                }
                $this->actualizaEstatusEnvio($k, $mdlEnvioDetalle, $mdlEnvio);
            }
        }
    }

    private function preparaDetalleCapturaDevolucion($idEnvio, $idViaje)
    {
        // leo el detalle del envio y le agrego campos para captura y vizual
        $mdlViajeEnvio = new ViajeEnvioMdl();
        $regsDet = $mdlViajeEnvio->getRegistrosDetalleParaDevolucion($idEnvio, $idViaje);

        foreach ($regsDet as $k => $r) {
            $regsDet[$k]['capturada'] = 0;
            if (isset($_SESSION['vViaje']['envios'][$idEnvio]['lstArt'][$r['nIdArticulo']])) {
                $rt = $_SESSION['vViaje']['envios'][$idEnvio]['lstArt'][$r['nIdArticulo']];
                $regsDet[$k]['capturada'] = $rt[8];
            }
            if ($r['fDevolver'] != '0') $regsDet[$k]['capturada'] = round(floatval($r['fDevolver']), 3);
            $regsDet[$k]['fPorRecibir'] = round(floatval($regsDet[$k]['fPorRecibir']), 3);
            $regsDet[$k]['entregado'] = $regsDet[$k]['fPorRecibir'] - $regsDet[$k]['capturada'];
        }
        return $regsDet;
    }

    private function validaInventarioViaje($idViaje)
    {
        // devuelve un arreglo con los envios que no tienen la suficiente existencia para surtir
        //   enviajeenvio.
        $mdlInventario = new InventarioMdl();
        $mdlViaje = new ViajeMdl();
        $regs = $mdlViaje->select('enviaje.nIdViaje, enviaje.nIdSucursal, a.nIdEnvio, b.nIdArticulo, b.fPorRecibir, b.cModoEnv, c.cSinExistencia')
            ->join('enviajeenvio a', 'enviaje.nIdViaje = a.nIdViaje', 'inner')
            ->join('enviajeenviodetalle b', 'a.nIdViajeEnvio = b.nIdViajeEnvio', 'inner')
            ->join('alarticulo c', 'b.nIdArticulo = c.nIdArticulo', 'inner')
            ->where('enviaje.nIdViaje', $idViaje)
            ->findAll();
        $arr = [];
        $msj = '';
        foreach ($regs as $r) {
            if ($r['cSinExistencia'] == '1' || $r['cModoEnv'] == '1') continue;
            $rInv = $mdlInventario->getArticuloSucursal($r['nIdArticulo'], $r['nIdSucursal'], true);
            if ($rInv == null) {
                if (!isset($arr[$r['nIdEnvio']])) {
                    $arr[$r['nIdEnvio']] = [];
                    $msj .= $r['nIdEnvio'];
                }
                $arr[$r['nIdEnvio']][$r['nIdArticulo']] = [0, $r['fPorRecibir']];
            } else {
                if ($rInv['fExistencia'] < $r['fPorRecibir']) {
                    if (!isset($arr[$r['nIdEnvio']])) {
                        $arr[$r['nIdEnvio']] = [];
                        $msj .= $r['nIdEnvio'];
                    }
                    $arr[$r['nIdEnvio']][$r['nIdArticulo']] = [$rInv['fExistencia'], $r['fPorRecibir']];
                }
            }
        }
        return $msj;
    }
}
