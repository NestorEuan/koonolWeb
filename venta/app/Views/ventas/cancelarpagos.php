<?php if ($sePuedeCancelar) : ?>
    <div class="container position-relative">
        <div id="cancelarpagoAlert">
            <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
            </div>
        </div>
        <div class="p-2 border rounded">
            <h4><?= $titulo ?></h4>
            <div class="row">
                <div class="col">
                    <div class="input-group mb-1">
                        <span class="input-group-text"><?= $operacion == 'deposito' ? 'Folio Deposito' : 'Folio Movto Caja' ?></span>
                        <span class="form-control"><?= $registro['folio'] ?></span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text">Capturó</span>
                        <span class="form-control"><?= $registro['usuario'] ?></span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text">Fecha</span>
                        <span class="form-control text-center"><?= $registro['fecha'] ?></span>
                        <span class="input-group-text">Importe</span>
                        <span class="form-control text-end"><?= $registro['importe'] ?></span>
                    </div>
                    <?php if ($operacion == 'deposito') : ?>
                        <div class="input-group">
                            <span class="input-group-text">Cliente que depositó</span>
                            <span class="form-control"><?= $registro['cliente'] ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Motivo</span>
                        <span class="form-control"><?= $registro['motivo'] ?></span>
                    </div>
                    <hr>
                </div>
            </div>
            <?php if (!$sePuedeCancelar) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= $msjerr ?? '' ?>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" id="btnBackCanPag">Regresar</button>
                    <button type="button" class="btn btn-primary" id="btnOKCanPag">Confirma Cancelación</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let app = {
                esCancelacionDeposito: '<?= ($esDeposito ?? false) ? '1' : '0' ?>',
                init: function() {
                    $('#btnBackCanPag').on('click', app.regresa);
                    $('#btnOKCanPag').on('click', app.enviar);
                },

                regresa: function() {
                    if (app.esCancelacionDeposito == '1') {
                        $('#frmModal').modal('hide');
                        return;
                    }
                    $.get('<?= $frmURLant ?>', {}, null, 'html')
                        .done(function(data, textStatus, jqxhr) {
                            $('#frmModal .modal-body').html(data);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                },

                enviar: function() {
                    $.post('<?= $frmURL ?>', {}, null, 'html')
                        .done(function(data, textStatus, jqxhr) {
                            $('#frmModal .modal-body').html(data);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                },


            };
            app.init();
        });
    </script>
<?php else : ?>
    <div class="container">
        <h4><?= $titulo ?></h4>
        <hr>
        <div class="alert alert-danger text-center" role="alert">
            <?= $msjerr ?>
        </div>
        <div class="row">
            <div class="col px-4 text-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
<?php endif; ?>