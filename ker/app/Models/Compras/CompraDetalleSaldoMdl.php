<?php

namespace App\Models\Compras;

use CodeIgniter\Model;

class CompraDetalleSaldoMdl extends Model{
    protected $table = 'cpcompradetallesaldo';

    protected $allowedFields = ['nIdCompra','nIdCompraDetalle',  'nIdArticulo', 'fSaldo', ];
    
    protected $primaryKey = 'nIdCompraDetalleSaldo';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    /*
    public function updtRecibidos( $id, $idArt, $fRecibido )
    {
        $this->set('fRecibido', 'fRecibido + ' . $fRecibido, false )
            ->set('fPorRecibir', 'fPorRecibir - ' . $fRecibido, false )
            ->where(['nIdCompra' => $id, 'nIdArticulo' => $idArt ])
            ->update();
        ///->getCompiledSelect()
        //var_dump(  $fRecibido . ' ' . $sql);
        //exit;
    }

    public function updtPrecio( $id, $idArt, $fImporte )
    {
        $this->set('fImporte', $fImporte )
            ->where(['nIdCompra' => $id, 'nIdArticulo' => $idArt ])
            ->update();
        ///->getCompiledSelect()
        //var_dump(  $fRecibido . ' ' . $sql);
        //exit;
    }

    public function getRegistros($id = 0, $paginado = 0)
    {
        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = cpcompradetalle.nIdArticulo';
        if($paginado === 0)
            return $this
                ->join($join1,$wjoin1,'left')
                ->where(['nIdCompra' =>  $id])
                ->findAll();
        else
            return $this
                ->join($join1,$wjoin1,'left')
                ->where(['nIdCompra' =>  $id])
                ->paginate($paginado);
    }
    */
    
    public function getRegistrosByField($field = 'nIdCompra', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }
    
}
/***********
 * 

CREATE TABLE `cpcompradetalle` (
  `nIdCompraDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `nIdCompra` int(11) DEFAULT NULL,
  `nIdArticulo` int(11) DEFAULT NULL,
  `fCantidad` decimal(10,0) DEFAULT NULL,
  `fImporte` decimal(10,0) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdCompraDetalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * 
 ***********/
?>