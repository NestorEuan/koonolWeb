<?php

namespace App\Controllers;
// use App\Models\Seguridad\MenuMdl;


class Main extends BaseController
{

    public function index()
    {
        $r = $this->validaSesion(true);
        if ($r !== false) return $r;
        $permisoAccesoCajero = isset($this->aPermiso['oAccessCaja']);
        // se valida que sea un cajero con acceso directo
        // si es asi, paso a cargar caja directamente
        // y ahi en caja es donde me manda al corte o cierre de caja
        echo view('templates/header', $this->dataMenu);
        $data = $this->dataMenu;
        if ($permisoAccesoCajero) $data['paraCaja'] = true;
        echo view('desktop', $data);
        echo view('templates/footer', $this->dataMenu);
    }
}
