<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addTipoPagofrm">
    <input type="hidden" id="nIdTipoPago" name="nIdTipoPago" value="<?= $id ?>">
    <div class="mb-3">
        <label for="sLeyenda" class="form-label">Leyenda a mostrar</label>
        <?php generaCampoTexto('sLeyenda', $error ?? false, null, $registro ?? null, $modo, '', 'maxlength="25"'); ?>
    </div>
    <div class="mb-3">
        <label for="sDescripcion" class="form-label">Descripción</label>
        <?php generaCampoTexto('sDescripcion', $error ?? false, 'textarea', $registro ?? null, $modo, '', 'maxlength="90"'); ?>
    </div>
    <div class="mb-3">
        <label for="nComision" class="form-label">% Comisión</label>
        <?php generaCampoTexto('nComision', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sTipoSAT" class="form-label">Codigo SAT</label>
        <?php generaCampoTexto('sTipoSAT', $error ?? false, null, $registro ?? null, $modo, '', 'maxlength="2"'); ?>
    </div>
    <div class="mb-3">
        <label for="cTipo" class="form-label">Tipo aplicación</label>
        <select class="form-select" aria-label="Selecciona tipo" <?= $modo == 'B' ? 'readonly' : '' ?> id="cTipo" name="cTipo">
            <?php foreach ($opcTipo as $k => $v) : ?>
                <option value="<?= $k ?>" <?= esc($registro['cTipo'] ?? '') == $k ? 'selected' : '' ?> > 
                    <?= $v ?>
                </option>
            <?php endforeach; ?>
        </select>
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
        miGlobal.entregaCampo = '#sLeyenda';
        let addTipoPago = {
            init: function() {
                $('#btnGuardar').on('click', addTipoPago.enviar);
                $(miGlobal.entregaCampo).select();
            },
            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addTipoPagofrm').serialize(),
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
        addTipoPago.init();
    });
</script>