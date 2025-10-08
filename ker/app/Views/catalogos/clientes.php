<div class="container bg-light mt-4 border">
    <h4>Clientes</h4>
    <div class="row">
        <form class="col-12 col-md-8 border rounded py-2" action="<?= base_url('cliente') ?>" method="get">
            <div class="input-group">
                <span class="input-group-text">Contiene en Nombre</span>
                <input type="text" name="sDescri" id="sDescri" class="form-control" value="<?= set_value('sDescri', '') ?>">
                <button type="submit" class="btn btn-primary" style="z-index:auto;">Filtrar</button>
            </div>
        </form>
        <div class="col-12 col-md-4 border rounded py-2">
            <div class="input-group justify-content-end">
                <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="cliente/a" style="z-index:auto;">Agregar</button>
                <button class="btn btn-primary bg-gradient" style="z-index:auto;">Exportar</button>
            </div>
        </div>
    </div>

    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Celular</th>
                        <th>R.F.C.</th>
                        <th>eMail</th>
                        <th>Saldo</th>
                        <th>Reg. Fiscal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="5" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdCliente'] ?></td>
                                <td><?= $r['sNombre'] ?></td>
                                <td><?= $r['sDireccion'] ?></td>
                                <td><?= $r['sCelular'] ?></td>
                                <td><?= $r['sRFC'] ?></td>
                                <td><?= $r['email'] ?></td>
                                <td class="text-end"><?= number_format(floatval($r['nSaldo']), 2) ?></td>
                                <td><?= $r['cIdRegimenFiscal'] ?></td>
                                <td>
                                    <?php if ($r['cTipoCliente'] == 'N') : ?>
                                        <i class="bi bi-pencil-fill text-primary me-1" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="cliente/e/<?= $r['nIdCliente'] ?>" style="cursor:pointer;"></i>
                                        <i class="bi bi-trash-fill text-danger" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="cliente/b/<?= $r['nIdCliente'] ?>" style="cursor:pointer;"></i>
                                    <?php else : ?>
                                        <i class="bi bi-pencil-fill text-secondary me-1" ></i>
                                        <i class="bi bi-trash-fill text-secondary" ></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
    </div>

</div>

<?= generaModalGeneral('frmModal', 'modal-dialog-scrollable') ?>

<script type="text/javascript">
    $(document).ready(function() {
        let appCliente = {

            init: function() {
                $('#frmModal').on('show.bs.modal', appCliente.agregar);
            },

            agregar: function(e) {
                let d = $(e.relatedTarget).data('llamar');
                $.ajax({
                    url: baseURL + '/' + d,
                    method: 'GET',
                    data: {},
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModal .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };
        appCliente.init();
    });
</script>