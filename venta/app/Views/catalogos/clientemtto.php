<?php $deshabilitaListaPrecio = isset($deshabilitaListaPrecio); ?>
<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="<?= $frmURL ?>" method="post" id="addClientefrm">
    <input type="hidden" id="nIdCliente" name="nIdCliente" value="<?= $id ?>">
    <?php generaCampoTexto('nIdCliente', $error ?? false, 'hidden', $registro ?? null, $modo, 'form-control-sm'); ?>
    <div class="mb-3">
        <label for="sNombre" class="form-label">Nombre</label>
        <?php generaCampoTexto('sNombre', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm'); ?>
    </div>
    <div class="mb-3">
        <label for="sDireccion" class="form-label">Direcci&oacute;n</label>
        <?php generaCampoTexto('sDireccion', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm'); ?>
    </div>
    <div class="mb-3">
        <label for="sNombreConstanciaFiscal" class="form-label">Nombre Fiscal</label>
        <?php generaCampoTexto('sNombreConstanciaFiscal', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm'); ?>
    </div>
    <div class="row">
        <div class="mb-3 col-5">
            <label for="sRFC" class="form-label">R.F.C.</label>
            <?php generaCampoTexto('sRFC', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm', 'maxlength="15"'); ?>
        </div>
        <div class="mb-3 col-3">
            <label for="cCP" class="form-label">CP</label>
            <?php generaCampoTexto('cCP', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm', 'maxlength="5"'); ?>
        </div>
        <div class="mb-3 col-4">
            <label for="sCelular" class="form-label">Celular</label>
            <?php generaCampoTexto('sCelular', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm', 'maxlength="10"'); ?>
        </div>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">eMail</label>
        <?php generaCampoTexto('email', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm'); ?>
    </div>
    <div class="mb-3">
        <label for="cIdUsoCfdi" class="form-label">Uso CFDI</label>
        <?php generaCampoTexto('cIdUsoCfdi', $error ?? false, 'select', $registro ?? null, $modo, 'form-select-sm', '', $usoCfdi, 'sDescripcion'); ?>
    </div>
    <div class="mb-3">
        <label for="cIdRegimenFiscal" class="form-label">Régimen Fiscal</label>
        <?php generaCampoTexto('cIdRegimenFiscal', $error ?? false, 'select', $registro ?? null, $modo, 'form-select-sm', '', $regfis, 'sDescripcion'); ?>
    </div>
    <div class="mb-3">
        <label for="nIdTipoLista" class="form-label">Lista de precios usada</label>
        <?php generaCampoTexto('nIdTipoLista', $error ?? false, 'select', $registro ?? null, $deshabilitaListaPrecio ? 'B' : $modo, 'form-select-sm', '', $tipoLista, 'cNombreTipo'); ?>
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
        miGlobal.entregaCampo = '#sNombre';

        let addCliente = {
            desdeVentas: <?= $modo == 'A' && $id == '1' ? 'true' : 'false' ?>,
            init: function() {
                $('#btnGuardar').on('click', addCliente.enviar);
                $(miGlobal.entregaCampo).select();
            },
            enviar: function(e) {
                let c = $('#sNombreConstanciaFiscal').val();
                $('#sNombreConstanciaFiscal').val(c.toUpperCase());
                c = $('#sRFC').val();
                $('#sRFC').val(c.toUpperCase());
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addClientefrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        if(addCliente.desdeVentas) {
                            id = data.substr(2, 7).trim();
                            $('#frmBuscaCliente #nIdCliente').val(id);
                            $('#frmBuscaCliente')[0].submit();
                        } else {
                            location.reload();
                        }
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
        };
        addCliente.init();
    });
</script>