<div class="container bg-light mt-4 border">
    <h4>Usuarios</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div class="row">
                <label for="slogin" class="col-3 col-md-2 col-form-label">Login</label>
                <div class="col-6 col-md-7"><input type="text" name="sLogin" class="form-control" id="sLogin"></div>
                <div class="col-3 col-md-3"><button class="btn btn-secondary bg-gradient">Filtrar</button></div>
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="usuario/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Login</th>
                        <th>Nombre</th>
                        <th>Perfil</th>
                        <th>Sucursal</th>
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
                            <?php if (!$esSuper && $r['nIdPerfil'] == '-1') continue; ?>
                            <tr>
                                <td><?= $r['nIdUsuario'] ?></td>
                                <td><?= $r['sLogin'] ?></td>
                                <td><?= $r['sNombre'] ?></td>
                                <td><?= $r['nomPerfil'] ?></td>
                                <td><?= $r['nomSucursal'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="usuario/e/<?= $r['nIdUsuario'] ?>" style="cursor:pointer;"></i>
                                    <?php if ($r['cEdoBaja'] == '1') : ?>
                                        <i class="bi bi-key-fill text-secondary me-3 "></i>
                                    <?php else : ?>
                                        <i class="bi bi-key-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="usuario/r/<?= $r['nIdUsuario'] ?>" style="cursor:pointer;"></i>
                                    <?php endif; ?>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="usuario/b/<?= $r['nIdUsuario'] ?>" style="cursor:pointer;"></i>
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

<div class="modal fade" id="mdConfirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Resetear contraseña</h3>
                <hr>
                <p>Confirma reseteo de contraseña?</p>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelReset">No</button>
                    <button type="button" class="btn btn-primary" id="btnResetPass">Si</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= generaModalGeneral('frmModal', 'modal-dialog-scrollable') ?>

<script type="text/javascript">
    const appUsuario = {

        llamar: '',
        init: function() {
            document.getElementById('frmModal').addEventListener('show.bs.modal', appUsuario.agregar);
            $('#mdConfirma').on('shown.bs.modal', (e) => {
                appUsuario.llamar = baseURL + '/' + $(e.relatedTarget).data('llamar');
                $('#btnResetPass').on('click', appUsuario.resetpass);
            });
        },
        resetpass: function(e) {
            $.ajax({
                url: appUsuario.llamar,
                method: 'POST',
                data: {},
                dataType: 'html'
            }).done(function(data, textStatus, jqxhr) {
                if (data.substr(0, 2) === 'oK') {
                    location.reload();
                } else {
                    $('#frmModal .modal-body').html(data);
                }
            }).fail(function(jqxhr, textStatus, err) {
                console.log('fail', jqxhr, textStatus, err);
            });
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
    $(document).ready(function() {
        appUsuario.init();
    });
</script>