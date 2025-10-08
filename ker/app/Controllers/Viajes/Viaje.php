<?php

namespace App\Controllers\Viajes;

use App\Controllers\Viajes\ViajeBase;
use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Viajes\EnvioDetalleMdl;
use App\Models\Viajes\EnvioMdl;
use App\Models\Viajes\ViajeEnvioDetalleMdl;
use App\Models\Viajes\ViajeEnvioMdl;
use App\Models\Viajes\ViajeMdl;
use DateTime;

class Viaje extends ViajeBase
{
    public function index()
    {
        $this->validaSesion();

        $data = [
            'cOpciones' => [
                ['Viajes Programados', '0', ''],
                ['Viajes Asignados', '1', ''],
                ['Viajes En Transito', '2', ''],
                ['Fecha Programada', '3', ''],
                ['Folio de Viaje', '4', 'Viaje'],
                ['Folio de Remision', '5', 'Remision'],
                ['Folio de Envio', '6', 'Envio'],
                ['Todos', '7', '']
            ],
            'registros' => [],
            'pager' => '',
            'fecIni' => '',
            'fecFin' => ''
        ];
        $this->leeRegistrosFiltrados($data['registros'], $data['pager'], '0');

        echo view('templates/header', $this->dataMenu);
        echo view('Viajes/viajes', $data);
        echo view('templates/footer', $this->dataMenu);
        return;
    }

    public function accion($tipoaccion, $id = false)
    {
        $this->validaSesion();
        $data = [
            'modoAccionEnEnvio' => 'c',  // modo consulta
            'tipoAccion' => $tipoaccion,
            'cNomChofer' => '',
            'fechaProg' => '',
            'sObservacionViaje' => '',
            'baseURL' => base_url('viaje/' . $tipoaccion . ($id ? '/' . $id : '')),
            'baseURLimp' => base_url('viaje/imprime' . ($id ? '/' . $id : ''))
        ];

        $aComplementoTitulo = ['a' => 'Agrega', 'b' => 'Cancela', 'e' => 'Modifica', 'c' => 'Consulta', 's' => 'Asigna e Imprime para cargar'];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            $a = [
                'dViaje' => $this->request->getVar('fecha'),
                'sObservacion' => $this->request->getVar('observacion')
            ];
            $mdlViaje = new ViajeMdl();
            if ($tipoaccion == 'a') {
                $a['nIdChofer'] = 0;
                $a['nIdSucursal'] = $this->nIdSucursal;
                $a['cEstatus'] = '0';
                $idViaje = $mdlViaje->insert($a);
            } elseif ($tipoaccion == 'e') {
                $idViaje = $id;
                $mdlViaje->update($id, $a);
            }
            $this->actualizaViaje($idViaje);

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

            if ($tipoaccion == 'a') {

                $data['registros'] = $this->initArrayEnvios();
                $data['idViaje'] = '';
                $data['cNomChofer'] = 'Pendiente';
                $data['fechaProg'] = (new DateTime())->format('Y-m-d');
                $data['sObservacionViaje'] = '';
                $data['modoAccionEnEnvio'] = 'e';
            } else {
                $data['titulo'] .= ' :  ' . $id;
                $mdlViaje = new ViajeMdl();
                $regViaje = ($mdlViaje->getRegistros($id))[0];
                $data['idViaje'] = $id;
                $data['cNomChofer'] = $regViaje['nomChofer'];
                $data['fechaProg'] = (new DateTime($regViaje['dViaje']))->format('Y-m-d');
                $data['sObservacionViaje'] = $regViaje['sObservacion'];
                $this->cargaEnArrayEnviosDelViaje($id);
                // se cargan los envios de un viaje
                $_SESSION['vViaje']['idViaje'] = $id;
                $data['registros'] = $this->initArrayEnvios($id, (in_array($tipoaccion, ['c', 'b', 's'])));
                $data['modoAccionEnEnvio'] = $tipoaccion == 'e' ? 'e' : 'c';
            }
        }

        echo view('templates/header', $this->dataMenu);
        echo view('Viajes/viajemtto', $data);
        echo view('templates/footer', $this->dataMenu);
        return;
    }

    public function envio($tipoaccion, $idEnvio, $idViaje = false, $idVentasEnvio = false)
    {
        $this->validaSesion();
        $mdlSucu = new SucursalMdl();

        $aComplementoTitulo = ['e' => 'Procesa', 'c' => 'Consulta', 'n' => 'Procesa en otra sucursal el '];

        $data = [
            'baseURL' => base_url('viaje/envio/' . $tipoaccion . '/' . $idEnvio . ($idViaje ? '/' . $idViaje : ''))
        ];
        $view = 'Viajes/viajeenviomtto';
        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion == 'e') {
                // buscon envios si existe
                // agrego los que no estan sustituyo los otros
                // updateVViajeEnvios
                $nIdEnvio = $this->request->getVar('idEnvio');
                $cObserva = $this->request->getVar('observacion');
                $arrDet = $this->request->getVar('det');
                if (!isset($_SESSION['vViaje']['envios'][$nIdEnvio])) {
                    $_SESSION['vViaje']['envios'][$nIdEnvio] = [
                        'observacion' => '',
                        'peso' => 0,
                        'conDevolucion' => '0',
                        'lstArt' => []
                    ];
                }
                $_SESSION['vViaje']['envios'][$nIdEnvio]['observacion'] = $cObserva;
                foreach ($arrDet as $r) {
                    if (isset($_SESSION['vViaje']['envios'][$nIdEnvio]['lstArt'][$r['idArt']])) {
                        $_SESSION['vViaje']['envios'][$nIdEnvio]['lstArt'][$r['idArt']][2] = $r['capturada'];
                        $_SESSION['vViaje']['envios'][$nIdEnvio]['lstArt'][$r['idArt']][3] = $r['peso'];
                        $_SESSION['vViaje']['envios'][$nIdEnvio]['lstArt'][$r['idArt']][4] = $r['modenv'];
                    } else {
                        $_SESSION['vViaje']['envios'][$nIdEnvio]['lstArt'][$r['idArt']] = [
                            $r['idArt'], 0, $r['capturada'],
                            $r['peso'], $r['modenv'], 0, $r['cSinExistencia']
                        ];
                    }
                }
            } elseif ($tipoaccion == 'n') {
                $this->creaEnvioEnOtraSucursal($idEnvio);
            } elseif ($tipoaccion == 'r') {
                $msj = $this->reasignaEnvioAVentas($idEnvio, $idVentasEnvio);
                if ($msj) {
                    echo $msj;
                    return;
                }
            }

            $registros = $this->initArrayEnvios($idViaje);
            echo view('Viajes/viajemttodetalle', [
                'registros' => $registros,
                'tipoAccion' => $_SESSION['vViaje']['tipoAccion'],
                'modoAccionEnEnvio' => $tipoaccion,
                'permisoCapDevoluciones' => isset($this->aPermiso['oPermitirDevol']),
                'idViaje' => ($idViaje ? $idViaje : '')
            ]);
            return;
        } else {
            $mdlEnvio = new EnvioMdl();
            $data['titulo'] = $aComplementoTitulo[$tipoaccion] . ' Envio : ' . $idEnvio;
            $data['idEnvio'] = $idEnvio;
            $data['tipoAccion'] = $tipoaccion;
            $data['sObservacionEnvio'] = $_SESSION['vViaje']['envios'][$idEnvio]['observacion'] ?? '';
            $data['regsDet'] = $this->preparaDetalleCapturaEnvio($idEnvio);
            if ($tipoaccion == 'n') {
                $this->preparaDetalleCapturaEnvioAotraSucursal($idEnvio, $data['regsDet']);
                $view = 'Viajes/viajeenviootrasucmtto';
                $data['lstSuc'] = $mdlSucu->getRegistros(false, false, $this->nIdSucursal, true);
            }
            $data['regEnv'] = $mdlEnvio->getEnviosPendientesParaViaje($idEnvio);
            if ($data['regEnv']['cOrigen'] == 'traspaso') {
                $data['fechaSol'] = substr($data['regEnv']['dSolTraspaso'], 8, 2) . '-' .
                    substr($data['regEnv']['dSolTraspaso'], 5, 2) . '-' .
                    substr($data['regEnv']['dSolTraspaso'], 0, 4);
                $data['fechaAlta'] = substr($data['regEnv']['dAltaTraspaso'], 8, 2) . '-' .
                    substr($data['regEnv']['dAltaTraspaso'], 5, 2) . '-' .
                    substr($data['regEnv']['dAltaTraspaso'], 0, 4);
                $regSuc = $mdlSucu->getRegistros($data['regEnv']['idSucursalDest']);
                $data['sucursalDestino'] = $regSuc['sDescripcion'];
            } else {
                $data['fechaSol'] = '';
                $data['fechaAlta'] = substr($data['regEnv']['dAltaVenta'], 8, 2) . '-' .
                    substr($data['regEnv']['dAltaVenta'], 5, 2) . '-' .
                    substr($data['regEnv']['dAltaVenta'], 0, 4);
                $data['sucursalDestino'] = '';
            }
        }
        echo view($view, $data);
        return;
    }

    public function imprime($idViaje)
    {
        $this->validaSesion();

        $mdlViaje = new ViajeMdl();
        $mdlSucursal = new SucursalMdl();

        $regV = $mdlViaje->select('cEstatus')
            ->where('nIdViaje', $idViaje)->first();
        if ($regV != null && $regV['cEstatus'] == '0') {
            // actualizamos el estatus del viaje para pasarlo a cargar
            $mdlViaje->update($idViaje, ['cEstatus' => '1']);
        }

        $_SESSION['vViaje'] = [
            'idViaje' => '',
            'obs' => '',
            'fecProgramada' => '',
            'nomChof' => 'Pendiente',
            'envios' => []
        ];
        $this->cargaEnArrayEnviosDelViaje($idViaje);
        $regis = $this->initArrayEnvios($idViaje, true);
        $nSumPeso = 0;
        foreach ($regis as $k => $r) {
            $nSumPeso += $r['peso'];
            $regis[$k]['det'] = $this->preparaDetalleCapturaEnvio($k);
            if ($regis[$k]['cOrigen'] == 'traspaso') {
                $regis[$k]['fechaSol'] = substr($regis[$k]['dSolTraspaso'], 8, 2) . '-' .
                    substr($regis[$k]['dSolTraspaso'], 5, 2) . '-' .
                    substr($regis[$k]['dSolTraspaso'], 0, 4);
                $regis[$k]['fechaAlta'] = substr($regis[$k]['dAltaTraspaso'], 8, 2) . '-' .
                    substr($regis[$k]['dAltaTraspaso'], 5, 2) . '-' .
                    substr($regis[$k]['dAltaTraspaso'], 0, 4);
                $regSuc = $mdlSucursal->getRegistros($regis[$k]['idSucursalDest']);
                $regis[$k]['sucursalDestino'] = $regSuc['sDescripcion'];
            } else {
                $regis[$k]['fechaSol'] = '';
                $regis[$k]['fechaAlta'] = substr($regis[$k]['dAltaVenta'], 8, 2) . '-' .
                    substr($regis[$k]['dAltaVenta'], 5, 2) . '-' .
                    substr($regis[$k]['dAltaVenta'], 0, 4);
                $regis[$k]['sucursalDestino'] = '';
            }
        }

        $data = [
            'dat' => ($mdlViaje->getRegistros($idViaje))[0],
            'det' => $regis
        ];
        $data['dat']['fPeso'] = $nSumPeso;
        $data['aInfoSis'] = $this->dataMenu['aInfoSis'];
        echo view('viajes/viajeimprimir', $data);
        return;
    }

    private function actualizaViaje($idViaje)
    {
        // se leen todos los registros relacionados con el viaje (tanto de enviajeenvio como enviajeenviodetalle)
        // para saber el id a sobreescribir
        // se repasa para ver que envios estan con marca para guardar y se agregan o modifican
        $mdlInventario = new InventarioMdl();
        $mdlViajeEnvioDet = new ViajeEnvioDetalleMdl();
        $mdlViajeEnvio = new ViajeEnvioMdl();
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDetalle = new EnvioDetalleMdl();

        $regsIdEnvios = $mdlViajeEnvio->getIDsViajeEnvios($idViaje);
        $nIdxEnv = 0;    // indice para regsIdEnvios
        $nIdxEnvTope = count($regsIdEnvios);

        $regsIdEnviosDet = $mdlViajeEnvio->getIDsViajeEnviosDetalles($idViaje);
        $nIdxDet = 0;    // indice para regsIdEnviosDet
        $nIdxDetTope = count($regsIdEnviosDet);    // indice para regsIdEnviosDet

        $this->recuperaComprometidosYenvios($mdlInventario, $regsIdEnviosDet, $mdlEnvioDetalle);

        foreach ($_SESSION['vViaje']['envios'] as $k => $v) {
            if ($v['marca'] == '0') continue;
            $a = [
                'nIdEnvio' => $k,
                'cEstatus' => '1',
                'sObservacion' => $v['observacion']
            ];
            if ($nIdxEnv < $nIdxEnvTope) {
                $mdlViajeEnvio->update($regsIdEnvios[$nIdxEnv]['id'], $a);
                $idViajeEnvio = $regsIdEnvios[$nIdxEnv]['id'];
                $nIdxEnv++;
            } else {
                $a['nIdViaje'] = $idViaje;
                $idViajeEnvio = $mdlViajeEnvio->insert($a);
            }
            // ahora se guarda el detalle del envioViaje
            foreach ($v['lstArt'] as $kd => $vd) {
                //  contiene [idArt, cantAsurtir, cantCapturada, pesoProducto, modoenv, comprometido, cSinExistencia]
                if (round($vd[2], 3) == 0) continue;
                $b = [
                    'nIdViajeEnvio' => $idViajeEnvio,
                    'nIdArticulo' => $kd,
                    'fPorRecibir' => $vd[2],
                    'fPeso' => round($vd[2] * $vd[3], 3),
                    'cModoEnv' => $vd[4]
                ];
                if ($nIdxDet < $nIdxDetTope) {
                    $mdlViajeEnvioDet->update($regsIdEnviosDet[$nIdxDet]['id'], $b);
                    $nIdxDet++;
                } else {
                    $mdlViajeEnvioDet->insert($b);
                }
                // se actualiza el detalle del envio
                $regEnvDet = $mdlEnvioDetalle->where([
                    'nIdEnvio' => $k,
                    'nIdArticulo' => $kd
                ])->first();
                if ($regEnvDet != null) {
                    $mdlEnvioDetalle->builder()->set(
                        [
                            'fRecibido' => 'enenviodetalle.fRecibido + ' . round($vd[2], 3),
                            'fPorRecibir' => 'enenviodetalle.fPorRecibir - ' . round($vd[2], 3)
                        ],
                        '',
                        false
                    )->where(['nIdEnvioDetalle' => $regEnvDet['nIdEnvioDetalle']])
                        ->update();
                }

                if ($vd[6] == '1' || $vd[4] == '1') continue;    // no hay movto de inventario
                // se realiza la microtransaccion del inventario
                $this->comprometeInventario($mdlInventario, $kd, round($vd[2], 3));
            }
            $this->actualizaEstatusEnvio($k, $mdlEnvioDetalle, $mdlEnvio);
            $_SESSION['vViaje']['enviosOri'][$k] = false;   // indica que el estatus del envio ya se aplico
        }
        // se borran los registros excedentes
        for ($i = $nIdxDet; $i < $nIdxDetTope; $i++)
            $mdlViajeEnvioDet->set('nIdArticulo', 0)
                ->set('fPorRecibir', 0)->set('fPeso', 0)->set('cModoEnv', '0')
                ->where('nIdViajeEnvioDetalle', $regsIdEnviosDet[$i]['id'])
                ->update();
        for ($i = $nIdxEnv; $i < $nIdxEnvTope; $i++)
            $mdlViajeEnvio->set('nIdEnvio', 0)
                ->set('cEstatus', '0')
                ->set('sObservacion', '')->set('fPeso', 0)
                ->where('nIdViajeEnvio', $regsIdEnvios[$i]['id'])
                ->update();
        $this->recuperaEstatusEnviosOriginales($mdlEnvioDetalle, $mdlEnvio);
    }

    private function creaEnvioEnOtraSucursal($idEnvio)
    {
        // se crea el envio destino
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $mdlSucu = new SucursalMdl();

        $nIdSucursal = $this->request->getVar('idSucursal');
        $cOrigen = $this->request->getVar('cOrigen');
        $nIdOrigen = $this->request->getVar('nIdOrigen');
        $nIdCliente = $this->request->getVar('nIdCliente');
        $nIdDirEntrega = $this->request->getVar('nIdDirEntrega');
        $arrDet = $this->request->getVar('det');
        $regSuc = $mdlSucu->getRegistros($nIdSucursal, false, false, true);

        $nIdEnvioNuevo = $mdlEnvio->insert([
            'nIdOrigen' => $nIdOrigen,
            'cOrigen' => $cOrigen,
            'dSolicitud' => (new DateTime())->format('Y-m-d'),
            'nIdSucursal' => $nIdSucursal,
            'nIdCliente' => $nIdCliente,
            'nIdDirEntrega' => $nIdDirEntrega,
            'cEstatus' => '1'
        ], true);
        foreach ($arrDet as $r) {
            $mdlEnvioDet->insert([
                'nIdEnvio' => $nIdEnvioNuevo,
                'nIdArticulo' => $r['idArt'],
                'fCantidad' => $r['capturada'],
                'fPorRecibir' => $r['capturada'],
                'fRecibido' => '0',
                'cModoEnv' => '0'
            ]);
            // se actualiza el envio modificado
            $mdlEnvioDet->builder()->set(
                [
                    'fCantidad' => 'enenviodetalle.fCantidad - ' . $r['capturada'],
                    'fPorRecibir' => 'enenviodetalle.fPorRecibir - ' . $r['capturada']
                ],
                '',
                false
            )->where([
                'nIdEnvio' => $idEnvio,
                'nIdArticulo' => $r['idArt']
            ])->update();
        }
        $this->actualizaEstatusEnvio($idEnvio, $mdlEnvioDet, $mdlEnvio);
    }

    private function reasignaEnvioAVentas($idEnvio, $idVentas)
    {
        //  si el envio no tiene nada que reasignar se devuelve un mensaje
        //  envio, enviodetalle    ventasdet
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $mdlViajeEnvio = new ViajeEnvioMdl();
        $mdlVentasDet = new VentasDetMdl();

        $regE = $mdlEnvio->where('nIdEnvio', $idEnvio)->first();
        if ($regE['cEstatus'] == '5') {
            return 'nooKmsjEl envio ' . $idEnvio . ' tiene asignado todo el producto, no hay nada que reasignar';
        }

        $regsE = $mdlEnvioDet->where([
            'nIdEnvio' => $idEnvio,
            'fPorRecibir >' => 0
        ])->findAll();
        foreach ($regsE as $r) {
            $nPorRecibir = round(floatval($r['fPorRecibir']), 3);
            $nRecibido = round(floatval($r['fRecibido']), 3);
            if ($nPorRecibir == 0) continue;

            if ($nRecibido == 0)
                $mdlEnvioDet->where([
                    'nIdEnvio' => $idEnvio,
                    'nIdArticulo' => $r['nIdArticulo']
                ])->delete();
            else
                $mdlEnvioDet->set([
                    'fPorRecibir' => 0,
                    'fCantidad' => $nRecibido
                ])->where([
                    'nIdEnvio' => $idEnvio,
                    'nIdArticulo' => $r['nIdArticulo']
                ])->update();

            // falta agregar si es con articulo relacionado
            $mdlVentasDet->builder()->set([
                'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nPorRecibir
            ], '', false)->where([
                'nIdVentas' => $idVentas,
                'nIdArticulo' => $r['nIdArticulo']
            ])->update();   //getCompiledUpdate(false);   //                
        }
        $regSuma = $mdlEnvioDet->select('SUM(enenviodetalle.fCantidad) AS fCantidad', false)
            ->where('nIdEnvio', $idEnvio)->first();

        if ($regSuma == null || round(floatval($regSuma['fCantidad'] ?? 0), 3) == 0)
            $mdlEnvio->delete($idEnvio);
        else
            $mdlEnvio->set('cEstatus', '5')->where('nIdEnvio', $idEnvio)->update();
        return false;
    }

    private function preparaDetalleCapturaEnvio($idEnvio)
    {
        // valido si tiene ya alguna captura el envio
        $nSumCapturada = 0;
        if (isset($_SESSION['vViaje']['envios'][$idEnvio])) {
            foreach ($_SESSION['vViaje']['envios'][$idEnvio]['lstArt'] as $r) $nSumCapturada += $r[2];
        }
        $bHayCantidadCapturada = round($nSumCapturada, 3) > 0;
        // leo el detalle del envio y le agrego campos para captura y vizual
        $mdlInventario = new InventarioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $regsDet = $mdlEnvioDet->getRegistros($idEnvio);

        foreach ($regsDet as $k => $r) {
            $regsDet[$k]['cModoEnv'] = '0';
            $regsDet[$k]['comprometido'] = 0;
            $cantCapturada = false;
            if (isset($_SESSION['vViaje']['envios'][$idEnvio]['lstArt'][$r['nIdArticulo']])) {
                // [idArt, cantAsurtir, cantCapturada, pesoProducto, modoenv, comprometido, cSinExistencia]
                $rt = $_SESSION['vViaje']['envios'][$idEnvio]['lstArt'][$r['nIdArticulo']];
                $regsDet[$k]['cModoEnv'] = $rt[4];
                $regsDet[$k]['comprometido'] = $rt[5];
                $cantCapturada = round($rt[2], 3);
            }

            $regsDet[$k]['fCantidad'] = round(floatval($regsDet[$k]['fCantidad']), 3);
            $regsDet[$k]['fRecibido'] = round(floatval($regsDet[$k]['fRecibido']), 3);
            $regsDet[$k]['fPorRecibir'] = round(floatval($regsDet[$k]['fPorRecibir']), 3);
            $regsDet[$k]['fPeso'] = round(floatval($r['fPeso']), 3);
            $regsDet[$k]['porSurtir'] = $regsDet[$k]['fCantidad'] - $regsDet[$k]['fRecibido'] + $regsDet[$k]['comprometido'];

            if ($bHayCantidadCapturada) {
                if ($cantCapturada === false)
                    $regsDet[$k]['capturada'] = $regsDet[$k]['porSurtir'];
                else
                    $regsDet[$k]['capturada'] = $cantCapturada <= $regsDet[$k]['porSurtir'] ? $cantCapturada : $regsDet[$k]['porSurtir'];
            } else
                $regsDet[$k]['capturada'] = $regsDet[$k]['porSurtir'];
            $regsDet[$k]['fRecibido'] -= $regsDet[$k]['comprometido'];

            $regsDet[$k]['disponible'] = 0; // nota: este disponible se inicializa con comprometido 
            $regsDet[$k]['sindisponible'] = false; // nota: me sirve para indicador vizual
            if ($r['cSinExistencia'] == '0') {
                $rInv = $mdlInventario->getRegistrosInv($r['nIdArticulo'], $this->nIdSucursal);
                $regsDet[$k]['disponible'] = round(floatval($rInv['exiSuc']) + floatval($regsDet[$k]['comprometido']), 3);
                if ($regsDet[$k]['capturada'] > $regsDet[$k]['disponible']) $regsDet[$k]['sindisponible'] = true;
            }
        }
        return $regsDet;
    }

    private function preparaDetalleCapturaEnvioAotraSucursal($idEnvio, &$regsDet)
    {
        $noMarcado = !isset($_SESSION['vViaje']['envios'][$idEnvio]);
        foreach ($regsDet as $k => $r) {
            if ($r['nIdEnvio'] != $idEnvio) continue;
            if ($noMarcado)
                $capturada = 0;
            else
                $capturada = $regsDet[$k]['capturada'];
            $regsDet[$k]['capturaActual'] = $capturada;
            $porSurtir = $regsDet[$k]['porSurtir'] - $capturada;
            $regsDet[$k]['capturada'] = $porSurtir;
            $regsDet[$k]['porSurtir'] = $porSurtir;
            $regsDet[$k]['entregado'] = 0;
        }
    }

    private function recuperaEstatusEnviosOriginales(EnvioDetalleMdl &$mdlEnvioDetalle, EnvioMdl &$mdlEnvio)
    {
        foreach ($_SESSION['vViaje']['enviosOri'] as $k => $v) {
            if ($v === false) continue;
            $this->actualizaEstatusEnvio($k, $mdlEnvioDetalle, $mdlEnvio);
        }
    }

    private function recuperaComprometidosYenvios(InventarioMdl &$mdlInventario, &$regsIdEnviosDet, EnvioDetalleMdl &$mdlEnvioDetalle)
    {
        // se regresa los comprometidos de los envios modificados
        // se regresa el detalle de los envios modificados
        foreach ($regsIdEnviosDet as $r) {
            if ($r['nIdArticulo'] != '0') {
                $regEnvDet = $mdlEnvioDetalle->where([
                    'nIdEnvio' => $r['nIdEnvio'],
                    'nIdArticulo' => $r['nIdArticulo']
                ])->first();
                if ($regEnvDet != null) {
                    $mdlEnvioDetalle->builder()->set(
                        [
                            'fRecibido' => 'enenviodetalle.fRecibido - ' . $r['fPorRecibir'],
                            'fPorRecibir' => 'enenviodetalle.fPorRecibir + ' . $r['fPorRecibir']
                        ],
                        '',
                        false
                    )->where(['nIdEnvioDetalle' => $regEnvDet['nIdEnvioDetalle']])
                        ->update();
                }
            }
            if ($r['cModoEnv'] == '1' || $r['nIdArticulo'] == '0' || $r['cSinExistencia'] == '1') continue;
            // realizo  una microtransaccion con el inventario
            $mdlInventario->db->transStart();

            $fPorRecibir = round(floatval($r['fPorRecibir']), 3);

            $rInv = $mdlInventario->getArticuloSucursal($r['nIdArticulo'], $this->nIdSucursal, true, true);
            $nSobreComprometido = round(floatval($rInv['fSobreComprometido']), 3);
            $nComprometido = round(floatval($rInv['fComprometido']), 3);

            if ($fPorRecibir <= $nSobreComprometido) {
                $nSobreComprometido -= $fPorRecibir;
                $mdlInventario->set('fSobreComprometido', round($nSobreComprometido, 3));
            } else {
                $fPorRecibir -= $nSobreComprometido;    // sobrecomprometido se vuelve cero.
                if ($fPorRecibir <= $nComprometido) {
                    $nComprometido -= $fPorRecibir;
                } else {
                    $nComprometido = 0;
                }
                $mdlInventario
                    ->set('fComprometido', $nComprometido)
                    ->set('fSobreComprometido', 0);
            }
            // descomprometemos el inventario del producto
            $mdlInventario
                ->where([
                    'nIdSucursal' => $this->nIdSucursal,
                    'nIdArticulo' => intval($r['nIdArticulo'])
                ])->update();
            $mdlInventario->db->transComplete();
        }
    }
}
