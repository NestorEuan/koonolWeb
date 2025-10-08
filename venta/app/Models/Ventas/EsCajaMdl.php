<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class EsCajaMdl extends Model
{
    protected $table = 'vtescaja';

    protected $allowedFields = ['nIdSucursal', 'cTipoMov', 'sMotivo', 'nImporte'];

    protected $primaryKey = 'nIdEScaja';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $idSuc = false, $aCond = false, $paraImpresion = false)
    {
        $select = 'vtescaja.*, ' .
            'IF(vtescaja.cTipoMov = \'E\', \'Ingreso\', \'Egreso\') as cTipMov, ' .
            'IFNULL(v.nFolioRemision, \'\') as nFolioRemision, IFNULL(d.sNombre, \'\') as nomCliente';

        $this->join('vtescreditopago b', 'vtescaja.nIdEScaja = b.nIdEScaja', 'left')
            ->join('vtcreditopago c', 'b.nIdCreditoPago = c.nIdCreditoPago', 'left')
            ->join('vtventas v', 'c.nIdVentas = v.nIdVentas', 'left')
            ->join('vtcliente d', 'v.nIdCliente = d.nIdCliente', 'left');

        if ($paraImpresion) {
            $this->select($select . ', IFNULL(u.nIdUsuario, 0) AS nIdUsuario, ' .
                'IFNULL(u.sNombre, \'\') AS nomUsuario, IFNULL(b.nIdEScaja, 0) AS nIdEScaja2, cc.dtCierre AS cierreCorte', false);
            $this->join('vtcortedet bb', 'vtescaja.nIdEScaja = bb.nIdMovto AND bb.cTipoMov = \'2\'', 'left', false)
                ->join('vtcorte cc', 'bb.nIdCorte = cc.nIdCorte', 'left', false)
                ->join('sgusuario u', 'cc.nIdUsuario = u.nIdUsuario', 'left');
        } else {
            $this->select($select, false);
        }

        if ($id === false) {
            if ($aCond) {
                $wFec = 'vtescaja.dtAlta>= \'' . $aCond['dIni'] . '\' and ' .
                    'vtescaja.dtAlta < \'' . ((new DateTime($aCond['dFin']))
                        ->add(new DateInterval('P1D'))
                        ->format('Y-m-d')) . '\'';
                $this->where($wFec, null, false);

                if ($aCond['opc'] == '1') {
                    $this->where('vtescaja.cTipoMov', 'E');
                } elseif ($aCond['opc'] == '2') {
                    $this->where('vtescaja.cTipoMov', 'S');
                }
            }
            if ($idSuc !== false) {
                $this->where(['vtescaja.nIdSucursal' => intval($idSuc)]);
            }
            if ($paginado) {
                $this->orderBy('vtescaja.nIdEScaja', 'desc');
                return $this->paginate(10);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['vtescaja.nIdEScaja' => $id])->first();
    }
}
/*
CREATE TABLE IF NOT EXISTS `vtescaja` (
  `nIdEScaja` INT NOT NULL AUTO_INCREMENT,
  `cTipoMov` VARCHAR(1) NULL,
  `sMotivo` VARCHAR(250) NULL,
  `nImporte` DECIMAL(10,2) NULL,
  `dtAlta` DATETIME NULL,
  PRIMARY KEY (`nIdEScaja`))
ENGINE = InnoDB
*/
