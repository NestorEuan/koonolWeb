<?php

namespace App\Models\Seguridad;

use CodeIgniter\Model;

class MenuMdl extends Model
{
    protected $table = 'sgmenu';

    protected $allowedFields = ['nIdPadre', 'sDescripcion', 'sAbrev', 'sLink', 'nOrden'];

    protected $primaryKey = 'nIdMenu';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    //protected $afterFind = ['checkPass'];

    public function getRegistros($id = 0)
    {
        if ($id === 0) {
            return $this->findAll();
        }
        return $this->where(['nIdMenu' =>  $id])->first();
    }

    public function getRegistroByFld($sFld, $val = false)
    {
        return $this->where([$sFld => $val])->first();
    }

    public function buildMenu($idPadre = 0, $idUsuario = 0): array
    {
        $sql = $this->db->table("sgusuario u")->where(["nIdUsuario" => $idUsuario])->getCompiledSelect();
        $user = $this->db->query($sql)->getResult();
        $nIdPerfil = $user[0]->nIdPerfil;

        $builder = $this->db->table("sgpermisoperfil p")->select("nIdMenu")
            ->join('sgusuario u', 'u.nIdPerfil = p.nIdPerfil', 'left')
            ->where(['u.nIdUsuario' => $idUsuario, 'p.nIdMenu >' => 0]);

        $sql = $builder->getCompiledSelect();
        $arrayRol = $this->db->query($sql);
        $aPermisos = [];
        if ($idUsuario == 1 || $nIdPerfil == -1)
            $aPermisos[] = -1;
        else
            foreach ($arrayRol->getResult() as $permiso) {
                $aPermisos[] = intval($permiso->nIdMenu);
            }
        if (empty($aPermisos)) return [10, 'Acceso no autorizado', []];
        //$menu = $this->recorreArbol($idPadre, $idUsuario, $aPermisos);
        return $this->recorreArbol($idPadre, $idUsuario, $aPermisos);
    }

    public function recorreArbol($idPadre = 0, $idUsuario = 0, &$aPermisos = []): array
    {
        $nodos = $this->where(['nIdPadre' => $idPadre])->orderBy('nOrden', 'ASC')->orderBy('nIdMenu', 'ASC')->findAll();
        if (empty($nodos)) return [0, '', []];
        $sHTML = '';
        $nodoAnt = 'T';
        $aOpciones = [];
        foreach ($nodos as $nodo) {
            if ($nodo['sDescripcion'] === '$divider$') {
                if ($nodoAnt == 'N') {
                    $sHTML = $sHTML . '<li><hr class="dropdown-divider"></li>';
                    $nodoAnt = 'D';
                }
                continue;
            }
            if ($aPermisos[0] !== -1)
                if (array_search(intval(($nodo['nIdMenu'])), $aPermisos) === false) continue;

            $miArr = $this->recorreArbol($nodo['nIdMenu'], $idUsuario, $aPermisos);
            $aOpciones = $aOpciones + $miArr[2];
            if ($nodo['sDescripcion'] === '$opcion$') {
                $aOpciones[$nodo['sLink']] = true;
                continue;
            }
            //Si miArr[0] = 0 no tengo hijos. Construyo el item del menu.
            if ($miArr[0] === 0) {
                if ($idPadre === 0) //Si el padre es 0, es el menú principal
                {
                    $sHTML = $sHTML .
                        '<li class="nav-item">' .
                        '   <a class="nav-link" href=" ' . base_url() . '/' . $nodo['sLink'] .
                        '" tabindex="-1">' . $nodo['sDescripcion'] . '</a>' .
                        '</li>';
                } else {
                    $sHTML = $sHTML . chr(13) . '<li><a class="dropdown-item" href="' . base_url() . '/' .
                        $nodo['sLink'] . '">' . $nodo['sDescripcion'] . '</a></li>';
                    $nodoAnt = 'N';
                }
            } else {
                $sHTML = $sHTML . chr(13) .
                    '<li class="nav-item dropdown">' .
                    '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"' .
                    ' data-bs-toggle="dropdown" aria-expanded="false">' .
                    $nodo['sDescripcion'] .
                    '</a>' .
                    '<ul class="dropdown-menu" aria-labelledby="navbarDropdown">' .
                    $miArr[1] .
                    '</ul>' .
                    '</li>';
            }
        }
        $miArr[0] = 10; // $nodos->count(); 
        $miArr[1] = $sHTML;
        $miArr[2] = $aOpciones;

        return $miArr;
    }
}

/***********
 *

CREATE TABLE `sgmenu` (
  `nIdMenu` int(11) NOT NULL AUTO_INCREMENT,
  `nIdPadre` int(11) DEFAULT 0,
  `sDescripcion` varchar(85) DEFAULT NULL,
  `sAbrev` varchar(45) DEFAULT NULL,
  `sLink` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdMenu`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

 *
 ***********/
