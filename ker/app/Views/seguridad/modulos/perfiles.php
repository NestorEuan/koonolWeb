<div class="container bg-light mt-4 border">
    <h4>Perfiles</h4>
    <div class="row">
        <div class="col border rounded py-2" >
            <div class="input-group justify-content-end">
                <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="perfil/a" style="z-index:auto;">Agregar</button>
            </div>
        </div>
    </div>
    <div class="row border rounded">
        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Descripción</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="2" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdPerfil'] ?></td>
                                <td><?= $r['sPerfil'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="perfil/e/<?= $r['nIdPerfil'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="perfil/b/<?= $r['nIdPerfil'] ?>" style="cursor:pointer;"></i>
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

<?= generaModalGeneral('frmModal', 'modal-lg') ?>

<script type="text/javascript">
    $(document).ready(function() {
        const appPerfil = {
            init: function() {
                $('#frmModal').on('show.bs.modal', appPerfil.agregar);
                // $('#frmFiltro button').on('click', appArticulo.aplicaFiltro);
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

        appPerfil.init();
    });
</script>