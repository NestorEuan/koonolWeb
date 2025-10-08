<?php
$cajas = [];
for ($i = 1; $i <= 10; $i++) {
    $v = strval($i);
    if (array_search($v, $cajasA) === false) {
        $cajas[] = [
            'nNumCaja' => $v,
            'sDes' => 'Caja ' . $v
        ];
    }
}

?>

<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h4>Corte de Caja</h4>
                <hr>
            </div>
        </div>
        <form action="" method="post" class="row" id="addCorteCajafrm" onsubmit="return false;">
            <?php if (isset($IdCorteAcerrar)) : ?>
                <input type="hidden" name="idCorteParaCerrar" value="<?= $IdCorteAcerrar ?>">
            <?php endif; ?>
            <p class="border-bottom-secondary mb-2">Usuario: <span class="fw-bold"><?= $nombreUsu ?></span></p>
            <div class="col-12 mb-3">
                <label for="nNumCaja" class="form-label">Caja</label>
                <?php generaCampoTexto('nNumCaja', $error ?? false, 'select', $registro ?? null, $modo, '', '', $cajas); ?>
            </div>
            <div class="col-7">
                <label for="nSaldoIni" class="form-label">Saldo de efectivo en Caja</label>
                <?php generaCampoTexto('nSaldoIni', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
            </div>
        </form>
        <hr>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let appCorteCaja = {
            cantAnt: '',
            idCorteParaCerrar: '<?= isset($IdCorteAcerrar) ? $IdCorteAcerrar : "0" ?>',
            desdeVentas: '<?= $desdeVentas ? '1' : '0' ?>',
            init: function() {
                $('#btnGuardar').on('click', appCorteCaja.enviar);
                $('#nSaldoIni').on('input', appCorteCaja.onInput);
            },

            onInput: function(e) {
                miGlobal.valNumero(e, appCorteCaja);
            },
            bndGuardado: false,
            enviar: function(e) {
                if (appCorteCaja.bndGuardado) return;
                appCorteCaja.bndGuardado = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addCorteCajafrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        if (appCorteCaja.desdeVentas == '0')
                            location.reload();
                        else
                            location.href = '<?= base_url('ventas') ?>';
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };
        appCorteCaja.init();
    });
</script>