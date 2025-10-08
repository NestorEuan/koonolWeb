<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class FacturasMdl extends Model
{
    protected $table = 'vtfacturas';

    protected $allowedFields = [
        'nIdVentas', 'nFolioFactura', 'sSerie', 'cUsoCFDI',
        'cMetodoPago', 'bCancelada', 'sUUID', 'dFechaTimbrado',
        'dFechaFactura', 'nTotalFactura'
    ];

    protected $primaryKey = 'nIdFacturas';

    protected $useTimestamps = true;

    protected $useSoftDeletes = false;

    protected $createdField = 'dtAlta';

    protected $deletedField = '';

    protected $updatedField = '';


    // falta consulta por rango de fechas
    public function getRegistros($id = false)
    {
        if ($id === false) {
            return $this->findAll();
        }
        return $this->where(['nIdVentas' => $id])->first();
    }

    public function getFacturasRemision($idVentas, $timbrada = false)
    {
        if ($timbrada)
            $this->where('sUUID IS NOT NULL AND TRIM(sUUID) <> \'\'', null, true);
        return $this->where(['nIdVentas' => $idVentas])->findAll();
    }

    public function getDatosFactura($idFactura)
    { // $this->select('alsucursal.*, rz.sRFC, rz.sRazonSocial, rz.sIdPAC, rz.sPswPAC, rz.sCertificado, rf.*')
        return $this->db->query(
            'SELECT f.nIdFacturas, f.nFolioFactura, IFNULL(f.sUUID, \'\') AS sUUID , f.nIdVentas, ' .
                ' f.cUsoCFDI, f.cMetodoPago, f.bCancelada, uc.sDescripcion AS sDesUsoCfdi, ' .
                ' f.sSerie, IFNULL(f.dFechaFactura, \'\') AS dFechaFactura, vc.sNombreConstanciaFiscal AS sNombre, ' .
                ' vc.sRFC, vc.email, vc.cCP, vc.cIdRegimenFiscal, vc.cTipoCliente, ' .
                ' sr.sDescripcion AS sRegFis, ' .
                ' s.sCP as lugarExpedicion, s.sEmail AS emailSucursal, vt.nFolioRemision, ' .
                ' rz.sRFC AS rzsRFC, rz.sRazonSocial AS rzsRazonSocial, rz.sIdPAC AS rzsIdPAC, rz.sPswPAC AS rzsPswPAC, rz.sCertificado AS rzsCertificado, ' .
                ' rf.cIdRegimenFiscal AS rfcIdRegimenFiscal, rf.sDescripcion AS rfsDescripcion ' .
                'FROM vtfacturas f ' .
                'INNER JOIN vtventas vt ON f.nIdVentas = vt.nIdVentas ' .
                'INNER JOIN vtcliente vc ON vt.nIdCliente = vc.nIdCliente ' .
                'INNER JOIN satregimenfiscal sr ON vc.cIdRegimenFiscal = sr.cIdRegimenFiscal ' .
                'INNER JOIN alsucursal s ON vt.nIdSucursal = s.nIdSucursal ' .
                'INNER JOIN satrazonsocial rz ON s.nIdRazonSocial = rz.nIdRazonSocial ' .
                'INNER JOIN satregimenfiscal rf ON rz.cIdRegimenFiscal = rf.cIdRegimenFiscal ' .
                'INNER JOIN satusocfdi uc ON f.cUsoCFDI = uc.cIdUsoCfdi ' .
                'WHERE f.nIdFacturas = ' . $idFactura,
            false
        )->getResultArray();
    }

    public function leeDetalleFactura($idFactura, $idVenta)
    {
        $select = 'SELECT vd.nIdVentasDet, vd.nIdArticulo, vd.nPrecio, vd.nDescuentoTotal, ' .
            'aa.sCveProdSer, aa.sCveUnidad, aa.cUnidad, ' .
            'IFNULL(vdd.sDescripcion, aa.sDescripcion) AS sDescripcion ';
        $inner = 'INNER JOIN alarticulo aa ON vd.nIdArticulo = aa.nIdArticulo ' .
            'LEFT JOIN vtventasdetdescripcion vdd ON vd.nIdVentasDet = vdd.nIdVentasDet ';
        $aRegs = $this->db->query(
            $select . ', fd.nCant ' .
                'FROM vtfacturasdet fd ' .
                'INNER JOIN vtventasdet vd ON fd.nIdVentasDet = vd.nIdVentasDet ' .
                $inner .
                'WHERE fd.nIdFacturas = ' . $idFactura,
            false
        )->getResultArray();
        if (count($aRegs) == 0) {
            $aRegs = $this->db->query(
                $select . ', vd.nCant ' .
                    'FROM vtventasdet vd ' . $inner .
                    'WHERE vd.nIdVentas = ' . $idVenta
            )->getResultArray();
        }
        return $aRegs;
    }

    public function getDatosPagos($idVenta)
    {
        return $this->db->query(
            'SELECT vt.sTipoSAT, vp.nImporte ' .
                'FROM vtventaspago vp ' .
                'INNER JOIN vttipopago vt ON vp.nIdTipoPago = vt.nIdTipoPago ' .
                'WHERE vp.nIdVentas = ' . $idVenta .
                ' AND vt.cTipo <> \'1\' ' .
                ' LIMIT 1',
            false
        )->getResultArray();
    }

    public function saveXML($idFactura, &$sXml, &$pdfBase64)
    {
        return $this->db->query(
            'INSERT INTO vtfacturasxml (nIdFactura, sXml, sPDF64) VALUES (' .
                $idFactura . ', \'' . $sXml . '\', \'' . $pdfBase64 . '\')'
        );
    }
}
