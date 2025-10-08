<?php

namespace App\Models\Envios;

use CodeIgniter\Model;

class ViajeEnvioMdl extends Model
{
    protected $table = 'enviajeenvio';

    protected $allowedFields = [
        'nIdViaje', 'nIdEnvio', 'cEstatus', 'sObservacion', 'fPeso'
    ];

    protected $primaryKey = 'nIdViajeEnvio';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = 0)
    {
        $resp = $this->where([$this->primaryKey => $id])->first();
        return $resp;
    }

    public function getViajeEnvios($idviaje)
    {
        $model = $this->select(
            "{$this->table}.nIdViajeEnvio, {$this->table}.nIdViaje, {$this->table}.nIdEnvio, e.nIdOrigen, " .
                "fPeso, e.cOrigen, e.nIdSucursal, e.cEstatus,  " .
                "date_format(e.dSolicitud,'%Y-%m-%d') dSolicitud, " .
                "date_format(e.dAsignacion,'%Y-%m-%d') dAsignacion, " .
                "date_format(e.dEntrega,'%Y-%m-%d') dEntrega, " .
                "s.sDescripcion, s.sDireccion, " .
                "c.nIdCliente, c.sNombre,  " .
                "d.sEnvDireccion, d.sEnvReferencia, d.sEnvEntrega, d.sEnvColonia, d.sEnvTelefono, " .
                "IFNULL(vt.nFolioRemision, 0) as nFolioRemision, IFNULL(tr.nIdTraspaso, 0) as nFolioTraspaso," .
                "IFNULL(vt.dtAlta, '') as dAltaVenta, IFNULL(tr.dtAlta, 0) as dAltaTraspaso"
        )
            ->join("enenvio e", "e.nIdEnvio = {$this->table}.nIdEnvio", "left")
            ->join("alsucursal s", "s.nIdSucursal = e.nIdSucursal", "left")
            ->join("vtcliente c", "c.nIdCliente = e.nIdCliente", "left")
            ->join("vtdirentrega d", "d.nIdDirEntrega = e.nIdDirEntrega", "left")
            ->join("vtventas vt", "e.nIdOrigen = vt.nIdVentas", "left")
            ->join("altraspaso tr", "e.nIdOrigen = tr.nIdTraspaso", "left")
            ->orderBy("{$this->table}.nIdEnvio")
            ->where(['nIdVIaje' => $idviaje])
            //->where("enenvio.cEstatus !=", "3")
        ;
        //$resp = $model->builder()->getCompiledSelect(false); var_dump($resp); exit;
        $this->verQuery($model);
        $resp = $model->findAll();
        return $resp;
    }

    public function getRegsEnviados($idviajeenvio, $idSuc = 0)
    {
        $sselect =
            'enviajeenvio.nIdEnvio, nIdViajeEnvioDetalle, ' .
            'ed.cModoEnv, ed.fCantidad, ed.fRecibido, ed.fPorRecibir, ' .
            'a.sDescripcion, a.fPeso, ' .
            'i.fExistencia, i.fComprometido, ' .
            'ved.nIdArticulo, ved.nIdViajeEnvio, ved.fPorRecibir fXRecibir';
        $mdl = $this->select($sselect)
            ->join('enviajeenviodetalle ved', 'ved.nIdViajeEnvio =  enviajeenvio.nIdViajeEnvio', 'left')
            ->join('enenviodetalle ed', 'ed.nIdEnvio = enviajeenvio.nIdEnvio AND  ed.nIdArticulo = ved.nIdArticulo', 'left')
            ->join('alinventario i', 'i.nIdArticulo = ved.nIdArticulo AND i.nIdSucursal = ' . $idSuc, 'left')
            ->join('alarticulo a', 'a.nIdArticulo = ved.nIdArticulo', 'left')
            ->where(['enviajeenvio.nIdViajeEnvio' => $idviajeenvio])
            //->orderBy('ve.nIdViaje,  ve.nIdEnvio, ved.nIdArticulo')
        ;
        $this->verQuery($mdl);
        return  $mdl->findAll();
    }

    public function getRegsDevueltos($idviajeenvio, $idSuc = 0)
    {
        $sselect =
            'enviajeenvio.nIdEnvio, ved.nIdViajeEnvioDetalle, ' .
            'ed.cModoEnv, ed.fCantidad, ed.fRecibido, ed.fPorRecibir, ' .
            'a.sDescripcion, a.fPeso, fDevolver, ' .
            'i.fExistencia, i.fComprometido, ' .
            'ved.nIdArticulo, ved.nIdViajeEnvio, ved.fPorRecibir fXRecibir';
        $mdl = $this->select($sselect)
            ->join('enviajeenviodetalle ved', 'ved.nIdViajeEnvio =  enviajeenvio.nIdViajeEnvio', 'left')
            ->join('enviajeenviodevolucion vedd', 'vedd.nIdViajeEnvioDetalle =  ved.nIdViajeEnvioDetalle', 'left')
            ->join('enenviodetalle ed', 'ed.nIdEnvio = enviajeenvio.nIdEnvio AND  ed.nIdArticulo = ved.nIdArticulo', 'left')
            ->join('alinventario i', 'i.nIdArticulo = ved.nIdArticulo AND i.nIdSucursal = ' . $idSuc, 'left')
            ->join('alarticulo a', 'a.nIdArticulo = ved.nIdArticulo', 'left')
            ->where(['enviajeenvio.nIdViajeEnvio' => $idviajeenvio])
            //->orderBy('ve.nIdViaje,  ve.nIdEnvio, ved.nIdArticulo')
        ;
        $this->verQuery($mdl);
        return  $mdl->findAll();
    }

    private function verQuery($mdl)
    {
        /* * /
        echo '<BR>SQL CONSULTO<BR>';
        $sql = $mdl->builder()->getCompiledSelect(false);
        var_dump($sql);
        exit; 
        / * */
        // echo  $mdl->builder()->getCompiledSelect(false) . ' SQL <BR>'; 
    }
}
/***********
 * 

CREATE TABLE `ferromat`.`enviajedetalle` (
  `nIdenviajedetalle` INT NOT NULL AUTO_INCREMENT,
  `nIdViaje` INT NULL,
  `nIdEnvio` INT NULL,
  `cEstatus` VARCHAR(45) NULL,
  `sObservacion` VARCHAR(256) NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdenviajedetalle`));

 * 
 ***********/
