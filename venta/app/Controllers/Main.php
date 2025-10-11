<?php

namespace App\Controllers;

class Main extends BaseController
{

    public function index()
    {
        // Validar sesión
        $r = $this->validaSesion(true);
        if ($r !== false) return $r;

        // Verificar permiso de acceso a caja
        $permisoAccesoCajero = false; // isset($this->aPermiso['oAccessCaja']);

        $dataDesktop = $this->dataMenu;
        /*
        // Preparar datos para la vista SPA
        $dataSPA = [
            'menuItems' => $resultado[1],  // Array estructurado del menú
            'nIdUsuario' => $this->nIdUsuario,
            'slogin' => $this->sLogin,
            'sucursal' => $this->sSucursal,
            'aInfoSis' => $this->aInfoSis,
        ];

        // Datos adicionales para desktop
        $dataDesktop = $dataSPA;
        */
        if ($permisoAccesoCajero) {
            $dataDesktop['paraCaja'] = true;
        }

        // Cargar vistas con los datos del SPA
        echo view('templates/header', $this->dataMenu);
        echo view('desktop', $dataDesktop);
        echo view('templates/footer', $this->dataMenu);
    }

    public function desktopContent()
    {
        $r = $this->validaSesion(true);
        if ($r !== false) return $r;

        $permisoAccesoCajero =  false; // isset($this->aPermiso['oAccessCaja']);

        $dataDesktop = $this->dataMenu;

        if ($permisoAccesoCajero) {
            $dataDesktop['paraCaja'] = true;
        }

        // SOLO devuelve la vista desktop, SIN header ni footer
        echo view('desktop', $dataDesktop);
    }
}
