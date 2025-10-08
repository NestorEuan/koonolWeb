<?php

namespace App\Models\Compras;

use CodeIgniter\Model;

class CompraDetalleMdl extends Model
{
    protected $table = 'cpcompradetalle';

    protected $allowedFields = [
        'nIdCompra', 'nIdArticulo', 'fCantidad',
        'fImporte', 'fRecibido', 'fPorRecibir',
        'nIdViajeEnvio', 'fIVA'
    ];

    protected $primaryKey = 'nIdCompraDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function updtRecibidos($id, $idArt, $fRecibido)
    {
        // solo se pueden actualizar las compras sin envio
        // porque las entradas por envios son devoluciones.
        $this->set('fRecibido', 'fRecibido + ' . $fRecibido, false)
            ->set('fPorRecibir', 'fPorRecibir - ' . $fRecibido, false)
            ->where(['nIdCompra' => $id, 'nIdArticulo' => $idArt, 'nIdViajeEnvio' => 0]);
        $this->update();
    }

    public function updtPrecio($id, $idArt, $fImporte)
    {
        $this->set('fImporte', $fImporte)
            ->where(['nIdCompra' => $id, 'nIdArticulo' => $idArt])
            ->update();
        ///->getCompiledSelect()
        //var_dump(  $fRecibido . ' ' . $sql);
        //exit;
    }

    public function getRegistros($id = 0, $paginado = 0)
    {
        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = cpcompradetalle.nIdArticulo';
        $join2 = 'enviajeenvio b';
        $wjoin2 = 'b.nIdViajeEnvio = cpcompradetalle.nIdViajeEnvio';
        $this->select('cpcompradetalle.*, ' .
            'a.sDescripcion, a.nCosto, a.cSinExistencia, a.fPeso, ' .
            'IFNULL(b.nIdViajeEnvio, 0) AS nIdViajeEnvio, IFNULL(b.nIdEnvio, 0) AS nIdEnvio, IFNULL(b.nIdViaje, 0) AS nIdViaje,', false);
        if ($paginado === 0)
            return $this
                ->join($join1, $wjoin1, 'left')
                ->join($join2, $wjoin2, 'left')
                ->where(['nIdCompra' =>  $id])
                ->findAll();
        else
            return $this
                ->join($join1, $wjoin1, 'left')
                ->join($join2, $wjoin2, 'left')
                ->where(['nIdCompra' =>  $id])
                ->paginate($paginado);
    }

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
