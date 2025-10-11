<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('desktop');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

//TEMPORAL PARA HACER PRUEBAS DIVERSAS
$routes->get('prueba', 'Catalogos\Prueba::index');

$routes->get('cierrasesion', 'Seguridad\Login::cierraSesion');

$routes->match(['get', 'post'], 'login', 'Seguridad\Login::index');
//$routes->get('/', 'Seguridad\Login::index');
$routes->match(['get', 'post'], '/', 'Main::index');
$routes->match(['get', 'post'], 'desktop', 'Main::index');
$routes->match(['get', 'post'], 'desktop/content', 'Main::desktopContent');

$routes->get('articulo', 'Catalogos\Articulo::index');
$routes->match(['get', 'post'], 'articulo/([aeb])(/(:num))?', 'Catalogos\Articulo::accion/$1/$3');
$routes->match(['get', 'post'], 'articulo/(:any)', 'Catalogos\Articulo::$1');

$routes->get('existencias', 'Catalogos\Articulo::inventario');
$routes->match(['get', 'post'], 'existencia/(:num)/(:num)', 'Almacen\Inventario::leeRegistro/$1/$2');

$routes->get('artclasificacion', 'Catalogos\ArtClasificacion::index');
$routes->match(['get', 'post'], 'artclasificacion/([aeb])(/(:num))?', 'Catalogos\ArtClasificacion::accion/$1/$3');
$routes->match(['get', 'post'], 'artclasificacion/(:segment)/(:segment)', 'Catalogos\ArtClasificacion::$1/$2');

$routes->get('sucursal', 'Catalogos\Sucursal::index');
$routes->match(['get', 'post'], 'sucursal/([aeb])(/(:num))?', 'Catalogos\Sucursal::accion/$1/$3');
$routes->match(['get', 'post'], 'sucursal/(:segment)/(:segment)', 'Catalogos\Sucursal::$1/$2');

$routes->get('proveedor', 'Catalogos\Proveedor::index');
$routes->match(['get', 'post'], 'proveedor/([aeb])(/(:num))?', 'Catalogos\Proveedor::accion/$1/$3');
$routes->match(['get', 'post'], 'proveedor/(:any)', 'Catalogos\Proveedor::$1');

$routes->get('cliente', 'Catalogos\Cliente::index');
$routes->match(['get', 'post'], 'cliente/([aeb])(/(:num))?', 'Catalogos\Cliente::accion/$1/$3');
$routes->match(['get', 'post'], 'cliente/(:segment)/(:any)', 'Catalogos\Cliente::$1/$2');

$routes->get('chofer', 'Catalogos\Chofer::index');
$routes->match(['get', 'post'], 'chofer/([aeb])(/(:num))?', 'Catalogos\Chofer::accion/$1/$3');
$routes->match(['get', 'post'], 'chofer/(:segment)/(:segment)', 'Catalogos\Chofer::$1/$2');

$routes->get('modulos', 'Seguridad\Modulos::index');
$routes->match(['get', 'post'], 'modulos/([aebudq])/(:any)', 'Seguridad\Modulos::accion/$1/$2');

$routes->get('perfil', 'Seguridad\Perfiles::index');
$routes->match(['get', 'post'], 'perfil/([aeb])(/(:num))?', 'Seguridad\Perfiles::accion/$1/$3');

$routes->get('configuracion', 'Catalogos\Configuracion::index');
$routes->match(['get', 'post'], 'configuracion/([aeb])(/(:num))?', 'Catalogos\Configuracion::accion/$1/$3');
$routes->match(['get', 'post'], 'configuracion/(:any)', 'Catalogos\Configuracion::$1');

$routes->get('artprecios', 'Catalogos\ArtPrecios::index');
$routes->match(['get', 'post'], 'artprecios/([aeb])(/(:num))?', 'Catalogos\ArtPrecios::accion/$1/$3');
$routes->match(['get', 'post'], 'artprecios/(:any)', 'Catalogos\ArtPrecios::$1');

$routes->get('usuario', 'Seguridad\Usuario::index');
$routes->match(['get', 'post'], 'usuario/([aebr])(/(:num))?', 'Seguridad\Usuario::accion/$1/$3');
$routes->match(['get', 'post'], 'usuario/(:any)', 'Seguridad\Usuario::$1');

$routes->get('ventas', 'Ventas\Ventas::index');
$routes->match(['get', 'post'], 'ventas/rep/(:segment)', 'Ventas\Reportes\RepResumenVentasCorte::$1');
$routes->match(['get', 'post'], 'ventas/rep2/(:any)', 'Ventas\Reportes\ResumenMovtoRemision::$1');
$routes->match(['get', 'post'], 'ventas/rep3/(:any)', 'Ventas\Reportes\VentasporRemision::$1');
$routes->match(['get', 'post'], 'ventas/rep4', 'Ventas\Reportes\RepFacturasEspeciales::index');
$routes->match(['get', 'post'], 'ventas/rep4/(:any)', 'Ventas\Reportes\RepFacturasEspeciales::$1');
$routes->match(['get', 'post'], 'ventas/rep5', 'Ventas\Reportes\RepComisiones::index');
$routes->match(['get', 'post'], 'ventas/rep5/(:any)', 'Ventas\Reportes\RepComisiones::$1');
$routes->match(['get', 'post'], 'ventas/(:segment)', 'Ventas\Ventas::$1');
$routes->match(['get', 'post'], 'ventas/(:segment)/(:any)', 'Ventas\Ventas::$1/$2');

$routes->match(['get', 'post'], 'notacredito/(:any)', 'Ventas\NotaCredito::accion/$1');

$routes->get('movtoCajas', 'Ventas\EsCaja::index');
$routes->match(['get', 'post'], 'movtoCajas/(a)(/(:num))?', 'Ventas\EsCaja::accion/$1/$3');
$routes->match(['get', 'post'], 'movtoCajas/(:any)', 'Ventas\EsCaja::$1');

$routes->get('pagoAdel', 'Ventas\PagAdel::index');
$routes->match(['get', 'post'], 'pagoAdel/(a)(/(:num))?', 'Ventas\PagAdel::accion/$1/$3');
$routes->match(['get', 'post'], 'pagoAdel/(:any)', 'Ventas\PagAdel::$1');

$routes->get('agentesventas', 'Catalogos\AgenteVentas::index');
$routes->match(['get', 'post'], 'agentesventas/([aeb])(/(:num))?', 'Catalogos\AgenteVentas::accion/$1/$3');
$routes->match(['get', 'post'], 'agentesventas/(:any)', 'Catalogos\AgenteVentas::$1');

$routes->get('TipoPago', 'Catalogos\TipoPago::index');
$routes->match(['get', 'post'], 'TipoPago/([aeb])(/(:num))?', 'Catalogos\TipoPago::accion/$1/$3');

$routes->get('razonsocial', 'Catalogos\RazonSocial::index');
$routes->match(['get', 'post'], 'razonsocial/([aeb])(/(:num))?', 'Catalogos\RazonSocial::accion/$1/$3');

$routes->get('cortecaja', 'Ventas\CorteCaja::index');
$routes->match(['get', 'post'], 'cortecaja/([aeb])(/(:num))?', 'Ventas\CorteCaja::accion/$1/$3');
$routes->match(['get', 'post'], 'cortecaja/(:any)', 'Ventas\CorteCaja::$1');

$routes->get('remisiones', 'Ventas\Remisiones::index');
$routes->match(['get', 'post'], 'remisiones/(:any)', 'Ventas\Remisiones::$1');

$routes->get('cotizaciones', 'Ventas\Cotizaciones::index');
$routes->match(['get', 'post'], 'cotizaciones/(:any)', 'Ventas\Cotizaciones::$1');

/*********
$routes->get('movimiento/(:segment)', 'Almacen\Movimiento::index/$1');
// $routes->get('movimiento/compra/(:segment)', 'Almacen\Movimiento::index/compra/$1');
$routes->match(['get', 'post'], 'movimiento/(:segment)/([aebrpnc]|\d+)(/(:num))?', 'Almacen\Movimiento::accion/$1/$2/$4');
$routes->match(['post', 'get'], 'movimiento/(:any)', 'Almacen\Movimiento::$1');
// $routes->match(['get', 'post'], 'movimiento/(:segment)/(:segment)/(:segment)', 'Almacen\Movimiento::$1/$2/$3');

$routes->match(['get', 'post'], 'imprime/(:segment)/(:num)?', 'Almacen\Movimiento::imprime/$1/$2/$3');
 ********/

$routes->get('cuentas/(pagar|cobrar)', 'Cuentas\Cuentas::index/$1');
$routes->get('cuentas/exporta/(pagar|cobrar)', 'Cuentas\Cuentas::exporta/$1');
$routes->get('cuentas/leeMovimiento/(pagar|cobrar)/(:num)', 'Cuentas\Cuentas::leeMovimiento/$1/$2');
$routes->match(['get', 'post'], 'cuentas/(:segment)/([pdv]|\d+)(/(:num))?', 'Cuentas\Cuentas::accion/$1/$2/$4');

$routes->get('envio', 'Envio\Envio::index');
$routes->match(['get', 'post'], 'envio/([aebd])/(:num)', 'Envio\Envio::accion/$1/$2');
$routes->get('envio/(:any)', 'Envio\Envio::index/$1');

$routes->get('viaje', 'Viajes\Viaje::index');
$routes->match(['get', 'post'], 'viaje/envio/(:any)', 'Viajes\Viaje::envio/$1');
$routes->match(['get', 'post'], 'viaje/imprime/(:any)', 'Viajes\Viaje::imprime/$1');
$routes->match(['get', 'post'], 'viaje/([abecsr])(/(:any))?', 'Viajes\Viaje::accion/$1/$3');
$routes->match(['get', 'post'], 'viaje/rep6', 'Viajes\Reportes\RepModoEnv::index');
$routes->match(['get', 'post'], 'viaje/rep6/(:any)', 'Viajes\Reportes\RepModoEnv::$1');
$routes->match(['get', 'post'], 'viaje/(:any)', 'Viajes\Viaje::$1');

$routes->get('viajectrl', 'Viajes\ViajeCtrl::index');
$routes->match(['get', 'post'], 'viajectrl/devolucion/(:any)', 'Viajes\ViajeCtrl::devolucion/$1');
$routes->match(['get', 'post'], 'viajectrl/([becsf])(/(:any))?', 'Viajes\ViajeCtrl::accion/$1/$3');

$routes->get('tipolista', 'Catalogos\TipoLista::index');
$routes->match(['get', 'post'], 'tipolista/(:any)', 'Catalogos\TipoLista::accion/$1');

$routes->get('cardex', 'Almacen\Cardex::index');
$routes->match(['get', 'post'], 'cardex/(:any)', 'Almacen\Cardex::$1');

$routes->get('repmatrecep', 'Almacen\reportes\RepTraspasos::index');
$routes->match(['get', 'post'], 'repmatrecep/(:any)', 'Almacen\reportes\RepTraspasos::$1');

$routes->match(['get', 'post'], 'factura/(:any)', 'Ventas\Facturas::$1');

$routes->match(['get'], 'imprimeentrega', 'Almacen\ImprimeEntrega::index');
$routes->match(['post'], 'imprimeentrega/(:any)', 'Almacen\ImprimeEntrega::index/$1');

$routes->match(['get', 'post'], 'cancelapago/(:any)', 'Ventas\CancelarPago::index/$1');

/****************/
// Rutas de Movimientos de Almacén (optimizadas)
$routes->group('movimiento', ['namespace' => 'App\Controllers\Almacen'], function ($routes) {
    // Lista de movimientos
    $routes->get('(:segment)', 'Movimiento::index/$1');
    $routes->get('(:segment)/(:any)', 'Movimiento::index/$1/$2');

    // Buscar movimiento
    $routes->match(['get', 'post'], '(:segment)/buscar', 'Movimiento::buscar/$1');

    // CRUD Acciones
    $routes->match(['get', 'post'], '(:segment)/(:segment)', 'Movimiento::accion/$1/$2');
    $routes->match(['get', 'post'], '(:segment)/(:segment)/(:num)', 'Movimiento::accion/$1/$2/$3');

    // Agregar artículo
    $routes->post('(:segment)/(:segment)/agregar', 'Movimiento::agregaArticulo/$1/$2');
    $routes->post('(:segment)/(:segment)/agregar/(:num)', 'Movimiento::agregaArticulo/$1/$2/$3');
    $routes->post('(:segment)/(:segment)/agregarcompra', 'Movimiento::agregaArticuloCompra/$1/$2');
    $routes->post('(:segment)/(:segment)/agregarcompra/(:num)', 'Movimiento::agregaArticuloCompra/$1/$2/$3');

    // Agregar proveedor
    $routes->post('(:segment)/(:segment)/proveedor', 'Movimiento::agregaProveedor/$1/$2');
    $routes->post('(:segment)/(:segment)/proveedor/(:num)', 'Movimiento::agregaProveedor/$1/$2/$3');

    // Borrar artículo
    $routes->get('(:segment)/(:segment)/borrar/(:num)', 'Movimiento::borraArticulo/$3/$1/$2');
    $routes->get('(:segment)/(:segment)/borrar/(:num)/(:num)', 'Movimiento::borraArticulo/$3/$1/$2/$4');
    $routes->get('(:segment)/(:segment)/borrarcompra/(:num)/(:num)', 'Movimiento::borraArticuloCompra/$3/$4/$1/$2');
    $routes->get('(:segment)/(:segment)/borrarcompra/(:num)/(:num)/(:num)', 'Movimiento::borraArticuloCompra/$3/$4/$1/$2/$5');

    // Limpiar operación
    $routes->get('(:segment)/(:segment)/limpiar', 'Movimiento::limpiaOperacion/$1/$2');
    $routes->get('(:segment)/(:segment)/limpiar/(:num)', 'Movimiento::limpiaOperacion/$1/$2/$3');

    // Recepción
    $routes->post('recepcion/(:segment)/(:num)', 'Movimiento::recepcion/$1/$2');
    $routes->post('recepcioncompra/(:segment)/(:num)', 'Movimiento::recepcioncompra/$1/$2');

    // Imprimir
    $routes->get('(:segment)/imprime/(:num)', 'Movimiento::imprime/$1/$2');
});
/**************/
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
