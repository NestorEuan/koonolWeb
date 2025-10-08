<?php

namespace App\Models\Seguridad\Modulos;

use CodeIgniter\Model;

class ModulosMdl extends Model
{
    protected $table = 'sgmenu';

    protected $allowedFields = [
        'nIdPadre', 'nOrden', 'sDescripcion',
        'sAbrev', 'sLink'
    ];

    protected $primaryKey = 'nIdMenu';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';


    public function getRegistros($id = false, $idPadre = false)
    {
        if ($id === false) {
            if ($idPadre !== false) {
                $this->where('nIdPadre', $idPadre);
                $this->select(
                    'nIdMenu, nOrden, nIdPadre, ' .
                        'CASE ' .
                        ' WHEN sDescripcion = \'$divider$\' THEN \'-- Separador --\' ' .
                        ' WHEN sDescripcion = \'$opcion$\' THEN CONCAT(\'$OPC$ : \', sAbrev, \' | \', sLink)  ' .
                        ' ELSE sDescripcion ' .
                        'END AS sDescripcion ',
                    false
                );
            }
            $this->orderBy('nIdPadre ASC, nOrden ASC');
            return $this->findAll();
        }
        return $this->where(['nIdMenu' =>  $id])->first();
    }

    public function getModulosConPermisosPerfil($idPerfil = 0)
    {
        $this->select(
            'sgmenu.*, ' .
                ' IFNULL((SELECT a.nIdPermisoPerfil FROM sgpermisoperfil a' .
                ' WHERE a.nIdPerfil = ' . $idPerfil . ' AND a.nIdMenu = sgmenu.nIdMenu), 0) AS conPermiso ',
            false
        )
            ->orderBy('sgmenu.nIdPadre', 'ASC')
            ->orderBy('sgmenu.nOrden', 'ASC');
        $m = $this->builder()->getCompiledSelect(false);
        
            return $this->findAll();
    }
}
