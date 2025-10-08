<?php

namespace App\Models\Viajes;

use CodeIgniter\Model;
use DateInterval;
use DateTime;

class EnvioMdl extends Model
{
    protected $table = 'enenvio';

    protected $allowedFields = [
        'nIdOrigen', 'cOrigen', 'nIdChofer',
        'nIdSucursal', 'nIdCliente', 'dSolicitud',
        'dAsignacion', 'dEntrega', 'sObservacion',
        'nIdDirEntrega', 'cEstatus'
    ];

    protected $primaryKey = 'nIdEnvio';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    // public function getEnvioEnViaje($id, $idViaje = false)
    // {
    //     $select = 'enenvio.*, c.sNombre AS nomCli, ' .
    //         'd.sEnvDireccion, d.sEnvReferencia, d.sEnvEntrega, d.sEnvColonia, d.sEnvTelefono';

    //     if ($idViaje) {
    //         $select .= ', IFNULL(ve.sObservacion, \'\') AS sObservacion';
    //         $this->join('enviajeenvio ve', 'enenvio.nIdEnvio = ve.nIdEnvio AND ve.nIdViaje = ' .  $idViaje, 'left');
    //     } else {
    //         $select .= ', \'\' AS sObservacion';
    //     }
    //     return $this->select($select)
    //         ->join('vtcliente c', 'enenvio.nIdCliente = c.nIdCliente', 'inner')
    //         ->join('vtdirentrega d', 'enenvio.nIdDirEntrega = d.nIdDirEntrega', 'inner')
    //         ->where('nIdEnvio', $id)->first();
    // }

    public function getEnviosPendientesParaViaje($idEnvio = false, $idSuc = false, $nIdViaje = false, $soloEnviosDelViaje = true)
    {
        if ($idEnvio) {
            $where = 'enenvio.nIdEnvio = ' . $idEnvio;
        } else {
            if ($soloEnviosDelViaje)
                $whereEnviosPendientes = "";
            else    // para consulta, solo se muestran los envios del viaje
                $whereEnviosPendientes = "select a.nIdEnvio " .
                    "from enenvio a " .
                    "where a.cEstatus IN ('0', '1', '2', '3', '4') " .
                    " UNION ";

            $where = "enenvio.nIdEnvio = ANY ( " . $whereEnviosPendientes .
                "select b.nIdEnvio " .
                "from enviajeenvio b " .
                "where b.nIdViaje = " . ($nIdViaje ? $nIdViaje : '0') . " and b.nIdEnvio > 0" .
                " ) and enenvio.nIdSucursal = " . $idSuc;
        }
        $this->select(
            "enenvio.nIdEnvio, enenvio.nIdOrigen, enenvio.cOrigen, enenvio.nIdSucursal, " .
                "enenvio.dSolicitud, enenvio.dAsignacion, enenvio.dEntrega, enenvio.nIdDirEntrega, " .
                "c.nIdCliente, c.sNombre AS nomCli,  " .
                "d.sEnvDireccion, d.sEnvReferencia, d.sEnvEntrega, d.sEnvColonia, d.sEnvTelefono, 0 as nIdViajeEnvio," .
                "IFNULL(vt.nFolioRemision, 0) as nFolioRemision, IFNULL(tr.nIdTraspaso, 0) as nFolioTraspaso," .
                "IFNULL(vt.dtAlta, '') as dAltaVenta, IFNULL(tr.dtAlta, '') as dAltaTraspaso," .
                "IFNULL(tr.dSolicitud, '') as dSolTraspaso, IFNULL(tr.nIdSucursal, '') as idSucursalDest, ".
                "'0' AS marca, 0 AS peso, '0' AS conDevolucion ",
            false
        )
            ->join("vtcliente c", "c.nIdCliente = enenvio.nIdCliente", "inner")
            ->join("vtdirentrega d", "d.nIdDirEntrega = enenvio.nIdDirEntrega", "left")
            ->join("vtventas vt", "enenvio.nIdOrigen = vt.nIdVentas", "left")
            ->join("altraspaso tr", "enenvio.nIdOrigen = tr.nIdTraspaso", "left")
            ->where($where, null, false)
            ->orderBy('enenvio.nIdEnvio', 'asc');
        if ($idEnvio)
            return $this->first();
        else
            return $this->findAll();
    }
}
