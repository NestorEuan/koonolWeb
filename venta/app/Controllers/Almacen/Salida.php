<?php
/******
 * ***
 * Compra: Sucursal que solicita,Proveedor a solicitar
 * Traspaso: Sucursal que solicita, Sucursal solicitante
 * Entrada
 * Salida
 * ***

 - Cambiar a session los valores de data en las pantallas de capturas
 - Capturar por código las claves de sucursal, proveedor
 - Aplicar los cambios de foco al presionar la tecla ENTER
 - Terminar la parte de recepción


 ******/
namespace App\Controllers\Almacen;

use App\Controllers\BaseController;
use App\Models\Compras\ComprasMdl;
use App\Models\Compras\CompraDetalleMdl;
use App\Models\Envios\EnvioMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Almacen\SalidaMdl;
use App\Models\Almacen\SalidaDetalleMdl;
use App\Models\Almacen\TraspasoMdl;
use App\Models\Almacen\TraspasoDetalleMdl;
use App\Models\Almacen\MovimientoMdl;
use App\Models\Almacen\MovimientoDetalleMdl;

use App\Models\Almacen\InventarioMdl;

use App\Models\Seguridad\MenuMdl;

class Salida extends BaseController
{

    public function index($operacion='salida')
    {
        $this->validaSesion();

        switch($operacion)
        {
            case 'venta': 
                $titulo = 'Cotizaciones de compra'; 
                $model = new ComprasMdl(); 
                break;
            case 'salida': 
                $titulo = 'Salidas especiales'; 
                $model = new SalidaMdl(); 
                break;
            case 'traspaso':  
                $titulo = 'Traspaso almacén'; 
                $model = new TraspasoMdl(); 
                break;
        }

        $regs = $model->getRegistros(0,5);
    
        $data = [
            'registros' =>  $regs,
            'pager' => $model->pager,
            'titulo' => $titulo,
            'operacion' => $operacion,
        ];

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/salidas', $data);
        echo view('templates/footer');

        return;
    }

    public function accion($operacion,$tipoaccion, $id = 0)
    {
        $session = session();
        $this->ValidaSesion();

        $aTitulo = ['a' => 'Agregar','b' => 'Borrar', 'e' => 'Editar', 'r' => 'Recepción'];
        $stituloevento = $aTitulo[$tipoaccion] ?? 'Ver';
        $aKeys = [
            'compra'  => ['nIdCompra','dcompra','Cotizaciones de compra'], 
            'salida' => ['nIdSalida', 'dSalida', 'Salidas especiales'], 
            'traspaso' => ['nIdTraspaso','dTraspaso', 'Traspaso almacén'],
        ];
        $masterkey = $aKeys[$operacion][0];
        $datefld = $aKeys[$operacion][1];
        $stitulooperacion = $aKeys[$operacion][2];

        if (!isset($_SESSION[$operacion]) && $tipoaccion === 'a') {
            $this->initVarSesion($operacion,$masterkey);
        }
        else{
            //var_dump('O.M.G.!!!');
            //var_dump($_SESSION[$operacion]['registro']);
            //exit;
        }
        
        switch($operacion)
        {
            case 'compra': 
                $master = new ComprasMdl();        
                $detalle = new CompraDetalleMdl();
                break;
            case 'salida': 
                $master = new SalidaMdl(); 
                $detalle = new SalidaDetalleMdl(); 
                break;
            case 'traspaso':  
                $master = new TraspasoMdl();        
                $detalle = new TraspasoDetalleMdl();
                break;
        }
        $titulo = $stituloevento . ' ' . $stitulooperacion;
        $data = [
            'titulo' => $titulo,
            'operacion' => $operacion,
            'frmURL' => ('salida/' . $operacion . '/' . $tipoaccion . ($id > 0 ? '/'. $id : '') ),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
        ];
        //unset($_SESSION['lstArt']);

        if ($this->request->getMethod() == 'post') {
            if($tipoaccion === 'r')
                $id = $this->request->getVar($masterkey);
            if($tipoaccion==='b'){
                $master->delete($id);
                $detalle->delete($id);
                echo 'ok';
                return;
            }
            $a = $this->validaCampos($operacion);
            if (!$a) 
            {
                // guardar datos
                $r = [
                    "$datefld" => $_SESSION[$operacion]['registro']["$datefld"],
                    'nIdSucursal' => $_SESSION[$operacion]['registro']['nIdSucursal'],
                    'nIdProveedor' => $_SESSION[$operacion]['registro']['nIdProveedor'],
                    'fTotal' => 0, //$this->request->getVar('fTotal'),
                    'nProductos' => count($_SESSION[$operacion]['lstArt']), //$this->request->getVar('nProductos'),
                ];
                if($operacion !== 'compra')
                    $r['sObservacion'] = $_SESSION[$operacion]['registro']['sObservacion'];
                if($id > 0){
                    $r[$masterkey] = $_SESSION[$operacion]['registro']["$masterkey"];
                }
                $master->save($r);
                $inserted = $master->insertID();

                foreach( $_SESSION[$operacion]['lstArt'] as $rd ){
                    $det = [
                        $masterkey => $inserted,
                        'nIdArticulo' => $rd['nIdArticulo'],
                        'fCantidad' => $rd['fCantidad'],
                        'fRecibido' => 0,
                        'fPorRecibir' => $rd['fCantidad'],
                        'fImporte' => 0,
                     ];
                    $detalle->save($det);
                }
                if($operacion === 'traspaso'){
                    $envio = new EnvioMdl();
                    $enviodet = new EnvioDetalleMdl();
                    $e = [
                        'nIdOrigen' => $inserted,
                        'cOrigen' => $operacion,
                        //'nIdCliente' => $this->request->getVar('nIdCliente'),
                        //'nIdSucursal' => $this->request->getVar('nIdProveedor'),    
                    ];
                    $envio->save($e);
                    $envioinserted = $envio->insertID();
                    foreach($_SESSION[$operacion]['lstArt'] as $rd){
                        $edet = [
                            'nIdEnvio' => $envioinserted,
                            'nIdArticulo' => $rd['nIdArticulo'],
                            'fCantidad' => $rd['fCantidad'],
                            'fPorRecibir' => $rd['fCantidad'],
                        ];
                        $enviodet->save($edet);
                    }
                }
                $this->initVarSesion($operacion,$masterkey);
                return redirect()->to( base_url('salida/' . $operacion) );
            }
            else
            {
                $data = [
                    'error' => $a['amsj'],
                    'registros' => $_SESSION[$operacion]['lstArt'] ?? [],
                    'registro' => $_SESSION[$operacion]['registro'] ?? $registro,
                    'tipoaccion' => $tipoaccion,
                    'frmURL' => ('salida/' . $operacion . '/'. $tipoaccion . ($id > 0 ? '/'. $id : '') ),
                    'modo' => strtoupper($tipoaccion),
                    'id' => $id,
                    'enfoque' => $_SESSION[$operacion]['foc'] ?? 'art'
                ];
            }
        }
        else
        {
            if($tipoaccion !== 'a')
            {
                $this->initVarSesion($operacion,$masterkey);
                $registro = $master->getRegistros(intval($id));
                $timestamp = strtotime($registro[$datefld]); 
                $registro[$datefld] = date("Y-m-d", $timestamp );

                $arts = $detalle->getRegistros(intval($id,false));
                
                $prov =
                    [
                        'nIdProveedor' => $registro['nIdProveedor'],
                        'sNombre' => $registro['sNombre'],
                        'email' => '',
                        'sDireccion' => ''            
                    ];
                    
                $frmURL = 'salida/' .  $operacion . '/' . $tipoaccion . ($id > 0 ? '/'. $id : '');
                if($tipoaccion === 'r')
                    $frmURL = ('salida/recepcion/' .  $operacion . '/' .  $id );

                $data = [
                    'titulo' => $titulo,
                    'operacion' => $operacion,
                    'tipoaccion' => $tipoaccion,
                    'frmURL' => $frmURL,
                    'modo' => strtoupper($tipoaccion),
                    'id' => $id,
                    //'sql' => $sql,
                    'registros' => $arts, //
                    'enfoque' => 'art',
                    'registro' => $registro,
                    'proveedor' => $prov,
                ];        
            }
            else
            {
                $session = session();
                if($_SESSION[$operacion]['proveedor']['nIdProveedor'] == 0)
                {
                    $timestamp = strtotime($registro[$datefld]??date("d-m-Y")); 
                    $_SESSION[$operacion]['registro'][$datefld] = date("Y-m-d", $timestamp );
                }
                $data = [
                    'titulo' => $titulo,
                    'operacion' => $operacion,
                    'tipoaccion' => $tipoaccion,
                    'frmURL' => ('salida/' . $operacion . '/'. $tipoaccion . ($id > 0 ? '/'. $id : '') ),
                    'modo' => strtoupper($tipoaccion),
                    'id' => $id,
                    'registros' => $_SESSION[$operacion]['lstArt'] ?? [],
                    'registro' => $_SESSION[$operacion]['registro'],
                    'enfoque' => $_SESSION[$operacion]['foc'] ?? 'art',
                    'proveedor' => $_SESSION[$operacion]['proveedor'],
                ];
            }
        }
        $data['titulo'] = $titulo;
        $data['operacion'] = $operacion;
        $data['sSucursal'] = $this->sSucursal;
        //if( isset($_SESSION[$operacion]['proveedor']['nIdProveedor']) )
        //    $data['nIdProveedor'] = $_SESSION[$operacion]['proveedor']['nIdProveedor'];
        echo view('templates/header',$this->dataMenu);
        echo view('almacen/' . ($tipoaccion === 'r' ? 'recepcion' : 'salida') . 'mtto', $data);
        echo view('templates/footer');

    }

    private function initVarSesion($operacion, $masterkey)
    {
        $_SESSION[ $operacion ] = [
            'total' => 0.0,
            'pagoCompleto' => false,
            'lstArt' => [],
            'foc' => 'art',
            'proveedor' => [
                'nIdProveedor' => 0,
                'sNombre' => '',
                'email' => '',
                'sDireccion' => ''
            ],
            'registro' => [
                $masterkey => 0,
                'nIdSucursal' => $this->nIdSucursal,
                'sDescripcion' => $this->sSucursal,
                'nIdProveedor' => 0,
                'sNombre' => '',
                'sObservacion' => '',
            ],
        ];
    }

    public function agregaProveedor($operacion,$tipoaccion)
    {
        $session = session();

        $aKeys = [
            'compra'  => ['nIdCompra','dcompra','Cotizaciones de compra'], 
            'salida' => ['nIdSalida', 'dSalida', 'Salidas especiales'], 
            'traspaso' => ['nIdTraspaso','dTraspaso', 'Traspaso almacén'],
        ];
        $datefld = $aKeys[$operacion][1];

        $_SESSION[$operacion]['foc'] = 'art';
        if($tipoaccion == 'a')
        {
            $_SESSION[$operacion]['proveedor'] = 
            [
                'nIdProveedor' => $this->request->getVar('nIdProveedor'),
                'sNombre' => $this->request->getVar('dlProveedores0'),
                'email' => '',
                'sDireccion' => ''
            ];
            //echo 'UNO ';
            //var_dump($_SESSION[$operacion]['registro']);
            
            //$_SESSION[$operacion]['nIdProveedor'] = $this->request->getVar('nIdProveedor');
            $_SESSION[$operacion]['registro']['sObservacion'] = $this->request->getVar('sObservacion');
            $_SESSION[$operacion]['registro']["$datefld"] = $this->request->getVar("$datefld");
            $_SESSION[$operacion]['registro']['nIdProveedor'] = $this->request->getVar('nIdProveedor');
            $_SESSION[$operacion]['registro']['sNombre'] = $this->request->getVar('dlProveedores0');

            //echo '<BR> DOS';
            //var_dump($_SESSION[$operacion]['registro']);

        }
        return redirect()->to(base_url('salida/' . $operacion . '/a'));
    }

    public function agregaArticulo($operacion,$tipoaccion)
    {
        $session = session();

        if (!isset($_SESSION[$operacion]['lstArt'])) {
            $_SESSION[$operacion]['lstArt'] = array();
        }
        $nIdKey = 'nIdArticulo';
        $_SESSION[$operacion]['lstArt'][] = array(
            $nIdKey => $this->request->getVar("$nIdKey"),
            'sDescripcion' => $this->request->getVar('dlArticulos0'),
            'nPrecio' => $this->request->getVar('nPrecio'),
            'fImporte' => $this->request->getVar('nPrecio'),
            'fCantidad' => $this->request->getVar('nCant'),
            'fPorRecibir' => $this->request->getVar('nCant'),
        );

        $_SESSION[$operacion]['foc'] = 'art';
        return redirect()->to(base_url('salida/' . $operacion . '/a'));
    }

    public function borraArticulo($operacion,$id)
    {
        $session = session();
        $a = [];
        foreach ($_SESSION[$operacion]['lstArt'] as $r) {
            if ($r['nIdArticulo'] != $id) {
                $a[] = $r; 
            }
        }
        $_SESSION[$operacion]['lstArt'] = $a;
        $_SESSION[$operacion]['foc'] = 'art';
        return redirect()->to(base_url('salida/' . $operacion . '/a'));
    }

    public function limpiaOperacion($operacion,$masterkey)
    {
        $session = session();
        unset($_SESSION[$operacion]);
        $this->initVarSesion($operacion,$masterkey);
        return redirect()->to(base_url("/salida/$operacion/a"));
    }

    public function validaCampos($operacion)
    {
        $reglasMsj = [];

        if($this->nIdSucursal == 0)
        $reglasMsj['nIdSucursal'] = ['Se requiere seleccionar una sucursal'];
        if($operacion === 'compra'){
            if($_SESSION[$operacion]['proveedor']['nIdProveedor'] === 0)
                $reglasMsj['nIdProveedor'] = ['Se requiere seleccionar un proveedor'];
        }
        if(!empty($reglasMsj)){
            return ['amsj' => $reglasMsj];
        }
        return false;
    }

/*
    public function buscar($operacion = 'compra')
    {
        $this->validaSesion();
        session();
        $data = [];
        $titulo = 'Recepción de ';
        $label = 'Cotización de ';
        
        $aKeys = [
            'compra'  => ['nIdCompra','Recepción de compra','Cotización de compra'], 
            'entrada' => ['nIdSalida', 'Recepción de entradas especiales', 'Entrada especial'], 
            'traspaso' => ['nIdTraspaso','Recepción de traspaso de almacén', 'Traspaso almacén'],
        ];
        $masterkey = $aKeys[$operacion][0];
        $titulo = $aKeys[$operacion][1];
        $label = $aKeys[$operacion][2]; 
        
        switch($operacion)
        {
            case 'compra': $model = new ComprasMdl(); break;
            case 'traspaso': $model = new TraspasoMdl(); break;
            case 'entrada': $model = new SalidaMdl(); break;
        }

        if($this->request->getMethod() === 'post') {
            $a = $this->validaBusqueda();
            if ($a === false) {
                $r = $model->getRegistros($this->request->getVar('nIdCompra'));
                if($r !== null)
                {
                    return redirect()->to(base_url("salida/$operacion/r/" . $r[$masterkey]));
                }
                $a['amsj'] = ['nIdCompra' => 'No existe el folio solicitado'];
            }
            $data =
            [
                'error' => $a['amsj'],
                'operacion' => $operacion,
                'titulo' => $titulo,
                'label' => $label,
            ];
        }
        else{
            $data =
            [
                'operacion' => $operacion,
                'titulo' => $titulo,
                'label' => $label,
            ];
        }


        echo view('templates/header',$this->dataMenu);        
        echo view('almacen/entradabuscar', $data);
        echo view('templates/footer');

        return;
    }

    public function recepcion($operacion, $id=0)
    {
        
        $this->validaSesion();
        
        $movto = new MovimientoMdl();
        $movotdetail = new MovimientoDetalleMdl();

        $inventario = new InventarioMdl();

        switch($operacion){
            case 'compra': 
                $masterkey = 'nIdCompra'; 
                $origen = new CompraDetalleMdl();
                break;
            case 'traspaso': 
                $masterkey = 'nIdTraspaso'; 
                $origen = new TraspasoDetalleMdl();
                break;
            case 'entrada': 
                $masterkey = 'nIdSalida'; 
                $origen = new SalidaDetalleMdl();
                break;
        }
        $idOrigen = $this->request->getVar($masterkey);
        $r = [
            'nIdOrigen' => $idOrigen,
            'cOrigen' => $operacion,
        ];
            
        $movto->save($r);
        $inserted = $movto->insertID();
        
        $nIdSucursal = $this->request->getVar('nIdSucursal');
        
        if($operacion === 'traspaso')
            $nIdFuente = intval($this->request->getVar('nIdProveedor'));
        
        $arrRecibido = $this->request->getVar($operacion);
        foreach( $arrRecibido  as $idArt => $fRecibido)
        {
            
            $det = [];
            $det = [
                'nIdMovimiento' => $inserted,
                'nIdArticulo' => $idArt,
                'nIdSucursal' => $nIdSucursal,
                'fCantidad' => $fRecibido,
            ];
            $movotdetail->save($det);
            
            $origen->updtRecibidos( $idOrigen, $idArt, $fRecibido);

            $inventario->updtArticuloSucursalInventario($nIdSucursal, $idArt, $fRecibido);
            
            if($operacion === 'traspaso'){
                $inventario->updtArticuloSucursalInventario($nIdFuente, $idArt, -1 * $fRecibido);
            }
            
        }
        return redirect()->to( base_url('salida/buscar/' . $operacion) );
    }

    public function validaBusqueda()
    {
        $reglas = [
            'nIdCompra' => 'required',
        ];
        $reglasMsj = [
            'nIdCompra' => [
                'required'   => 'Falta folio de orden de compra',
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }
******************/
}

?>
