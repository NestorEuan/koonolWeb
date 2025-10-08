<?php

namespace App\Controllers\Almacen;

use App\Controllers\BaseController;
use App\Models\Almacen\InventarioMdl;

use App\Models\Seguridad\MenuMdl;

class Inventario extends BaseController
{

    public function index()
    {   
        $this->validaSesion();
        $model = new InventarioMdl(); 

        $data['registros'] =  $model->paginate(8,'inventario');
        $data['inventariopager'] = $model->pager;

        helper(['cookie']);
        $idUsuario = get_cookie('nIdUsuario');

        $menubuilder = new MenuMdl();
        $miArr = $menubuilder->buildMenu(0,$this->nIdUsuario);

        $data['navbar'] = $miArr[1];
        echo view('templates/header',$data);
        echo view('almacen/inventario',$data);
        echo view('templates/footer', $this->dataMenu);
    }
    
    public function update($idSucursal, $idArticulo, $fCantidad, $fComprometido){
        $model = new InventarioMdl();

        $reg = $model->getRegistros($idSucursal,$idArticulo);
        if( !empty($reg) )
        {
            $reg['fExistencia'] = $reg['fExistencia'] + $fCantidad;
            $reg['fComprometido'] = $reg['fComprometido'] + $fComprometido;
            $model->save($reg);
        }
    }

    public function accion($tipoaccion, $id = 0){
        helper(['form']);

        $model = new InventarioMdl(); 

        switch($tipoaccion){
            case 'a': $stitulo = 'Agrega'; break;
            case 'b': $stitulo = 'Borra'; break;
            case 'e': $stitulo = 'Edita'; break;
            default : $stitulo = 'Ver';
        }
        $data = [
            'titulo' => $stitulo . ' Inventario',
            'frmURL' => base_url('inventario/' . $tipoaccion . ($id > 0 ? '/'. $id : '') ),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'param1' => $tipoaccion,
            'param2' => $id,
        ];
        
        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $r = [
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'nPrecio' => $this->request->getVar('nPrecio')
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdArticulo'] = $id;
                }
                $model->save($r);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $model->getRegistros($id);
            }
        }
        helper(['vistas']);
        echo view('catalogos/articulomtto', $data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sDescripcion' => 'required|min_length[3]',
            'nPrecio' => 'required|decimal'
        ];
        $reglasMsj = [
            'sDescripcion' => [
                'required'   => 'Falta la descripcion',
                'min_length' => 'Debe tener 3 caracteres como minimo'
            ],
            'nPrecio' => [
                'required'   => 'Falta el precio unitario',
                'decimal' => 'Debe ser un numero'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($idSuc, $id = false)
    {
        $model = new InventarioMdl();
        $reg =  $model->getRegistros($id, $idSuc);
        if($reg == null) {
            return json_encode(['ok' => '0']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($nId)
    {
        $model = new InventarioMdl();
        $reg =  $model->getRegistrosByName($nId);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

}

?>
