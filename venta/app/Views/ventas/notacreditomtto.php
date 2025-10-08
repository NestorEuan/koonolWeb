<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<div id="frmNotCred" class="container">
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="Fecha" class="form-label">Fecha de la Nota</label>
                <?php generaCampoTexto('Fecha', $error ?? false, 'date', $registro ?? null, $modo); ?>
            </div>
        </div>
        <div class="col">
            <div class="mb-3">
                <label for="nImporte" class="form-label">Importe de la nota</label>
                <?php generaCampoTexto('nImporte', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="sMotivo" class="form-label">Motivo de la Nota</label>
        <?php generaCampoTexto('sMotivo', $error ?? false, 'textarea', $registro ?? null, $modo, '', 'maxlength="90"'); ?>
    </div>

</div>
<hr>
<div class="d-flex justify-content-end">
    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
    <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        let appNotaCredito = {
            init: function() {
                $('#btnGuardar').on('click', appNotaCredito.enviar);
                $('#Fecha').focus();
            },
            proceso: false,
            enviar: function(e) {
                if (appNotaCredito.proceso) return;
                appNotaCredito.proceso = true;
                $('#btnGuardar')[0].disabled = true;
                miGlobal.toggleBlockPantalla('Guardando Nota Credito');
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#frmNotCred .form-control').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        appNotaCredito.proceso = false;
                        $('#btnGuardar')[0].disabled = false;
                        miGlobal.toggleBlockPantalla('');
                        $('#frmModal .modal-body').html(data);

                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    appNotaCredito.proceso = false;
                    $('#btnGuardar')[0].disabled = false;
                    miGlobal.toggleBlockPantalla('');
                });
            },
        };
        appNotaCredito.init();
    });
</script>