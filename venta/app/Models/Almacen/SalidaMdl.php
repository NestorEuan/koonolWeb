<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class SalidaMdl extends Model
{
    protected $table = 'alsalida';

    protected $allowedFields = [
        'dSolicitud', 'dSalida', 'nIdSucursal', 'nIdProveedor',
        'fTotal', 'nProductos', 'sObservacion', 'cEdoEntrega',
        'cTipoSalida'
    ];

    protected $primaryKey = 'nIdSalida';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    protected $datefld = 'dSalida';

    public function getRegistros($idSuc = false, $id = 0, $paginado = 0, $aCond = null)
    {
        $join1 = 'cpproveedor p';
        $wjoin1 = 'p.nIdProveedor = alsalida.nIdProveedor';
        $join2 = 'alsucursal a';
        $wjoin2 = 'a.nIdSucursal = alsalida.nIdSucursal';
        $aWhere = [];
        if ($idSuc != false)
            $aWhere = ['a.nIdSucursal' => $idSuc];
        $this
            ->join($join1, $wjoin1, 'left')
            ->join($join2, $wjoin2, 'left');
        if ($aCond != null) {
            $edo = false;
            switch ($aCond['Edo']) {
                case '0':
                    $edo = ['0', '2'];
                    break;
                case '1':
                    $edo = ['3'];
                    break;
            }
            if ($edo) $this->whereIn('alsalida.cEdoEntrega', $edo);

            if ($aCond['dIni'] != null) {
                $aWhere['dSolicitud >='] = $aCond['dIni'];
                $aWhere['dSolicitud <='] = $aCond['dFin'];
            }
        }
        if ($id === 0) {
            if ($paginado === 0)
                return $this
                    ->where($aWhere)
                    ->findAll();
            else
                return $this
                    ->where($aWhere)
                    ->paginate($paginado);
        }
        $aWhere[$this->primaryKey] = $id;
        return $this
            ->where($aWhere)
            ->first();
    }

    public function getRegistrosByField($field = 'nIdSalida', $val = false)
    {
        return $this->like([$field => $val])->findAll();
    }

    public function updateFecha($idSuc, $id, $dMovto)
    {
        $this->set($this->datefld, $dMovto)
            ->where(['nIdSucursal' => $idSuc, $this->primaryKey => $id])
            ->update();
    }

    public function updateEdo($idSuc = 0, $id = 0)
    {
        $this->builder()->set('cEdoEntrega', '(SELECT IF( SUM(alsalidadetalle.fCantidad) - SUM(alsalidadetalle.fRecibido) = 0,"3","2") '
            . 'FROM alsalidadetalle where alsalidadetalle.nIdSalida = ' . $id . ')', false)
            //->where('nIdSucursal', intval($idSuc))
            //->where('nIdEntrada',intval($id))
            ->where(['nIdSucursal' => intval($idSuc), 'nIdSalida' => intval($id)])
            ->update()
            //->getCompiledUpdate()
        ;
    }
}
/***********
 * 
cTipoSalida : 
   0 - sin tipificacion
   1 - Conversion (por ejemplo 1 saco de cemento a bolsas de 1 kilo)
   2 - Por Merma
   3 - Para Consumo (por ejemplo cuando de pide algo para el rancho del dueño)
 * 
 ***********/
