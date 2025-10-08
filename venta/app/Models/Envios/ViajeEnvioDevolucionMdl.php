<?php

namespace App\Models\Envios;

use CodeIgniter\Model;
class ViajeEnvioDevolucionMdl extends Model{
    protected $table = 'enviajeenviodevolucion';

    protected $allowedFields = [ 
        'nIdViajeEnvioDetalle', 'nIdViajeEnvio', 'nIdArticulo', 'fDevolver', 'fPeso']; 

    protected $primaryKey = 'nIdViajeEnvioDevolucion';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = 0)
    {
        $resp = $this->where(['nIdViajeEnvio' => $id])->findAll();
        return $resp;
    }
        
    public function getArtsEnViaje($id = 0, $idViaje = 0)
    {
        $model = $this
            ->select(
            "nIdViaje, ve.nIdEnvio, nIdEnvioDetalle, " .
            "ed.nIdArticulo, ed.cModoEnv, ed.fCantidad, " .
            "ed.fRecibido, ed.fPorRecibir, " .
            "$this->table.nIdViajeEnvio, $this->table.fPorRecibir fXRecibir, $this->table.nIdViajeEnvioDetalle, " .
            "ev.nIdOrigen, ev.cOrigen, ev.nIdSucursal"
            /*
        $mdl = $this->select('ve.nIdViaje, enenviodetalle.nIdEnvio, ved.nIdArticulo, ' .
            'enenviodetalle.fCantidad, enenviodetalle.fRecibido, enenviodetalle.fPorRecibir, ' .
            'ved.fPorRecibir fXRecibir, e.nIdOrigen, e.cOrigen, e.nIdSucursal')
            */
            )
            ->join("enviajeenvio ve","ve.nIdViajeEnvio = $this->table.nIdViajeEnvio","left")
            ->join("enenviodetalle ed","ed.nIdEnvio = ve.nIdEnvio AND ed.nIdArticulo = $this->table.nIdArticulo","left")
            ->join("enenvio ev", "ev.nIdEnvio = ve.nIdEnvio","left")
            ->where("ve.nIdViaje",$idViaje)
        ;
        // $sql = $model->builder()->getCompiledSelect(false);
        // echo $sql . '<BR>'; exit;
        $regs = $model->findAll();
        return $regs;

        /****************
         * **

        ****************/
    }

    public function getRegsDevueltos($idviajeenvio, $idSuc = 0)
    {
        $sselect = 
            'enviajeenviodevolucion.nIdViajeEnvio, enviajeenviodevolucion.nIdViajeEnvioDetalle, ' .
            'ed.cModoEnv, ed.fCantidad, ed.fRecibido, ed.fPorRecibir, fDevolver, ' .
            'a.sDescripcion, a.fPeso, ' .
            'i.fExistencia, i.fComprometido, ' .
            'ved.nIdArticulo, ved.nIdViajeEnvio, ved.fPorRecibir fXRecibir'
            ;
        $mdl = $this->select($sselect)
            ->join('enviajeenvio ve', 've.nIdViajeEnvio =  enviajeenviodevolucion.nIdViajeEnvio', 'left')
            ->join('enviajeenviodetalle ved', 'ved.nIdViajeEnvio =  enviajeenvio.nIdViajeEnvio', 'left')
            ->join('enenviodetalle ed', 'ed.nIdEnvio = enviajeenvio.nIdEnvio AND  ed.nIdArticulo = ved.nIdArticulo', 'left')
            ->join('alinventario i', 'i.nIdArticulo = ved.nIdArticulo AND i.nIdSucursal = ' . $idSuc, 'left')
            ->join('alarticulo a', 'a.nIdArticulo = ved.nIdArticulo', 'left')
            ->where(['enviajeenviodevolucion.nIdViajeEnvio' => $idviajeenvio])
            //->orderBy('ve.nIdViaje,  ve.nIdEnvio, ved.nIdArticulo')
        ;
        /* */
        echo '<BR>SQL CONSULTO<BR>';
        $sql =$mdl->builder()->getCompiledSelect(false);
        var_dump($sql);
        exit; 
        /* */
        // echo  $mdl->builder()->getCompiledSelect(false) . ' SQL <BR>'; 
        return  $mdl->findAll();
    }
    /*  
    public function getRegistrosByField($field = 'nIdEnvio', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }
    
    */
}
/***********
 * 

DROP TABLE IF EXISTS `enviajeenviodevolucion`; 
CREATE TABLE IF NOT EXISTS `enviajeenviodevolucion` (
  `nIdViajeEnvioDevolucion` int(11) NOT NULL AUTO_INCREMENT,
  `nIdViajeEnvioDetalle` int(11) DEFAULT NULL,
  `nIdViajeEnvio` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fDevolver` decimal(10,3) DEFAULT NULL,
  `fPeso` double NOT NULL DEFAULT 0,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdViajeEnvioDevolucion`),
  KEY `nIdViajeEnvioDetalle` (`nIdViajeEnvioDetalle`),
  KEY `nIdViajeEnvio` (`nIdViajeEnvio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 * 
 ***********/
