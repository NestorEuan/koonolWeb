<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\ClienteMdl;
use App\Models\Catalogos\RegimenFiscalMdl;
use App\Models\Catalogos\TipoListaMdl;
use App\Models\Catalogos\UsoCfdiMdl;

class Cliente extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new ClienteMdl();

        $data['registros'] =  $model->getRegistros(
            false,
            8,
            $_SESSION['perfilUsuario'] == -1,
            $this->request->getVar('sDescri') ?? false
        );
        $data['pager'] = $model->pager;
        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/clientes', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0)
    {
        $model = new ClienteMdl();
        $mdlRegFiscal = new RegimenFiscalMdl();
        $mdlTipoLista = new TipoListaMdl();
        $mdlUsoCfdi = new UsoCfdiMdl();

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo[$tipoaccion] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Cliente',
            'frmURL' => base_url('cliente/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'regfis' => $mdlRegFiscal->getRegistros(false, null, null, true),
            'tipoLista' => $mdlTipoLista->getRegistros(false, false, false, 0, true),
            'usoCfdi' => $mdlUsoCfdi->getRegistros(false, false, null, null, true)
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);

                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $tipoLista = $this->request->getVar('nIdTipoLista') ?? '1';
                $r = [
                    'sNombre' => $this->request->getVar('sNombre'),
                    'sDireccion' => $this->request->getVar('sDireccion'),
                    'sCelular' => $this->request->getVar('sCelular'),
                    'sRFC' => $this->request->getVar('sRFC'),
                    'email' => $this->request->getVar('email'),
                    'cCP' => $this->request->getVar('cCP'),
                    'cIdRegimenFiscal' => $this->request->getVar('cIdRegimenFiscal'),
                    'cIdUsoCfdi' => $this->request->getVar('cIdUsoCfdi'),
                    'nIdTipoLista' => ($tipoLista == '-1' ? '1' : $tipoLista),
                    'sNombreConstanciaFiscal' => $this->request->getVar('sNombreConstanciaFiscal')
                ];
                if ($tipoaccion === 'e') {
                    $r['nIdCliente'] = $id;
                } else {
                    $r['cTipoCliente'] = 'N';   // si se agrega pone cliente normal
                }

                $model->save($r);
                if ($tipoaccion == 'a' && $id == 1) {   // si se da de alta en la venta
                    $newID = $model->insertID();
                    $newID = sprintf('%7d', $newID);
                    echo 'oK' . $newID;
                } else {
                    echo 'oK';
                }
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $model->getRegistros($id);
            } else {
                $data['deshabilitaListaPrecio'] = true;
                $data['id'] = 0;
            }
        }
        echo view('catalogos/clientemtto', $data);
    }

    public function validaCampos()
    {
        $reglas = [
            'nIdCliente' => 'greater_than_equal_to[0]',
            'sNombre' => 'required|min_length[2]|is_unique[vtcliente.sNombre, nIdCliente, {nIdCliente}]',
            'sRFC' => 'required|min_length[2]|is_unique[vtcliente.sRFC, nIdCliente, {nIdCliente}]',
            'sNombreConstanciaFiscal' => 'required|min_length[2]'
        ];
        $reglasMsj = [
            'nIdCliente' =>  [
                'greater_than_equal_to' => 'Id Artículo erróneo'
            ],
            'sNombre' => [
                'required'   => 'Falta el nombre',
                'min_length' => 'Debe tener 8 caracteres como minimo',
                'is_unique' => 'El Nombre ya existe para otro cliente'
            ],
            'sRFC' => [
                'required'   => 'Falta el rfc',
                'min_length' => 'Debe tener 2 caracteres como minimo',
                'is_unique' => 'El RFC ya existe para otro cliente'
            ],
            'sNombreConstanciaFiscal' => [
                'required'   => 'Falta el nombre en la constancia fiscal (para CFDI)',
                'min_length' => 'Debe tener 3 caracteres como minimo'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id, $esc = false)
    {
        $model = new ClienteMdl();
        $reg =  $model->getRegistros($id);
        if ($reg == null) {
            return json_encode(['ok' => '0']);
        }
        if ($esc) $reg['sNombre'] = esc($reg['sNombre']);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion)
    {
        $model = new ClienteMdl();
        $reg =  $model->getRegistrosByName($sDescripcion);
        $t = count($reg);
        for ($i = 0; $i < $t; $i++) {
            $reg[$i]['sNombre'] = esc($reg[$i]['sNombre']);
            $reg[$i]['sDireccion'] = esc($reg[$i]['sDireccion']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
