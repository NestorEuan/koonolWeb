<div class="container bg-light mt-4 border">
    <h4>Proveedores</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div class="row">
                <label for="nombre" class="col-3 col-md-2 col-form-label">Nombre</label>
                <div class="col-6 col-md-7"><input type="text" name="nombre" class="form-control" id="nombre"></div>
                <div class="col-3 col-md-3"><button class="btn btn-secondary bg-gradient">Filtrar</button></div>
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" 
                data-bs-target="#frmModal" id="btnAgregar" data-llamar="proveedor/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Representante</th>
                        <th>Direcci&oacute;n</th>
                        <th>Celular</th>
                        <th>R.F.C.</th>
                        <th>eMail</th>
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
                                <td><?= $r['nIdProveedor'] ?></td>
                                <td><?= $r['sNombre'] ?></td>
                                <td><?= $r['sRepresentante'] ?></td>
                                <td><?= $r['sDireccion'] ?></td>
                                <td><?= $r['sCelular'] ?></td>
                                <td><?= $r['sRFC'] ?></td>
                                <td><?= $r['email'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal"
                                        data-bs-target="#frmModal" data-llamar="proveedor/e/<?= $r['nIdProveedor'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal"
                                        data-bs-target="#frmModal" data-llamar="proveedor/b/<?= $r['nIdProveedor'] ?>" style="cursor:pointer;"></i>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $provpager->links('proveedor','catalogos') ?>
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
    const appProveedor = {

        init: function () {
            document.getElementById('frmModal').addEventListener('show.bs.modal', appProveedor.agregar);
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
        appProveedor.init();
    });
</script>