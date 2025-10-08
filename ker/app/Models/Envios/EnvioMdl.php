<?php

namespace App\Models\Envios;

use CodeIgniter\Model;

class EnvioMdl extends Model
{
    protected $table = 'enenvio';

    protected $allowedFields = [
        'nIdOrigen', 'cOrigen', 'nIdChofer',
        'nIdSucursal', 'nIdCliente', 'dSolicitud',
        'dAsignacion', 'dEntrega', 'sObservacion',
        'nIdDirEntrega', 'cEstatus'
    ];

    protected $primaryKey = 'nIdEnvio';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($idSuc, $id = 0)
    {
        if($id == 0) 
            return $this->where(['idSucursal' => $idSuc])->findAll();
        return $this->where(['idEnvio' => $id])->first();
    }

    public function getViajePendiente($idSuc, $id = 0, $paginado = 0)
    {
        $model = $this->select(
            "enenvio.nIdEnvio, enenvio.nIdOrigen, enenvio.cOrigen, enenvio.nIdSucursal, " .
                "enenvio.dSolicitud, enenvio.dAsignacion, enenvio.dEntrega, " .
                "s.sDescripcion, s.sDireccion sEnviarDesde, " .
                "c.nIdCliente, c.sNombre, 0 nIdViaje, 0 fPeso, '' sObservacion, " .
                "d.sEnvDireccion, d.sEnvReferencia, d.sEnvEntrega, d.sEnvColonia, d.sEnvTelefono, 0 as nIdViajeEnvio,".
                "IFNULL(vt.nFolioRemision, 0) as nFolioRemision, IFNULL(tr.nIdTraspaso, 0) as nFolioTraspaso,".
                "IFNULL(vt.dtAlta, '') as dAltaVenta, IFNULL(tr.dtAlta, 0) as dAltaTraspaso"
        )
            ->join("alsucursal s", "s.nIdSucursal = enenvio.nIdSucursal", "left")
            ->join("vtcliente c", "c.nIdCliente = enenvio.nIdCliente", "left")
            ->join("vtdirentrega d", "d.nIdDirEntrega = enenvio.nIdDirEntrega", "left")
            ->join("vtventas vt", "enenvio.nIdOrigen = vt.nIdVentas", "left")
            ->join("altraspaso tr", "enenvio.nIdOrigen = tr.nIdTraspaso", "left");
        $aWhere = [];
        $aWhere = ['enenvio.nIdSucursal' => $idSuc, 'enenvio.cEstatus <' => 5];
        
        /* * /
        $qry = $model->builder()->where($aWhere)->getCompiledSelect(false);
        //
        var_dump($qry);
        exit;
        / * */

        if ($id == 0) {
            if ($paginado == 0) {
                $resp = $model->where($aWhere)->findAll();
            } else {
                $resp = $model->where($aWhere)->paginate($paginado);
            }
        } else {
            $resp = $model->where(['enenvio.nIdEnvio' => $id])->first();
        }
        return $resp;
    }

    public function getEnviosViajes($idSuc, $id = 0, $idViaje = 0, $cEstatus = '')
    {
        $paginado = 0;
        //$sWhereJoin = $idViaje > 0 ? "and ve.nIdViaje = $idViaje" : "";
        $this->select(
            "enenvio.nIdEnvio, enenvio.nIdOrigen, enenvio.cOrigen, enenvio.nIdSucursal, " .
                "enenvio.dSolicitud, enenvio.dAsignacion, enenvio.dEntrega, " .
                "s.sDescripcion, s.sDireccion sEnviarDesde, " .
                "c.nIdCliente, c.sNombre,  " .
                "ve.nIdViajeEnvio, IFNULL(ve.nIdViaje, 0) AS nIdViaje, ve.fPeso, ve.sObservacion, ve.dtAlta AS dAsignado," .
                "d.sEnvDireccion, d.sEnvReferencia, d.sEnvEntrega, d.sEnvColonia, d.sEnvTelefono,".
                "IFNULL(vt.nFolioRemision, 0) as nFolioRemision, IFNULL(tr.nIdTraspaso, 0) as nFolioTraspaso,".
                "IFNULL(vt.dtAlta, '') as dAltaVenta, IFNULL(tr.dtAlta, '') as dAltaTraspaso"
        )
            ->join("alsucursal s", "s.nIdSucursal = enenvio.nIdSucursal", "left")
            ->join("vtcliente c", "c.nIdCliente = enenvio.nIdCliente", "left")
            ->join("vtdirentrega d", "d.nIdDirEntrega = enenvio.nIdDirEntrega", "left")
            ->join("vtventas vt", "enenvio.nIdOrigen = vt.nIdVentas", "left")
            ->join("altraspaso tr", "enenvio.nIdOrigen = tr.nIdTraspaso", "left")
            ->join("enviajeenvio ve", "ve.nIdEnvio = enenvio.nIdEnvio AND ve.dtBaja IS NULL", "left");
        $aWhere = "( ve.nIdViaje = $idViaje ) OR " .
                  "(enenvio.nIdSucursal = $idSuc AND enenvio.cEstatus < '5')"  
                  ;
        $this->where($aWhere);

        /* * /
        echo $this->builder()->getCompiledSelect();
        echo '<BR>-----------------<P>';
        echo $this->builder()->where($aWhere)->getCompiledSelect();
        exit;
        / * */
        if ($id == 0) {
            if ($paginado == 0) {
                //$sql = $model->builder()->where($aWhere)->getCompiledSelect();
                //echo '1 <BR>'; var_dump($sql);
                $resp = $this->findAll();
            } else {
                //$sql = $model->builder()->where($aWhere)->getCompiledSelect();
                //echo '2 <BR>'; var_dump($sql);
                $resp = $this->paginate($paginado);
            }
        } else {
            $resp = $this->first();
        }
        return $resp;
    }

    public function getRegistrosByField($field = 'nIdEnvio', $val = null)
    {
        return $this->where([$field => $val])->findAll();
    }

    public function enviosdet($idSuc = false, $id = 0, $paginado = 0, $aCond = false)
    {
        $model = $this->select(
            "enenvio.nIdEnvio, enenvio.nIdOrigen, enenvio.cOrigen, enenvio.nIdSucursal, enenvio.cEstatus, " .
                "date_format(enenvio.dSolicitud,'%Y-%m-%d') dSolicitud, " .
                "date_format(enenvio.dAsignacion,'%Y-%m-%d') dAsignacion, " .
                "date_format(enenvio.dEntrega,'%Y-%m-%d') dEntrega, " .
                "s.sDescripcion, s.sDireccion, " .
                "c.nIdCliente, c.sNombre,  " .
                "d.sEnvDireccion, d.sEnvReferencia, d.sEnvEntrega, d.sEnvColonia, d.sEnvTelefono, " .
                "a.nIdArticulo, a.sDescripcion sArticulo, " .
                "format(ed.fCantidad,0) fCantidad, format(ed.fRecibido,0) fRecibido, format(ed.fPorRecibir,0) fPorRecibir, ed.cModoEnv,".
                "IFNULL(vt.nFolioRemision, 0) as nFolioRemision, IFNULL(tr.nIdTraspaso, 0) as nFolioTraspaso,".
                "IFNULL(vt.dtAlta, '') as dAltaVenta, IFNULL(tr.dtAlta, 0) as dAltaTraspaso"
        )
            ->join("enenviodetalle ed", "ed.nIdEnvio = enenvio.nIdEnvio", "left")
            ->join("alarticulo a", "a.nIdArticulo = ed.nIdArticulo", "left")
            ->join("alsucursal s", "s.nIdSucursal = enenvio.nIdSucursal", "left")
            ->join("vtcliente c", "c.nIdCliente = enenvio.nIdCliente", "left")
            ->join("vtdirentrega d", "d.nIdDirEntrega = enenvio.nIdDirEntrega", "left")
            ->join("vtventas vt", "enenvio.nIdOrigen = vt.nIdVentas", "left")
            ->join("altraspaso tr", "enenvio.nIdOrigen = tr.nIdTraspaso", "left")
            ->orderBy('enenvio.nIdEnvio,ed.nIdArticulo')
            //->where("enenvio.cEstatus !=","3")
        ;
        $aWhere = [];
        if ($idSuc !== false)  $aWhere = ['enenvio.nIdSucursal' => $idSuc];
        if ($aCond !== null) {
            switch ($aCond['Edo']) {
                case '0':
                    $aWhere['enenvio.cEstatus <'] = '5';
                    break;
                case '1':
                    $aWhere['enenvio.cEstatus >'] = '3';
                    break;
            }
            if ($aCond['dIni'] != null) {
                $aWhere['enenvio.dSolicitud >='] = $aCond['dIni'];
                $aWhere['enenvio.dSolicitud <='] = $aCond['dFin'];
            }
        }
        if ($id == 0) {
            if ($paginado == 0) {
                $resp = $model->where($aWhere)->findAll();
            } else {
                $resp = $model->where($aWhere)->paginate($paginado, 'detalle');
            }
        } else {
            $resp = $model->where(['enenvio.nIdEnvio' => $id])->first();
        }
        //echo ($model->where($aWhere)->builder()->getCompiledSelect());
        return $resp;
    }

    public function enviosart($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $model = $this->select(
            "enenvio.nIdEnvio, nIdOrigen, cOrigen, enenvio.nIdSucursal, enenvio.cEstatus, " .
                "date_format(dSolicitud,'%Y-%m-%d') dSolicitud, " .
                "date_format(dAsignacion,'%Y-%m-%d') dAsignacion, " .
                "date_format(dEntrega,'%Y-%m-%d') dEntrega, " .
                "s.sDescripcion, s.sDireccion, " .
                "a.nIdArticulo, a.sDescripcion sArticulo, a.nIdArtClasificacion, " .
                "format(sum(ed.fCantidad),0) fCantidad, " .
                "format(sum(ed.fRecibido),0) fRecibido, " .
                "format(sum(ed.fPorRecibir),0) fPorRecibir"
        )
            ->join("enenviodetalle ed", "ed.nIdEnvio = enenvio.nIdEnvio", "left")
            ->join("alarticulo a", "a.nIdArticulo = ed.nIdArticulo", "left")
            ->join("alsucursal s", "s.nIdSucursal = enenvio.nIdSucursal", "left")
            ->orderBy('a.nIdArtClasificacion, ed.nIdArticulo')
            ->groupBy('ed.nIdArticulo')
            //->where("enenvio.cEstatus !=", "3")
            ;
        $aWhere = [];
        if ($idSuc !== false)  $aWhere = ['enenvio.nIdSucursal' => $idSuc];
        if ($aCond !== null) {
            switch ($aCond['Edo']) {
                case '0':
                    $aWhere['enenvio.cEstatus <'] = '5';
                    break;
                case '1':
                    $aWhere['enenvio.cEstatus >'] = '3';
                    break;
            }
            if ($aCond['dIni'] != null) {
                $aWhere['enenvio.dSolicitud >='] = $aCond['dIni'];
                $aWhere['enenvio.dSolicitud <='] = $aCond['dFin'];
            }
        }
        //$bldr = $this->builder()->where($aWhere);
        //echo($bldr->getCompiledSelect(false));

        if ($id == 0) {
            if ($paginado == 0) {
                $resp = $model->where($aWhere)->findAll();
            } else {
                $resp = $model->where($aWhere)->paginate($paginado, 'articulos');
            }
        } else {
            $resp = $model->where(['enenvio.nIdEnvio' => $id])->first();
        }
        // echo ($model->where($aWhere)->builder()->getCompiledSelect());
        return $resp;
    }

    public function enviosclientes($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $model = $this->select(
            "enenvio.nIdEnvio, enenvio.nIdOrigen, enenvio.cOrigen, enenvio.nIdSucursal, enenvio.cEstatus,  " .
                "date_format(enenvio.dSolicitud,'%Y-%m-%d') dSolicitud, " .
                "date_format(enenvio.dAsignacion,'%Y-%m-%d') dAsignacion, " .
                "date_format(enenvio.dEntrega,'%Y-%m-%d') dEntrega, " .
                "s.sDescripcion, s.sDireccion, " .
                "c.nIdCliente, c.sNombre,  " .
                "d.sEnvDireccion, d.sEnvReferencia, d.sEnvEntrega, d.sEnvColonia, d.sEnvTelefono,". 
                "IFNULL(vt.nFolioRemision, 0) as nFolioRemision, IFNULL(tr.nIdTraspaso, 0) as nFolioTraspaso,".
                "IFNULL(vt.dtAlta, '') as dAltaVenta, IFNULL(tr.dtAlta, 0) as dAltaTraspaso"
        )
            ->join("alsucursal s", "s.nIdSucursal = enenvio.nIdSucursal", "left")
            ->join("vtcliente c", "c.nIdCliente = enenvio.nIdCliente", "left")
            ->join("vtdirentrega d", "d.nIdDirEntrega = enenvio.nIdDirEntrega", "left")
            ->join("vtventas vt", "enenvio.nIdOrigen = vt.nIdVentas", "left")
            ->join("altraspaso tr", "enenvio.nIdOrigen = tr.nIdTraspaso", "left")
            ->orderBy('enenvio.nIdEnvio')
            //->where("enenvio.cEstatus !=", "3")
            ;
        $aWhere = [];
        if ($idSuc !== false)  $aWhere = ['enenvio.nIdSucursal' => $idSuc];
        if ($aCond !== null) {
            switch ($aCond['Edo']) {
                case '0':
                    $aWhere['enenvio.cEstatus <'] = '5';
                    break;
                case '1':
                    $aWhere['enenvio.cEstatus >'] = '3';
                    break;
            }
            if ($aCond['dIni'] != null) {
                $aWhere['enenvio.dSolicitud >='] = $aCond['dIni'];
                $aWhere['enenvio.dSolicitud <='] = $aCond['dFin'];
            }
        }
        /*
        echo $model->builder()->where($aWhere)->getCompiledSelect();
        echo '<BR>-----------------<P>';
        echo $model->builder()->where($aWhere)->getCompiledSelect(false);
        exit;
        */
        if ($id == 0) {
            if ($paginado == 0) {
                $resp = $model->where($aWhere)->findAll();
            } else {
                $resp = $model->where($aWhere)->paginate($paginado, 'clientes');
            }
        } else {
            $resp = $model->where(['enenvio.nIdEnvio' => $id])->first();
        }
        //echo ($model->where($aWhere)->builder()->getCompiledSelect());
        return $resp;
    }
}
/***********
 * 

CREATE TABLE `enenvio` (
  `nIdEnvio` int(11) NOT NULL AUTO_INCREMENT,
  `nIdOrigen` int(11) DEFAULT NULL,
  `cOrigen` varchar(45) DEFAULT NULL COMMENT 'Puede ser de ventas/traspaso almacen',
  `nIdChofer` int(11) DEFAULT NULL,
  `dAsignacion` date DEFAULT NULL,
  `dEntrega` date DEFAULT NULL,
  `sObservacion` varchar(500) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdEnvio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
