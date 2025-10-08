<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateTime;

class CorteCajaMdl extends Model
{
    protected $table = 'vtcorte';

    protected $allowedFields = [
        'nIdSucursal', 'nIdUsuario', 'nNumCaja',
        'nSaldoIni', 'nSaldoFin', 'sObservaciones',
        'dtApertura', 'dtCierre'
    ];

    protected $primaryKey = 'nIdCorte';

    protected $useTimestamps = true;

    protected $useSoftDeletes = false;

    protected $createdField = 'dtApertura';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getRegistros($id = false, $idsuc = false, $paginado = false, $activos = false)
    {
        // ' nIdVentas IN (SELECT nIdMovto FROM vtcortedet WHERE nIdCorte = vtcorte.nIdCorte AND cTipoMov = \'1\') ';
        // '(SELECT SUM(f.nPorEntregar) FROM vtventasdet as f WHERE f.nIdVentas = vtventas.nIdVentas) AS sumEntrega';
        $this->select(
            'vtcorte.*, u.sNombre as nomUsu, s.sDescripcion as nomSuc, ' .
                '(SELECT COUNT(aa.nIdFacturas) AS jj' .
                ' FROM vtfacturas aa' .
                ' INNER JOIN vtventas ab ON aa.nIdVentas = ab.nIdVentas' .
                ' WHERE aa.sUUID IS NULL' .
                ' AND ab.cEdo IN (\'1\', \'2\') ' .
                ' AND aa.nIdVentas IN (' .
                ' SELECT ab.nIdMovto' .
                ' FROM vtcortedet ab' .
                ' WHERE ab.nIdCorte = vtcorte.nIdCorte' .
                ' AND ab.cTipoMov = \'1\'' .
                ')) as sumFacPendientes',
            false
        )
            ->join('sgusuario as u', 'u.nIdUsuario = vtcorte.nIdUsuario')
            ->join('alsucursal as s', 's.nIdSucursal = vtcorte.nIdSucursal')
            ->orderBy('nIdCorte', 'DESC');

        if ($id === false) {
            if ($activos) {
                $this->where('vtcorte.dtCierre IS NULL', null, false);
            }
            if ($idsuc !== false) {
                $this->where(['vtcorte.nIdSucursal' => $idsuc]);
            }
            if ($paginado) {
                return $this->paginate(10);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['nIdCorte' => $id])->first();
    }

    public function getIdCorteActivo($idUsuario, $idSucursal)
    {
        return $this
            ->where([
                'nIdSucursal' => $idSucursal,
                'nIdUsuario' => $idUsuario
            ])
            ->orderBy('nIdCorte', 'DESC')
            ->limit(1)
            ->first();
    }

    public function getCajasActivas($idSucursal)
    {
        $r = $this->select('GROUP_CONCAT(DISTINCT nNumCaja) as m', false)->where('dtCierre IS NULL')
            ->where('nIdSucursal', $idSucursal)
            ->orderBy('nNumCaja', 'ASC')
            ->first();
        if ($r === null || $r['m'] === null) {
            $ret = [];
        } else {
            $ret = explode(',', $r['m']);
        }
        return $ret;
    }

    /**
     * @param string $tipoMov 1- ventas, 2 - Movto Caja, 
     *                3 - deposito de clientes, 4 - recepcion pagos
     * 
     */
    public function guardaFolioMovimiento($idUsuario, $idSucursal, $idFolio, $tipoMov)
    {
        return $this->db->query('INSERT INTO vtcortedet VALUES(' .
            '(SELECT nIdCorte from vtcorte ' .
            'WHERE nIdSucursal = ' . $idSucursal . ' AND nIdUsuario = ' . $idUsuario .
            ' ORDER BY nIdCorte DESC LIMIT 1), ' .
            'NOW(), \'' . $tipoMov . '\', ' . $idFolio . ')');
    }

    /**
     * @param mixed  $id Id del corte
     * @param string $tipo Tipo de resultado: 
     *                     1 - Todos, 2 - Solo Facturas, 3 - solo remisiones
     * 
     */
    public function getLstCorteVentas($id, $tipo = '1')
    {
        if ($tipo == '1') {
            $camposAdicionales = ', ';
            $where = 'AND c.cTipoCliente = \'P\'';
        } elseif ($tipo == '2') {
            $where = 'AND b.nFolioFactura IS NOT NULL';
        } else {
            $where = 'AND b.nFolioFactura IS NULL';
        }
        return $this->db->query(
            'SELECT a.nFolioRemision, IFNULL(CONCAT(b.sSerie, \' \', b.nFolioFactura), \'0\') as nFolioFactura, ' .
                'a.nTotal, IFNULL(b.nTotalFactura, 0) AS nTotalFactura, ' .
                'IFNULL(( ' .
                'SELECT SUM(bb.nImporte) as sumaNC FROM vtventasnc bb WHERE bb.nIdVentas = a.nIdVentas ' .
                ' ), 0) as notaCred ' .
                'FROM vtventas a ' .
                'LEFT JOIN vtfacturas b ON b.nIdVentas = a.nIdVentas ' .
                'INNER JOIN vtcliente c ON c.nIdCliente = a.nIdCliente ' .
                'WHERE a.nIdVentas IN ' .
                '(SELECT nIdMovto FROM vtcortedet WHERE nIdCorte = ' . $id . ' AND cTipoMov = \'1\') ' .
                'AND a.cEdo <> 5 ' .
                $where,
            false
        )->getResultArray();
    }

    public function getLstCorteCajero($idCorte)
    {
        //  se lee el archivo del corte y se devuelven los datos
        return $this->db->query(
            'SELECT a.nIdVentas, IFNULL(b.nFolioFactura, 0) AS nFactura, ' .
                'DATE_FORMAT(a.dtAlta, \'%d/%m/%Y\') AS dtAlta, a.nFolioRemision, a.nIdCliente, ' .
                'a.nSubTotal, a.nImpuestos, a.nTotal, a.cEdo, ' .
                '( ' .
                'SELECT GROUP_CONCAT(aa.nIdTipoPago, \'&\', aa.nImporte ORDER BY aa.nIdTipoPago SEPARATOR \'|\') ' .
                'FROM vtventaspago aa WHERE aa.nIdVentas = a.nIdVentas ' .
                ') AS cadPagos, SUM(IFNULL(vdx.nImpDescuentoProd + vdx.nImpDescuentoGral, 0)) AS sumaDescuento, ' .
                'IFNULL(( ' .
                'SELECT SUM(bb.nImporte) as sumaNC FROM vtventasnc bb WHERE bb.nIdVentas = a.nIdVentas ' .
                ' ), 0) as notaCred ' .
                'FROM vtventas a ' .
                'LEFT JOIN vtfacturas b ON a.nIdVentas = b.nIdVentas ' .
                'INNER JOIN vtventasdet c ON a.nIdVentas = c.nIdVentas ' .
                'LEFT JOIN vtventasdetaux vdx ON c.nIdVentasDet = vdx.nIdVentasDet ' .
                'WHERE a.nIdVentas IN (SELECT nIdMovto FROM vtcortedet WHERE nIdCorte = ' . $idCorte . ' AND cTipoMov = \'1\') ' .
                'GROUP BY a.nIdVentas ' .
                'ORDER BY a.nIdVentas ',
            false
        )->getResultArray();
    }

    public function getLstCorteEScaja($id, $soloEfectivo = true)
    {
        $having = '';
        if ($soloEfectivo)
            $having = 'HAVING nIdTipoPago = 1 ';
        else
            $having = 'HAVING nIdTipoPago <> 1 ';

        return $this->db->query(
            'SELECT a.cTipoMov, a.nImporte, a.sMotivo, ' .
                'IFNULL(d.sLeyenda, \'Efectivo\') AS nomPago, IFNULL(c.nIdTipoPago, 1) AS nIdTipoPago, ' .
                'IFNULL(v.nFolioRemision, 0) AS nFolioRemision ' .
                'FROM vtescaja a ' .
                'LEFT JOIN vtescreditopago b ON a.nIdEScaja = b.nIdEScaja ' .
                'LEFT JOIN vtcreditopago c ON b.nIdCreditoPago = c.nIdCreditoPago ' .
                'LEFT JOIN vttipopago d ON c.nIdTipoPago = d.nIdTipoPago ' .
                'LEFT JOIN vtventas v ON c.nIdVentas = v.nIdVentas ' .
                'WHERE a.nIdEScaja IN ' .
                '(SELECT nIdMovto FROM vtcortedet WHERE nIdCorte = ' . $id . ' AND cTipoMov = \'2\') ' .
                $having .
                'ORDER BY a.cTipoMov DESC',
            false
        )->getResultArray();
    }

    public function getLstCortePagos($id)
    {
        return $this->db->query(
            'SELECT a.nIdTipoPago, IF(b.sTipoSAT = \'01\' AND b.cTipo = \'0\', \'1\', \'0\') as efectivo, ' .
                'b.sDescripcion, SUM(a.nImporte) nImporte ' .
                'FROM vtventaspago a ' .
                'INNER JOIN vttipopago b ON b.nIdTipoPago = a.nIdTipoPago ' .
                'INNER JOIN vtventas c ON a.nIdVentas = c.nIdVentas ' .
                'WHERE a.nIdVentas IN ' .
                '(SELECT nIdMovto FROM vtcortedet WHERE nIdCorte = ' . $id . ' AND cTipoMov = \'1\') ' .
                'AND c.cEdo <> 5 ' .
                'GROUP BY a.nIdTipoPago ORDER BY a.nIdTipoPago',
            false
        )->getResultArray();
    }

    public function getLstCorteDepositos($id, $soloEfectivo = true)
    {
        $where = '';
        if ($soloEfectivo)
            $where = 'AND a.nIdTipoPago IN (0, 1)';
        else
            $where = 'AND a.nIdTipoPago NOT IN (0, 1)';

        return $this->db->query(
            'SELECT a.nIdCliente, a.nImporte, a.sObservaciones, b.sNombre, ' .
                'IFNULL(c.sLeyenda, \'Efectivo\') as nomPago ' .
                'FROM vtpagosadelantados a ' .
                'INNER JOIN vtcliente b ON b.nIdCliente = a.nIdCliente ' .
                'LEFT JOIN vttipopago c ON a.nIdTipoPago = c.nIdTipoPago ' .
                'WHERE a.nIdPagosAdelantados IN ' .
                '(SELECT nIdMovto FROM vtcortedet WHERE nIdCorte = ' . $id . ' AND cTipoMov = \'3\') ' .
                $where .
                'ORDER BY a.nIdPagosAdelantados DESC',
            false
        )->getResultArray();
    }

    public function getCorteFacturasParaProcesar($idCorte, $tipo = 'C')
    {
        if ($tipo == 'C')
            $q = 'SELECT f.nIdFacturas, v.nTotal, c.sNombre, v.nFolioRemision, ' .
                'v.bVariasFacturas, f.nIdVentas, v.nIdCliente, f.sSerie, f.nFolioFactura ' .
                'FROM vtcortedet cd ' .
                'INNER JOIN vtventas v ON cd.nIdMovto = v.nIdVentas ' .
                'LEFT JOIN vtfacturas f ON v.nIdVentas = f.nIdVentas ' .
                'INNER JOIN vtcliente c ON v.nIdCliente = c.nIdCliente ' .
                'WHERE cd.nIdCorte = ' . $idCorte . ' AND cd.cTipoMov = \'1\' ' .
                'HAVING f.nIdFacturas IS NOT NULL ';
        else    // tipo V de venta
            $q = 'SELECT f.nIdFacturas, v.nTotal, c.sNombre, v.nFolioRemision, ' .
                'v.bVariasFacturas, f.nIdVentas, v.nIdCliente, f.sSerie, f.nFolioFactura ' .
                'FROM vtventas v ' .
                'LEFT JOIN vtfacturas f ON v.nIdVentas = f.nIdVentas ' .
                'INNER JOIN vtcliente c ON v.nIdCliente = c.nIdCliente ' .
                'WHERE v.nIdVentas = ' . $idCorte . ' ' .
                'HAVING f.nIdFacturas IS NOT NULL ';
        return $this->db->query($q, false)->getResultArray();
    }

    public function getAvanceCorteFacturasParaProcesar($idCorte, $tipo = 'C')
    {
        if ($tipo == 'C')
            $q = 'SELECT SUM(IF(IFNULL(f.sUUID, \'\') = \'\', 0, 1)) AS timbrados, ' .
                'COUNT(f.nIdFacturas) as totales,  f.nIdFacturas ' .
                'FROM vtcortedet cd ' .
                'INNER JOIN vtventas v ON cd.nIdMovto = v.nIdVentas ' .
                'LEFT JOIN vtfacturas f ON v.nIdVentas = f.nIdVentas ' .
                'INNER JOIN vtcliente c ON v.nIdCliente = c.nIdCliente ' .
                'WHERE cd.nIdCorte = ' . $idCorte . ' AND cd.cTipoMov = \'1\' ' .
                'AND f.nIdFacturas IS NOT NULL ';
        else
            $q = 'SELECT SUM(IF(IFNULL(f.sUUID, \'\') = \'\', 0, 1)) AS timbrados, ' .
                'COUNT(f.nIdFacturas) as totales,  f.nIdFacturas ' .
                'FROM vtventas v ' .
                'LEFT JOIN vtfacturas f ON v.nIdVentas = f.nIdVentas ' .
                'INNER JOIN vtcliente c ON v.nIdCliente = c.nIdCliente ' .
                'WHERE v.nIdVentas = ' . $idCorte . ' ' .
                'AND f.nIdFacturas IS NOT NULL ';

        return $this->db->query($q, false)->getResultArray();
    }

    public function repCorteVentas(DateTime $dFecIni = null, DateTime $dFecFin = null, $idSuc = false, $soloUsuarios = false, $idUsuario = false)
    {
        $a = [
            'vtcorte.dtApertura >= ' => $dFecIni->format('Y-m-d') . ' 00:00:00',
            'vtcorte.dtApertura <= ' => $dFecFin->format('Y-m-d') . ' 23:59:59'
        ];
        if ($idSuc) {
            $a['vtcorte.nIdSucursal'] = $idSuc;
        }

        if ($soloUsuarios) {
            $a['cd.cTipoMov'] = '1';
            $this->select('vtcorte.nIdUsuario, u.sNombre ', false)
                ->where($a)
                ->join('sgusuario u', 'vtcorte.nIdUsuario = u.nIdUsuario', 'inner')
                ->join('vtcortedet cd', 'vtcorte.nIdCorte = cd.nIdCorte', 'inner')
                ->builder()->groupBy('vtcorte.nIdUsuario')->orderBy('u.sNombre', 'ASC');
        } else {
            if ($idUsuario !== false) $a['vtcorte.nIdUsuario'] = $idUsuario;
            $a['cd.cTipoMov'] = '1';
            $a['v.cEdo <>'] = '5';
            $this->select(
                'vtcorte.nIdCorte, vtcorte.nNumCaja, vtcorte.dtApertura, ' .
                    'v.nIdVentas, v.nFolioRemision, v.nTotal, v.dtAlta, ' .
                    'c.sNombre, ' .
                    'if(s.nIdVentas IS NULL, \'0\', \'1\') AS conCredito, ' .
                    'IFNULL(s.nSaldo, 0) AS nSaldo, ' .
                    'p.nIdTipoPago as tipopago, tp.sLeyenda, tp.sTipoSAT, ' .
                    'p.nImporte AS nImportePagado, ' .
                    'DATE_FORMAT(vtcorte.dtApertura, \'%Y-%m-%d\') AS fecApertura, ' .
                    'u.sNombre as nomUsuario, ' .
                    'IFNULL((SELECT SUM(nc.nImporte) AS nImporteNC FROM vtventasnc nc WHERE nc.nIdVentas = v.nIdVentas), 0) AS nImporteNC',
                false
            )
                ->where($a)
                ->join('vtcortedet cd', 'vtcorte.nIdCorte = cd.nIdCorte', 'inner')
                ->join('vtventas v', 'cd.nIdMovto = v.nIdVentas', 'inner')
                ->join('vtcliente c', 'v.nIdCliente = c.nIdCliente', 'inner')
                ->join('vtventassaldo s', 'v.nIdVentas = s.nIdVentas', 'left')
                ->join('vtventaspago p', 'v.nIdVentas = p.nIdVentas', 'inner')
                ->join('vttipopago tp', 'p.nIdTipoPago = tp.nIdTipoPago', 'inner')
                ->join('sgusuario u', 'vtcorte.nIdUsuario = u.nIdUsuario', 'inner')
                ->orderBy('vtcorte.nNumCaja', 'ASC')
                ->orderBy('vtcorte.nIdCorte', 'ASC')
                ->orderBy('vtcorte.dtApertura', 'ASC');
        }


        // $m = $this->builder()->getCompiledSelect(false);
        // echo $m; exit;
        return $this->findAll();
    }
}
/*


*/
