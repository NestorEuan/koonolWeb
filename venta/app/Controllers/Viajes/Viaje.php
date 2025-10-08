<?php

namespace App\Controllers\Viajes;

use App\Controllers\Viajes\ViajeBase;
use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Catalogos\ClienteMdl;
use App\Models\Catalogos\PrecioArticuloMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Viajes\EnvioDetalleMdl;
use App\Models\Viajes\EnvioDireccionMdl;
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

        $aComplementoTitulo = ['a' => 'Agrega', 'b' => 'Cancela', 'e' => 'Modifica', 'c' => 'Consulta', 's' => 'Asigna e Imprime para cargar', 'r' => 'Recupera de asignar'];

        if ($this->request->getMethod() == 'post') {
            $a = [
                'dViaje' => $this->request->getVar('fecha'),
                'sObservacion' => $this->request->getVar('observacion')
            ];
            $mdlViaje = new ViajeMdl();
            if ($tipoaccion == 's') {
                $regV = $mdlViaje->select('cEstatus')
                    ->where('nIdViaje', $id)->first();
                if ($regV != null && $regV['cEstatus'] == '0') {
                    // actualizamos el estatus del viaje para pasarlo a cargar
                    $mdlViaje->update($id, ['cEstatus' => '1']);
                }
                return json_encode(['ok' => '1']);
            } elseif ($tipoaccion == 'r') {
                $regV = $mdlViaje->select('cEstatus')
                    ->where('nIdViaje', $id)->first();
                if ($regV != null && $regV['cEstatus'] == '1') {
                    // actualizamos el estatus del viaje para regresarlo
                    $mdlViaje->update($id, ['cEstatus' => '0']);
                }
                echo 'ok';
                return;
            } elseif ($tipoaccion == 'a') {
                $a['nIdChofer'] = 0;
                $a['nIdSucursal'] = $this->nIdSucursal;
                $a['cEstatus'] = '0';
                $idViaje = $mdlViaje->insert($a);
            } elseif ($tipoaccion == 'e') {
                $idViaje = $id;
                $mdlViaje->update($id, $a);
            }
            $msj = $this->actualizaViaje($idViaje);
            if ($msj == '')
                return json_encode([
                    'nuevo' => ($tipoaccion == 'a' ? '1' : '0'),
                    'error' => '0',
                    'url' => base_url('viaje')
                ]);
            else
                if ($tipoaccion == 'a') {
                $mdlViaje->delete($idViaje, true);
                $idViaje = 0;
            }
            return json_encode([
                'error' => '1',
                'idviaje' => $idViaje,
                'msj' => $msj
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
                $data['estadoViaje'] = $regViaje['cEstatus'];
            }
        }

        echo view('templates/header', $this->dataMenu);
        echo view('Viajes/viajemtto', $data);
        echo view('templates/footer', $this->dataMenu);
        return;
    }

    public function envio($tipoaccion, $idEnvio, $idViaje = false, $idVentasEnvio = false, $desdeEnviosPendientes = false)
    {
        $this->validaSesion();
        $mdlSucu = new SucursalMdl();

        $aComplementoTitulo = ['e' => 'Procesa', 'c' => 'Consulta', 'n' => 'Procesa en otra sucursal el '];

        $data = [
            'baseURL' => base_url('viaje/envio/' . $tipoaccion . '/' . $idEnvio . ($idViaje ? '/' . $idViaje : ''))
        ];
        $view = 'Viajes/viajeenviomtto';
        if ($this->request->getMethod() == 'post') {
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
                $msj = $this->creaEnvioEnOtraSucursal($idEnvio);
                if ($msj) {
                    echo $msj;
                    return;
                }
            } elseif ($tipoaccion == 'r') {
                $msj = $this->reasignaEnvioAVentas($idEnvio, $idVentasEnvio);
                if ($msj) {
                    echo $msj;
                    return;
                }
                if ($desdeEnviosPendientes !== false) {
                    echo 'ok';
                    return;
                }
            }

            $registros = $this->initArrayEnvios($idViaje);
            echo view('Viajes/viajemttodetalle', [
                'registros' => $registros,
                'tipoAccion' => $_SESSION['vViaje']['tipoAccion'],
                'modoAccionEnEnvio' => $tipoaccion,
                'permisoCapDevoluciones' => isset($this->aPermiso['oPermitirDevol']),
                'idViaje' => ($idViaje ? $idViaje : ''),
                'esMobil' => $this->request->getUserAgent()->isMobile()
            ]);
            return;
        } else {
            $mdlEnvio = new EnvioMdl();
            $data['titulo'] = $aComplementoTitulo[$tipoaccion] . ' Envio : ' . $idEnvio;
            $data['idEnvio'] = $idEnvio;
            $data['tipoAccion'] = $tipoaccion;
            $data['sObservacionEnvio'] = $_SESSION['vViaje']['envios'][$idEnvio]['observacion'] ?? '';
            $data['regsDet'] = $this->preparaDetalleCapturaEnvio($idEnvio, $tipoaccion == 'c');
            if ($tipoaccion == 'n') {
                $this->preparaDetalleCapturaEnvioAotraSucursal($idEnvio, $data['regsDet']);
                $view = 'Viajes/viajeenviootrasucmtto';
                $data['lstSuc'] = $mdlSucu->getRegistros(false, false, $this->nIdSucursal, true);
            }
            $data['regEnv'] = $mdlEnvio->getEnviosPendientesParaViaje($idEnvio);
            if ($data['regEnv'] == null) {
                $data['fechaSol'] = '';
                $data['fechaAlta'] = '';
                $data['sucursalDestino'] = '';
            } elseif ($data['regEnv']['cOrigen'] == 'traspaso') {
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

    public function imprime($idViaje, $tipo = '1')
    {
        $this->validaSesion();

        $mdlViaje = new ViajeMdl();
        $mdlSucursal = new SucursalMdl();
        $mdlCliente = new ClienteMdl();
        $data = ['dat' => []];

        if ($tipo == '2') {
            $rTmp = $mdlSucursal->select('alsucursal.sDescripcion, alsucursal.sDireccion, rz.sLeyenda')
                ->join('satrazonsocial rz', 'alsucursal.nIdRazonSocial = rz.nIdRazonSocial', 'inner')
                ->where('nIdSucursal', $this->nIdSucursal)->first();
            $data['dat']['datSuc'] = strtoupper($rTmp['sDescripcion'] . '<br>' . $rTmp['sDireccion']);
            $data['dat']['rzLeyenda'] = strtoupper($rTmp['sLeyenda']);
        }

        $mdlPrecio = new PrecioArticuloMdl();
        $mdlVentas = new VentasMdl();
        $mdlVentasDet = new VentasDetMdl();
        $mdlEnvioDireccion = new EnvioDireccionMdl();

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
            $regis[$k]['det'] = $this->preparaDetalleCapturaEnvio($k, true);
            if ($tipo == '2') {
                $rTmp = $mdlCliente->select('nIdCliente, sNombre, sRFC, cCP, sDireccion, nIdTipoLista')
                    ->where('nIdCliente', $r['nIdCliente'])->first();
                $regis[$k]['datCli'] = '<strong>CÓdigo: ' . sprintf('%06d ', intval($rTmp['nIdCliente'])) .
                    $rTmp['sNombre'] . '</strong>' . '<br>' .
                    '  RFC: ' . strtoupper(trim($rTmp['sRFC'])) .
                    '  C.P.: ' . $rTmp['cCP'] . '<br>' .
                    $rTmp['sDireccion'];
                $tipoListaTapado = $rTmp['nIdTipoLista'];
                $rTmp = $mdlEnvioDireccion->where([
                    'nIdDirEntrega' => $r['nIdDirEntrega'],
                    'nIdEnvio <=' => $k
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
                            $a .= '<br>';
                        else
                            $a .= ' ';
                        if ($nContTok == 3) $a .= '. Tel. ';
                        $tok = strtok('||');
                    }
                    $regis[$k]['enviarA'] = $a;
                } else {
                    $regis[$k]['enviarA'] = $r['sEnvEntrega'] . '<br>' .
                        $r['sEnvDireccion'] . ' ' .
                        $r['sEnvColonia'] . '. Tel. ' .
                        $r['sEnvTelefono'] . '<br>' .
                        $r['sEnvReferencia'];
                }
                if ($r['cOrigen'] == 'ventas') {
                    $rventas = $mdlVentas->where('nIdVentas', $r['nIdOrigen'])->first();
                    $porcenComisionBancaria = round(floatval($rventas['nPorcenComision']), 2);
                    $nIdSucursal = $rventas['nIdSucursal'];
                } else {
                    $porcenComisionBancaria = 0;
                    $nIdSucursal = $this->nIdSucursal;
                }
                $bSeEnviaTodo = true;
                // se prepara el detalle
                foreach ($regis[$k]['det'] as $kk => $rd) {
                    $pt = $mdlPrecio->buscaPrecio(
                        $nIdSucursal,
                        $tipoListaTapado,
                        $rd['nIdArticulo'],
                        1
                    );
                    $nPrecioT = round(floatval($pt == null ? 0 : $pt['fPrecioTapado']), 2);
                    $importeComisionBancaria = round($nPrecioT * $porcenComisionBancaria, 2);
                    $nPrecioT = $nPrecioT + $importeComisionBancaria;

                    if ($r['cOrigen'] == 'ventas') {
                        $rvd = $mdlVentasDet->where([
                            'nIdVentas' => $r['nIdOrigen'],
                            'nIdArticulo' => $rd['nIdArticulo']
                        ])->first();
                        if ($rvd === null) {
                            $nPrecio = 0;
                            $nCant = 0;
                        } else {
                            $nPrecio = round(floatval($rvd['nPrecio']), 2);
                            $nCant = round(floatval($rvd['nCant']), 3);
                        }
                        // if (round(floatval($rd['capturada']), 3) < $nCant) $bSeEnviaTodo = false;
                    } else {
                        $nPrecio = 0;
                        // $bSeEnviaTodo = false;
                    }
                    if ($nPrecioT < $nPrecio) $nPrecioT = round($nPrecio * 1.20, 2);
                    $regis[$k]['det'][$kk]['nPrecioT'] = $nPrecioT;
                }
                // $regis[$k]['bSeEnviaTodo'] = $bSeEnviaTodo;
            }
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

        if ($tipo == '1') {
            $data = [
                'dat' => ($mdlViaje->getRegistros($idViaje))[0],
                'det' => $regis,
                'idViaje' => $idViaje
            ];
            $data['dat']['fPeso'] = $nSumPeso;
            $data['aInfoSis'] = $this->dataMenu['aInfoSis'];
            echo view('Viajes/viajeimprimir', $data);
        } else {
            $data['det'] = $regis;
            $data['idViaje'] = $idViaje;
            $data['aInfoSis'] = $this->dataMenu['aInfoSis'];

            echo view('Viajes/imprimeenviosviaje', $data);
        }

        return;
    }

    public function resumen()
    {
        /*
          array[idArt] = [
                    00 - idArticulo
                    01 - cantidad a surtir
                    02 - cant capturada o a enviar en el viaje
                    03 - pesoProducto
                    04 - modoENV
        */
        $this->validaSesion();

        $mdlEnvio = new EnvioMdl();
        $mdlArticulo = new ArticuloMdl();
        $registros = [];
        foreach ($_SESSION['vViaje']['envios'] as $k => $r) {

            $registros[$k] = $mdlEnvio->getEnviosPendientesParaViaje($k);

            $registros[$k]['fechas'] = $this->formateaFechas($registros[$k]);

            $registros[$k]['det'] = [];
            $sumapeso = 0;
            foreach ($r['lstArt'] as $kk => $rr) {
                $rArt = $mdlArticulo->getRegistros($kk);
                $registros[$k]['det'][$kk] = [
                    $kk, $rr[1], $rr[2], round(floatval($rArt['fPeso']), 2),
                    $rr[4], $rArt['sDescripcion'], ($rr[2] * round(floatval($rArt['fPeso']), 2))
                ];
                $sumapeso += $registros[$k]['det'][$kk][2] * $registros[$k]['det'][$kk][3];
            }
            $registros[$k]['peso'] = $sumapeso;
        }
        $data['registros'] = $registros;
        echo view('Viajes/viajeresumen', $data);
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

        // se verifican que los envios a procesar, no los haya cancelado ventas.
        $mdlEnvio->db->transStart();
        $msj = '';
        $vacio = false;
        $aEnviosRevisados = [];
        foreach ($_SESSION['vViaje']['envios'] as $k => $v) {
            if ($v['marca'] == '0') continue;

            $regE = $mdlEnvio->where('nIdEnvio = ' . $k .
                ' AND dtBaja IS NULL for update', null, false)
                ->builder()->get()->getFirstRow('array');
            if ($regE == null) {
                $msj .= ($vacio) ? '<br>' : '';
                $msj .= 'El envio <strong>' . $k . '</strong> ' . 'ya no existe, fue cancelado en ventas.'; // por si lo cancela ventas
                $vacio = true;
            }
            $mdlEnvio->update($k, ['cEstatus' => '5']);
            $aEnviosRevisados[$k] = true;
        }
        if ($msj != '') {
            $msj = 'Se encontraron envios actualizados por ventas.<br>' .
                'Se actualizará el listado de envios.<br>' . $msj;
            $mdlEnvio->db->transRollback();
            return $msj;
        }
        $mdlEnvio->db->transComplete();

        $this->recuperaComprometidosYenvios($mdlInventario, $regsIdEnviosDet, $mdlEnvioDetalle);

        foreach ($_SESSION['vViaje']['envios'] as $k => $v) {
            if ($v['marca'] == '0') continue;
            $a = [
                'nIdEnvio' => $k,
                'cEstatus' => '1',
                'sObservacion' => $v['observacion']
            ];
            if ($nIdxEnv < $nIdxEnvTope) {
                $a['dtAlta'] = (new DateTime())->format('Y-m-d H:i:s');
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
        return '';
    }

    private function creaEnvioEnOtraSucursal($idEnvio)
    {
        // se crea el envio destino
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $mdlSucu = new SucursalMdl();
        $mdlEnvioDireccion = new EnvioDireccionMdl();

        $nIdSucursal = $this->request->getVar('idSucursal');
        $cOrigen = $this->request->getVar('cOrigen');
        $nIdOrigen = $this->request->getVar('nIdOrigen');
        $nIdCliente = $this->request->getVar('nIdCliente');
        $nIdDirEntrega = $this->request->getVar('nIdDirEntrega');
        $arrDet = $this->request->getVar('det');
        $regSuc = $mdlSucu->getRegistros($nIdSucursal, false, false, true);

        $mdlEnvio->db->transStart();
        $regE = $mdlEnvio->where('nIdEnvio', $idEnvio . ' for update ', false)
            ->builder()->get()->getFirstRow('array');
        $msj = '';
        if ($regE == null) {
            $msj = 'Ya no existe el envio, ya fue cancelado. Vuelva a consultar.';
        } elseif ($regE['cEstatus'] == '5') {
            $msj = 'El envio cambió de estado, intente de nuevo guardar';
        }
        if ($msj != '') {
            $mdlEnvio->db->transRollback();
            return 'nooKmsj' . $msj;
        }
        $mdlEnvio->update($idEnvio, ['cEstatus' => '5']);
        $mdlEnvio->db->transComplete();

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

        $refDirec = $mdlEnvioDireccion->where([
            'nIdDirEntrega' => $nIdDirEntrega,
            'nIdEnvio' => $idEnvio
        ])->first();
        if ($refDirec == null) {
            $refDirec = $mdlEnvioDireccion->where([
                'nIdDirEntrega' => $nIdDirEntrega,
                'nIdEnvio <=' => $idEnvio
            ])->orderBy('nIdDirEntrega, nIdEnvio DESC')
                ->limit(1)->first();
            if ($refDirec != null) {
                $mdlEnvioDireccion->insert([
                    'nIdDirEntrega' => $nIdDirEntrega,
                    'nIdEnvio' => $nIdEnvioNuevo,
                    'sDirecEnvio' => $refDirec['sDirecEnvio']
                ]);
            }
        } else {
            $mdlEnvioDireccion->insert([
                'nIdDirEntrega' => $nIdDirEntrega,
                'nIdEnvio' => $nIdEnvioNuevo,
                'sDirecEnvio' => $refDirec['sDirecEnvio']
            ]);
        }

        $this->actualizaEstatusEnvio($idEnvio, $mdlEnvioDet, $mdlEnvio);
    }

    private function reasignaEnvioAVentas($idEnvio, $idVentas)
    {
        //  si el envio no tiene nada que reasignar se devuelve un mensaje
        //  envio, enviodetalle    ventasdet
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $mdlVentasDet = new VentasDetMdl();

        $mdlEnvio->db->transStart();
        $regE = $mdlEnvio->where('nIdEnvio', $idEnvio . ' for update ', false)
            ->builder()->get()->getFirstRow('array');
        // $regE = $mdlEnvio->where('nIdEnvio', $idEnvio)->first();
        $msj = '';
        if ($regE == null) {
            $msj = 'Ya no existe el envio, ya fue cancelado. Vuelva a consultar.';
        } elseif ($regE['cEstatus'] == '5') {
            $msj = 'El envio ' . $idEnvio . ' tiene asignado todo el producto, no hay nada que reasignar';
        }

        if ($msj != '') {
            $mdlEnvio->db->transRollback();
            return 'nooKmsj' . $msj;
        }
        $mdlEnvio->update($idEnvio, ['cEstatus' => '5']);
        $mdlEnvio->db->transComplete();

        $regsE = $mdlEnvioDet->select('enenviodetalle.nIdArticulo, enenviodetalle.fRecibido, enenviodetalle.fPorRecibir, b.nIdArticuloAcumulador, b.nMedida')
            ->join('alarticulo b', 'enenviodetalle.nIdArticulo = b.nIdArticulo', 'inner')
            ->where([
                'enenviodetalle.nIdEnvio' => $idEnvio,
                'enenviodetalle.fPorRecibir >' => 0
            ])->findAll();
        // registros ventas detalle (este proceso solo se realiza en remisiones, por lo tanto siempre es una venta el envio y no un traspaso)
        $rvd = $mdlVentasDet->select('vtventasdet.nIdVentasDet, vtventasdet.nIdArticulo')
            ->join('alarticulo b', 'vtventasdet.nIdArticulo = b.nIdArticulo', 'inner')
            ->where([
                'nIdVentas' => $regE['nIdOrigen'], 'b.cConArticuloRelacionado' => '1'
            ])
            ->findAll();
        $aKeysrvd = array_column($rvd, 'nIdArticulo');
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

            if ($r['nIdArticuloAcumulador'] == '0') {
                // falta agregar si es con articulo relacionado
                $mdlVentasDet->builder()->set([
                    'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nPorRecibir
                ], '', false)->where([
                    'nIdVentas' => $idVentas,
                    'nIdArticulo' => $r['nIdArticulo']
                ])->update();
            } else {
                $keyArt = array_search($r['nIdArticuloAcumulador'], $aKeysrvd);
                if ($keyArt === false) {
                    $mdlVentasDet->builder()->set([
                        'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nPorRecibir
                    ], '', false)->where([
                        'nIdVentas' => $regE['nIdOrigen'],
                        'nIdArticulo' => $r['nIdArticulo']
                    ])->update();
                } else {
                    $nMedida = round(floatval($r['nMedida']) * floatval($r['fPorRecibir']), 2);
                    $mdlVentasDet->builder()->set([
                        'nPorEntregar' => 'vtventasdet.nPorEntregar + ' . $nMedida
                    ], '', false)->where([
                        'nIdVentasDet' => $rvd[$keyArt]['nIdVentasDet']
                    ])->update();
                }
            }
        }
        $regSuma = $mdlEnvioDet->select('SUM(enenviodetalle.fCantidad) AS fCantidad', false)
            ->where('nIdEnvio', $idEnvio)->first();

        if ($regSuma == null || round(floatval($regSuma['fCantidad'] ?? 0), 3) == 0)
            $mdlEnvio->delete($idEnvio);
        else
            $mdlEnvio->set('cEstatus', '5')->where('nIdEnvio', $idEnvio)->update();
        return false;
    }

    private function preparaDetalleCapturaEnvio($idEnvio, $soloConsulta = false)
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
            $regsDet[$k]['sobrecomprometido'] = 0;
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
                    $regsDet[$k]['capturada'] = $soloConsulta ? 0 : $regsDet[$k]['porSurtir'];
                else
                    $regsDet[$k]['capturada'] = $cantCapturada <= $regsDet[$k]['porSurtir'] ? $cantCapturada : $regsDet[$k]['porSurtir'];
            } else
                $regsDet[$k]['capturada'] = $regsDet[$k]['porSurtir'];
            $regsDet[$k]['fRecibido'] -= $regsDet[$k]['comprometido'];

            $regsDet[$k]['disponible'] = 0;
            $regsDet[$k]['sindisponible'] = false; // nota: me sirve para indicador vizual
            if ($r['cSinExistencia'] == '0') {
                $rInv = $mdlInventario->getRegistrosInv($r['nIdArticulo'], $this->nIdSucursal);
                if ($soloConsulta) {
                    $regsDet[$k]['disponible'] = round(floatval($rInv['exiFis']), 3);
                } else {
                    // minimo debe de haber de comprometidos la cantidad de comprometido
                    if (round(floatval($rInv['fCompro']), 3) >= round(floatval($regsDet[$k]['comprometido']), 3)) {
                        $regsDet[$k]['disponible'] = round(floatval($rInv['exiSuc']) + floatval($regsDet[$k]['comprometido']), 3);
                    } else {
                        if (round(floatval($rInv['fSobreCompro']), 3) >= round(floatval($regsDet[$k]['comprometido']), 3)) {
                            $regsDet[$k]['sobrecomprometido'] = round(floatval($rInv['exiSuc']) + floatval($regsDet[$k]['comprometido']), 3);
                        }
                    }
                }
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
            if ($rInv != null) {
                $nSobreComprometido = round(floatval($rInv['fSobreComprometido']), 3);
                $nComprometido = round(floatval($rInv['fComprometido']), 3);
            } else {
                $nSobreComprometido = 0;
                $nComprometido = 0;
            }

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

    public function buscaArticuloDeEnvio($idEnvio, $idArticulo)
    {
        $mdlviajeEnvio = new ViajeEnvioMdl();
        $mdlEnvioD = new EnvioDetalleMdl();




        $regs = $mdlviajeEnvio->getArticuloEnEnvio($idEnvio, $idArticulo);

        // se realizan las validaciones correspondientes
        $msj = '';
        if (count($regs) == 0) {
            $regs = $mdlEnvioD
                ->select('a.nIdSucursal, s.sDescripcion AS nomSuc, enenviodetalle.fCantidad, enenviodetalle.nIdArticulo')
                ->join('enenvio a', 'enenviodetalle.nIdEnvio = a.nIdEnvio', 'inner')
                ->join('alsucursal s', 'a.nIdSucursal = s.nIdSucursal', 'inner')
                ->where('enenviodetalle.nIdEnvio', $idEnvio)
                ->findAll();
            $nCont = 0;
            $bExiste = false;
            foreach ($regs as $r) {
                $nCont++;
                if ($r['nIdSucursal'] != $this->nIdSucursal) {
                    $msj = 'El envio no pertenece a esta sucursal, es de ' . $r['nomSuc'];
                    break;
                }
                if ($r['nIdArticulo'] == $idArticulo) $bExiste = true;
            }
            if ($msj == '') {
                if ($nCont == 0) $msj = 'El folio de envio ' . $idEnvio . ' no existe.';
                elseif (!$bExiste) $msj = 'El artículo no existe en el envio ' . $idEnvio;
                else
                    $msj = 'El envio no esta asignado o programado en un viaje.';
            }
        } else {
            $nCont = -1;
            $nContModoENV = 0;
            $indiceUltimoModoENV = 0;
            foreach ($regs as $r) {
                if ($r['nIdSucursal'] != $this->nIdSucursal) {
                    $msj = 'El envio no pertenece a esta sucursal es de ' . $r['nomSuc'];
                    break;
                }
                if (!in_array($r['cEstatus'], ['0', '1'])) {
                    $msj = 'El estado del viaje debe ser "Programado" o "Asignado Para Cargar" para poder asignar el articulo en la compra.';
                    break;
                }
                $nCont++;
                if ($r['cModoEnv'] == '1') {
                    $nContModoENV++;
                    $indiceUltimoModoENV = $nCont;
                }
            }
            if ($nContModoENV == 0 && $msj == '') {
                $msj = 'El articulo no esta marcado como ENV en el viaje. Debe marcarse primero.';
            }
        }
        /*
 'enviajeenvio.nIdViajeEnvio, ' .
            'enviajeenvio.nIdEnvio, enviajeenvio.nIdViaje, d.cModoEnv, d.fPorRecibir,' .
            'd.nIdViajeEnvioDetalle, v.cEstatus, v.nIdSucursal, s.sDescripcion AS nomSuc')
            ->join('enviajeenviodetalle d', 'enviajeenvio.nIdViajeEnvio = d.nIdViajeEnvio', 'inner')
            ->join('enviaje v', 'enviajeenvio.nIdViaje = v.nIdViaje', 'inner')
            ->join('alsucursal s', 'v.nIdSucursal = s.nIdSucursal', 'inner')
            ->where('enviajeenvio.nIdEnvio', $idEnvio)
            ->where('d.nIdArticulo', $idArticulo)
            ->where('d.fPorRecibir >', 0)
*/
        if ($msj == '') {
            if ($nContModoENV > 1)
                return json_encode(['Ok' => 2, 'registro' => view('almacen/selectviajeenvio', ['regs' => $regs])]);
            else
                return json_encode(['Ok' => 1, 'registro' => $regs[$indiceUltimoModoENV]]);
        }
        return json_encode(['Ok' => 0, 'msj' => $msj]);
    }
}
