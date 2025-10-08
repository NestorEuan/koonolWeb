<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class VentasMdl extends Model
{
    protected $table = 'vtventas';

    protected $allowedFields = [
        'nIdSucursal',
        'nFolioRemision',
        'nIdCliente',
        'cEdo',
        'nSubTotal',
        'nImpuestos',
        'nTotal',
        'bEnvio',
        'nPorcenDescuento',
        'nImpDescuento',
        'nPorcenComision',
        'nImpComision',
        'cEdoEnvio',
        'cEdoEntrega',
        'nIdDirEntrega',
        'cClasificacionVenta',
        'cNomEntrega',
        'dtCancela',
        'nIdCancela',
        'sMotivoCancela',
        'bVariasFacturas',
        'nIdUsuario'
    ];

    protected $primaryKey = 'nIdVentas';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';
    // consulta por rango de fechas y por id
    public function getRegistros($id = false, DateTime $dFecIni = null, DateTime $dFecFin = null, $orden = 'DESC', $paginado = true, $idSuc = 0, $tipoFiltro = false, $filtro = false)
    {
        $this->select(
            'vtventas.*, b.sNombre, IFNULL(c.nFolioFactura, 0) as nFolioFactura, ' .
                'IFNULL(c.sUUID, \'\') as sUUID, ' .
                'IFNULL(c.nTotalFactura, 0) as nTotalFactura, ' .
                'DATE_FORMAT(vtventas.dtAlta, \'%d-%m-%Y\' ) as fecha ,' .
                'IFNULL(u.sNombre, \'\') as nomUsuario, ' .
                'CASE vtventas.cEdo ' .
                'WHEN \'1\' THEN \'Por Surtir\' ' .
                'WHEN \'2\' THEN \'Surtido Asignado\' ' .
                'WHEN \'3\' THEN \'Facturado\' ' .
                'WHEN \'4\' THEN \'Timbrado\' ' .
                'WHEN \'5\' THEN \'Cancelado\' ' .
                'WHEN \'6\' THEN \'Cancelado Parcial\' ' .
                'WHEN \'7\' THEN \'Cerrado\' ' .
                'END as cEstatus, ' .
                'CASE vtventas.cEdoEntrega ' .
                'WHEN \'0\' THEN \'\' ' .
                'WHEN \'1\' THEN \'Por Entregar\' ' .
                'WHEN \'2\' THEN \'Entregado Parcial\' ' .
                'WHEN \'3\' THEN \'Entregado\' ' .
                'END as cEdoEntrega, ' .
                'CASE vtventas.cEdoEnvio ' .
                'WHEN \'0\' THEN \'\' ' .
                'WHEN \'1\' THEN \'Por Enviar\' ' .
                'WHEN \'2\' THEN \'Asignado al chofer\' ' .
                'WHEN \'3\' THEN \'Asignado a Viaje\' ' .
                'WHEN \'4\' THEN \'Enviado Parcial\' ' .
                'WHEN \'5\' THEN \'Enviado\' ' .
                'END as cEdoEnvio , ' .
                '(SELECT SUM(f.nPorEntregar) FROM vtventasdet as f WHERE f.nIdVentas = vtventas.nIdVentas ) AS sumEntrega, ' .
                'IFNULL((SELECT fa.nIdFacturas FROM vtfacturas fa WHERE fa.nIdVentas = vtventas.nIdVentas AND fa.sUUID IS NULL  LIMIT 1), 0) AS conFactSinTimbrar ',
            false
        )->join('vtcliente as b', 'b.nIdCliente = vtventas.nIdCliente', 'inner')
            ->join('vtfacturas as c', 'c.nIdVentas = vtventas.nIdVentas', 'left')
            ->join('sgusuario as u', 'u.nIdUsuario = vtventas.nIdUsuario', 'left');
        if ($id === false) {
            if ($dFecIni === null) {
                $dFecFin = new DateTime();
                $dFecIni = clone $dFecFin;
                $dFecIni->sub(new DateInterval('P60D'));
            } else {
                if ($dFecFin == null) {
                    $dFecFin = clone $dFecIni;
                    $dFecFin->add(new DateInterval('P1M'));
                }
            }
            $a = [
                'vtventas.dtAlta >= ' => $dFecIni->format('Y-m-d') . ' 00:00:00',
                'vtventas.dtAlta <= ' => $dFecFin->format('Y-m-d') . ' 23:59:59'
            ];
            if ($idSuc != 0) {
                $a['vtventas.nIdSucursal'] = $idSuc;
            }
            $this->where($a)->orderBy('dtAlta', $orden);
            // if ($idUsu) {
            //     $this->whereIn('vtventas.nIdUsuario', [0, intval($idUsu)]);
            // }
            if ($tipoFiltro === '1') {
                // $this->having('sumEntrega >', 0);
                $this->whereNotIn('vtventas.cEdo', ['5', '7']);
                $this->where('(SELECT SUM(f.nPorEntregar) FROM vtventasdet AS f WHERE f.nIdVentas = vtventas.nIdVentas) > 0', null, true);
            } elseif ($tipoFiltro === '2') {
                $this->whereIn('vtventas.nIdUsuario', [0, intval($filtro)]);
            } elseif ($tipoFiltro === '3') {
                $this->like('b.sNombre', $filtro);
            } elseif ($tipoFiltro === '4') {
                $this->where('vtventas.nFolioRemision', $filtro);
            } elseif ($tipoFiltro === '5') {
                $this->whereNotIn('vtventas.cEdo', ['5', '7']);
                $this->whereIn('vtventas.nIdUsuario', [0, intval($filtro)]);
                $this->where('(SELECT SUM(f.nPorEntregar) FROM vtventasdet AS f WHERE f.nIdVentas = vtventas.nIdVentas) > 0', null, true);
            }
            if ($paginado) {
                return $this->paginate(8);
            } else {
                return $this->findAll();
            }
        }
        return $this->where(['vtventas.nIdVentas' => $id])->first();
    }

    public function getRegs($idSuc = false, $id = 0, $aCond = null, $paginado = 0, $conDetalle = false)
    {
        $join1 = 'vtcliente c';
        $wjoin1 = 'c.nIdCliente = vtventas.nIdCliente';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = vtventas.nIdSucursal';
        $join3 = 'vtventassaldo vs';
        $wjoin3 = 'vs.nIdVentas = vtventas.nIdVentas';

        $aWhere = [];
        if ($idSuc != false)
            $aWhere = ['a.nIdSucursal' => $idSuc];
        $selectAdd = '';
        if ($conDetalle) {
            $selectAdd = ',vd.nIdArticulo, vd.nCant, vd.nPrecio, vd.nDescuentoTotal,' .
                'ar.sDescripcion AS sDescripcionArt';
        }
        $this
            ->select(
                'vtventas.nIdVentas, vtventas.nIdSucursal, vtventas.nIdCliente, ' .
                    'vs.nSaldo fSaldo, vtventas.dtAlta, vtventas.dtAlta as dcompra, ' .
                    'cEdoEntrega, a.sDescripcion, c.sNombre, vtventas.nFolioRemision, vtventas.nTotal AS nTotalVenta, ' .
                    'a.sDescripcion AS nomSucursal ' .
                    $selectAdd
            )
            ->join($join1, $wjoin1, 'left')
            ->join($join2, $wjoin2, 'left')
            ->join($join3, $wjoin3, 'left')
            ->orderBy('vtventas.nIdSucursal', 'ASC')
            ->orderBy('vtventas.nIdVentas', 'DESC');
        if ($conDetalle) {
            $this->join('vtventasdet vd', 'vtventas.nIdVentas = vd.nIdVentas', 'left')
                ->join('alarticulo ar', 'vd.nIdArticulo = ar.nIdArticulo', 'inner');
        }
        if ($aCond != null) {
            if ($aCond['dIni'] != null && $aCond['Edo'] != '0') {
                $aWhere1 = [];
                $aWhere1['vtventas.dtAlta >='] = $aCond['dIni'];
                $aWhere1['vtventas.dtAlta <'] = (new DateTime($aCond['dFin']))
                    ->add(new DateInterval('P1D'))
                    ->format('Y-m-d');
                $this->where($aWhere1);
            }
            switch ($aCond['Edo']) {
                case '0':
                    $this->where('vs.dFecSaldado IS NULL', null, false);
                    break;
                case '1':
                    $this->where('vs.dFecSaldado IS NOt NULL', null, false);
                    break;
            }
            if ($aCond['idCliente'] != '') $this->where(['vtventas.nIdCliente' => $aCond['idCliente']]);
        }

        if ($id === 0) {
            $this->where($aWhere);
            // echo $this->builder()->getCompiledSelect(); exit;
            if ($paginado === 0)
                return $this->findAll();
            else
                return $this->paginate($paginado);
        }

        $aWhere['vtventas.nIdVentas'] = $id;
        return $this->where($aWhere)->first();
    }

    public function getRemision($idsuc, $id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where([
            'nIdSucursal' => $idsuc,
            'nFolioRemision' => $id
        ])->first();
    }

    public function repVentasMayorMenor(DateTime $dFecIni = null, DateTime $dFecFin = null, $idSuc = false, $paginado = false, $orden = false)
    {
        $a = [
            'vtventas.dtAlta >= ' => $dFecIni->format('Y-m-d') . ' 00:00:00',
            'vtventas.dtAlta <= ' => $dFecFin->format('Y-m-d') . ' 23:59:59'
        ];
        if ($idSuc) {
            $a['vtventas.nIdSucursal'] = $idSuc;
        }
        if ($orden) {
            if ($orden == '1') {
                $this->orderBy('porCant', 'DESC');
            } else {
                $this->orderBy('cantFac', 'DESC');
            }
        }
        $this->where($a);
        // si la factura esta cancelada no entra el detalle,
        // si la factura ya fue surtida completa pero no tiene
        // 
        $this->select('b.nIdArticulo, c.sDescripcion, SUM(b.nCant) AS porCant, ' .
            'SUM(b.nEntregado) AS porEnt,' .
            'SUM(IF(ISNULL(d.nIdVentas), 0, b.nCant)) AS cantFac')
            ->join('vtventasdet b', 'vtventas.nIdVentas = b.nIdVentas', 'inner')
            ->join('alarticulo c', 'b.nIdArticulo = c.nIdArticulo', 'inner')
            ->join('vtfacturas d', 'vtventas.nIdVentas = d.nIdVentas', 'left')
            ->builder()->groupBy('b.nIdArticulo');

        if ($paginado) {
            return $this->paginate($paginado);
        } else {
            return $this->findAll();
        }
    }

    public function getRepoVentasCorte(DateTime $dFecIni, DateTime $dFecFin, $idSuc = false, $idCli = false)
    {
        $dIni = '\'' . $dFecIni->format('Y-m-d') . ' 00:00:00\' ';
        $dFin = '\'' . $dFecFin->format('Y-m-d') . ' 23:59:59\' ';
        $whereSuc = $idSuc !== false ? ' AND a.nIdSucursal = ' . strval($idSuc) : '';
        $whereCli = $idCli !== false ? ' AND a.nIdCliente = ' . strval($idCli) : '';


        $q = 'SELECT a.nIdVentas, IFNULL(b.nFolioFactura, 0) AS nFactura, b.sSerie, ' .
            'DATE_FORMAT(a.dtAlta, \'%d/%m/%Y\') AS dtAlta, a.nFolioRemision, a.nIdCliente, ' .
            'a.nSubTotal, a.nImpuestos, a.nTotal, IFNULL(b.nTotalFactura, 0) AS nTotalFactura, ' .
            '( ' .
            'SELECT GROUP_CONCAT(aa.nIdTipoPago, \'&\', aa.nImporte ORDER BY aa.nIdTipoPago SEPARATOR \'|\') ' .
            'FROM vtventaspago aa WHERE aa.nIdVentas = a.nIdVentas ' .
            ') AS cadPagos, SUM(c.nDescuentoTotal) AS sumaDescuento, a.cEdo, ' .
            'd.sNombre as nomCliente,  IFNULL(f.nNumCaja, \'0\') as caja ' .
            'FROM vtventas a ' .
            'LEFT JOIN vtfacturas b ON a.nIdVentas = b.nIdVentas ' .
            'INNER JOIN vtventasdet c ON a.nIdVentas = c.nIdVentas ' .
            'INNER JOIN vtcliente d ON a.nIdCliente = d.nIdCliente ' .
            'LEFT JOIN vtcortedet e ON a.nIdVentas = e.nIdMovto AND e.cTipoMov = \'1\'' .
            'INNER JOIN vtcorte f ON e.nIdCorte = f.nIdCorte ' .
            'WHERE a.dtAlta >= ' . $dIni . ' AND a.dtAlta <= ' . $dFin .
            $whereSuc . $whereCli .
            ' GROUP BY a.nIdVentas ' .
            'ORDER BY a.nIdVentas ';
        return $this->db->query($q, false)->getResultArray();
    }

    public function updateEdoSurtido($idSuc = 0, $id = 0, $cTipo = 'Entrega')
    {
        $c_TipoEstado = 'cEdoEntrega';
        if ($cTipo === 'Envio')
            $c_TipoEstado = 'cEdoEnvio';
        //$coco = $this->builder()->set($c_TipoEstado, "$c_TipoEstado + " . '(SELECT IF( SUM(vtventasdet.nCant) - SUM(vtventasdet.nEntregado) = 0,"-1","0") '
        $this->builder()->set($c_TipoEstado, "$c_TipoEstado - 1", false)
            ->where(['nIdSucursal' => intval($idSuc), $this->primaryKey => intval($id)])
            ->update();
        //$coco = $this->builder()->set($c_TipoEstado, "$c_TipoEstado + " . '(SELECT IF( SUM(vtventasdet.nCant) - SUM(vtventasdet.nEntregado) = 0,"-1","0") '
        $this->builder()->set("cEdo", "(SELECT IF( SUM(vtventasdet.nCant) - SUM(vtventasdet.nEntregado) = 0,7,cEdo)" .
            "FROM vtventasdet where vtventasdet.{$this->primaryKey} = $id )", false)
            ->where(['nIdSucursal' => intval($idSuc), $this->primaryKey => intval($id)])
            ->update();
    }
    /*
    public function repVentas(DateTime $dFecIni = null, DateTime $dFecFin = null, $idSuc = false)
    {
        $a = [
            'vtventas.dtAlta >= ' => $dFecIni->format('Y-m-d') . ' 00:00:00',
            'vtventas.dtAlta <= ' => $dFecFin->format('Y-m-d') . ' 23:59:59'
        ];
        $b = [
            'cp.dtPago >= ' => $dFecIni->format('Y-m-d') . ' 00:00:00',
            'cp.dtPago <= ' => $dFecFin->format('Y-m-d') . ' 23:59:59'
        ];
        if ($idSuc) {
            $a['vtventas.nIdSucursal'] = $idSuc;
        }

        $this->select(
            'vtventas.nIdVentas, vtventas.nFolioRemision, vtventas.nTotal, vtventas.dtAlta, ' .
                'c.sNombre, ' .
                'if(s.nIdVentas IS NULL, \'0\', \'1\') AS conCredito, ' .
                'IFNULL(s.nSaldo, 0) AS nSaldo, ' .
                'p.nIdTipoPago as tipopago, tp.sLeyenda, tp.sTipoSAT, ' .
                'p.nImporte AS nImportePagado, ' .
                'IFNULL(cp.dtPago, \'\') AS dtPago, IFNULL(cp.nIdTipoPago, 0) AS nIdTipoPagoCred, IFNULL(cp.fPago, 0) AS pagoCredito ',
            false
        )
            ->where($a)
            ->join('vtcliente c', 'vtventas.nIdCliente = c.nIdCliente', 'inner')
            ->join('vtventassaldo s', 'vtventas.nIdVentas = s.nIdVentas', 'left')
            ->join('vtventaspago p', 'vtventas.nIdVentas = p.nIdVentas', 'inner')
            ->join('vttipopago tp', 'p.nIdTipoPago = tp.nIdTipoPago', 'inner')
            ->join('vtcreditopago cp', 'vtventas.nIdVentas = cp.nIdVentas', 'left')
            ->orderBy('vtventas.dtAlta', 'ASC')
            ->orderBy('cp.nIdCreditoPago', 'ASC');
        $this->builder()->groupStart()
        ->where($b)
        ->orWhere('cp.dtPago IS NULL')
        ->groupEnd();
        // $m = $this->builder()->getCompiledSelect(false);
        // echo $m; exit;
        return $this->findAll();
    }
    */
}
