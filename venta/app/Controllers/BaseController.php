<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['cookie', 'vistas', 'form'];

    protected $nIdUsuario;
    protected $nIdSucursal;
    protected $nIdSucursalCliente;
    protected $nIdDirEntrega;
    protected $sNombre;
    protected $sLogin;
    protected $sSucursal;
    protected $sSucursalDir;
    protected $sSucursalTel;
    protected $dataMenu;
    protected $aPermiso;
    protected $aInfoSis;
    protected $sNombreUsuario;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $mdlInfoSistem = new \App\Models\Catalogos\SistemInfoMdl();
        $this->aInfoSis = $mdlInfoSistem->getRegistro();

        // E.g.: $this->session = \Config\Services::session();
        $this->nIdUsuario = get_cookie('nIdUsuario');
        $this->nIdSucursal = get_cookie('nIdSucursal');
        $this->nIdSucursalCliente = get_cookie('nIdSucursalCliente');
        $this->nIdDirEntrega = get_cookie('nIdSucursalCliente');
        $this->sSucursal = get_cookie('sSucursal');
        $this->sLogin = get_cookie('sLogin');
        $this->sNombreUsuario = get_cookie('sNombre');
        if ($this->nIdUsuario === null || $this->nIdUsuario == '') {
            $this->nIdUsuario = null;
            $this->nIdSucursal = null;
            $this->dataMenu['aInfoSis'] = $this->aInfoSis;
        } else {
            $menubuilder = new \App\Models\Seguridad\MenuMdl();
            $this->aPermiso = $menubuilder->buildMenu(0, $this->nIdUsuario);
            $this->dataMenu = [
                'navbar' => $this->aPermiso[1],
                'nIdUsuario' => $this->nIdUsuario,
                'slogin' => $this->sLogin,
                'sucursal' => $this->sSucursal,
                'aInfoSis' => $this->aInfoSis,
            ];
            $this->aPermiso = $this->aPermiso[2];
        }
    }

    protected function validaSesion($directo = false)
    {
        $salir = false;
        if ($this->nIdUsuario === null) {
            $data = [
                'msjError' => 'Debe acceder al sistema',
                'titulo' => 'AVISO',
                'login' => '1'
            ];
            $salir = true;
        } else {
            session();
            if (!isset($_SESSION['login'])) {
                $data = [
                    'msjError' => 'Ha excedido el tiempo inactivo en la sesión<br>' .
                        'Debe volver a acceder al sistema',
                    'titulo' => 'AVISO',
                    'login' => '1'
                ];
                $salir = true;
            }
        }
        if($salir) {
            if($directo) {
                return redirect()->to(base_url("/login"));
            } else {
                echo view('templates/header', $this->dataMenu ?? []);
                echo view('templates/mensajemodal', $data);
                echo view('templates/footer', $this->dataMenu);
                exit;
            }
        }
        return false;
    }

    protected function validaTurnoCorte()
    {
        $mdlCorte = new \App\Models\Ventas\CorteCajaMdl();
        $r = $mdlCorte->getIdCorteActivo($this->nIdUsuario, $this->nIdSucursal);
        if ($r == null || $r['dtCierre'] !== null) {
            $data = [
                'msjError' => 'El usuario no ha iniciado un turno en Corte de Caja',
                'titulo' => 'AVISO',
                'nuevoCorteCajero' => '1'
            ];
            $_SESSION['nuevoCorteCajero'] = true;
            echo view('templates/header', $this->dataMenu);
            echo view('templates/mensajemodal', $data);
            echo view('templates/footer', $this->dataMenu);
            exit;
        }
        $lastFecApertura = new DateTime(substr($r['dtApertura'], 0, 10));
        $fecActual = new DateTime();
        if($lastFecApertura->format('Ymd') != $fecActual->format('Ymd')) {
            $data = [
                'msjError' => 'Debe cerrar el Corte de Caja anterior.',
                'titulo' => 'AVISO',
                'nuevoCorteCajero' => '1'
            ];
            $_SESSION['nuevoCorteCajero'] = false;
            echo view('templates/header', $this->dataMenu);
            echo view('templates/mensajemodal', $data);
            echo view('templates/footer', $this->dataMenu);
            exit;
        } 

    }
    
        protected function ExportaCsv( $nomArchivo, $objArray)
    {
        $dbutil = \Config\Database::utils();

        //$query = $db->query('SELECT * FROM mytable');

        // $data  = $dbutil->getCSVFromResult($objArray);
        //$this->load->dbutil();
        $nom = tempnam('assets', $nomArchivo);
        $ainfo = pathinfo($nom);
        $fName = 'assets/' . $ainfo['filename'] . '.csv';
    
        $datatxt = '';
        $filehndl = fopen($fName,'w');
        foreach($objArray as $item)
        {
            //$datatxt .= $item;
            fputcsv($filehndl,$item);
        }
        fclose($filehndl);

        $text = file_get_contents($fName);
        unlink($fName);
        unlink($nom);


        return $text; //this->response->download( $nomArchivo, $text);
        //write_file($fName, $datatxt);
        // echo $this->dbutil->csv_from_result($objArray);
    }

}
