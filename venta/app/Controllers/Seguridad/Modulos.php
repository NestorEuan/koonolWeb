<?php

namespace App\Controllers\Seguridad;

use App\Controllers\BaseController;
use App\Models\Seguridad\Modulos\ModulosMdl;

class Modulos extends BaseController
{
    public function index()
    {
        $this->validaSesion();

        $mdlModulos = new ModulosMdl();

        $data['regsMod'] =  $mdlModulos->getRegistros(false, 0);

        echo view('templates/header', $this->dataMenu);
        echo view('seguridad/Modulos/modulos', $data);
        echo view('templates/footer', $this->dataMenu);
    }
    //"http://proyectos/repo/ferromat/web/public/modulos/u/mod/5"
    //"http://proyectos/repo/ferromat/web/public/modulos/e/mod/5/0"
    // http://proyectos/repo/ferromat/web/public/modulos/q/mod/4
    // http://proyectos/repo/ferromat/web/public/modulos/q/mod/3
    public function accion($tipoaccion, $tipomov, $id = 0, $idPadre = 0)
    {
        // tipo movto modulo udq
        $mdlModulos = new ModulosMdl();
        $conDialogo = stripos('abe', $tipoaccion) !== false;

        if ($conDialogo) {
            $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
            $stitulo = $aTitulo[$tipoaccion];
            //   modulos/[abe]/mod/(id)/(idpadre)
            $data = [
                'titulo' => $stitulo . ' Modulo',
                'frmURL' => base_url('modulos/' . $tipoaccion . '/' . $tipomov . '/' . $id . '/' . $idPadre),
                'modo' => strtoupper($tipoaccion),
                'id' => $id,
                'idPadre' => $idPadre,
                'param1' => $tipoaccion,
                'param2' => $id,
            ];
        }
        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'a') {
                $r = [
                    'nIdPadre' => $idPadre,
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'sAbrev' => $this->request->getVar('sAbrev'),
                    'sLink' => $this->request->getVar('sLink')
                ];
                $nIdMenu = $mdlModulos->insert($r);
                $this->reordenar($nIdMenu, $id, $idPadre, $this->request->getVar('sDescripcion'));
                echo 'oK';
                return;
            } elseif ($tipoaccion === 'e') {
                $r = [
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'sAbrev' => $this->request->getVar('sAbrev'),
                    'sLink' => $this->request->getVar('sLink')
                ];
                $mdlModulos->update($id, $r);
                echo 'oK';
                return;
            } elseif ($tipoaccion === 'b') {
                $mdlModulos->delete($id);
                echo 'oK';
                return;
            } elseif ($tipoaccion === 'u') {
                $reg = $mdlModulos->getRegistros($id);
                $regs = $mdlModulos->getRegistros(false, $reg['nIdPadre']);
                $idAnt = '-1';
                $tope = count($regs);
                for ($i = 0; $i < $tope; $i++) {
                    if ($regs[$i]['nIdMenu'] == $id) break;
                    $idAnt = $regs[$i]['nIdMenu'];
                }
                // reorganizar
                $listado = $this->reordenar($id, $idAnt, $reg['nIdPadre'], $reg['sDescripcion']);
                return json_encode(['ok' => '1', 'listado' => $listado]);
            } elseif ($tipoaccion === 'd') {
                $reg = $mdlModulos->getRegistros($id);
                $regs = $mdlModulos->getRegistros(false, $reg['nIdPadre']);
                $idAnt = '0';
                $idAnt2 = '0';
                $tope = count($regs);
                for ($i = count($regs) - 1; $i >= 0; $i--) {
                    if ($regs[$i]['nIdMenu'] == $id) break;
                    $idAnt = $idAnt2;
                    $idAnt2 = $regs[$i]['nIdMenu'];
                }
                // reorganizar
                $listado = $this->reordenar($id, $idAnt, $reg['nIdPadre'], $reg['sDescripcion']);
                return json_encode(['ok' => '1', 'listado' => $listado]);
            } elseif ($tipoaccion === 'q') {
                $reg = $mdlModulos->getRegistros($id);
                $regs = $mdlModulos->getRegistros(false, $reg['nIdMenu']);
                $listado = [];
                foreach ($regs as $r) $listado[] = [$r['nOrden'], $r['nIdMenu'], $r['sDescripcion']];
                return json_encode(['ok' => '1', 'listado' => $listado]);
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $mdlModulos->getRegistros($id);
            }
        }
        echo view('seguridad/Modulos/modulomtto', $data);
    }

    public function reordenar($idMenu, $idDestino, $idPadre, $des)
    {
        $mdlModulos = new ModulosMdl();
        $regs = $mdlModulos->getRegistros(false, $idPadre);
        $ord = [];
        $contPos = 10;
        if (intval($idDestino) == -1) {
            $idDestino = -2;
            $ord[] = [$contPos, $idMenu, $des];
            $contPos += 10;
        }
        foreach ($regs as $r) {
            if (intval($r['nIdMenu']) == intval($idMenu)) continue;
            if (intval($idDestino) == intval($r['nIdMenu'])) {
                $ord[] = [$contPos, $idMenu, $des];
                $contPos += 10;
                $idDestino = -2;
            }
            $ord[] = [$contPos, $r['nIdMenu'], $r['sDescripcion']];
            $contPos += 10;
        }
        if(intval($idDestino) == 0) {
            $ord[] = [$contPos, $idMenu, $des];
        }
        foreach($ord as $r){
            $mdlModulos->update($r[1], [
                'nOrden' => $r[0]
            ]);
        }
        return $ord;
    }

    
}
