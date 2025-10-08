<?php

namespace App\Controllers\Seguridad;

use App\Controllers\BaseController;
use App\Models\Seguridad\Modulos\ModulosMdl;
use App\Models\Seguridad\PerfilMdl;
use App\Models\Seguridad\PermisoPerfilMdl;

class Perfiles extends BaseController
{
    public function index()
    {
        $this->validaSesion();

        $mdlPerfiles = new PerfilMdl();
        $data = [
            'registros' => $mdlPerfiles->getRegistros(false, 10),
            'pager' => $mdlPerfiles->pager
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('seguridad/Modulos/perfiles', $data);
        echo view('templates/footer', $this->dataMenu);
    }


    public function accion($tipoaccion, $id = 0)
    {
        $mdlPerfiles = new PerfilMdl();
        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo[$tipoaccion];
        $data = [
            'titulo' => $stitulo . ' Perfil',
            'frmURL' => base_url('perfil/' . $tipoaccion . '/' . $id),
            'modo' => strtoupper($tipoaccion),
            'id' => $id
        ];
        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'b') {
                $mdlPerfiles->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $r = [
                    'sPerfil' => $this->request->getVar('sPerfil')
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdPerfil'] = $id;
                }
                $mdlPerfiles->save($r);
                if ($tipoaccion == 'a') $id = $mdlPerfiles->getInsertID();
                $this->guardaPermisos($id);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $mdlPerfiles->getRegistros($id);
            }
        }
        // cargar el arbol de permisos
        $mdlModulos = new ModulosMdl();
        $p = $id == '' ? '0' : $id;
        $data['registros'] = $mdlModulos->getModulosConPermisosPerfil($p);
        echo view('seguridad/Modulos/perfilmtto', $data);
    }
   
    public function validaCampos()
    {

        $reglas = [
            'sPerfil' => 'required|min_length[3]',
        ];
        $reglasMsj = [
            'sPerfil' => [
                'required'   => 'Se requiere el nombre del perfil',
                'min_length' => 'Debe tener 3 caracteres como mínimo'
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    private function guardaPermisos($id)
    {
        $mdlPermisos = new PermisoPerfilMdl();
        $regsPerfil = $mdlPermisos->leePermisosPerfil($id);
        $topePerfil = count($regsPerfil);
        $aPermisos = $this->request->getVar('permisos') ?? [];
        $nPos = -1;
        foreach($aPermisos as $v) {
            $nPos++;
            $r = [ 'nIdMenu' => $v, 'nIdPerfil' => $id ];
            if($nPos < $topePerfil) $r['nIdPermisoPerfil'] = $regsPerfil[$nPos]['nIdPermisoPerfil'];
            $mdlPermisos->save($r);
        }
        for($i = $nPos + 1; $i < $topePerfil; $i++){
            $r = [
                'nIdMenu' => 0,
                'nIdPermisoPerfil' => $regsPerfil[$i]['nIdPermisoPerfil']
            ];
            $mdlPermisos->save($r);
        }

    }
}
