<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\ClienteMdl;
use App\Models\Catalogos\RazonSocialMdl;
use App\Models\Catalogos\SucursalMdl;

class Sucursal extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new SucursalMdl();

        // $data['registros'] =  $model->paginate(8);
        // $data['sucpager'] = $model->pager;
        $data = [
            'registros' => $model->getRegistros(false, true),
            'pager' => $model->pager
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/sucursales', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0)
    {

        $model = new SucursalMdl();

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo['tipoaccion'] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Sucursal',
            'frmURL' => base_url('sucursal/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];

        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'b') {
                $model = new SucursalMdl();
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $r = [
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'sClave' => $this->request->getVar('sClave'),
                    'sDireccion' => $this->request->getVar('sDireccion'),
                    'sCelular' => $this->request->getVar('sCelular'),
                    'sEncargado' => $this->request->getVar('sEncargado'),
                    'sCP' => $this->request->getVar('sCP'),
                    'nFolioRemision' => $this->request->getVar('nFolioRemision'),
                    'nFolioCotizacion' => $this->request->getVar('nFolioCotizacion'),
                    'nFolioFactura' => $this->request->getVar('nFolioFactura'),
                    'sSerie' => $this->request->getVar('sSerie'),
                    'sEmail' => $this->request->getVar('sEmail'),
                    'nIdRazonSocial' => $this->request->getVar('nIdRazonSocial')
                ];
                if ($tipoaccion == 'a') {
                    $r['nIdCliente'] = $this->guardaCliente();
                } else {
                    $r['nIdSucursal'] = $id;
                }
               
                $model = new SucursalMdl();
                $model->save($r);

                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $model = new SucursalMdl();
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        //if( $id > 0 ) $data['registro'] =  $model->getRegistros($id);  
        $mdlRazSocial = new RazonSocialMdl();
        $data['regRazSocial'] = $mdlRazSocial->getRegistros(false, false, true);
        echo view('catalogos/sucursalmtto', $data);
    }

    private function guardaCliente()
    {
        $mdlCliente = new ClienteMdl();
        $r = [
            'sNombre' => $this->request->getVar('sEncargado'),
            'sDireccion' => $this->request->getVar('sDireccion'),
            'sCelular' => $this->request->getVar('sCelular'),
            'sRFC' => '.',
            'email' => '.',
            'cCP' => $this->request->getVar('sCP'),
            'cIdRegimenFiscal' => '601',
            'nIdTipoLista' => 1,
            'cTipoCliente' => 'S',
            'nIdRazonSocial' => $this->request->getVar('nIdRazonSocial')
        ];
        $mdlCliente->save($r);
        return $mdlCliente->getInsertID();
    }

    public function validaCampos()
    {

        $reglas = [
            'sDescripcion' => 'required|min_length[5]|is_unique[alsucursal.sDescripcion, nIdSucursal, {nIdSucursal}]',
            'sCP' => 'required',
            'sSerie' => 'required',
            'nFolioRemision' => 'required',
            'nFolioCotizacion' => 'required',
            'nFolioFactura' => 'required',
            'nIdRazonSocial' => 'required|greater_than[0]'
        ];
        $reglasMsj = [
            'sDescripcion' => [
                'required'   => 'Falta la descripci&oacute;n de la sucursal',
                'min_length' => 'Debe tener 5 caracteres como m&iacute;nimo',
                'is_unique'  => 'Ya se ha registrado una sucursal con la misma descripci&oacute;n'
            ],
            'sCP' => [
                'required'   => 'Falta el codigo postal de la sucursal'
            ],
            'nFolioRemision' => [
                'required'   => 'Falta el folio de remision de la sucursal'
            ],
            'nFolioCotizacion' => [
                'required'   => 'Falta el folio de cotizacion de la sucursal'
            ],
            'sSerie' => [
                'required'   => 'Falta la serie de la facturacion de la sucursal'
            ],
            'nFolioFactura' => [
                'required'   => 'Falta el folio de factura de la sucursal'
            ],
            'nIdRazonSocial' => [
                'required'   => 'Falta la razon social a donde se timbra la sucursal',
                'greater_than' => 'Debe seleccionar una opcion'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new SucursalMdl();
        $reg =  $model->getRegistros($id);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion)
    {
        $this->validaSesion();

        $model = new SucursalMdl();
        $reg =  $model->getRegistrosByName($sDescripcion, $this->nIdSucursal);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
