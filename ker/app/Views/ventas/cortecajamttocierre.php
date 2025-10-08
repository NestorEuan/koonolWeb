<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h4 class="text-center">Cierre de Corte de Caja</h4>
                <hr>
            </div>
        </div>
        <form action="" method="post" class="row" id="addCorteCajafrm" onsubmit="return false;">
            <p class="border-bottom-secondary mb-2">Usuario: <span class="fw-bold"><?= $nombreUsu ?></span></p>
            <p class="border-bottom-secondary mb-2">Caja: <span class="fw-bold"><?= $numCaja ?></span></p>
            <p class="border-bottom-secondary mb-2">Fecha Apertura: <span class="fw-bold"><?= $dtApertura ?></span></p>
            <hr>
            <div class="col-7">
                <label for="nSaldoFin" class="form-label">Saldo de efectivo en Caja</label>
                <?php generaCampoTexto('nSaldoFin', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
            </div>
            <div class="col-12">
                <label for="sObservaciones" class="form-label">Observaciones</label>
                <?php generaCampoTexto('sObservaciones', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
            </div>
        </form>
        <hr>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let appCorteCaja = {
            cantAnt: '',

            init: function() {
                $('#btnGuardar').on('click', appCorteCaja.enviar);
                $('#nSaldoFin').on('input', (e) => {
                    miGlobal.valNumero(e, appCorteCaja);
                });
                $('#nSaldoFin').select();
            },

            enviar: function(e) {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addCorteCajafrm').serialize(),
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
            }
        };
        appCorteCaja.init();
    });
</script>