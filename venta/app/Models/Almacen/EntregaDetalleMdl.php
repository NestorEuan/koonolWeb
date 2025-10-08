<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class EntregaDetalleMdl extends Model
{
    protected $table = 'alentregadetalle';

    protected $allowedFields = [
        'nIdEntrega', 'nIdArticulo', 'fCantidad',
        'fImporte', 'fRecibido', 'fPorRecibir'
    ];

    protected $primaryKey = 'nIdEntregaDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function updtRecibidos($id, $idArt, $fRecibido)
    {
        $this->set('fRecibido', 'fRecibido + ' . $fRecibido, false)
            ->set('fPorRecibir', 'fPorRecibir - ' . $fRecibido, false)
            ->where(['nIdEntrega' => $id, 'nIdArticulo' => $idArt])
            ->update();
    }

    public function getRegistros($id = 0, $paginado = 0, $idSuc = 0)
    {
        $sselect = 'nIdEntregaDetalle, nIdEntrega, a.nIdArticulo, fCantidad, ' . 
        'fRecibido, fPorRecibir, fImporte, a.sDescripcion, ' .
        'IFNULL(i.fExistencia, 0) AS fExistencia, IFNULL(i.fComprometido, 0) AS fComprometido, a.cSinExistencia ';

        $join1 = 'alarticulo a';
        $wjoin1 = 'a.nIdArticulo = alentregadetalle.nIdArticulo';
        $join2 = 'alinventario i';
        $wjoin2 = 'i.nIdArticulo = alentregadetalle.nIdArticulo AND i.nIdSucursal = ' . $idSuc;
        if ($paginado === 0)
            return $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->join($join2, $wjoin2, 'left')
                ->where(['nIdEntrega' =>  $id])
                ->findAll();
        else
            return $this
                ->select($sselect)
                ->join($join1, $wjoin1, 'left')
                ->join($join2, $wjoin2, 'left')
                ->where(['nIdEntrega' =>  $id])
                ->paginate($paginado);
    }

    public function getRegistrosByField($field = 'nIdEntrega', $val = false)
    {
        return $this->where([$field => $val])->findAll();
    }

    public function getRegDet($id)
    {
        return $this->where('nIdEntrega', $id)->findAll();
    }
}
/***********
 * 
CREATE TABLE `alentregadetalle` (
	`nIdEntregaDetalle` INT(11) NOT NULL AUTO_INCREMENT,
	`nIdEntrega` INT(11) NULL DEFAULT NULL,
	`nIdArticulo` INT(11) NULL DEFAULT NULL,
	`fCantidad` DECIMAL(10,2) NULL DEFAULT '0.00',
	`fRecibido` DECIMAL(10,2) NULL DEFAULT '0.00',
	`fPorRecibir` DECIMAL(10,2) NULL DEFAULT '0.00',
	`fImporte` DECIMAL(10,2) NULL DEFAULT '0.00',
	`dtAlta` DATETIME NULL DEFAULT NULL,
	`dtBaja` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`nIdEntregaDetalle`) USING BTREE,
	INDEX `nIdEntrega` (`nIdEntrega`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB;

 * 
 ***********/
