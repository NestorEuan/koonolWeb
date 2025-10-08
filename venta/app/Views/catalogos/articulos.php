<div class="container bg-light mt-4 border">
    <h4>Articulos</h4>
    <div class="row">
        <form class="col-12 col-md-8 border rounded py-2" action="<?= base_url('articulo') ?>" method="get" id="frmFiltrArticulos">
            <div class="input-group">
                <span class="input-group-text">Contiene en Descripción</span>
                <input type="text" name="sDescri" id="sDescri" class="form-control" value="<?= set_value('sDescri', '') ?>">
                <button type="submit" class="btn btn-primary" style="z-index:auto;">Filtrar</button>
            </div>
        </form>
        <div class="col-12 col-md-4 border rounded py-2" >
            <div class="input-group justify-content-end">
                <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="articulo/a" style="z-index:auto;">Agregar</button>
                <button class="btn btn-primary bg-gradient" style="z-index:auto;" id="btnExportArticulos">Exportar</button>
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
                        <th>Peso Kg</th>
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
                                <td><?= $r['nIdArticulo'] ?></td>
                                <td><?= $r['sDescripcion'] ?></td>
                                <td><?= $r['fPeso'] . ($r['fPeso'] > 1000 ? sprintf(' (%01.2f Tons)', $r['fPeso'] / 1000) : '') ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="articulo/e/<?= $r['nIdArticulo'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="articulo/b/<?= $r['nIdArticulo'] ?>" style="cursor:pointer;"></i>
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

<?= generaModalGeneral($id = 'frmModal', 'modal-lg') ?>

<script type="text/javascript">
    $(document).ready(function() {
        const appArticulo = {
            init: function() {
                $('#frmModal').on('show.bs.modal', appArticulo.agregar);
                $('#btnExportArticulos').on('click', appArticulo.exportArticulos);

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
            },

            aplicaFiltro: function(e) {
                let f = $('#frmFiltro')[0];
                f.action = baseURL + '/articulos';
                f.submit();
            },
            
            exportArticulos: function() {
                window.open(baseURL + '/articulo/exportArticulos?' + $('#frmFiltrArticulos').serialize());
            },

        };

        appArticulo.init();
    });
</script>