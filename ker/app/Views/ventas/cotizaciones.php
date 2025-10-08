<?php
$nCont = 0;
$aSel = [
    'Pendientes',
    'Usuario Actual',
    'Cliente',
    'Folio de Cotizacion'
];
$select = set_value('inputGroupSelectOpciones', '0');
?>

<div class="container bg-light mt-4 border">
    <h4>Cotizaciones Activas</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col">
            <form action="<?php base_url('cotizaciones') ?>" method="get" id="frmFiltro">
                <div class="row">
                    <div class="col-3">
                        <div class="input-group">
                            <label class="input-group-text" for="inputGroupSelectOpciones">Filtrar por</label>
                            <select class="form-select" id="inputGroupSelectOpciones" name="inputGroupSelectOpciones">
                                <?php foreach ($aSel as $k => $v) : ?>
                                    <option value="<?= $k ?>" <?= strval($k) == $select ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <input type="text" class="form-control <?= $select == '2' ? '' : 'd-none me-3' ?>" size="30" id="idCliente" name="idCliente" placeholder="Contiene en Nombre" aria-label="nombre del cliente" value="<?= set_value('idCliente', '') ?>" />
                            <input type="text" class="form-control <?= $select == '3' ? '' : 'd-none me-3' ?>" name="nFolioCotizacion" id="nFolioCotizacion" value="<?= set_value('nFolioCotizacion', '') ?>" placeholder="Folio de Cotizacion">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <button type="button" class="btn btn-primary me-3" style="z-index:auto;">Filtrar</button>
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
                        <th class="text-center">Folio Cotizacion</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Vigencia Limite</th>
                        <th>Cliente</th>
                        <th>Usuario Alta</th>
                        <th class="text-end pe-4">Importe<br>Cotizacion</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="7" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td class="text-center"><?= $r['nFolioCotizacion'] ?></td>
                                <td class="text-center"><?= $r['fecAlta'] ?></td>
                                <td class="text-center"><?= $r['fecVig'] ?></td>
                                <td><?= $r['sNombre'] ?></td>
                                <td><?= $r['nomUsu'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['nTotal']), 2), 2) ?></td>
                                <td class="text-center">
                                    <a class="bi bi-file-ruled-fill text-primary me-3 " href="ventas/aplicaCotizacion/<?= $r['nIdCotizaciones'] ?>/R" style="cursor:pointer;" title="Aplicar Para Venta"></a>
                                    <a class="bi bi-pencil-fill text-primary me-3 " href="ventas/aplicaCotizacion/<?= $r['nIdCotizaciones'] ?>/C" style="cursor:pointer;" title="Modificar cotizacion"></a>
                                    <i class="bi bi-filetype-xlsx text-primary me-3 " data-idcotexp="<?= $r['nIdCotizaciones'] ?>" style="cursor:pointer;" title="Exportar Cotizacion" data-bs-toggle="modal" data-bs-target="#frmModaldf" data-llamar="cotizaciones/selecExporta/<?= $r['nIdCotizaciones'] ?>"></i>
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
<?= generaModalGeneral('frmModaldf', 'modal-dialog-centered modal-dialog-scrollable') ?>

<script type="text/javascript">
    $(document).ready(function() {
        const appCotizaciones = {

            init: function() {
                $('#inputGroupSelectOpciones').on('change', appCotizaciones.aplicaSeleccion);
                $('#frmFiltro button').on('click', appCotizaciones.aplicaFiltro);
                // $('#bodyTabla').on('click', 'i[data-idcotexp]', appCotizaciones.exportaCotizacion);
                $('#frmModaldf').on('show.bs.modal', appCotizaciones.exportaCotizacion);
            },

            aplicaFiltro: function(e) {
                let f = $('#frmFiltro')[0];
                f.action = baseURL + '/cotizaciones';
                f.submit();
            },

            aplicaSeleccion: function(e) {
                $('#idCliente, #nFolioCotizacion').toggleClass('d-none', true);
                if (e.target.selectedIndex == 2) {
                    $('#idCliente').removeClass('d-none');
                } else if (e.target.selectedIndex == 3) {
                    $('#nFolioCotizacion').removeClass('d-none');
                }
            },

            exportaCotizacion: function(e) {
                $.ajax({
                    url: baseURL + '/' + $(e.relatedTarget).data('llamar'),
                    method: 'GET',
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModaldf .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
                // window.open(baseURL + '/cotizaciones/exportaCotizacion/' + $(e.target).data('idcotexp'));
            },

            onInput: function(e) {
                miGlobal.valNumero(e, appCotizaciones, {
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
        appCotizaciones.init();
    });
</script>