<?php

namespace App\Models\Envios;

use CodeIgniter\Model;

class EnvioDetalleMdl extends Model
{
    protected $table = 'enenviodetalle';

    protected $allowedFields = ['nIdEnvio', 'nIdArticulo', 'fCantidad', 'fRecibido', 'fPorRecibir', 'cModoEnv'];

    protected $primaryKey = 'nIdEnvioDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function updtRecibidos($id, $fRecibido)
    {
        $this->set('fRecibido', 'fRecibido + ' . $fRecibido, false)
            ->set('fPorRecibir', 'fPorRecibir - ' . $fRecibido, false)
            ->where(['nIdEnvioDetalle' => $id])
            ->update();
    }

    public function getRegistros($id = 0, $paginado = 0)
    {
        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = enenviodetalle.nIdArticulo';
        if ($paginado === 0)
            return $this
                ->join($join1, $wjoin1, 'left')
                ->where(['nIdEnvioDetalle' =>  $id])
                ->findAll();
        else
            return $this
                ->join($join1, $wjoin1, 'left')
                ->where(['nIdEnvioDetalle'  =>  $id])
                ->paginate($paginado);
    }

    public function getRegsExistenciaYEnviados($id = 0, $idSuc = 0, $viajenvio = 0, $paginado = 0)
    {
        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = enenviodetalle.nIdArticulo';
        $sselect = 'nIdEnvio, nIdEnvioDetalle, enenviodetalle.nIdArticulo, ' .
            'enenviodetalle.cModoEnv, enenviodetalle.fCantidad, ' .
            'enenviodetalle.fRecibido, enenviodetalle.fPorRecibir, ' .
            'a.sDescripcion, a.fPeso, ' .
            'i.fExistencia, i.fComprometido, ' .
            'ved.nIdViajeEnvio, ved.fPorRecibir fXRecibir, ved.nIdViajeEnvioDetalle';

        if ($paginado === 0) {
            $model = $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->join('alinventario i', 'i.nIdArticulo = enenviodetalle.nIdArticulo AND i.nIdSucursal = ' . $idSuc, 'left')
                ->join('enviajeenviodetalle ved', 'ved.nIdArticulo = enenviodetalle.nIdArticulo AND ved.nIdViajeEnvio = ' . $viajenvio, 'left')
                ->where(['nIdEnvio' =>  $id, 'i.nIdSucursal' => $idSuc]);
            /*            
            ->where(['nIdEnvio' =>  $id, 'i.nIdSucursal' => $idSuc]);
            */
            /* * /
            $sql = $model->builder()->getCompiledSelect(false);
            echo $sql . '<BR>'; exit;
            / * */
            /*
            $sql = $model->builder()->getCompiledSelect(false);
            var_dump($sql);
            exit;
            */
            return $model->findAll();
        } else
            /*
        'nIdEnvio, enenviodetalle.nIdArticulo, a.sDescripcion sArticulo,' . 
                            'enenviodetalle.fCantidad, enenviodetalle.fRecibido, ' .
                            'enenviodetalle.fPorRecibir, i.fExistencia, ' . 
                            'i.fComprometido, ved.nIdViajeEnvio, ved.fPorRecibir'
                            */
            return $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->join('alinventario i', 'i.nIdArticulo = enenviodetalle.nIdArticulo', 'left')
                ->join('enviajeenviodetalle ved', 'ved.nIdArticulo = enenviodetalle.nIdArticulo AND ved.nIdViajeEnvio = ' . $viajenvio, 'left')
                ->where(['nIdEnvio' =>  $id, 'i.nIdSucursal' => $idSuc])
                ->paginate($paginado);
    }

   /************
     * 
    SELECT 
    -- ,sum(ved.fPorRecibir)
    , ved.nIdViajeEnvio, ved.fPorRecibir
    FROM enenvio

    left join enenviodetalle ed ON ed.nIdEnvio = enenvio.nIdEnvio
    left join alarticulo a ON a.nIdArticulo = ed.nIdArticulo
    left join enviajeenviodetalle ved on  ved.nIdArticulo = ed.nIdArticulo and ved.nIdViajeEnvio = 1
    -- group by enenvio.nIdEnvio, ed.nIdArticulo

    order By enenvio.nIdEnvio, ed.nIdArticulo     * 
     * 
     *************/

     public function getRegistrosByField($field = 'nIdEnvioDetalle', $val = false)
     {
         return $this->where([$field => $val])->findAll();
     }
     
     public function getRegByArray($arr = false)
    {
        if( $arr === false) return null;
        return $this->where($arr)->findAll();
    }
    
}
/***********
 * 

CREATE TABLE `enenviodetalle` (
  `nIdEnvioDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `nIdEnvio` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fCantidad` decimal(10,2) DEFAULT 0.00,
  `fRecibido` decimal(10,2) DEFAULT 0.00,
  `fPorRecibir` decimal(10,2) DEFAULT 0.00,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdEnvioDetalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
