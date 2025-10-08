<?php

namespace App\Controllers\Viajes;

use App\Controllers\BaseController;
use App\Models\Almacen\InventarioMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Viajes\EnvioDetalleMdl;
use App\Models\Viajes\EnvioMdl;
use App\Models\Viajes\ViajeEnvioDetalleMdl;
use App\Models\Viajes\ViajeEnvioMdl;
use App\Models\Viajes\ViajeMdl;
use DateTime;

class  ViajeBase extends BaseController
{
    protected function leeRegistrosFiltrados(&$regs, &$pager, $opcionInicial)
    {
        $opcion = $this->request->getVar('inputGroupSelectOpciones') ?? $opcionInicial;
        $aCond = [
            'opc' => $opcion
        ];
        $mdlViaje = new ViajeMdl();
        if ($opcion == '0' || $opcion == '1' || $opcion == '2') {
            $regs = $mdlViaje->getRegistros(
                false,
                ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal,
                8,
                $aCond
            );
        } elseif ($opcion == '3' || $opcion == '7') {
            $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'));
            $aCond['dIni'] = $f[0]->format('Y-m-d');
            $aCond['dFin'] = $f[1]->format('Y-m-d');
            $regs = $mdlViaje->getRegistros(
                false,
                ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal,
                8,
                $aCond
            );
            $data['dFecIni'] = $f[0]->format('Y-m-d');
            $data['dFecFin'] = $f[1]->format('Y-m-d');
        } elseif ($opcion == '4') {
            $regs = $mdlViaje->getRegistros(
                $this->request->getVar('nFolio'),
                ($this->nIdUsuario == '1' || isset($this->aPermiso['oVerTodasSuc'])) ? false : $this->nIdSucursal,
                8
            );
        } elseif ($opcion == '5') {
            $mdlVentas = new VentasMdl();
            $regVentas = $mdlVentas->select('nIdVentas')->where('nIdSucursal', $this->nIdSucursal)
                ->where('nFolioRemision', $this->request->getVar('nFolio'))
                ->where('cEdo <>', '5')->first();
            if ($regVentas != null) {
                $regs = $mdlViaje->getRegsPorIdVentas($regVentas['nIdVentas'], $this->nIdSucursal, 8);
            }
        } elseif ($opcion == '6') {
            $regs = $mdlViaje->getRegsPorIdEnvio($this->request->getVar('nFolio'), $this->nIdSucursal, 8);
        }
        $pager = $mdlViaje->pager;
    }

    protected function initArrayEnvios($idViaje = false, $soloParaConsulta = false)
    {
        $mdlEnvio = new EnvioMdl();
        $regs = $mdlEnvio->getEnviosPendientesParaViaje(false, $this->nIdSucursal, $idViaje, $soloParaConsulta);
        $regDest = [];
        // $nIndCantCalculoPeso = ($soloParaConsulta ? 5 : 2); // inicializo el indice para calculo del peso.
        foreach ($_SESSION['vViaje']['envios'] as $k => $v) {
            // inicializo la marca de producto asignado del envio a no asignado
            $_SESSION['vViaje']['envios'][$k]['marca'] = '0';
            // se suman los pesos capturados
            $nSumPeso = 0;
            $nSumCantSeleccionada = 0;
            foreach ($v['lstArt'] as $vv) {
                $nSumPeso += $vv[2] * $vv[3];
                $nSumCantSeleccionada += $vv[2];
            }
            $nSumCantSeleccionada = round($nSumCantSeleccionada, 3);
            //  $v contiene [idArt, cantAsurtir, cantCapturada, pesoProducto, modoenv, comprometido, cSinExistencia]
            // el comprometido se inicializa con el campo enviajeenviodetalle.fPorRecibir al editar el envio.
            foreach ($regs as $kk => $r) {
                if ($r['nIdEnvio'] == $k && $nSumCantSeleccionada > 0) {
                    $regs[$kk]['marca'] = '1';
                    $regDest[$k] = $r;
                    $regDest[$k]['marca'] = '1';
                    $regDest[$k]['conDevolucion'] = $_SESSION['vViaje']['envios'][$k]['conDevolucion'];
                    if ($soloParaConsulta) $regDest[$k]['observa'] = $_SESSION['vViaje']['envios'][$k]['observacion'];
                    $regDest[$k]['peso'] = round($nSumPeso, 3);
                    $regDest[$k]['fechas'] = $this->formateaFechas($r);
                    $_SESSION['vViaje']['envios'][$k]['marca'] = '1';
                    break;
                }
            }
        }
        foreach ($regs as $k => $r) {
            if ($r['marca'] == '1') continue;
            $regDest[$r['nIdEnvio']] = $r;
            $regDest[$r['nIdEnvio']]['fechas'] = $this->formateaFechas($r);
        }
        return $regDest;
    }

    protected function cargaEnArrayEnviosDelViaje($idViaje)
    {
        // detalle de los envios
        $mdlEnvioViaje = new ViajeEnvioMdl();
        $regsdet = $mdlEnvioViaje->getEnviosViajeDet($idViaje);
        $idenvio = '0';
        foreach ($regsdet as $r) {
            if ($r['nIdEnvio'] != $idenvio) {
                $idenvio = $r['nIdEnvio'];
                $_SESSION['vViaje']['envios'][$idenvio] = [
                    'nIdViajeEnvio' => $r['nIdViajeEnvio'],
                    'observacion' => $r['sObservacion'],
                    'peso' => 0,
                    'marca' => '0',
                    'conDevolucion' => '0',
                    'lstArt' => []
                ];
                $_SESSION['vViaje']['enviosOri'][$idenvio] = $idenvio;
            }
            //  contiene [idArt, cantAsurtir, cantCapturada, pesoProducto, modoenv, comprometido, cSinExistencia]
            // enviajeenvio.nIdEnvio, ved.nIdArticulo, ved.fPorRecibir, a.fPeso AS pesoArt, ved.cModoEnv
            $_SESSION['vViaje']['envios'][$idenvio]['lstArt'][$r['nIdArticulo']] = [
                $r['nIdArticulo'],
                0,
                round(floatval($r['fPorRecibir']), 3),
                round(floatval($r['pesoArt']), 3),
                $r['cModoEnv'],
                round(floatval($r['fPorRecibir']), 3),
                $r['cSinExistencia'],
                $r['nIdViajeEnvioDetalle'],
                0
            ];
            if($r['idDevolucion'] != '0') $_SESSION['vViaje']['envios'][$idenvio]['conDevolucion'] = '1';
        }
    }

    protected function formateaFechas($r)
    {
        $ret = '';
        if ($r['cOrigen'] == 'ventas') {
            $ret = (new DateTime($r['dAltaVenta']))->format('d-m-Y');
        } else {
            $ret = (new DateTime($r['dAltaTraspaso']))->format('d-m-Y') .
                ' <b>/</b> ' .
                (new DateTime($r['dSolTraspaso']))->format('d-m-Y');
        }
        return $ret;
    }

    protected function actualizaEstatusEnvio($nIdEnvio, EnvioDetalleMdl &$mdlEnvioDetalle, EnvioMdl &$mdlEnvio)
    {
        // se actualiza el estatus del envio 
        $regDetSum = $mdlEnvioDetalle
            ->select('SUM(fCantidad) as nCant, SUM(fRecibido) as reci', false)
            ->where('nIdEnvio', $nIdEnvio)
            ->where('nIdArticulo >', 0)
            ->first();
        $nCant = round(floatval($regDetSum['nCant']), 3);
        $nReci = round(floatval($regDetSum['reci']), 3);
        $cEstatus = '1';
        if ($nCant == $nReci) $cEstatus = '5';
        elseif ($nReci == 0) $cEstatus = '1';
        elseif ($nCant > $nReci) $cEstatus = '4';
        $mdlEnvio->update($nIdEnvio, ['cEstatus' => $cEstatus]);
    }

    protected function comprometeInventario(InventarioMdl &$mdlInventario, $idArt, $nCant)
    {
        $mdlInventario->db->transStart();

        $rInv = $mdlInventario->getArticuloSucursal($idArt, $this->nIdSucursal, true, true);
        $disponibleEnSuc =  round(floatval($rInv['fExistencia']), 3) - round(floatval($rInv['fComprometido']), 3);
        $nSobreComprometido = round(floatval($rInv['fSobreComprometido']), 3);
        if ($disponibleEnSuc < $nCant) {
            $nComprometido = round(floatval($rInv['fExistencia']), 3);
            $nSobreComprometido += round($nCant - $disponibleEnSuc, 3);
            $mdlInventario->set('fComprometido', $nComprometido)
                ->set('fSobreComprometido', $nSobreComprometido);
        } else {
            $nComprometido = round(floatval($rInv['fComprometido']), 3) + $nCant;
            $mdlInventario->set('fComprometido', $nComprometido);
        }
        // comprometemos el inventario
        $mdlInventario
            ->where([
                'nIdSucursal' => $this->nIdSucursal,
                'nIdArticulo' => intval($idArt)
            ])->update();


        $mdlInventario->db->transComplete();
    }
}
