<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addArtClasificacionfrm">
    <input type="hidden" id="nIdArtClasificacion" name="nIdArtClasificacion" value="<?= $id ?>">
    <div class="mb-3">
        <label for="sClasificacion" class="form-label">Clasificaci&oacute;n</label>
        <?php generaCampoTexto('sClasificacion', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="nPorcentajeComision" class="form-label">Porcentaje Comision</label>
        <?php generaCampoTexto('nPorcentajeComision', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <span class="form-label">Color usado en el Reporte de Comisiones:</span>
        <?php generaCampoTexto('sColor', $error ?? false, 'color', $registro ?? null, $modo, 'form-control-color'); ?>
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
        let addArtClasificacion = {
            init: function() {
                $('#btnGuardar').on('click', addArtClasificacion.enviar);
                $('#nPorcentajeComision').on('input', function(e) {
                    miGlobal.valNumero(e, addArtClasificacion, {
                        re: /^\d*(?:\.\d{0,2})?$/g,
                        numeroMax: 90.99,
                    })
                });
            },
            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addArtClasificacionfrm').serialize(),
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
        addArtClasificacion.init();
    });
</script>