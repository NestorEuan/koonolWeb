<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="" method="post" id="addRazonfrm">
    <input type="hidden" id="nIdRazonSocial" name="nIdRazonSocial" value="<?= $id ?>">
    <div class="mb-3">
        <label for="sRFC" class="form-label">RFC</label>
        <?php generaCampoTexto('sRFC', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sRazonSocial" class="form-label">Razon Social</label>
        <?php generaCampoTexto('sRazonSocial', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="cIdRegimenFiscal" class="form-label">Reg. Fiscal</label>
        <?php generaCampoTexto('cIdRegimenFiscal', $error ?? false, 'select', $registro ?? null, $modo ?? '', '', '', $regRegFis, 'sDescripcion'); ?>
    </div>
    <div class="mb-3">
        <label for="sCertificado" class="form-label">Folio Certificado</label>
        <?php generaCampoTexto('sCertificado', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sIdPAC" class="form-label">Usuario PAC</label>
        <?php generaCampoTexto('sIdPAC', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sPswPAC" class="form-label">Contraseña</label>
        <?php generaCampoTexto('sPswPAC', $error ?? false, 'password', $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sPswPAC2" class="form-label">Confirmar Contraseña</label>
        <?php generaCampoTexto('sPswPAC2', $error ?? false, 'password', $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sLeyenda" class="form-label">Leyenda en Impresion</label>
        <?php generaCampoTexto('sLeyenda', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
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
        let addRazon = {
            init: function() {
                $('#btnGuardar').on('click', addRazon.enviar);
            },
            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addRazonfrm').serialize(),
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
        addRazon.init();
    });
</script>