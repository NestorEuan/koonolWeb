<?php

namespace App\Controllers\Seguridad;

use App\Controllers\BaseController;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Seguridad\UsuarioMdl;

class Login extends BaseController
{
    private function cierraSesion() {
        $session = session();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], true
            );
        }
        set_cookie('nIdUsuario', '');
        set_cookie('nIdSucursal', '');
        set_cookie('sNombre', '');
        $session->destroy();
    }

    public function index()
    {
        $mdlSucursal = new SucursalMdl();
        $data['regSucursales'] = $mdlSucursal->getRegistros();
        if($this->request->getMethod() === 'post')
        {
            $a = $this->validaCampos();
            if($a === false)
            {
                $session = session();
                //Ir a desktop
                $model = new UsuarioMdl();
                $user = $model->getRegistroByLogin($this->request->getVar('sLogin'));
                if(empty($user)){
                    $data['error'] = ['sLogin'=>'Usuario no válido o no registrado'];
                    echo view('templates/headerlogin',$this->dataMenu);
                    echo view('seguridad/login',$data);
                    echo view('templates/footer', $this->dataMenu);

                    return;
                }
                if($user['sPsw'] === '' || $user['sPsw'] === null)
                {
                    return redirect()->to(base_url('usuario/changepasswd/login/' . $user['nIdUsuario'] . '/' . $user['sPsw'] ));
                }
                if($user['sPsw'] === md5( $this->request->getVar('sPsw') ))
                {
                    $regSuc = $mdlSucursal->getRegistros($this->request->getVar('nIdSucursal'), false, false, true);
                    set_cookie('nIdUsuario', $user['nIdUsuario'], '', '', '/', '', false, true);
                    set_cookie('nIdSucursal', $regSuc['nIdSucursal'] ?? '0', '', '', '/', '', false, true);
                    set_cookie('sNombre', $user['sNombre']);
                    set_cookie( 'nIdSucursalCliente', $regSuc['nIdDirEntrega'] ?? 0);
                    set_cookie( 'sLogin', $user['sLogin']);
                    set_cookie( 'sSucursal', $regSuc['sDescripcion'] ?? '');
                    set_cookie( 'sSucursalDir', $regSuc['sDireccion'] ?? '');
                    set_cookie( 'sSucursalTel', $regSuc['sCelular'] ?? '');               
                    $_SESSION['login'] = true;
                    $_SESSION['perfilUsuario'] = $user['nIdPerfil'];
                    $model->update($user['nIdUsuario'], [ 'idLastSucursal' => ($regSuc['nIdSucursal'] ?? '0')]);

                    //set_cookie('sSucursal', $user['sSucursal']); //cambiar la consulta del modelo getRegistroByLogin
                    return redirect()->to(base_url('/'))->withCookies();
                }
                else
                {
                    $data['error'] = ['sPsw'=>'Contraseña incorrecta'];
                    echo view('templates/headerlogin',$this->dataMenu);
                    echo view('seguridad/login',$data);
                    echo view('templates/footer', $this->dataMenu);
                    return;
                }
            }
            else
            {
                $data['error'] = $a['amsj'];
                echo view('templates/headerlogin', $this->dataMenu);
                echo view('seguridad/login',$data);
                echo view('templates/footerlogin', $this->dataMenu);
                return;
            }
        } else {
            $this->cierraSesion();
        }
        echo view('templates/headerlogin',$this->dataMenu);
        echo view('seguridad/login', $data);
        echo view('templates/footerlogin', $this->dataMenu);
    }

    public function validaCampos()
    {

        $reglas = [
            'sLogin' => 'required|min_length[5]',
            'nIdSucursal' => 'required|greater_than_equal_to[0]',
        ];
        $reglasMsj = [
            'sLogin' => [
                'required'   => 'Usuario requerido',
                'min_length' => 'Debe tener 5 caracteres como minimo'
            ],
            'nIdSucursal' => [
                'required'   => 'Sucursal requerida',
                'greater_than_equal_to' => 'Debe seleccionar la sucursal'
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

}

?>
