<div class="container bg-light mt-4 border">
    <h4>Agentes de Ventas</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div class="row">
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 text-end">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" 
                data-bs-target="#frmModal" id="btnAgregar" data-llamar="agentesventas/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="3" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdAgenteVentas'] ?></td>
                                <td><?= $r['sNombre'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal"
                                        data-bs-target="#frmModal" data-llamar="agentesventas/e/<?= $r['nIdAgenteVentas'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal"
                                        data-bs-target="#frmModal" data-llamar="agentesventas/b/<?= $r['nIdAgenteVentas'] ?>" style="cursor:pointer;"></i>
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
    const appAgenteVtas = {

        init: function () {
            $('#frmModal').on('show.bs.modal', appAgenteVtas.agregar);
        },

        agregar: function(e){
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
    $(document).ready(function() {
        appAgenteVtas.init();
    });
</script>
