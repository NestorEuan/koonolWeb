<?php

namespace App\Controllers\Almacen;

use App\Config\MovimientoConfig;

/**
 * Factory para crear instancias de modelos según el tipo de operación
 * Elimina todos los switch/case repetidos
 */
class MovimientoFactory
{
    protected MovimientoConfig $config;

    public function __construct()
    {
        $this->config = new MovimientoConfig();
    }

    /**
     * Crea el modelo master (ComprasMdl, EntradaMdl, etc)
     */
    public function createMaster(string $operacion)
    {
        $opConfig = $this->config->getOperacion($operacion);
        if (!$opConfig) {
            throw new \Exception("Operación '$operacion' no configurada");
        }

        $modelClass = $opConfig['master_model'];
        if (!class_exists($modelClass)) {
            throw new \Exception("Modelo $modelClass no existe");
        }

        return new $modelClass();
    }

    /**
     * Crea el modelo detalle (CompraDetalleMdl, EntradaDetalleMdl, etc)
     */
    public function createDetalle(string $operacion)
    {
        $opConfig = $this->config->getOperacion($operacion);
        if (!$opConfig) {
            throw new \Exception("Operación '$operacion' no configurada");
        }

        $modelClass = $opConfig['detalle_model'];
        if (!class_exists($modelClass)) {
            throw new \Exception("Modelo $modelClass no existe");
        }

        return new $modelClass();
    }

    /**
     * Obtiene la configuración de una operación
     */
    public function getConfig(string $operacion): array
    {
        $opConfig = $this->config->getOperacion($operacion);
        if (!$opConfig) {
            throw new \Exception("Operación '$operacion' no configurada");
        }
        return $opConfig;
    }

    /**
     * Obtiene el campo clave (nIdCompra, nIdEntrada, etc)
     */
    public function getMasterKey(string $operacion): string
    {
        return $this->getConfig($operacion)['master_key'];
    }

    /**
     * Obtiene el campo de fecha
     */
    public function getDateField(string $operacion): string
    {
        return $this->getConfig($operacion)['date_field'];
    }

    /**
     * Obtiene el signo para inventario (1 = suma, -1 = resta)
     */
    public function getSignoInventario(string $operacion): int
    {
        return $this->getConfig($operacion)['signo_inventario'];
    }
}