<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addArticulofrm">
    <input type="hidden" id="nIdArticulo" name="nIdArticulo" value="<?= $id ?>">
    <div class="mb-3">
        <label for="sDescripcion" class="form-label">Descripción</label>
        <?php generaCampoTexto('sDescripcion', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="row mb-3">
        <div class="col-6">
            <label for="sCodigo" class="form-label">Codigo</label>
            <?php generaCampoTexto('sCodigo', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
        <div class="col-6">
            <label for="nIdArtClasificacion" class="form-label">Clasificación</label>
            <?php generaCampoTexto('nIdArtClasificacion', $error ?? false, 'select', $registro ?? null, $modo ?? '', '', '', $regClasificacion, 'sClasificacion'); ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-2">
            <label for="fPeso" class="form-label">Peso (Kg)</label>
            <?php generaCampoTexto('fPeso', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
        </div>
        <div class="col-2">
            <label for="cUnidad" class="form-label">Unidad</label>
            <?php generaCampoTexto('cUnidad', $error ?? false, null, $registro ?? null, $modo, 'text-center', 'size="5"'); ?>
        </div>
        <div class="col-2">
            <label for="nMedida" class="form-label">Medida</label>
            <?php generaCampoTexto('nMedida', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
        </div>
        <div class="col-2">
            <label for="nCosto" class="form-label">Costo</label>
            <?php generaCampoTexto('nCosto', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
        </div>
        <div class="col-4" style="padding-top:2.5rem;">
            <?php generaCampoTexto('cConDescripcionAdicional', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ?>
            <label for="cConDescripcionAdicional" class="form-check-label">Con Descripcion Adicional</label>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-4">
            <?php generaCampoTexto('cSinExistencia', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ?>
            <label for="cSinExistencia" class="form-check-label">Articulo sin existencia</label>
        </div>
        <div class="col-4">
            <?php generaCampoTexto('cImporteManual', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ?>
            <label for="cImporteManual" class="form-check-label">Importe manual</label>
        </div>
        <div class="col-4">
            <?php generaCampoTexto('cPrecioTapadoFactura', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ?>
            <label for="cPrecioTapadoFactura" class="form-check-label">Usa precio tapado en facturas</label>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-3" style="padding-top:0.5rem;">
            <?php generaCampoTexto('cConArticuloRelacionado', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ?>
            <label for="cConArticuloRelacionado" class="form-check-label">Es articulo base</label>
        </div>
        <div class="col-4">
            <div class="input-group">
                <span class="input-group-text">ID articulo base</span>
                <?php generaCampoTexto('nIdArticuloAcumulador', $error ?? false, null, $registro ?? null, $modo, '', 'size="5"'); ?>
            </div>
            <!-- <label for="nIdArticuloAcumulador" class="form-label">ID articulo base</label> -->
        </div>
    </div>
    <h6 class="fw-bold">Datos SAT-CFDI</h6>
    <div class="row mb-3 ">
        <div class="col-6">
            <label for="sCveProdSer" class="form-label">Clave</label>
            <?php generaCampoTexto('sCveProdSer', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
        <div class="col-6">
            <label for="sCveUnidad" class="form-label">Clave Unidad</label>
            <?php generaCampoTexto('sCveUnidad', $error ?? false, null, $registro ?? null, $modo); ?>
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
        let addArticulo = {
            init: function() {
                $('#btnGuardar').on('click', addArticulo.enviar);
                $('#fPeso').on('input', addArticulo.onInput);
                $('#cConArticuloRelacionado').on('change', function(e) {
                    $('#nIdArticuloAcumulador')[0].disabled = e.target.checked;
                });
                $('#nIdArticuloAcumulador')[0].disabled = $('#cConArticuloRelacionado')[0].checked;
            },
            onInput: function(e) {
                miGlobal.valNumero(e, addArticulo);
            },
            enProceso: false,
            enviar: function(e) {
                if (addArticulo.enProceso) return;
                addArticulo.enProceso = true;
                $('#btnGuardar')[0].disabled = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addArticulofrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    addArticulo.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;
                });
            },
        };
        addArticulo.init();
    });
</script>