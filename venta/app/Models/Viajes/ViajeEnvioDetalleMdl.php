<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;

class ViajeEnvioDetalleMdl extends Model
{
    protected $table      = 'enviajeenviodetalle';

    protected $allowedFields = [
        'nIdViajeEnvio',
        'nIdArticulo',
        'fPorRecibir',
        'fPeso',
        'cModoEnv',
        'dtAlta',
        'dtBaja'
    ];

    protected $primaryKey = 'nIdViajeEnvioDetalle';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function getRegistros($id = 0)
    {
        $resp = $this->where(['nIdViajeEnvio' => $id])->findAll();
        return $resp;
    }

    public function getArtsEnViaje($id = 0, $idViaje = 0)
    {
        $model = $this
            ->select(
                "nIdViaje, ve.nIdEnvio, nIdEnvioDetalle, " .
                    "ed.nIdArticulo, ed.cModoEnv, ed.fCantidad, " .
                    "ed.fRecibido, ed.fPorRecibir, " .
                    "$this->table.nIdViajeEnvio, $this->table.fPorRecibir fXRecibir, $this->table.nIdViajeEnvioDetalle, " .
                    "ev.nIdOrigen, ev.cOrigen, ev.nIdSucursal"
            )
            ->join("enviajeenvio ve", "ve.nIdViajeEnvio = $this->table.nIdViajeEnvio", "left")
            ->join("enenviodetalle ed", "ed.nIdEnvio = ve.nIdEnvio AND ed.nIdArticulo = $this->table.nIdArticulo", "left")
            ->join("enenvio ev", "ev.nIdEnvio = ve.nIdEnvio", "left")
            ->where("ve.nIdViaje", $idViaje);
        $regs = $model->findAll();
        return $regs;
    }

    public function getArtsEnEnvio($idEnvio = 0, $idArticulo = 0)
    {
        $model = $this
            ->select(
                "nIdViaje, ve.nIdEnvio, nIdEnvioDetalle, " .
                    "ed.nIdArticulo, $this->table.cModoEnv, ed.fCantidad, " .
                    "ed.fRecibido, ed.fPorRecibir, " .
                    "$this->table.nIdViajeEnvio, $this->table.fPorRecibir fXRecibir, $this->table.nIdViajeEnvioDetalle, " .
                    "ev.nIdOrigen, ev.cOrigen, ev.nIdSucursal"
            )
            ->join("enviajeenvio ve", "ve.nIdViajeEnvio = $this->table.nIdViajeEnvio", "left")
            ->join("enenviodetalle ed", "ed.nIdEnvio = ve.nIdEnvio AND ed.nIdArticulo = $this->table.nIdArticulo", "left")
            ->join("enenvio ev", "ev.nIdEnvio = ve.nIdEnvio", "left")
            ->where("ve.nIdEnvio", $idEnvio);
        if ($idArticulo > 0)
            $model->where("$this->table.nIdArticulo", $idArticulo);
        $regs = $model->first();
        return $regs;
    }
}
