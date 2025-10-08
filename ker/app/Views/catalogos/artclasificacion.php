<div class="container bg-light mt-4 border">
    <h4>Clasificaci&oacute;n de Art&iacute;culos</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div class="row">
                <label for="clasificacion" class="col-3 col-md-2 col-form-label">Clasificaci&oacute;n</label>
                <div class="col-6 col-md-7"><input type="text" name="clasificacion" class="form-control" id="clasificacion"></div>
                <div class="col-3 col-md-3"><button class="btn btn-secondary bg-gradient">Filtrar</button></div>
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" 
                data-bs-target="#frmModal" id="btnAgregar" data-llamar="artclasificacion/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Clasificaci&oacute;n</th>
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
                                <td><?= $r['nIdArtClasificacion'] ?></td>
                                <td><?= $r['sClasificacion'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal"
                                        data-bs-target="#frmModal" data-llamar="artclasificacion/e/<?= $r['nIdArtClasificacion'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal"
                                        data-bs-target="#frmModal" data-llamar="artclasificacion/b/<?= $r['nIdArtClasificacion'] ?>" style="cursor:pointer;"></i>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $artclaspager->links('artclasificacion','catalogos') ?>
    </div>

</div>

<div class="modal fade" id="frmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const appArtClasificacionartclasificacion = {

        init: function () {
            document.getElementById('frmModal').addEventListener('show.bs.modal', appArtClasificacionartclasificacion.agregar);
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
        appArtClasificacionartclasificacion.init();
    });
</script>
