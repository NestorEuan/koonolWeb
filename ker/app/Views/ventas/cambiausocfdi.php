<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded" id="mdlFrmUsoC">
        <div class="row">
            <div class="col">
                <h5>Modificar el Uso del CFDI</h5>
                <h5 class="text-primary">Remision: <?= $folRemision ?></h5>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <?php generaCampoTexto('cIdUsoCfdi', $error ?? false, 'select', $registro ?? null, $modo, 'form-select-sm', '', $lstUso, 'sDescripcion'); ?>
            </div>
        </div>
        <?php if ($bCambioFecha == '1') : ?>
            <div class="row mb-3">
                <div class="col">
                    <?php generaCampoTexto('dFechaFactura', $error ?? false, 'date', $registro ?? null, $modo, 'form-control-sm'); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarUso">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let artDispo = {
            init: function() {
                $('#btnGuardarUso').on('click', artDispo.enviar);
                $('#cIdUsoCfdi').focus();
            },

            enviar: function(e) {
                $.ajax({
                    url: '<?= $url ?>',
                    method: 'POST',
                    data: $('#mdlFrmUsoC [name]').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModaldf .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };
        artDispo.init();
    });
</script>