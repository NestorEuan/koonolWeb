<?php

namespace App\Models\Envios;

use CodeIgniter\Model;

class ViajeMdl extends Model
{
    protected $table = 'enviaje';

    protected $allowedFields = [
        'nIdChofer', 'nIdSucursal', 'nViaje',
        'dViaje', 'fPeso', 'sObservacion',
        'cEstatus'
    ];

    protected $primaryKey = 'nIdViaje';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $model = $this->select(
            "nIdViaje, dViaje, enviaje.nIdChofer, " .
                "sObservacion, sChofer, cEstatus, " .
                "enviaje.nIdSucursal, sDescripcion, fPeso"
        )
            ->join("enchofer c", "c.nIdChofer = enviaje.nIdChofer")
            ->join("alsucursal s", "s.nIdSucursal = enviaje.nIdSucursal")
            //->having(["cOrigen" => "traspaso"])->paginate(8)
            ///->paginate(8)
        ;
        $aWhere = [];
        if ($idSuc !== false)  $aWhere = ['enviaje.nIdSucursal' => $idSuc];
        if ($aCond != null) {
            if ($aCond['Edo'] != null)
                if ($aCond['Edo'] != '9')
                    $aWhere['cEstatus'] = $aCond['Edo'];
            if ($aCond['dIni'] != null) {
                $aWhere['dViaje >='] = $aCond['dIni'];
                $aWhere['dViaje <='] = $aCond['dFin'];
            }
        }
        /*
        // "enenvio.nIdOrigen = (SELECT vtventas.nIdVentas FROM vtventas WHERE vtventas.nFolioRemision = 254 ) AND enenvio.cOrigen = 'ventas'";

        $aWhere = "
        enviaje.nIdViaje IN
        (
            SELECT enviajeenvio.nIdViaje FROM enviajeenvio  WHERE
            enviajeenvio.nIdEnvio =
            (
                SELECT enenvio.nIdEnvio FROM enenvio WHERE 
                enenvio.nIdOrigen = 
                (SELECT vtventas.nIdVentas FROM vtventas
                WHERE vtventas.nFolioRemision = 254)
                AND
                enenvio.cOrigen = 'ventas'
                )
                )
                ";
        //$sql = $model->where($aWhere)->builder()->getCompiledSelect(false);
        */
        if ($id == 0) {
            if ($paginado == 0) {
                $resp = $model->where($aWhere)->findAll();
            } else {
                $resp = $model->where($aWhere)->paginate($paginado);
            }
        } else {
            $resp = $model->where(['enviaje.nIdViaje' => $id])->first();
        }
        return $resp;
    }

    public function updatePeso($idViaje, $fImporte)
    {
        $this->set('fPeso', 'fPeso + ' . $fImporte, false)
            ->where(['nIdViaje' => $idViaje])
            ->update();
    }

    public function getRegistrosByField($field = 'nIdEnvio', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }

    public function envios()
    {
        $model = $this->select(
            "nIdEnvio, nIdOrigen, cOrigen, enenvio.nIdCliente, enenvio.nIdSucursal, " .
                "dAsignacion, dEntrega, " .
                "sDescripcion, s.sDireccion sEnviarDesde, " .
                "c.nIdCliente nIdProveedor, c.sNombre, " .
                "d.sDireccion sEnviarADir, d.sReferencia"
        )
            ->join("alsucursal s", "s.nIdSucursal = enenvio.nIdSucursal", "left")
            ->join("vtcliente c", "c.nIdCliente = enenvio.nIdCliente", "left")
            ->join("vtclientedireccion d", "d.nIdCliente = c.nIdCliente", "left")
            //->having(["cOrigen" => "traspaso"])->paginate(8)
            ->paginate(8)
            // ->getCompiledSelect(false)
        ;
        //echo $model . '<BR>';
        //exit;
        return $model;
    }

    public function getImpresion($id)
    {
        $this->select(
            'enviaje.nIdViaje, enviaje.cEstatus, ve.nIdEnvio, ved.nIdArticulo, a.sDescripcion sArticulo, ' .
                'ved.fPorRecibir, e.nIdOrigen, e.cOrigen, d.sEnvEntrega, d.sEnvColonia, ' .
                'd.sEnvDireccion, d.sEnvReferencia, d.sEnvTelefono, c.nIdCliente, d.nIdDirEntrega, ' .
                'cf.sChofer, ' .
                'enviaje.dViaje, IF(ve.nIdEnvio IS NULL, 0, ' .
                '(SELECT vtventas.nFolioRemision FROM vtventas WHERE vtventas.nIdVentas = e.nIdOrigen)) as nFolioRemision',
            false
        )
            ->join('enviajeenvio ve', 've.nIdViaje = enviaje.nIdViaje AND ve.dtBaja IS NULL', 'left')
            ->join('enviajeenviodetalle ved', 'ved.nIdViajeEnvio = ve.nIdViajeEnvio', 'left')
            ->join('alarticulo a', 'a.nIdArticulo = ved.nIdArticulo', 'left')
            ->join('enenvio e', 'e.nIdEnvio = ve.nIdEnvio', 'left')
            ->join('vtcliente c', 'c.nIdCliente = e.nIdCliente', 'left')
            ->join('vtdirentrega d', 'd.nIdDirEntrega = e.nIdDirEntrega', 'left')
            ->join('enchofer cf', 'enviaje.nIdChofer = cf.nIdChofer', 'left')
            ->where(['enviaje.nIdViaje' => $id])
            ->orderBy('enviaje.nIdViaje', 've.nIdEnvio', 'ved.nIdArticulo');
        //$m = $this->builder->getCompiledSelect(false);
        //echo $m; exit;
        return $this->findAll();
    }
}
/***********
 * 

CREATE TABLE `ferromat`.`enviaje` (
  `nIdenviaje` INT NOT NULL AUTO_INCREMENT,
  `nIdChofer` INT NULL,
  `nIdSucursal` INT NULL,
  `nViaje` INT NULL,
  `dViaje` DATETIME NULL,
  `sObservacion` VARCHAR(256) NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdenviaje`));

 * 
 ***********/
