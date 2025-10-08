<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class SalidaDetalleMdl extends Model{
    protected $table = 'alsalidadetalle';

    protected $allowedFields = ['nIdSalida', 'nIdArticulo', 'fCantidad', 'fImporte', 'fRecibido', 'fPorRecibir' ];

    protected $primaryKey = 'nIdSalidaDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function updtRecibidos( $id, $idArt, $fRecibido    )
    {
        $this->set('fRecibido', 'fRecibido + ' . $fRecibido, false )
        ->set('fPorRecibir', 'fPorRecibir - ' . $fRecibido, false )
        ->where(['nIdSalida' => $id, 'nIdArticulo' => $idArt ])
        ->update();
    }

    public function getRegistros($id = 0, $paginado = 0)
    {
        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = alsalidadetalle.nIdArticulo';
        if($paginado === 0)
            return $this
                ->join($join1,$wjoin1,'left')
                ->where(['nIdSalida' =>  $id])
                ->findAll();
        else
            return $this
                ->join($join1,$wjoin1,'left')
                ->where(['nIdSalida' =>  $id])
                ->paginate($paginado);
    }
    
    public function getRegistrosByField($field = 'nIdSalida', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }
    
}
/***********
 * 

CREATE TABLE `alsalidadetalle` (
  `nIdSalidaDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `nIdSalida` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fCantidad` decimal(10,2) DEFAULT 0.00,
  `fRecibido` decimal(10,2) DEFAULT 0.00,
  `fPorRecibir` decimal(10,2) DEFAULT 0.00,
  `fImporte` decimal(10,2) DEFAULT 0.00,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdSalidaDetalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
?>
