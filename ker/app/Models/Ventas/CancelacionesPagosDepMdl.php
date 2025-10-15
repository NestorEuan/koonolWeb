<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class CancelacionesPagosDepMdl extends Model
{
    protected $table = 'vtcancelacionespagosdep';

    protected $allowedFields = [
        'cDestinoPago', 'nIdDestinoPago', 'nImporteCancelado', 
        'nIdAfectado', 'cObservaciones', 'dtAlta',
    ];

    protected $primaryKey = 'nIdCancelacionesPagosDep';

    protected $useTimestamps = true;

    protected $useSoftDeletes = false;

    protected $createdField = 'dtAlta';

    protected $deletedField = '';

    protected $updatedField = '';

}
/*
cDestinoPago : Campo que indica a donde se aplica el pago
               1-Pago Remision (idVentas, vtventas)
               2-Pago Compra (idCompra, cpcompra)
               3-Deposito Cliente (idDeposito, vtpagosadelantados)
nIdAfectado  : Segun sDestinoPago es: 
               1 -> nIdEScaja
               2 -> nIdCompra
               3 -> nIdEScaja
Proceso:               
Cuando se cancela el pago de una remision, las tablas a actualizar son:
   vtescaja se borra el registro de caja.
   vtcreditopago se borra el registro de pago.
   vtventassaldo se modifica la columna nSaldo para aumentar el pago cancelado
                  y la columna dFecSaldado a null.
En el caso del pago de una compra:
   cpcomprapago se borra el pago correspondiente.
   cpcompra se modifica la columna fSaldo para aumentar el pago cancelado.
En el caso de deposito del cliente:
      vtpagosadelantados se borra el registro (soft) solo si cumple la condicion
                         de que el saldo actual del deposito sea igual al monto 
                         del deposito.
      vtsaldopagosadelantados se libera el registro actualizando las columnas 
                              nSaldo y nIdPagosAdelantados a 0.
      vtcliente se resta del saldo el importe  del depósito.

La unica restriccion que se tiene es que solo se puede aplicar en los pagos
que sean de un cohte activo.
*/
