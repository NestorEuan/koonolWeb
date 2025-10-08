<div class="container bg-light mt-4 border">
    <h4>Configuración</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div class="row">
                <label for="sDescripcion" class="col-3 col-md-2 col-form-label">Configuraci&oacute;n</label>
                <div class="col-6 col-md-7"><input type="text" name="sDescripcion" class="form-control" id="sDescripcion"></div>
                <div class="col-3 col-md-3"><button class="btn btn-secondary bg-gradient">Filtrar</button></div>
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-5" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="configuracion/a">Agregar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Id Variable</th>
                        <th>Descripción</th>
                        <th>Valor Cadena</th>
                        <th>Valor numérico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="6" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdConfiguracion'] ?></td>
                                <td><?= $r['sID'] ?></td>
                                <td><?= $r['sDescripcion'] ?></td>
                                <td><?= $r['sValor'] ?></td>
                                <td><?= $r['fValor'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="configuracion/e/<?= $r['nIdConfiguracion'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="configuracion/b/<?= $r['nIdConfiguracion'] ?>" style="cursor:pointer;"></i>
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
        const appConfiguracion = {

            init: function() {
                document.getElementById('frmModal').addEventListener('show.bs.modal', appConfiguracion.agregar);
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
        appConfiguracion.init();
    });
</script>