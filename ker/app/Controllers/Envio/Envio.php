<?php

namespace App\Controllers\Envio;

use App\Controllers\BaseController;

use App\Models\Envios\EnvioMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Almacen\InventarioMdl;
use App\Models\Viajes\EnvioDireccionMdl;

class Envio extends BaseController
{

    public function index($operacion = 'envio')
    {
        $this->validaSesion();

        $aWhere = [
            'Edo' => $this->request->getVar('cEstado') ?? '0',
            'dIni' => $this->request->getVar('dIni'),
            'dFin' => $this->request->getVar('dFin')
        ];

        $model = new EnvioMdl();
        $envios =  $model->enviosdet($this->nIdSucursal, 0, 0, $aWhere);

        $mdlEnvioDireccion = new EnvioDireccionMdl();

        $nIdEnvio = '0';
        foreach ($envios as $k => $v) {
            if ($v['nIdEnvio'] != $nIdEnvio) {
                $nIdEnvio = $v['nIdEnvio'];
                $direccionEnvio = $this->buscaDireccionDeEnvio($mdlEnvioDireccion, $nIdEnvio, $v['nIdDirEntrega']);
                if ($direccionEnvio !== false) {
                    $envios[$k]['sEnvEntrega'] = $direccionEnvio[0] ?? '';
                    $envios[$k]['sEnvDireccion'] = $direccionEnvio[1] ?? '';
                    $envios[$k]['sEnvColonia'] = $direccionEnvio[2] ?? '';
                    $envios[$k]['sEnvTelefono'] = $direccionEnvio[3] ?? '';
                    $envios[$k]['sEnvReferencia'] = $direccionEnvio[4] ?? '';
                }
            }
        }

        $modelart = new EnvioMdl();
        $arts = $modelart->enviosart($this->nIdSucursal, 0, 0, $aWhere);

        $modelcliente = new EnvioMdl();

        $clientes = $modelcliente->enviosclientes($this->nIdSucursal, 0, 0, $aWhere);

        $data = [
            'registros' => $clientes,
            'articulos' => $arts,
            'detallenvio' => $envios,
            'titulo' => 'Envios pendientes',
            'operacion' => 'envio',
            'tipoaccoin' => 'a',
            'modo' => 'a',
            'frmURL' => '#',
            'aWhere' => $aWhere,
        ];

        if ($operacion === 'p')
            $data['printerMode'] = 'true';
        echo view('templates/header', $this->dataMenu);
        echo view('envios/envios', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function accion($tipoaccion, $id = 0)
    {
        $mdl = new EnvioMdl();
        $mdldet = new EnvioDetalleMdl();

        session();
        $this->validaSesion();

        switch ($tipoaccion) {
            case 'b':
                
                $regs = $mdl->getRegistrosByField('nIdEnvio', $id);
                $registro = $regs[0];
                $regdet = $mdldet->getRegistrosByField('nIdEnvio', $id);
                
                // echo '<BR>REGISTRO <BR>';
                // var_dump($registro);
                
                $vtdet = new VentasDetMdl();
                $inventario = new InventarioMdl();

                foreach ($regdet as $rd) {
                    $vtdet->actualizaSaldo($registro['nIdOrigen'], $rd['nIdArticulo'], $rd['fPorRecibir'], '+');
                    $inventario->updtArticuloSucursalInventario($this->nIdSucursal, $rd['nIdArticulo'], 0, -1 * $rd['fPorRecibir']);
                    $mdldet->delete($rd['nIdEnvioDetalle']);

                    // echo '<BR>          Detalle ' . ' ' . $registro['nIdOrigen'] . ' ' . $rd['nIdArticulo'] . ' ' . $rd['fPorRecibir'] . ' <BR>';
                    // var_dump($rd);
                }
                $mdl->delete($registro['nIdEnvio']);

                //exit();
                break;
        }
        return 'oK';
    }

    protected function buscaDireccionDeEnvio(EnvioDireccionMdl &$mdlEnvioDireccion, $idEnvio, $idDirEntrega)
    {
        $aRet = false;
        $rTmp = $mdlEnvioDireccion->where([
            'nIdDirEntrega' => $idDirEntrega,
            'nIdEnvio <=' => $idEnvio
        ])->orderBy('nIdDirEntrega, nIdEnvio DESC')
            ->limit(1)->first();
        if ($rTmp != null) {
            $tok = strtok($rTmp['sDirecEnvio'], '||');
            $a = [];
            $nContTok = 0;
            $nInd = -1;
            while ($tok !== false) {
                $nContTok++;
                $nInd++;
                $a[$nInd] = $tok;
                $tok = strtok('||');
            }
            $aRet = $a;
        }
        return $aRet;
    }
}
