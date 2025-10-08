<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addSucursalfrm">
    <input type="hidden" id="nIdSucursal" name="nIdSucursal" value="<?= $id ?>">
    <div class="mb-3">
        <label for="sDescripcion" class="form-label">Nombre</label>
        <?php generaCampoTexto('sDescripcion', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sDireccion" class="form-label">Direccion</label>
        <?php generaCampoTexto('sDireccion', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="nIdRazonSocial" class="form-label">Razon Social para Timbrar CFDI</label>
        <?php generaCampoTexto('nIdRazonSocial', $error ?? false, 'select', $registro ?? null, $modo ?? '', '', '', $regRazSocial, 'sDescripcion', ''); ?>
    </div>
    <div class="row">
        <div class="col-5 mb-3">
            <label for="sClave" class="form-label">Clave</label>
            <?php generaCampoTexto('sClave', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
        <div class="col-3 mb-3">
            <label for="sCP" class="form-label">Cod. Postal</label>
            <?php generaCampoTexto('sCP', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
        <div class="col-4 mb-3">
            <label for="sCelular" class="form-label">Celular</label>
            <?php generaCampoTexto('sCelular', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
    </div>
    <div class="mb-3">
        <label for="sEmail" class="form-label">Email</label>
        <?php generaCampoTexto('sEmail', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sEncargado" class="form-label">Encargado</label>
        <?php generaCampoTexto('sEncargado', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="row">
        <div class="col-3 mb-3 text-center">
            <label for="nFolioRemision" class="form-label">Folio Remision</label>
            <?php generaCampoTexto('nFolioRemision', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
        <div class="col-3 mb-3 text-center">
            <label for="nFolioCotizacion" class="form-label">Folio Cotizacion</label>
            <?php generaCampoTexto('nFolioCotizacion', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
        <div class="col-3 mb-3 text-center">
            <label for="sSerie" class="form-label"><br>Serie</label>
            <?php generaCampoTexto('sSerie', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
        <div class="col-3 mb-3 text-center">
            <label for="nFolioFactura" class="form-label">Folio<br>Factura</label>
            <?php generaCampoTexto('nFolioFactura', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
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
        let addSucursal = {
            init: function() {
                $('#btnGuardar').on('click', addSucursal.enviar);
            },
            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addSucursalfrm').serialize(),
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
        addSucursal.init();
    });
</script>