<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class SaldoPagAdelMdl extends Model
{
    protected $table = 'vtsaldopagosadelantados';

    protected $allowedFields = [
        'nIdPagosAdelantados', 'nSaldo'
    ];

    protected $primaryKey = 'nIdSaldoPagosAdelantados';

    protected $useTimestamps = false;

    protected $useSoftDeletes = false;

    protected $createdField = '';

    protected $deletedField = '';

    protected $updatedField = '';

    public function getIdSaldo($id)
    {
        return $this->where('nIdPagosAdelantados', $id)->first();
    }

    public function getIdVacioSaldoDeposito()
    {
        // hago un update para bloquear
        $r = $this->select('nIdSaldoPagosAdelantados as id, nIdPagosAdelantados')
            ->where('nIdPagosAdelantados', 0)->first();
        if (intval($r['id'] ?? 0) == 0) return false;
        $res = $this->set('nSaldo', 0)
            ->where('nIdSaldoPagosAdelantados', intval($r['id']))->update();
        if ($res === false) return false;
        return $r['id'];

        // return $this->select('nIdSaldoPagosAdelantados as id, nIdPagosAdelantados')
        //     ->where('nIdPagosAdelantados = 0 for update ', null, false)
        //     ->builder->get()->getFirstRow('array');
    }

    public function guardaSaldoDeposito($idDeposito, $importe, $id = false)
    {
        $this->set([
            'nIdPagosAdelantados' => $idDeposito,
            'nSaldo' => $importe
        ]);
        if ($id === false) {
            $this->insert();
        } else {
            $this->where('nIdSaldoPagosAdelantados', $id)
                ->update();
        }
    }

    public function recuperaSaldoPorCancelacion($nImporte, $idPagoAdelantado, $idSaldoPagAde = false)
    {
        $a = [
            'nIdPagosAdelantados' => $idPagoAdelantado,
            'nSaldo' => ($idSaldoPagAde === false ? '' : 'nSaldo+') . strval($nImporte)
        ];
        $this->set($a, '', false);
        if ($idSaldoPagAde === false) {
            $this->insert();
        } else {
            $this->where('nIdSaldoPagosAdelantados', $idSaldoPagAde);
            $this->update();
        }
    }

    public function getRegSaldoCliente($idCli)
    {
        $this->select('vtsaldopagosadelantados.*')
            ->join('vtpagosadelantados b', 'vtsaldopagosadelantados.nIdPagosAdelantados = b.nIdPagosAdelantados', 'inner')
            ->where('vtsaldopagosadelantados.nIdPagosAdelantados > 0')
            ->where('b.nIdCliente', intval($idCli))
            ->orderBy('vtsaldopagosadelantados.nIdPagosAdelantados', 'ASC');
        return $this->first();
    }

    public function actualizaSaldo($id, $importe)
    {
        if ($importe == 0) {
            $this->set([
                'nIdPagosAdelantados' => 0,
                'nSaldo' => 0
            ]);
        } else {
            $this->set('nSaldo', $importe);
        }
        return $this->update($id);
    }
}
