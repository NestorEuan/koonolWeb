<?php

namespace App\Controllers\Seguridad;

use App\Controllers\BaseController;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Seguridad\UsuarioMdl;

use App\Models\Seguridad\PerfilMdl;

class Usuario extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $data['registros'] =  $model->getRegistros(false, true, intval($this->nIdUsuario) == 1 ? false : $this->nIdSucursal, ($_SESSION['perfilUsuario'] == '-1'));
        $data['pager'] = $model->pager;

        echo view('templates/header', $this->dataMenu);
        echo view('seguridad/usuarios', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function permisodescuento()
    {
        $this->validaSesion();
        $model = new UsuarioMdl();
        $data = [
            'registros' => $model->getRegistros(false, true, intval($this->nIdUsuario) == 1 ? false : $this->nIdSucursal, intval($this->nIdUsuario) == 1),
            'pager' => $model->pager,
            'permisoDescuentos' => isset($this->aPermiso['oPermitirDescuentos']),
            'permisoCambiarPrecio' => isset($this->aPermiso['oCambioPrecio']),
            'permisoVentaSinDisponibles' => isset($this->aPermiso['oEntregaSinDisponibles']),
            'permisoCancelarRemision' => isset($this->aPermiso['oPermitirCancelRem']),
            'permisoGuardarSinValidarDivFac' => isset($this->aPermiso['oDivFacSaveNoVal']),
            'permisoActivarPrecioSinComisionDescuento' => isset($this->aPermiso['oPrecioSinRecalculo']),
            'permisoMantenerPrecioCoti' => isset($this->aPermiso['oMantenerPrecioCoti']),
            'permisoCambiaFechaFactura' => isset($this->aPermiso['oCambiaFechaFactura']),
            'permisoPermiteCredito' => isset($this->aPermiso['oPermitirCredito'])
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('seguridad/usuariosdescuentos', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function activadescuento($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'D');
    }

    public function activacambioprecio($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'P');
    }
    
    public function activacambiopreciosincomisiondescuento($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'PSC');
    }

    public function activaventasindisponible($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'V');
    }

    public function activadivfacnovalidar($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'NV');
    }
    
    public function activacancelaremision($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'CR');
    }
    
    public function activamantenerpreciocoti($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'MPC');
    }
    
    public function activacambiafecfac($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'CFF');
    }

    public function activapermitecredito($id, $activa)
    {
        $this->validaSesion();
        $model = new UsuarioMdl();

        $model->activaPermiso($id, $activa == 'true' ? 1 : 0, 'PC');
    }

    public function accion($tipoaccion, $id = 0)
    {
        session();
        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo['tipoaccion'] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Usuarios',
            'frmURL' => base_url('usuario/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];

        $model = new UsuarioMdl();
        $mdlPerfil = new PerfilMdl();
        $mdlSucursal = new SucursalMdl();

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            if ($tipoaccion === 'r') {
                $model->updtPassword($id, '');
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                // guardar datos
                $r = [
                    'sLogin' => $this->request->getVar('sLogin'),
                    'sNombre' => $this->request->getVar('sNombre'),
                    'nIdPerfil' => $this->request->getVar('nIdPerfil'),
                    'nIdSucursal' => $this->request->getVar('nIdSucursal')
                ];
                if ($id > 0) {
                    $r['nIdUsuario'] = $this->request->getVar('nIdUsuario');
                }
                $model->save($r);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $reg =  $model->getRegistros($id);
                $data['registro'] = $reg;
            }
        }
        //if( $id > 0 ) $data['registro'] =  $model->getRegistros($id);    
        $r = $mdlPerfil->getRegistros();
        $data['regPerfil'] = $r;
        if ($_SESSION['perfilUsuario'] == '-1') {
            array_splice($data['regPerfil'], 0, 0, [['nIdPerfil' => '-1', 'sPerfil' => 'Super Admin', 'dtAlta' => null, 'dtBaja' => null]]);
        }
        $r = $mdlSucursal->getRegistros();
        $data['regSucursal'] = $r;
        echo view('seguridad/usuariomtto', $data);
    }

    public function changepasswd($logeado, $nIdUsuario, $password  = '')
    {
        $model = new UsuarioMdl();
        $user = $model->getRegistros($nIdUsuario);
        $data['titulo'] = 'Cambio de contraseña' . $password === '' ? 'requerido' : '';

        $header = $logeado === 'login' ? 'templates/headerlogin' : 'templates/header';

        if (strtoupper($this->request->getMethod()) === 'POST') {
            $a = $this->validaContrasenia();
            if ($a === false) {
                //Ir a desktop
                $id = intval($this->request->getVar('nIdUsuario'));
                $model = new UsuarioMdl();
                $user = $model->updtPassword(
                    $id,
                    md5($this->request->getVar('sPsw'))
                );
                return redirect()->to(base_url($logeado === 'login' ? '/login' : '/'));
            } else {

                $data['registro'] = $user;
                $data['error'] = $a['amsj'];
                echo view($header);
                echo view('seguridad/updtpass', $data);
                echo view('templates/footer', $this->dataMenu);
                return;
            }
        }
        $data['registro'] = $user;


        echo view($header, $this->dataMenu ?? []);
        echo view('seguridad/updtpass', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function validaContrasenia()
    {
        $reglas = [
            'sPsw' => 'required|min_length[5]',
            'sConfirmar' => 'required|matches[sPsw]'
        ];
        $reglasMsj = [
            'sPsw' => [
                'required'   => 'Contrase&ntilde;a requerida',
                'min_length' => 'Debe tener 5 caracteres como m&iacute;nimo',
            ],
            'sConfirmar' => [
                'required'   => 'Confirmaci&oacute;n de contrase&ntilde;a requerida',
                'matches' => 'No coincide',
            ],
        ];

        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function validaCampos()
    {
        if ($this->nIdUsuario == '1')
            $reglaPerfil = 'required';
        else
            $reglaPerfil = 'required|is_natural_no_zero';

        $reglas = [
            'sLogin' => 'required|min_length[5]|is_unique[sgusuario.sLogin, nIdUsuario, {nIdUsuario}]',
            'sNombre' => 'required|min_length[5]|is_unique[sgusuario.sNombre, nIdUsuario, {nIdUsuario}]',
            'nIdSucursal' => 'required|is_natural_no_zero',
            'nIdPerfil' => $reglaPerfil
        ];
        $reglasMsj = [
            'sLogin' => [
                'required'   => 'Falta el usuario',
                'min_length' => 'Debe tener 5 caracteres como minimo'
            ],
            'sNombre' => [
                'required'   => 'Falta el nombre',
                'min_length' => 'Debe tener 5 caracteres como minimo'
            ],
            'nIdSucursal' => [
                'required'   => 'Falta seleccionar la sucursal',
                'is_natural_no_zero' => 'Falta seleccionar la sucursal'
            ],
            'nIdPerfil' => [
                'required'   => 'Falta seleccionar el perfil',
                'is_natural_no_zero' => 'Falta seleccionar el perfil'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function leeRegistro($id)
    {
        $model = new Usuariomdl();
        $reg =  $model->getRegistros($id);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion)
    {
        $model = new UsuarioMdl();
        $reg =  $model->getRegistrosByLogin($sDescripcion);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
