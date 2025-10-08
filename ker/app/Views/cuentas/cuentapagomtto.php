<?php

$aKeys = [
    'pagar' => ['nIdCompraPago', 'nIdCompra', 'nIdProveedor', 'Pagar a'],
    'cobrar' => ['nIdVentaPago', 'nIdVentas', 'nIdCliente', 'Pago de'],
];
$masterkey = $aKeys[$operacion][0];
$sourcekey = $aKeys[$operacion][1];
$campoproveedor = $aKeys[$operacion][2];
$cobrarpagar = $aKeys[$operacion][3];
$datefld = 'dtPago';
?>

<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addPagofrm">
    <input type="hidden" id="<?= $masterkey ?>" name="<?= $masterkey ?>" value="<?= $id ?>">
    <input type="hidden" id="<?= $sourcekey ?>" name="<?= $sourcekey ?>" value="<?= $id ?>">
    <input type="hidden" id="fSaldo" name="fSaldo" value="<?= $registro['fSaldo'] ?>">
    <div class="mb-3">
        <label for="sNombre" class="form-label"><?= $cobrarpagar; ?></label>
        <?php generaCampoTexto('sNombre', $error ?? false, 'text', $registro ?? null, '', '', 'readonly'); ?>
    </div>
    <div class="mb-3">
        <label for="fSaldo2" class="form-label">Saldo</label>
        <?php generaCampoTexto('fSaldo2', $error ?? false, 'text', $registro ?? null, '', 'text-end', 'readonly'); ?>
    </div>
    <div class="col">
        <label for="<?= $datefld ?>" class="form-label">F. de solicitud de <?= $operacion ?></label>
        <div class="input-group ">
            <?php generaCampoTexto("$datefld", $error ?? false, 'date', $registro ?? null, $modo); ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-6">
            <label for="nIdTipoPago" class="form-label">Tipo pago</label>
            <?php generaCampoTexto('nIdTipoPago', $error ?? false, 'select', $registro ?? null, $modo ?? '', '', '', $regTipoPago, 'sDescripcion'); ?>
        </div>
        <div class="col-6">
            <label for="fPago" class="form-label">Importe pago</label>
            <?php generaCampoTexto('fPago', $error ?? false, 'text', $registro ?? null, $modo, 'text-end'); ?>
        </div>
    </div>
</form>
<hr>
<div class="d-flex justify-content-end">
    <?php if ($modo !== 'B') : ?>
        <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
    <?php else : ?>
        <span class="text-danger fw-bold me-4" id="txtBorrar">Desea borrar el registro?</span>
        <button type="button" class="btn btn-secondary px-2 me-2" id="btnGuardar">Si</button>
        <button type="button" class="btn btn-primary px-2" data-bs-dismiss="modal" id="btnCancelar">No</button>
    <?php endif; ?>

</div>

<script type="text/javascript">
    $(document).ready(function() {
        let addCuentaPago = {
            init: function() {
                $('#btnGuardar').on('click', addCuentaPago.enviar);
                $('#fPago').on('input', addCuentaPago.onInput);
            },
            onInput: function(e) {
                miGlobal.valNumero(e, addCuentaPago);
            },
            proceso: false,
            enviar: function(e) {
                if(addCuentaPago.proceso) return;
                addCuentaPago.proceso = true;
                $('#btnGuardar')[0].disabled = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addPagofrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
        };
        addCuentaPago.init();
    });
</script>