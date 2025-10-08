<?php
$nCont = 0;
?>

<div class="container bg-light mt-4 border">
    <h4>Entradas y Salidas de Caja</h4>
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
                data-bs-target="#frmModal" id="btnAgregar" data-llamar="movtoCajas/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo Movto.</th>
                        <th>Motivo</th>
                        <th class="text-end pe-4">Importe</th>
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
                                <td><?= ++$nCont ?></td>
                                <td><?= $r['cTipMov'] ?></td>
                                <td><?= $r['sMotivo'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['nImporte']), 2), 2) ?></td>
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
    const appEscaja = {

        init: function () {
            document.getElementById('frmModal').addEventListener('show.bs.modal', appEscaja.agregar);
        },

        agregar: function(e){
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
    $(document).ready(function() {
        appEscaja.init();
    });
</script>