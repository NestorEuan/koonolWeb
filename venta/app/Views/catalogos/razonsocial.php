<div class="container bg-light mt-4 border">
    <h4>Razones Sociales de la empresa para timbrar</h4>
    <div class="row">
        <form class="col-12 col-md-8 border rounded py-2" action="<?= base_url('razonsocial') ?>" method="get">
            <div class="input-group">
                <span class="input-group-text">Contiene en Descripción</span>
                <input type="text" name="sDescri" id="sDescri" class="form-control" value="<?= set_value('sDescri', '') ?>">
                <button type="submit" class="btn btn-primary" style="z-index:auto;">Filtrar</button>
            </div>
        </form>
        <div class="col-12 col-md-4 border rounded py-2" >
            <div class="input-group justify-content-end">
                <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="razonsocial/a" style="z-index:auto;">Agregar</button>
            </div>
        </div>
    </div>
    <div class="row border rounded">
        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>RFC</th>
                        <th>Razón Social</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="4" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdRazonSocial'] ?></td>
                                <td><?= $r['sRFC'] ?></td>
                                <td><?= $r['sRazonSocial'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="razonsocial/e/<?= $r['nIdRazonSocial'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="razonsocial/b/<?= $r['nIdRazonSocial'] ?>" style="cursor:pointer;"></i>
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

<?= generaModalGeneral() ?>

<script type="text/javascript">
    $(document).ready(function() {
        const appRazonSocial = {
            init: function() {
                $('#frmModal').on('show.bs.modal', appRazonSocial.agregar);
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

        appRazonSocial.init();
    });
</script>