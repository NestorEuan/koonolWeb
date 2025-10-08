<?php

namespace App\Models\Envios;

use CodeIgniter\Model;

class ViajeEnvioDetalleMdl extends Model{
    protected $table = 'enviajeenviodetalle';

    protected $allowedFields = [ 
        'nIdViajeEnvio', 'nIdArticulo', 'fPorRecibir', 'fPeso']; 

    protected $primaryKey = 'nIdViajeEnvioDetalle';

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
     
        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = enenviodetalle.nIdArticulo';
        $sselect = '' .
            '' .
            '' .
            'a.sDescripcion, a.fPeso, ' .
            'i.fExistencia, i.fComprometido, ' .
            '';

        if ($paginado === 0) {
            $model = $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->join('alinventario i', 'i.nIdArticulo = enenviodetalle.nIdArticulo AND i.nIdSucursal = ' . $idSuc, 'left')
                ->join('enviajeenviodetalle ved', 'ved.nIdArticulo = enenviodetalle.nIdArticulo AND ved.nIdViajeEnvio = ' . $viajenvio, 'left')
                ->where(['nIdEnvio' =>  $id, 'i.nIdSucursal' => $idSuc]);
            / *
            ->where(['nIdEnvio' =>  $id, 'i.nIdSucursal' => $idSuc]);
            * /
            return $model->findAll();

     ****************/


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

CREATE TABLE `enviajeenviodetalle` (
  `nIdViajeEnvioDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `nIdViajeEnvio` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fPorRecibir` decimal(10,3) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdViajeEnvioDetalle`),
  KEY `nIdViajeEnvio` (`nIdViajeEnvio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 * 
 ***********/
