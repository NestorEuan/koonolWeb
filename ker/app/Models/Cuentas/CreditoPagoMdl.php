<?php

namespace App\Models\Cuentas;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class CreditoPagoMdl extends Model
{
    protected $table = 'vtcreditopago';

    protected $allowedFields = [
        'nIdVentas', 'dtPago',
        'nIdTipoPago', 'fPago', 'nIdUsuario',
        'cEstadoPago'
    ];

    protected $primaryKey = 'nIdCreditoPago';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getLstRegs($id = 0, $idSuc = false, $aCond = null)
    {
        $this->select('vtcreditopago.*, p.sLeyenda')
            ->join('vttipopago p', 'vtcreditopago.nIdTipoPago = p.nIdTipoPago', 'inner');

        if ($id > 0) {
            return $this->where('vtcreditopago.nIdVentas', intval($id))
                ->orderBy('vtcreditopago.fPago', 'ASC')->findAll();
        }

        $wFec = '';
        $wCli = '';
        $wEdo = '';
        $wSuc = '';

        if ($aCond != null) {
            if ($aCond['dIni'] != null && $aCond['Edo'] != '0') {
                $wFec = 'v.dtAlta >= \'' . $aCond['dIni'] . '\' and ' .
                    'v.dtAlta < \'' . ((new DateTime($aCond['dFin']))
                        ->add(new DateInterval('P1D'))
                        ->format('Y-m-d')) . '\'';
            }

            switch ($aCond['Edo']) {
                case '0':
                    $wEdo = 'vs.dFecSaldado IS NULL';
                    break;
                case '1':
                    $wEdo = 'vs.dFecSaldado IS NOT NULL';
                    break;
            }
            if ($aCond['idCliente'] != '') $wCli = 'v.nIdCliente = ' . $aCond['idCliente'];
            if ($idSuc != false) $wSuc = 'v.nIdSucursal = ' . $idSuc;
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
                $where = 'vtcreditopago.nIdVentas in (select v.nIdVentas from vtventassaldo vs ' .
                    ' inner join vtventas v on vs.nIdVentas = v.nIdVentas where ' . $a . ')';
            else
                $where = 'vtcreditopago.nIdVentas in (select v.nIdVentas from vtventas v ' .
                    ' left join vtventassaldo vs on vs.nIdVentas = v.nIdVentas where ' . $a . ')';
        }
        $this->where($where, null, false);

        return $this->findAll();
    }
}
