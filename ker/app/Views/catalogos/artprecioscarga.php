<h5 class="bg-ligth px-2 fw-bold">Cargar Archivo de Precios</h5>
<hr>
<form action="" method="post" id="frmLoadPrecios" onsubmit="return false;" enctype="multipart/form-data" accept-charset="utf-8">
    <div class="mb-3">
        <label for="sArchivo" class="form-label">Selecciona el archivo de precios a cargar</label>
        <?php generaCampoTexto('sArchivo', $error ?? false, 'file', $registro ?? null, $modo ?? ''); ?>
    </div>
</form>
<hr>
<?php if ($msjError != '') : ?>
    <div class="mb-3 text-danger" id="errorCarga">
        <?= $msjError ?>
    </div>
    <hr>
<?php endif; ?>
<div class="d-flex justify-content-end">
    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
    <button type="button" class="btn btn-primary" id="btnGuardar">Cargar</button>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        let precios = {
            init: function() {
                $('#btnGuardar').on('click', precios.enviar);
            },
            enviar: function(e) {
                $('#errorCarga').html('<span></span>');
                let f = new FormData();
                let archivos = $('#sArchivo')[0].files;
                if (archivos.length == 0) return;
                f.append('sArchivo', archivos[0]);
                $.ajax({
                    url: 'artprecios/cargaPrecios',
                    method: 'POST',
                    data: f,
                    contentType: false,
                    processData: false,
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
        precios.init();
    });
</script>