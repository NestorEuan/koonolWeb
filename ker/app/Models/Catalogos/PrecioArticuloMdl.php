<?php

namespace App\Models\Catalogos;

use CodeIgniter\Model;

class PrecioArticuloMdl extends Model
{
    protected $table = 'alprecioarticulo';

    protected $allowedFields = [
        'nIdSucursal', 'nIdArticulo', 'nIdTipoLista',
        'fMaximo', 'fPrecio', 'fPrecioFactura', 'fPrecioTapado', 'faPartir'
    ];

    protected $primaryKey = 'nIdPrecioArticulo';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where(['nIdPrecioArticulo' => $id])->first();
    }

    public function buscaPrecio($idSuc, $idTipoLista, $idArt, $cant)
    {
        return $this->select('fPrecio, fPrecioFactura, fPrecioTapado')
            ->where([
                'nIdSucursal' => intval($idSuc),
                'nIdTipoLista' => intval($idTipoLista),
                'nIdArticulo' => intval($idArt),
                'fMaximo >=' => round(floatval($cant), 3)
            ])->orderBy('fMaximo', 'ASC')
            ->limit(1)->first();
    }

    public function getlstPrecios($idSuc = false, $paginado = false, $filtroDes = false, $filtroLista = false, $soloConPrecios = false, $idArticulo = false)
    {
        $sql = $this->db->query(
            'SELECT GROUP_CONCAT(' .
                '\'MAX(IF(b.nIdTipoLista = \', nIdTipoLista, \', b.fPrecio, 0)) AS "L\', nIdTipoLista, \'",\',' .
                '\'MAX(IF(b.nIdTipoLista = \', nIdTipoLista, \', b.fPrecioTapado, 0)) AS "T\', nIdTipoLista, \'",\',' .
                '\'MAX(IF(b.nIdTipoLista = \', nIdTipoLista, \', b.fPrecioFactura, 0)) AS "F\', nIdTipoLista, \'"\'' .
                ' ORDER BY nIdTipoLista ) AS ss  FROM altipolista ' .
                ($filtroLista ? 'WHERE nIdTipoLista = ' . $filtroLista : '')
        )->getResult('array')[0];

        $joinArticulos = 'a.nIdArticulo = b.nIdArticulo';
        if ($idSuc) $joinArticulos .= ' AND b.nIdSucursal = ' . $idSuc;
        if ($filtroLista) $joinArticulos .= ' AND b.nIdTipoLista = ' . $filtroLista;
        if ($filtroDes) $this->like('a.sDescripcion', trim($filtroDes));
        if ($soloConPrecios) $this->where('b.faPartir IS NOT NULL');
        if ($idArticulo) $this->where('a.nIdArticulo', $idArticulo);

        $this->select('a.nIdArticulo, b.faPartir, ' . $sql['ss'] . ',' .
            ' c.sClasificacion, a.sDescripcion', false)
            ->join('alprecioarticulo as b', $joinArticulos, 'left')
            ->join('alartclasificacion as c', 'a.nIdArtClasificacion = c.nIdArtClasificacion', 'inner')
            ->orderBy('c.nIdArtClasificacion, a.nIdArticulo, b.faPartir')
            ->withDeleted()
            ->builder()->groupBy('a.nIdArticulo, b.faPartir')
            ->from('alarticulo as a', true);
        $this->where('a.dtBaja IS NULL', null, false);
        if ($paginado) {
            return $this->paginate($paginado);
        } else {
            return $this->findAll();
        }
    }

    public function getIdRegistro($idSuc = false, $idLista = false, $idArt = 0)
    {
        $this->select('nIdPrecioArticulo')
            ->where([
                'nIdSucursal' => intval($idSuc),
                'nIdTipoLista' => intval($idLista),
                'nIdArticulo' => intval($idArt)
            ]);
        return $this->findAll();
    }
}
/****************
 *
 *
  select  fPrecio  from alprecioarticulo 
	where nidsucursal = 1 and nIdTipoLista = 3 and nIdArticulo = 10
	AND 200 < fMaximo
	LIMIT 1


 CREATE TABLE IF NOT EXISTS `alprecioarticulo` (
  `nIdPrecioArticulo` INT NOT NULL AUTO_INCREMENT,
  `nIdSucursal` INT NULL,
  `nIdArticulo` INT NULL,
  `nIdTipoLista` INT NULL,
  `fMaximo` DOUBLE NULL,
  `fPrecio` DECIMAL(11,2) NULL,
  `dtAlta` DATETIME NULL,
  `dtBaja` DATETIME NULL,
  PRIMARY KEY (`nIdPrecioArticulo`),
  INDEX `IDX_precio` (`nIdSucursal` ASC, `nIdTipoLista` ASC, `nIdArticulo` ASC))
ENGINE = InnoDB

 *
 */
