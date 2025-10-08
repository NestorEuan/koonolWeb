<?php

namespace App\Models\Cuentas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class CompraPagoMdl extends Model
{
    protected $table = 'cpcomprapago';

    protected $allowedFields = [
        'nIdCompra', 'dtPago',
        'nIdTipoPago', 'fPago', 'nIdUsuario', 
        'cEstadoPago', 'cObservaciones'
    ];

    protected $primaryKey = 'nIdCompraPago';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = false, $paginado = false, $conClasificacion = false, $filtro = false)
    {
        if ($conClasificacion) {
            $this->select('alarticulo.*, c.sClasificacion')->join(
                'alartclasificacion c',
                'c.nIdArtClasificacion = alarticulo.nIdArtClasificacion',
                'inner'
            );
        }
        if ($id === false) {
            if ($filtro) $this->like('sDescripcion', $filtro);
            if ($paginado) {
                return $this->paginate(8);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdArticulo' => $id])->first();
    }

    public function getRegistrosByName($val = false)
    {
        return $this->like(['sDescripcion' => $val])->findAll();
    }

    public function updtPrecio( $idArt, $fImporte )
    {
        $this->set('nPrecio', $fImporte )
            ->where([ 'nIdArticulo' => $idArt ])
            ->update();
    }

    public function getLstRegs($id = 0, $idSuc = false, $aCond = null)
    {
        $this->select("cpcomprapago.*, p.sLeyenda")
            ->join("vttipopago p", "cpcomprapago.nIdTipoPago = p.nIdTipoPago", 'inner');

        if ($id > 0) {
            return $this->where("cpcomprapago.nIdCompra", intval($id))
                ->orderBy("cpcomprapago.dtAlta", 'desc')->findAll();
        }

        $wFec = '';
        $wCli = '';
        $wEdo = '';
        $wSuc = '';

        if ($aCond != null) {
            if ($aCond['dIni'] != null && $aCond['Edo'] != '0') {
                $wFec = 'c.dtAlta >= \'' . $aCond['dIni'] . '\' and ' .
                    'c.dtAlta < \'' . ((new DateTime($aCond['dFin']))
                        ->add(new DateInterval('P1D'))
                        ->format('Y-m-d')) . '\'';
            }

            switch ($aCond['Edo']) {
                case '0':
                    $wEdo = 'c.fSaldo > 0';
                    break;
                case '1':
                    $wEdo = 'c.fSaldo = 0';
                    break;
            }
            if ($aCond['nIdProveedor'] != '') $wCli = 'c.nIdProveedor = ' . $aCond['nIdProveedor'];
            if ($idSuc != false) $wSuc = 'c.nIdSucursal = ' . $idSuc;
            $a = $wFec;
            if ($wEdo != '') {
                if ($a == '')
                    $a = $wEdo;
                else
                    $a .= ' and ' . $wEdo;
            }
            if ($wCli != '') {
                if ($a == '')
                    $a = $wCli;
                else
                    $a .= ' and ' . $wCli;
            }
            if ($wSuc != '') {
                if ($a == '')
                    $a = $wSuc;
                else
                    $a .= ' and ' . $wSuc;
            }
            if ($aCond['Edo'] == '0')
                $where = "$this->table.nIdCompra in (select c.nIdCompra from cpcompra c where $a )";
            else
                $where = "$this->table.nIdCompra in (select c.nIdCompra from cpcompra c where $a )";
        }
        $this->where($where, null, false);

        return $this->findAll();
    }

}
/***********************
 *

CREATE TABLE `alarticulo` (
  `nIdArticulo` int(11) NOT NULL AUTO_INCREMENT,
  `nIdArtClasificacion` int(11) DEFAULT NULL,
  `sDescripcion` varchar(450) DEFAULT NULL,
  `fExistencia` double DEFAULT 0,
  `nPrecio` decimal(10,2) DEFAULT 0,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdArticulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 *
 ***********/
