<?php
$nCont = 0;
$aSel = [
    'Todos',
    'Pendientes para surtir',
    'Usuario Actual',
    'Cliente',
    'Folio de Remision',
    'Pendientes para surtir, Usuario Actual'
];
$select = set_value('inputGroupSelectOpciones', '0');
?>

<div class="container bg-light mt-4 border">
    <h4>Remisiones</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col">
            <form action="<?php base_url('remisiones') ?>" method="get" id="frmFiltro">
                <div class="row">
                    <div class="col-4">
                        <div class="input-group">
                            <span class="input-group-text">Rango de Fechas</span>
                            <input type="date" name="dFecIni" id="dFecIni" class="form-control" value="<?= set_value('dFecIni', $fecIni) ?>">
                            <input type="date" name="dFecFin" id="dFecFin" class="form-control" value="<?= set_value('dFecFin', $fecFin) ?>">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <label class="input-group-text" for="inputGroupSelectOpciones">Filtrar por</label>
                            <select class="form-select" id="inputGroupSelectOpciones" name="inputGroupSelectOpciones">
                                <?php foreach ($aSel as $k => $v) : ?>
                                    <option value="<?= $k ?>" <?= strval($k) == $select ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="input-group">
                            <input type="text" class="form-control <?= $select == '3' ? '' : 'd-none' ?>" size="30" id="idCliente" name="idCliente" placeholder="Contiene en Nombre" aria-label="codigo/nombre del cliente" value="<?= set_value('idCliente', '') ?>" />
                            <input type="text" class="form-control <?= $select == '4' ? '' : 'd-none' ?>" name="nFolioRemision" id="nFolioRemision" value="<?= set_value('nFolioRemision', '') ?>" placeholder="Folio de Remision">
                            <button type="button" class="btn btn-primary" style="z-index:auto;">Filtrar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row border rounded">
        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">Folio Remision</th>
                        <th class="text-center">Folio Factura</th>
                        <th class="text-center">Fecha</th>
                        <th>Cliente</th>
                        <th class="text-end pe-4">Importe<br>Remision</th>
                        <th class="text-end pe-4">Importe<br>Factura</th>
                        <th class="text-center">Estado<br>Remision</th>
                        <th class="text-center">Entregas<br>Pendientes</th>
                        <th class="text-center">Envios<br>Pendientes</th>
                        <th class="text-center" style="width:160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="9" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php $nSum = 0; ?>
                        <?php foreach ($registros as $r) : ?>
                            <?php $nSum += floatval($r['nTotal']); ?>
                            <tr>
                                <td><span class="text-primary" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="ventas/rep2/resMovtos/<?= $r['nIdVentas'] ?>" style="cursor:pointer;" title="Ver detalles..."><?= $r['nFolioRemision'] ?></span> </td>
                                <td><?= $r['nFolioFactura'] ?></td>
                                <td><?= $r['fecha'] ?></td>
                                <td><?= $r['sNombre'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['nTotal']), 2), 2) ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['nTotalFactura']), 2), 2) ?></td>
                                <td class="text-center"><?= $r['cEstatus'] ?></td>
                                <td class="text-center"><?= $r['cEdoEntrega'] == '0' ? '' : $r['cEdoEntrega'] ?></td>
                                <td class="text-center"><?= $r['cEdoEnvio'] == '0' ? '' : $r['cEdoEnvio'] ?></td>
                                <td class="text-center">
                                    <?php if (false) : ?>
                                        <?php if ($r['cClasificacionVenta'] == '1' && round(floatval($r['sumEntrega']), 2) > 0 && $r['cEdo'] != '5' && $r['cEdo'] != '7') : ?>
                                            <i class="bi bi-entrega-mano-fill text-primary me-2 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="remisiones/entregaparcial/<?= $r['nIdVentas'] ?>" style="cursor:pointer;" title="Entregar/Enviar"></i>
                                        <?php else : ?>
                                            <i class="bi bi-entrega-mano-fill text-secondary me-2 "></i>
                                        <?php endif; ?>
                                        <?php if (intval($r['conFactSinTimbrar']) > 0 && ($r['cEdo'] != '5')) : ?>
                                            <i class="bi bi-factura text-primary me-2 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="cortecaja/factura/<?= $r['nIdVentas'] ?>/v" style="cursor:pointer;" title="Facturar"></i>
                                            <i class="bi bi-file-pluma-solid text-primary me-2 " data-bs-toggle="modal" data-bs-target="#frmModaldf" data-llamar="remisiones/cambiausocfdi/<?= $r['nIdVentas'] ?>" style="cursor:pointer;" title="Modificar uso de cfdi/fecha"></i>
                                        <?php else : ?>
                                            <i class="bi bi-factura text-secondary me-2 "></i>
                                            <i class="bi bi-file-pluma-solid text-secondary me-2 "></i>
                                        <?php endif; ?>
                                    <?php endif ?>
                                    <a class="bi bi-printer-fill text-primary me-2" href="ventas/imprimeRemision/<?= $r['nIdVentas'] ?>/1/back" style="cursor:pointer;" title="Reimprimir Remision"></a>
                                    <a class="bi bi-cart-x text-primary me-2" href="devolucion/<?= $r['nIdVentas'] ?>" style="cursor:pointer;" title="Devolución de mercancía"></a>
                                    <?php if ($bndCancela) : ?>
                                        <?php if ($r['cEdo'] == '5' || $r['cEdo'] == '7') : ?>
                                            <i class="bi bi-x-circle-fill text-secondary me-2 " title="Cancelar Remision"></i>
                                        <?php else : ?>
                                            <i class="bi bi-x-circle-fill text-primary me-2 " data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="remisiones/cancelaRemision/<?= $r['nIdVentas'] ?>" style="cursor:pointer;" title="Cancelar Remision"></i>
                                        <?php endif; ?>
                                    <?php endif; ?>
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

<?= generaModalGeneral('frmModal', 'modal-xl modal-dialog-scrollable') ?>
<?= generaModalGeneral('frmModaldf', 'modal-dialog-scrollable') ?>

<script type="text/javascript">
    $(document).ready(function() {
        const appRemisiones = {
            aEdoRem: ['Todos', 'Activo', 'Facturado', 'Cancelado', 'No Pagado', 'Pagado'],
            aEdoEnt: ['Todos', 'Por Entregar', 'Entregado', 'Entrega Parcial'],
            aEdoEnv: ['Por Enviar', 'Asignado', 'Enviado', 'Finalizado'],

            init: function() {
                $('#frmModal').on('show.bs.modal', appRemisiones.agregar);
                $('#frmModaldf').on('show.bs.modal', appRemisiones.agregar);
                // $('#frmModal').on('shown.bs.modal', () => {
                //     $(miGlobal.entregaCampo).select();
                // });
                $('#inputGroupSelectOpciones').on('change', appRemisiones.aplicaSeleccion);
                $('#frmFiltro button').on('click', appRemisiones.aplicaFiltro);
            },

            aplicaFiltro: function(e) {
                let f = $('#frmFiltro')[0];
                f.action = baseURL + '/remisiones';
                f.submit();
            },

            aplicaSeleccion: function(e) {
                $('#idCliente, #nFolioRemision').toggleClass('d-none', true);
                if (e.target.selectedIndex == 3) {
                    $('#idCliente').removeClass('d-none');
                } else if (e.target.selectedIndex == 4) {
                    $('#nFolioRemision').removeClass('d-none');
                }
            },

            onInput: function(e) {
                miGlobal.valNumero(e, appRemisiones, {
                    re: /^\d*$/g
                })
            },

            agregar: function(e) {
                let sTarget = $(e.relatedTarget).data('bs-target') + ' .modal-body';
                $.ajax({
                    url: baseURL + '/' + $(e.relatedTarget).data('llamar'),
                    method: 'GET',
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $(sTarget).html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };
        appRemisiones.init();
    });
</script>