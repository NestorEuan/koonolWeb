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
        $permisoAccesoCajero = isset($this->aPermiso['oAccessCaja']);
        
        // Reconstruir menú en formato array para SPA
        $menubuilder = new \App\Models\Seguridad\MenuMdl();
        $resultado = $menubuilder->buildMenu(0, $this->nIdUsuario);
        
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
        if ($permisoAccesoCajero) {
            $dataDesktop['paraCaja'] = true;
        }
        
        // Cargar vistas con los datos del SPA
        echo view('templates/header', $dataSPA);
        echo view('desktop', $dataDesktop);
        echo view('templates/footer', $dataSPA);
    }
}