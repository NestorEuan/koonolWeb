<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addTipoListafrm">
    <input type="hidden" id="nIdTipoLista" name="nIdTipoLista" value="<?= $id ?>">
    <div class="mb-3">
        <label for="cNombreTipo" class="form-label">Descripción</label>
        <?php generaCampoTexto('cNombreTipo', $error ?? false, null, $registro ?? null, $modo, '', 'maxlength="80"'); ?>
    </div>
    <div class="mb-3">
        <label for="cTipo" class="form-label">Abreviatura</label>
        <?php generaCampoTexto('cTipo', $error ?? false, null, $registro ?? null, $modo, '', 'maxlength="2"'); ?>
    </div>
    <div class="mb-3">
        <?php generaCampoTexto('bImprimirNota', $error ?? false, 'checkbox', $registro ?? null, $modo,'','',null,'1'); ?>
        <label for="bImprimirNota" class="form-check-label">Es precio tapado</label>
    </div>
</form>
<hr>
<div class="d-flex justify-content-end">
    <?php if ($modo === 'A' || $modo === 'E') : ?>
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
        miGlobal.entregaCampo = '#cNombreTipo';
        let addTipoLista = {
            init: function() {
                $('#btnGuardar').on('click', addTipoLista.enviar);
                $(miGlobal.entregaCampo).select();
            },
            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addTipoListafrm').serialize(),
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
        addTipoLista.init();
    });
</script>