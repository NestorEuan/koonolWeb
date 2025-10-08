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
        return $this->select(
            'vtventasdet.nIdVentasDet, ' .
                'vtventasdet.nIdVentas, ' .
                'vtventasdet.nIdArticulo, ' .
                'vtventasdet.nPrecio, ' .
                'vtventasdet.nCant, ' .
                'vtventasdet.nPorcenDescuento, ' .
                'vtventasdet.nEnvio, ' .
                'vtventasdet.nEntrega, ' .
                'vtventasdet.nPorEntregar, ' .
                'vtventasdet.nEntregado, ' .
                'IFNULL(vd.sDescripcion, c.sDescripcion) as nomArt, c.cConArticuloRelacionado, ' .
                'IFNULL(vdx.nImpComisionTotal - vdx.nImpDescuentoProd - vdx.nImpDescuentoGral, 0) AS nDescuentoTotal ',
            false
        )
            ->join('alarticulo c', 'c.nIdArticulo = vtventasdet.nIdArticulo')
            ->join('vtventasdetdescripcion vd', 'vtventasdet.nIdVentasDet = vd.nIdVentasDet', 'left')
            ->join('vtventasdetaux vdx', 'vtventasdet.nIdVentasDet = vdx.nIdVentasDet', 'left')
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
}
