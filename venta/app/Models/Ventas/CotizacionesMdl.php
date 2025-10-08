<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateTime;

class CotizacionesMdl extends Model
{
    protected $table = 'vtcotizaciones';

    protected $allowedFields = [
        'nIdSucursal', 'nFolioCotizacion', 'dVigencia',
        'nIdCliente', 'sNombre', 'nPorcenDescuento', 'nIdVentas',
        'cTipoListaPrecios', 'nIdUsuario', 'nTotal'
    ];

    protected $primaryKey = 'nIdCotizaciones';

    protected $useTimestamps = true;

    protected $useSoftDeletes = false;

    protected $createdField = 'dtAlta';

    protected $deletedField = '';

    protected $updatedField = '';


    public    function getRegistros($id = false, $idSuc = 0, DateTime $dFecIni = null, DateTime $dFecFin = null, $paginado = true, $tipoFiltro = false, $filtro = false)
    {
        $this->select('vtcotizaciones.*, u.sNombre as nomUsu, ' .
            'DATE_FORMAT(vtcotizaciones.dtAlta, \'%d-%m-%Y\') AS fecAlta, ' .
            'DATE_FORMAT(vtcotizaciones.dVigencia, \'%d-%m-%Y\') AS fecVig', false)
            ->join('sgusuario as u', 'u.nIdUsuario = vtcotizaciones.nIdUsuario', 'left');
        if ($id === false) {
            $a = [
                'vtcotizaciones.dtAlta >= ' => $dFecIni->format('Y-m-d') . ' 00:00:00',
                'vtcotizaciones.dtAlta <= ' => $dFecFin->format('Y-m-d') . ' 23:59:59'
            ];
            if($tipoFiltro != '3') {
                $a['vtcotizaciones.nIdVentas'] = 0;
            }
            if ($idSuc != 0) {
                $a['vtcotizaciones.nIdSucursal'] = $idSuc;
            }
            $this->where($a)->orderBy('nIdCotizaciones', 'DESC');
            if ($tipoFiltro === '1') {
                $this->whereIn('vtcotizaciones.nIdUsuario', [0, intval($filtro)]);
            } elseif ($tipoFiltro === '2') {
                $this->like('vtcotizaciones.sNombre', $filtro);
            } elseif ($tipoFiltro === '3') {
                $this->where('vtcotizaciones.nFolioCotizacion', $filtro);
            }

            if ($paginado) {
                return $this->paginate($paginado);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdCotizaciones' => $id])->first();
    }

    public function getCotizacion($idsuc, $id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where([
            'nIdSucursal' => $idsuc,
            'nFolioCotizacion' => $id
        ])->first();
    }

    public function actualizaFolioVenta($idCot, $idVenta)
    {
        return $this->set('nIdVentas', intval($idVenta))
            ->where('nIdCotizaciones', intval($idCot))
            ->update();
    }
}
/****************
 *
 *
 * 
 CREATE TABLE IF NOT EXISTS `vtcotizaciones` (
  `nIdCotizaciones` INT NOT NULL AUTO_INCREMENT,
  `nIdSucursal` INT NULL,
  `nFolioCotizacion` INT NULL,
  `dVigencia` DATE NULL,
  `nIdCliente` INT NULL,
  `sNombre` VARCHAR(150) NULL,
  `nPorcenDescuento` DECIMAL(5,2) NULL,
  `nIdVentas` INT NULL COMMENT 'id de la venta donde fue aplicada la cotizacion (para que no se vuelva a usar)',
  `dtAlta` DATETIME NULL,
  PRIMARY KEY (`nIdCotizaciones`),
  INDEX `idx_cotizacion` (`nIdSucursal` ASC, `nFolioCotizacion` ASC))
  ENGINE = InnoDB

 */
