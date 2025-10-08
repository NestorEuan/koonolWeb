<?php
$nCont = 0;
?>
<div class="container bg-light mt-4 border rounded" style="max-width:800px;">
    <h4>Tipos de Listas de Precios</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="tipolista/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-md">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Descripcion</th>
                        <th class="text-center">Abreviatura</th>
                        <th class="text-center">Precio<br>Tapado</th>
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
                                <th scope="row"><?= $r['nIdTipoLista'] ?></th>
                                <td><?= $r['cNombreTipo'] ?></td>
                                <td class="text-center"><?= $r['cTipo'] ?></td>
                                <td class="text-center"><input class="form-check-input" type="checkbox" <?= $r['bImprimirNota'] == '1' ? 'checked' : '' ?> disabled style="opacity:1;"></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="tipolista/e/<?= $r['nIdTipoLista'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="tipolista/b/<?= $r['nIdTipoLista'] ?>" style="cursor:pointer;"></i>
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
        const appTipoLista = {

            init: function() {
                document.getElementById('frmModal').addEventListener('show.bs.modal', appTipoLista.agregar);
            },

            agregar: function(e) {
                $.ajax({
                    url: baseURL + '/' + $(e.relatedTarget).data('llamar'),
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

        appTipoLista.init();
    });
</script>