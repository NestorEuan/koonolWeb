<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class InventarioMdl extends Model
{
    protected $table = 'alinventario';

    protected $allowedFields = ['nIdSucursal', 'nIdArticulo', 'fExistencia', 'fComprometido', 'fSobreComprometido'];

    protected $primaryKey = 'nIdInventario';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function updtArticuloSucursalInventario($idSuc, $idArt, $fCant, $fComprometido = false)
    {
        $bSalida = ($fCant < 0);
        $invArt = [];
        $invArt = [
            'nIdSucursal' => $idSuc,
            'nIdArticulo' => $idArt,
            'fExistencia' => $fCant,
            'fComprometido' => 0,
            'fSobreComprometido' => 0,
        ];
        $invartsucursal = $this->where(['nIdSucursal' => $idSuc, 'nIdArticulo' => $idArt])->first();
        if (!empty($invartsucursal)) {
            $invArt['nIdInventario'] = $invartsucursal['nIdInventario'];
            $invArt['fExistencia'] = $invartsucursal['fExistencia'] + $fCant;
            if ($bSalida && $fComprometido) $invartsucursal['fComprometido'] += $fCant;
            if ($invartsucursal['fComprometido'] < 0) $invartsucursal['fComprometido'] = 0;
            if ($invartsucursal['fSobreComprometido'] < 0) $invartsucursal['fSobreComprometido'] = 0;
            if ($bSalida) {
                if ($invArt['fExistencia'] < $invartsucursal['fComprometido']) $invartsucursal['fComprometido'] = $invArt['fExistencia'];
            } else {
                if ($invartsucursal['fSobreComprometido'] > 0) {
                    $dif = $invArt['fExistencia'] - $invartsucursal['fComprometido'];
                    if ($dif >= $invartsucursal['fSobreComprometido']) {
                        $invartsucursal['fComprometido'] += $invartsucursal['fSobreComprometido'];
                        $invartsucursal['fSobreComprometido'] = 0;
                    } else {
                        $invartsucursal['fComprometido'] = $invArt['fExistencia'];
                        $invartsucursal['fSobreComprometido'] = $invartsucursal['fSobreComprometido'] - $dif;
                    }
                }
            }
            $invArt['fComprometido'] = $invartsucursal['fComprometido'];
            $invArt['fSobreComprometido'] = $invartsucursal['fSobreComprometido'];
        }
        $this->save($invArt);
    }

    public function getArticuloSucursal($idArticulo, $idSucursal, $inv = false, $bloqueaRegistro = false)
    {
        $this->where([
            'nIdArticulo' => $idArticulo,
            'nIdSucursal' => $idSucursal . ($bloqueaRegistro ? ' for update' : '')
        ], null, false);
        if ($inv) $this->select('(fExistencia - IFNULL(fComprometido, 0.000)) AS disponible, ' .
            'fExistencia, fSobreComprometido, fComprometido');
        return $this->builder()->get()->getFirstRow('array');
    }

    public function getRegistros($idArticulo = false, $idSucursal = false)
    {
        $this->select('alinventario.*, b.sDescripcion as cNomSuc, a.sDescripcion')
            ->join('alsucursal as b', 'b.nIdSucursal = alinventario.nIdSucursal')
            ->join('alarticulo a', 'a.nIdArticulo = alinventario.nIdArticulo');
        if ($idArticulo === false) {
            if ($idSucursal === false) {
                return $this
                    ->orderBy('alinventario.nIdSucursal', 'ASC')
                    ->orderBy('alinventario.nIdArticulo', 'ASC')
                    ->findAll();
            } else {
                return $this->orderBy('alinventario.nIdArticulo', 'ASC')->where(['alinventario.IdSucursal' => $idSucursal])->findAll();
            }
        } else {
            $a['alinventario.nIdArticulo'] = $idArticulo;
            if ($idSucursal !== false) {
                $a['alinventario.nIdSucursal'] = $idSucursal;
                return $this->where($a)->findAll();
            }
        }
        return $this->orderBy('alinventario.nIdSucursal', 'ASC')->where(['alinventario.nIdArticulo' =>  $idArticulo])->findAll();
    }

    public function getRegistrosInv($idArt, $idSuc)
    {
        $this->select(
            'SUM(IF(`alinventario`.`nIdSucursal` = ' . $idSuc . ', `alinventario`.`fExistencia` - IFNULL(`alinventario`.`fComprometido`, 0.000), 0.000)) AS exiSuc, ' .
                'SUM(IF(`alinventario`.`nIdSucursal` = ' . $idSuc . ', IFNULL(`alinventario`.`fComprometido`, 0.000), 0.000)) AS fCompro, ' .
                'SUM(IF(`alinventario`.`nIdSucursal` = ' . $idSuc . ', IFNULL(`alinventario`.`fSobreComprometido`, 0.000), 0.000)) AS fSobreCompro, ' .
                'SUM(IF(`alinventario`.`nIdSucursal` <> ' . $idSuc . ', `alinventario`.`fExistencia` - IFNULL(`alinventario`.`fComprometido`, 0.000), 0.000)) AS exiOtros, ' .
                'SUM(IF(`alinventario`.`nIdSucursal` = ' . $idSuc . ', `alinventario`.`fExistencia`, 0.000)) as exiFis',
            false
        );
        return $this->where(['nIdArticulo' =>  intval($idArt)])->first();
    }

    public function getExistencias($idsuc, $paginado = false, $filtro = false, $bcomprometido = false)
    {
        // si es para comprometido
        if ($bcomprometido === 'on') {
            $ret = [];
            // se leen las entregas
            // se leen los envios asignados a viajes
            $qEntrega = '' .
                ' SELECT \'E\' as tipo, en.nIdSucursal AS idsuc,' .
                ' 0 AS viaje, 0 AS envio, en.nIdEntrega AS entrega, en.nIdOrigen AS idventas,' .
                ' en.dtAlta as fecha, ' . 
                ' en1.nIdArticulo, en1.fCantidad AS cant, \'0\' AS modoenv' .
                ' FROM alentrega en' .
                ' INNER JOIN alentregadetalle en1 ON en.nIdEntrega = en1.nIdEntrega' .
                ' WHERE en.cEdoEntrega <> \'3\' AND en.dtBaja IS NULL AND en.nIdSucursal = ' . $idsuc;

            $qEnvio = '' .
                ' SELECT \'V\' as tipo, v.nIdSucursal AS idsuc,' .
                ' v.nIdViaje AS viaje, v2.nIdEnvio AS envio, 0 AS entrega, env.nIdOrigen AS idventas, ' .
                ' v.dtAlta as fecha, ' . 
                ' v3.nIdArticulo, v3.fPorRecibir AS cant, v3.cModoEnv AS modoenv' .
                ' FROM enviaje v' .
                ' INNER JOIN enviajeenvio v2 ON v.nIdViaje = v2.nIdViaje' .
                ' INNER JOIN enviajeenviodetalle v3 ON v2.nIdViajeEnvio = v3.nIdViajeEnvio' .
                ' INNER JOIN enenvio env ON v2.nIdEnvio = env.nIdEnvio' .
                ' WHERE v.cEstatus IN (\'0\', \'1\') AND v.dtBaja IS NULL AND v.nIdSucursal = ' . $idsuc;
            $q = ' SELECT a.*, c.sNombre, v.nFolioRemision, art.sDescripcion as nomArt ' . 
                ' FROM (' . $qEntrega . ' UNION ' . $qEnvio . ') AS a ' .
                ' INNER JOIN alarticulo art ON a.nIdArticulo = art.nIdArticulo' .
                ' INNER JOIN vtventas v ON a.idventas = v.nIdVentas' .
                ' INNER JOIN vtcliente c ON v.nIdCliente = c.nIdCliente' .
                ' ORDER BY a.fecha, a.entrega, a.viaje, a.envio';
            $ret[] = $this->db->query($q)->getResult('array');
            // if ($conDetalle) {
            // } else {
            // }
            $q = ' SELECT a.nIdArticulo, SUM(a.cant) as cant FROM (' . $qEntrega . ' UNION ' . $qEnvio . ') AS a ' .
                ' GROUP BY a.nIdArticulo';
            $qi = 'select i.nIdArticulo, a.sDescripcion AS nomArt, i.fExistencia, i.fComprometido, i.fSobreComprometido, ' .
                'IFNULL(b.cant, 0) as cant ' .
                'from alinventario i ' .
                'inner join alarticulo a on i.nIdArticulo = a.nIdArticulo '  .
                'left join (' . $q . ') as b on i.nIdArticulo = b.nIdArticulo ' .
                'where i.nIdSucursal = ' . $idsuc .
                ' order by i.nIdArticulo';
            $ret[] = $this->db->query($qi)->getResult('array');
            return $ret;
            // $r = $this->db->query($q)->getResult('array');
        }
        $this->select('alinventario.*, a.sDescripcion AS nomArt, a.sCodigo AS codArt')
            ->join('alarticulo a', 'alinventario.nIdArticulo = a.nIdArticulo', 'inner')
            ->orderBy('alinventario.nIdArticulo', 'ASC')
            ->where('alinventario.nIdSucursal', $idsuc)
            ->where('a.dtBaja IS NULL');

        if ($filtro !== false) {
            $this->like('sDescripcion', $filtro);
        }

        if ($bcomprometido) {
            $this->where('(alinventario.fComprometido + alinventario.fSobreComprometido) > ', 0);
        }

        // var_dump($this->builder()->getCompiledSelect(false) ); exit;

        if ($paginado) {
            return $this->paginate($paginado);
        } else {
            return $this->findAll();
        }
    }

    public function guardaupd()
    {
        $mm = $this->builder()
            ->set('fComprometido', 10)
            ->set('fSobreComprometido', 5)
            ->where([
                'nIdSucursal' => 1,
                'nIdArticulo' => 4
            ])->where('for update', null, false)
            ->getCompiledSelect();
        return $mm;
    }
}
