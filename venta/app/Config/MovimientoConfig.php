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
            'usa_viajes' => true,
            'usa_permisos_sucursal' => false,        // NUEVO
            'usa_modelo_saldo_compra' => true,       // NUEVO
            'usa_cancelacion_entradas' => false,     // NUEVO
            'guarda_usuario_movimiento' => true,     // NUEVO
            'valida_sin_existencia' => true,         // NUEVO
            'usa_historial_navegacion' => true,      // NUEVO
            'pantalla_impresion_extendida' => true,  // NUEVO
            'permite_cancelar_entregas' => true,     // NUEVO (entregaC)
        ],
        'ker' => [
            'modulos_activos' => ['compra', 'entrada', 'salida', 'traspaso', 'capturainv'],
            'usa_envios' => false,
            'usa_viajes' => false,
            'usa_permisos_sucursal' => true,         // NUEVO
            'usa_modelo_saldo_compra' => false,      // NUEVO
            'usa_cancelacion_entradas' => true,      // NUEVO
            'guarda_usuario_movimiento' => false,    // NUEVO
            'valida_sin_existencia' => false,        // NUEVO
            'usa_historial_navegacion' => false,     // NUEVO
            'pantalla_impresion_extendida' => false, // NUEVO
            'permite_cancelar_entregas' => false,    // NUEVO
        ]
    ];

    // Configuración de cada tipo de movimiento
    public array $operaciones = [
        'compra' => [
            'titulo' => 'Cotizaciones de compra',
            'titulo_singular' => 'Cotización de compra',
            'master_model' => 'App\Models\Compras\ComprasMdl',
            'detalle_model' => 'App\Models\Compras\CompraDetalleMdl',
            'detalle_saldo_model' => 'App\Models\Compras\CompraDetalleSaldoMdl',
            'master_key' => 'nIdCompra',
            'date_field' => 'dcompra',
            'requiere_proveedor' => true,
            'signo_inventario' => 1,
            'actualiza_costo' => true,
            'tiene_saldo' => true,
        ],
        'entrada' => [
            'titulo' => 'Entradas especiales',
            'titulo_singular' => 'Entrada especial',
            'master_model' => 'App\Models\Almacen\EntradaMdl',
            'detalle_model' => 'App\Models\Almacen\EntradaDetalleMdl',
            'cancel_model' => 'App\Models\Almacen\EntradaCancelMdl',
            'master_key' => 'nIdEntrada',
            'date_field' => 'dEntrada',
            'requiere_proveedor' => false,
            'signo_inventario' => 1,
            'permite_cancelar' => true,
        ],
        'salida' => [
            'titulo' => 'Salidas especiales',
            'titulo_singular' => 'Salida especial',
            'master_model' => 'App\Models\Almacen\SalidaMdl',
            'detalle_model' => 'App\Models\Almacen\SalidaDetalleMdl',
            'master_key' => 'nIdSalida',
            'date_field' => 'dSalida',
            'requiere_proveedor' => false,
            'signo_inventario' => -1,
            'tiene_tipo_salida' => true,
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

    public function getAppConfig(): ?array
    {
        return $this->apps[$this->app] ?? null;
    }

    public function getTituloAccion(string $accion): string
    {
        return $this->acciones[$accion] ?? '';
    }

    public function moduloActivo(string $modulo): bool
    {
        return in_array($modulo, $this->apps[$this->app]['modulos_activos']);
    }

    public function getOperacion(string $operacion): ?array
    {
        return $this->operaciones[$operacion] ?? null;
    }

    /**
     * Verifica si una característica está habilitada en la app actual
     * 
     * @param string $caracteristica Nombre de la característica a verificar
     * @return bool
     */
    public function tieneCaracteristica(string $caracteristica): bool
    {
        $app = $this->getAppConfig();
        return $app[$caracteristica] ?? false;
    }
}
