<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class VentasSaldoMdl extends Model
{
    protected $table = 'vtventassaldo';

    protected $allowedFields = [
        'nIdCliente',
        'nIdVentas',
        'nSaldo',
        'dFecSaldado'
    ];

    protected $primaryKey = 'nIdVentasSaldo';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function updateSaldo($idSuc, $id, $fImporte)
    {
        $this->builder()
            ->set('nSaldo', 'nSaldo + ' . $fImporte, false)
            ->where(['nIdVentas' => intval($id)])
            ->update();
        $r = $this->where(['nIdVentas' => intval($id)])->first();
        if (round(floatval($r['nSaldo']), 2) == 0) {
            $fec = (new DateTime())->format('Y-m-d');
            $this->builder()
                ->set('dFecSaldado', $fec)
                ->where(['nIdVentas' => intval($id)])
                ->update();
        }
        return true;
    }

    public function getRegs($idSuc = false, $id = 0, $aCond = null, $paginado = 0, $conDetalle = false)
    {
        $bPorRemision = false;
        $aWhere = [];
        if ($idSuc != false) $aWhere = ['s.nIdSucursal' => $idSuc];

        if ($aCond != null) {
            if ($aCond['dIni'] != null && $aCond['Edo'] != '0') {
                $aWhere['v.dtAlta >='] = $aCond['dIni'];
                $aWhere['v.dtAlta <'] = (new DateTime($aCond['dFin']))
                    ->add(new DateInterval('P1D'))
                    ->format('Y-m-d');
                // $this->where($aWhere);
                $periodo = ' v.dtAlta >= \'' . $aCond['dIni'] . '\' AND ' .
                    ' v.dtAlta < \'' . $aWhere['v.dtAlta <'] . '\' ';
            }
            switch ($aCond['Edo']) {
                case '0':
                    $this->where('vtventassaldo.dFecSaldado IS NULL', null, false);
                    break;
                case '1':
                    $this->where('vtventassaldo.dFecSaldado IS NOT NULL', null, false);
                    break;
            }
            if ($aCond['idCliente'] != '') $this->where(['v.nIdCliente' => $aCond['idCliente']]);
            if (array_key_exists('idFolioRemision', $aCond) != null) {
                $aWhere['v.nFolioRemision'] = $aCond['idFolioRemision'];
                $bPorRemision = true;
            }
        }

        $selectAdd = '';
        if ($conDetalle) {
            if ($aCond['dIni'] != null && $aCond['Edo'] == '2' && $aCond['idCliente'] != '') {
                $from = ' (SELECT ' .
                    ' v.nIdVentas, v.nIdCliente, CAST(NULL AS DATETIME) AS dFecSaldado, '  .
                    ' CAST(0 AS DECIMAL(12,2)) AS nSaldo '  .
                    ' FROM vtventas v'  .
                    ' WHERE '  . $periodo .
                    ' AND v.nIdCliente = ' . $aCond['idCliente'] . ' AND v.cEdo <> 5'  .
                    ' UNION '  .
                    ' SELECT v.nIdVentas,  sa.nIdCliente, sa.dFecSaldado, sa.nSaldo '  .
                    ' FROM vtventassaldo sa'  .
                    ' INNER JOIN vtventas v ON sa.nIdVentas = v.nIdVentas'  .
                    ' WHERE sa.nIdCliente = ' . $aCond['idCliente'] .
                    ' AND (sa.dFecSaldado >= \'' . $aCond['dIni'] .  '\' OR sa.dFecSaldado IS NULL)' .
                    ' AND v.dtAlta < \'' . $aWhere['v.dtAlta <'] . '\' AND v.cEdo <> 5' .
                    ') as vtventassaldo ';
                $this->builder->from($from, true);
                unset($aWhere['v.dtAlta >=']);
                unset($aWhere['v.dtAlta <']);
            }
            $selectAdd = ',vd.nIdArticulo, vd.nCant, vd.nPrecio, ' .
                ' IFNULL(vdx.nImpComisionTotal - vdx.nImpDescuentoProd - vdx.nImpDescuentoGral, 0) AS nDescuentoTotal, ' .
                'a.sDescripcion AS sDescripcionArt';
        }
        $this
            ->select(
                'v.nIdVentas, v.nIdSucursal, v.nIdCliente, ' .
                    'vtventassaldo.nSaldo as fSaldo, v.dtAlta, v.dtAlta as dcompra, ' .
                    'v.cEdoEntrega, s.sDescripcion, c.sNombre, v.nFolioRemision, v.nTotal AS nTotalVenta, ' .
                    'v.cEdo AS cEdoVenta, vtventassaldo.dFecSaldado ' .
                    $selectAdd

            )
            ->join('vtventas v', 'vtventassaldo.nIdVentas = v.nIdVentas', 'left')
            ->join('vtcliente c', 'vtventassaldo.nIdCliente = c.nIdCliente', 'left')
            ->join('alsucursal s', 'v.nIdSucursal = s.nIdSucursal', 'left')
            ->orderBy('v.nIdSucursal', 'ASC')
            ->orderBy('v.nIdVentas', 'DESC');
        if ($conDetalle) {
            $this->join('vtventasdet vd', 'v.nIdVentas = vd.nIdVentas', 'left')
                ->join('vtventasdetaux vdx', 'vd.nIdVentasDet = vdx.nIdVentasDet', 'left')
                ->join('alarticulo a', 'vd.nIdArticulo = a.nIdArticulo', 'inner');
        }

        if ($bPorRemision)
            return $this->where($aWhere)->first();

        if ($id === 0) {
            $this->where($aWhere);
            // echo $this->builder()->getCompiledSelect(false);
            // exit;
            if ($paginado === 0)
                return $this->findAll();
            else
                return $this->paginate($paginado);
        }

        $aWhere['vtventassaldo.nIdVentas'] = $id;
        return $this->where($aWhere)->first();
    }

    public function getSaldoCliente($idCli)
    {
        $this->select('count(nIdCliente) AS nCont, SUM(nSaldo) AS nSum');
        $this->where('nIdCliente', $idCli)
            ->where('vtventassaldo.nSaldo >', 0)
            ->builder->groupBy('nIdCliente');
        return $this->findAll();
    }
}
