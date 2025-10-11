<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class MovimientoConfig extends BaseConfig
{
    // Para cambiar entre aplicaciones, cambiar este valor
    public string $app = 'venta'; // o 'ker'

    // Qué módulos están activos en cada aplicación
    public array $apps = [
        'venta' => [
            'modulos_activos' => ['compra', 'entrada', 'salida', 'traspaso', 'entrega'],
            'usa_envios' => true,
        ],
        'ker' => [
            'modulos_activos' => ['compra', 'entrada', 'salida', 'traspaso', 'capturainv'],
            'usa_envios' => false,
        ]
    ];

    // Configuración de cada tipo de movimiento
    public array $operaciones = [
        'compra' => [
            'titulo' => 'Cotizaciones de compra',
            'master_model' => 'App\Models\Compras\ComprasMdl',
            'detalle_model' => 'App\Models\Compras\CompraDetalleMdl',
            'master_key' => 'nIdCompra',
            'date_field' => 'dcompra',
            'requiere_proveedor' => true,
            'signo_inventario' => 1,
        ],
        'entrada' => [
            'titulo' => 'Entradas especiales',
            'master_model' => 'App\Models\Almacen\EntradaMdl',
            'detalle_model' => 'App\Models\Almacen\EntradaDetalleMdl',
            'master_key' => 'nIdEntrada',
            'date_field' => 'dEntrada',
            'requiere_proveedor' => false,
            'signo_inventario' => 1,
        ],
        'salida' => [
            'titulo' => 'Salidas especiales',
            'master_model' => 'App\Models\Almacen\SalidaMdl',
            'detalle_model' => 'App\Models\Almacen\SalidaDetalleMdl',
            'master_key' => 'nIdSalida',
            'date_field' => 'dSalida',
            'requiere_proveedor' => false,
            'signo_inventario' => -1,
        ],
        'traspaso' => [
            'titulo' => 'Traspaso almacén',
            'master_model' => 'App\Models\Almacen\TraspasoMdl',
            'detalle_model' => 'App\Models\Almacen\TraspasoDetalleMdl',
            'master_key' => 'nIdTraspaso',
            'date_field' => 'dTraspaso',
            'requiere_proveedor' => true,
            'signo_inventario' => -1,
        ],
        'entrega' => [
            'titulo' => 'Entrega de mercancías',
            'master_model' => 'App\Models\Almacen\EntregaMdl',
            'detalle_model' => 'App\Models\Almacen\EntregaDetalleMdl',
            'master_key' => 'nIdEntrega',
            'date_field' => 'dEntrega',
            'requiere_proveedor' => false,
            'signo_inventario' => -1,
        ],
        'capturainv' => [
            'titulo' => 'Captura modo inventario',
            'master_model' => 'App\Models\Almacen\InventarioCapturaMdl',
            'detalle_model' => 'App\Models\Almacen\InventarioCapturaDetalleMdl',
            'master_key' => 'idInventarioCaptura',
            'date_field' => 'dAplicacion',
            'requiere_proveedor' => false,
            'signo_inventario' => 1,
        ],
    ];

    public array $acciones = [
        'a' => 'Agregar',
        'b' => 'Cancelar',
        'e' => 'Editar',
        'r' => 'Recepción',
        'p' => 'Imprimir',
    ];

    public function moduloActivo(string $modulo): bool
    {
        return in_array($modulo, $this->apps[$this->app]['modulos_activos']);
    }

    public function getOperacion(string $operacion): ?array
    {
        return $this->operaciones[$operacion] ?? null;
    }
}