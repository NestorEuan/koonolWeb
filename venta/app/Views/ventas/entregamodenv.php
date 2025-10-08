<div class="container border">
    <h5>Entrega en modo ENV (El cliente recoge su pedido con el proveedor)</h5>
    <div class="row mb-1">
        <div class="col">
            <div class="input-group mb-1">
                <span class="input-group-text">Remision</span>
                <span class="form-control text-center"><?= $registro['nFolioRemision'] ?></span>
                <span class="input-group-text">Fecha</span>
                <span class="form-control text-center"><?= $registro['fecha'] ?></span>
                <span class="input-group-text">Importe</span>
                <span class="form-control text-center fw-bold"><?= number_format(floatval($registro['nTotal']), 2) ?></span>
            </div>
            <div class="input-group">
                <span class="input-group-text">Cliente</span>
                <span class="form-control"><?= $registro['sNombre'] ?></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table id="tblDetalle" class="table table-striped table-sm w-50 mx-auto">
                <thead>
                    <tr>
                        <th class="text-center">Cantidad</th>
                        <th>Descripcion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $r) : ?>
                        <tr>
                            <td class="text-center"><?= floatval($r['nCant']) ?></td>
                            <td><?= $r['nomArt'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (isset($err)) : ?>
        <div class="alert alert-danger" role="alert">
            <?= $err ?>
        </div>
    <?php endif; ?>
    <div class="row mb-2">
        <div class="col text-center">
            <button type="button" class="btn btn-secondary me-4" data-bs-dismiss="modal">Cerrar</button>
            <?php if (!isset($err)) : ?>
                <button type="button" class="btn btn-primary" id="btnAceptarEntENV">Aceptar</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        let appEnt = {
            init: function() {
                $('#btnAceptarEntENV').on('click', appEnt.enviar);
            },

            proceso: false,
            enviar: function(e) {
                if (appEnt.proceso) return;
                appEnt.proceso = true;
                $('#btnAceptarEntENV')[0].disabled = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: {},
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    location.reload();
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
        }
        appEnt.init();
    });
</script>