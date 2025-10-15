Bitacora de Modificación - Actualización - Unificación - Mejora del sistema de ventas
Ferromat - Demo - Kerlis

PASO 1. Actualizar versiones de producción con las versiones en localhost.

Modificaciones de App.php, DataBase.php no se consideran ya que es configuración de cada aplicación.

1- Demo es un copia identica de ferromat. Salgo que al ser modo demo se modifica lo siguiente:
Controllers\Seguridad\Usuarios.php
Demo: Se modifica para que cuando sea super usuario el perfil de usuario sea admin
Linea 25: $data['esSuper'] = $_SESSION['perfilUsuario'] == '-1';
Models\Envios\EnvioMdl.php
Demo: enviosart() lineas 188 - 192 y 201-202
Views\seguridad\usuarios.php
Demo: linea 37: Se valida que si el usuario no es administrador no se puedan modificar los usuarios.
<?php if (!$esSuper && $r['nIdPerfil'] == '-1') continue; ?>

PASO 2. 
Se actualiza el repositorio actual de Kerlis y Ferromat con lo que está en el directorio de cada proyecto.

PASO 2.1.
Se compararán las versiones de producción con el repositorio para actualizar lo necesario de cada proyecto (Kerlis y Ferromat).

Actualización KoonolWeb:
Controllers\
    Catalogos\
        AgenteVentas, ArtPrecio, Chofer, Cliente, Configuracion, RazonSocial, Sucursal, TipoPago. Se actualiza por la versión de CodeIgniter en Ker.
    Envio\
        Viaje.php: Está actualizado en Ker, sin embargo ahi no se usa. Hay que validar cual es la diferencia.
    Viajes\
        Viaje.php, ViajeBase.php, ViajeCtrl.php : En ker no hay viajes, por lo que todo lo actualizado en Ventas se pasa a Ker.