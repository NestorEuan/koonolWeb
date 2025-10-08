<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<form action="" method="post" id="addUsuario"  onsubmit="return false;">
    <input type="hidden" id="nIdUsuario" name="nIdUsuario" value="<?= $id ?>">
    <div class="mb-3">
        <label for="sLogin" class="form-label">Login</label>
        <?php generaCampoTexto('sLogin', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="sNombre" class="form-label">Nombre</label>
        <?php generaCampoTexto('sNombre', $error ?? false, null, $registro ?? null, $modo); ?>
    </div>
    <div class="mb-3">
        <label for="nIdPerfil" class="form-label">Perfil</label>
        <?php generaCampoTexto('nIdPerfil', $error ?? false, 'select',       $registro ?? null, $modo, '', '', $regPerfil, 'sPerfil'); ?>
    </div>
    <div class="mb-3">
        <label for="nIdSucursal" class="form-label">Sucursal</label>
        <?php generaCampoTexto('nIdSucursal', $error ?? false, 'select', $registro ?? null, $modo, '', '', $regSucursal, 'sDescripcion'); ?>
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
        let addUsuario = {
            init: function() {
                $('#btnGuardar').on('click', addUsuario.enviar);
            },
            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addUsuario').serialize(),
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
        addUsuario.init();
    });
</script>
