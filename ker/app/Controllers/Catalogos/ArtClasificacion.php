<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\ArtClasificacionMdl;

class ArtClasificacion extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new ArtClasificacionMdl();

        $data['registros'] =  $model->paginate(8, 'artclasificacion');
        $data['artclaspager'] = $model->pager;

        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/artclasificacion', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0)
    {

        $model = new ArtClasificacionMdl();

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo['tipoaccion'] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Clasificaci&oacute;n de art&iacute;culos',
            'frmURL' => base_url('artclasificacion/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'param1' => $tipoaccion,
            'param2' => $id,
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model = new ArtClasificacionMdl();
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $model = new ArtClasificacionMdl();
                $r = [
                    'sClasificacion' => $this->request->getVar('sClasificacion'),
                ];
                if ($id > 0) {
                    $r['nIdArtClasificacion'] = $this->request->getVar('nIdArtClasificacion');
                }
                $model->save($r);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $model = new ArtClasificacionMdl();
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        echo view('catalogos/artclasificacionmtto', $data);
    }

    public function validaCampos()
    {

        $reglas = [
            'sClasificacion' => 'required|min_length[3]',
        ];
        $reglasMsj = [
            'sClasificacion' => [
                'required'   => 'Falta la clasificaci&oacute;n',
                'min_length' => 'Debe tener 3 caracteres como m&iacute;nimo'
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new ArtClasificacionMdl();
        $reg =  $model->getRegistros($id);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sClasificacion)
    {
        $model = new ArtClasificacionMdl();
        $reg =  $model->getRegistrosByName($sClasificacion);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
