<?php
$nCont = 0;
$sCodigoModal = 'data-bs-toggle="modal" data-bs-target="#frmModal"';
?>
<div class="container bg-light mt-4 border">
    <h4>Turnos y Cortes de Caja</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div id="wAlert">
                <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                </div>
            </div>
            <div class="row">
                <form class="col position-relative" action="" method="GET" id="frmFilterCorte">
                    <div class="form-check float-end">
                        <input class="form-check-input" type="checkbox" id="flchk" name="flchk" <?= set_value('flchk', '') == 'on' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="flchk">
                            Mostrar solo activos
                        </label>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="cortecaja/a">Agregar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Num. Caja</th>
                        <th class="text-center">Fecha y Hora<br>Apertura</th>
                        <th class="text-center">Fecha y Hora<br>Cierre</th>
                        <?php if ($esUsuarioAdmin) : ?>
                            <th class="text-center">Sucursal</th>
                        <?php endif; ?>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="8" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdCorte'] ?></td>
                                <td><?= $r['nomUsu'] ?></td>
                                <td class="text-center"><?= $r['nNumCaja'] ?></td>
                                <td class="text-center"><?= $r['dtApertura'] ?></td>
                                <td class="text-center"><?= $r['dtCierre'] ?? '' ?></td>
                                <?php if ($esUsuarioAdmin) : ?>
                                    <td class="text-center"><?= $r['nomSuc'] ?></td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <?php $bHabilitar = ($r['dtCierre'] == null); ?>
                                    <i class="bi bi-pencil-fill me-3 text-primary" <?= $bHabilitar ? $sCodigoModal : '' ?> data-llamar="cortecaja/e/<?= $r['nIdCorte'] ?>" style="cursor:pointer;<?= $bHabilitar ? '' : 'visibility:hidden;' ?>" title="Cerrar Corte"></i>
                                    <a class="bi bi-printer-fill text-primary" href="cortecaja/imprime/<?= $r['nIdCorte'] ?>" style="cursor:pointer;" title="Imprimir Corte" target="_blank" ></a>
                                    <?php $bHabilitar = (intval($r['sumFacPendientes']) > 0); ?>
                                    <i class="bi bi-factura ms-3 text-primary" <?= $bHabilitar ? $sCodigoModal : '' ?> data-llamar="cortecaja/factura/<?= $r['nIdCorte'] ?>/c" style="cursor:pointer;<?= $bHabilitar ? '' : 'visibility:hidden;' ?>" title="Timbrar Facturas del Corte (Pendientes: <?= $r['sumFacPendientes'] ?>)"></i>
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

<?= generaModalGeneral('frmModal', 'modal-dialog-scrollable modal-lg') ?>

<script type="text/javascript">
    $(document).ready(function() {
        let appCorte = {
            aplicaAdd: '<?= $agregaActual ? '1' : '0' ?>',
            enProceso: false,
            init: function() {
                $('#frmModal').on('show.bs.modal', appCorte.agregar)
                // $('#frmModal')
                //     .on('hidden.bs.modal', () => {
                //         $('#frmModal .modal-dialog')[0].style.maxWidth = '500px';
                //     });
                $('#flchk').on('change', (e) => {
                    if (appCorte.enProceso) {
                        e.preventDefault();
                        return;
                    }
                    appCorte.enProceso = true;
                    $('#frmFilterCorte')[0].submit();
                });
            },

            agregar: function(e) {
                $('#frmModal .modal-body').html('');
                $.ajax({
                    url: baseURL + '/' + $(e.relatedTarget).data('llamar'),
                    method: 'GET',
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModal .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };
        appCorte.init();
        if (appCorte.aplicaAdd == '1') {
            $('#btnAgregar').click();
        }
    });
</script>