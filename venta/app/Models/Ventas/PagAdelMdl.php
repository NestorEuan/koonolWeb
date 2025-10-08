<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class PagAdelMdl extends Model
{
    protected $table = 'vtpagosadelantados';

    protected $allowedFields = [
        'nIdCliente',
        'nImporte',
        'sObservaciones',
        'nIdSucursal',
        'nIdVentaFacturada',
        'nIdTipoPago'
    ];

    protected $primaryKey = 'nIdPagosAdelantados';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $table_cliente = 'vtcliente';

    public function getRegistros($id = false, $paginado = false, $idSuc = false, $paraImpresion  = false, $idCliente = false)
    {
        $this->select('vtpagosadelantados.*, c.sNombre as nomCli, ' .
            'DATE_FORMAT(vtpagosadelantados.dtAlta, \'%d-%m-%Y\' ) as fecha, ' .
            'IFNULL(sp.nSaldo, 0) AS saldoPag, ' .
            'vtpagosadelantados.nIdPagosAdelantados AS idPagAdel, ' .
            'IFNULL(u.nIdUsuario, 0) AS nIdUsuario, IFNULL(u.sNombre, \'\') AS nomUsuario, ' .
            'cc.dtCierre AS cierreCorte', false);

        $this->join('vtcliente c', 'vtpagosadelantados.nIdCliente = c.nIdCliente', 'inner')
            ->join('vtsaldopagosadelantados sp', 'vtpagosadelantados.nIdPagosAdelantados = sp.nIdPagosAdelantados', 'left');
        $this->join('vtcortedet bb', 'vtpagosadelantados.nIdPagosAdelantados = bb.nIdMovto AND bb.cTipoMov = \'3\'', 'left', false)
            ->join('vtcorte cc', 'bb.nIdCorte = cc.nIdCorte', 'left', false)
            ->join('sgusuario u', 'cc.nIdUsuario = u.nIdUsuario', 'left');

        if (!$paraImpresion) {
            $this->orderBy('vtpagosadelantados.nIdPagosAdelantados', 'DESC');
        }


        if ($id === false) {
            if ($idSuc !== false) {
                $this->where('vtpagosadelantados.nIdSucursal', $idSuc);
            }
            if ($idCliente !== false) {
                $this->where('vtpagosadelantados.nIdCliente', $idCliente);
            }
            if ($paginado) {
                return $this->paginate(10);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['vtpagosadelantados.nIdPagosAdelantados' => $id])->first();
    }
}
/*
CREATE TABLE IF NOT EXISTS `vtpagosadelantados` (
  `nIdPagosAdelantados` INT NOT NULL AUTO_INCREMENT,
  `nIdCliente` INT NULL,
  `nImporte` DECIMAL(10,2) NULL,
  `sObservaciones` VARCHAR(250) NULL,
  `dtAlta` DATETIME NULL,
  PRIMARY KEY (`nIdPagosAdelantados`))
*/
