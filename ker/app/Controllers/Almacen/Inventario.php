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

        $data['registros'] =  $model->paginate(8, 'inventario');
        $data['inventariopager'] = $model->pager;

        helper(['cookie']);
        $idUsuario = get_cookie('nIdUsuario');

        $menubuilder = new MenuMdl();
        $miArr = $menubuilder->buildMenu(0, $this->nIdUsuario);

        $data['navbar'] = $miArr[1];
        echo view('templates/header', $data);
        echo view('almacen/inventario', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function update($idSucursal, $idArticulo, $fCantidad, $fComprometido)
    {
        $model = new InventarioMdl();

        $reg = $model->getRegistros($idSucursal, $idArticulo);
        if (!empty($reg)) {
            $reg['fExistencia'] = $reg['fExistencia'] + $fCantidad;
            $reg['fComprometido'] = $reg['fComprometido'] + $fComprometido;
            $model->save($reg);
        }
    }

    public function accion($tipoaccion, $id = 0)
    {
        helper(['form']);

        $model = new InventarioMdl();

        switch ($tipoaccion) {
            case 'a':
                $stitulo = 'Agrega';
                break;
            case 'b':
                $stitulo = 'Borra';
                break;
            case 'e':
                $stitulo = 'Edita';
                break;
            default:
                $stitulo = 'Ver';
        }
        $data = [
            'titulo' => $stitulo . ' Inventario',
            'frmURL' => base_url('inventario/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'param1' => $tipoaccion,
            'param2' => $id,
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
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
        if ($reg == null) {
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

    public function muestraExistencias($id)
    {
        $session = session();
        // se verifica si se controla existencia
        $idSucursal = $this->nIdSucursal;

        $model = new InventarioMdl();
        $r = $model->getRegistros($id, false);

        $disponible = 0.0;
        $total = 0.0;
        foreach ($r as $k => $v) {
            $val = round(floatval($v['fExistencia']), 3);
            $r[$k]['fReal'] = $val;
            $total += $val;
            if ($v['nIdArticulo'] == $id && $v['nIdSucursal'] == $idSucursal) {
                $disponible = $val;
            }
        }

        $data['registros'] = $r;
        $data['idSucursal'] = $idSucursal;
        $data['total'] = $total;
        $data['disponible'] = $disponible;
        $data['textoColor'] = 'text-primary';
        echo view('ventas/muestraexistencia', $data);
        return;
    }

    public function exportarExistencias()
    {
        $this->validaSesion();
        $model = new InventarioMdl();
        $idsucursal = $this->request->getVar('nIdSucursal') ?? $this->nIdSucursal;
        $regs =   $model->getExistencias(
            $idsucursal,
            false,
            $this->request->getVar('sDescri') ?? false
        );
        /*
        $idSuc = $this->request->getVar('nIdSucursal') ?? 0;
        $id = $this->request->getVar('sDescri') ?? 0;
        $model = new InventarioMdl();
        $reg =  $model->getRegistros($id, $idSuc);
        */
        if ($regs == null) {
            return json_encode(['ok' => '0']);
        }
        $crdx2Exp[] = [
            'Artículo.Id',
            'Artículo',
            'Código',
            'Existencia',
        ];
        foreach ($regs as $r) {
            $crdx2Exp[] = [
                $r['nIdArticulo'],
                $r['nomArt'],
                "'" . $r['sCodigo'],
                $r['fExistencia'],
            ];
        }
        $text = $this->ExportaCsv('InvenTmp.csv', $crdx2Exp);
        return $this->response->download('inventario.csv', $text);
    }
}
