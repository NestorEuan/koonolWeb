<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addConfiguracionfrm">
    <input type="hidden" id="nIdConfiguracion" name="nIdConfiguracion" value="<?= $id ?>">
    <div class="mb-3">
        <label for="sID" class="form-label">Variable ID</label>
        <?php generaCampoTexto('sID', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sDescripcion" class="form-label">Descripción</label>
        <?php generaCampoTexto('sDescripcion', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sValor" class="form-label">Valor string</label>
        <?php generaCampoTexto('sValor', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="fValor" class="form-label">Valor num&eacute;rico</label>
        <?php generaCampoTexto('fValor', $error ?? false, null, $registro ?? null, $modo); ?>
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
        let addConfiguracion = {
            init: function() {
                $('#btnGuardar').on('click', addConfiguracion.enviar);
                $('#sID').on('blur', (e) => {
                    let c = e.target.value.toString();
                    e.target.value = c.toUpperCase();
                });
            },
            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addConfiguracionfrm').serialize(),
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
        addConfiguracion.init();
    });
</script>
