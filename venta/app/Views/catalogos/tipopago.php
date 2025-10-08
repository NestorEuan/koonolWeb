<?php
$nCont = 0;
?>
<div class="container bg-light mt-4 border rounded" style="max-width:800px;">
    <h4>Tipos de Pagos</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div class="row">
                <label for="nombre" class="col-3 col-md-2 col-form-label">Nombre</label>
                <div class="col-6 col-md-7"><input type="text" name="nombre" class="form-control" id="nombre"></div>
                <div class="col-3 col-md-3"><button class="btn btn-secondary bg-gradient">Filtrar</button></div>
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="TipoPago/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-md">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Leyenda a Mostrar</th>
                        <th>Descripcion</th>
                        <th class="text-end pe-4">% Comision<br>(si aplica)</th>
                        <th>Cod. SAT</th>
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
                                <th scope="row"><?= ++$nCont ?></th>
                                <td><?= $r['sLeyenda'] ?></td>
                                <td><?= $r['sDescripcion'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['nComision']), 2), 2) ?></td>
                                <td class="text-center"><?= $r['sTipoSAT'] ?></td>
                                <td>
                                    <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="TipoPago/e/<?= $r['nIdTipoPago'] ?>" style="cursor:pointer;"></i>
                                    <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="TipoPago/b/<?= $r['nIdTipoPago'] ?>" style="cursor:pointer;"></i>
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

<div class="modal fade" id="frmModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let appTipoPago = {

            init: function() {
                document.getElementById('frmModal').addEventListener('show.bs.modal', appTipoPago.agregar);
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
        appTipoPago.init();
    });
</script>