<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class VentasDetMdl extends Model
{
    protected $table = 'vtventasdet';

    protected $allowedFields = [
        'nIdVentas', 'nIdArticulo', 'nPrecio', 'nCant',
        'nPorcenDescuento', 'nEnvio', 'nEntrega',
        'nPorEntregar', 'nEntregado', 'nDescuentoTotal', 'cModoEnv'
    ];

    protected $primaryKey = 'nIdVentasDet';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where(['nIdVentas' => $id])->first();
    }

    public function getDetalleVenta($id)
    {
        return $this->select('vtventasdet.*, IFNULL(vd.sDescripcion, c.sDescripcion) as nomArt')
            ->join('alarticulo c', 'c.nIdArticulo = vtventasdet.nIdArticulo')
            ->join('vtventasdetdescripcion vd', 'vtventasdet.nIdVentasDet = vd.nIdVentasDet', 'left')
            ->where(['nIdVentas' => $id])->findAll();
    }

    public function actualizaSaldo($idVenta, $idArticulo, $nCant, $tipoMov = '+')
    {
        return $this
            ->where([
                'nIdVentas' => intval($idVenta),
                'nIdArticulo' => intval($idArticulo)
            ])
            ->set('nPorEntregar', 'vtventasdet.nPorEntregar ' . $tipoMov
                . round(floatval($nCant), 3), false)
            ->update();
    }

    public function updtRecibidos($id, $idArt, $fRecibido)
    {
        $this->set('nEntregado', 'nEntregado + ' . $fRecibido, false)
            //->set('nPorEntregar', 'nPorEntregar - ' . $fRecibido, false )
            ->where(['nIdVentas' => $id, 'nIdArticulo' => $idArt])
            ->update();
    }

    public function getDetalleFiscal($idVenta)
    {
        return $this->select('a.nIdArticulo, a.sDescripcion, a.sCveProdSer, a.sCveUnidad, a.cUnidad')
            ->where('vtventasdet.nIdVentas', intval($idVenta))
            ->join('alarticulo a', 'vtventasdet.nIdArticulo = a.nIdArticulo')
            ->findAll();
    }

    public function getDetalleDev($nIdVentas){
        /*
SELECT `vtventasdet`.`nIdArticulo`, IFNULL(vdt.sDescripcion, a.sDescripcion) as nomArt, 
	`nPrecio`, `nCant`, IFNULL( vdd.fDevuelto, 0) fDevuelto 
FROM `vtventasdet` 
LEFT JOIN `alarticulo` `a` ON `a`.`nIdArticulo` = `vtventasdet`.`nIdArticulo` 
LEFT JOIN `vtventasdetdescripcion` `vdt` ON `vdt`.`nIdVentasDet` = `vtventasdet`.`nIdVentasDet` 
LEFT JOIN `vtventasdevdetalle` `vdd` ON `vdd`.`nIdVentas` = `vtventasdet`.`nIdVentas` 
WHERE `vtventasdet`.`nIdVentas` = '3362'
        */
        $model = $this->select("{$this->table}.nIdArticulo, IFNULL(vdt.sDescripcion, a.sDescripcion) as nomArt, " .
                             "nPrecio, nCant, IFNULL( vdd.fDevuelto, 0) fDevuelto,"
                            )
            ->join("alarticulo a", "a.nIdArticulo = {$this->table}.nIdArticulo", "left")
            ->join("vtventasdetdescripcion vdt", "vdt.nIdVentasDet = {$this->table}.nIdVentasDet", "left")
            ->join("vtventasdevdetalle vdd", "vdd.nIdVentas = {$this->table}.nIdVentas AND vdd.nIdArticulo = {$this->table}.nIdArticulo", "left")
            ->where(["{$this->table}.nIdVentas" => $nIdVentas])
            ->findAll()
            ;
        return $model;
        /*
        $sql = $model->builder()->getCompiledSelect(false);
        var_dump($sql);
        exit;
        */
    }


}
