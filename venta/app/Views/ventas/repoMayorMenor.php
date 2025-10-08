<?php
$nCont = 0;
$select = set_value('nOrden', '1');
?>

<div class="container bg-light mt-4 border">
    <h4>Reporte de Ventas de Productos de Mayor a Menor</h4>
    <form action="<?= base_url('remisiones/repMayorMenor') ?>" class="row border rounded mb-3 py-2" id="frmFiltro">
        <div class="col-sm-10 mb-sm-2 col-md-8 col-lg-6 mb-2">
            <div class="input-group">
                <span class="input-group-text">Rango de Fechas</span>
                <input type="date" name="dFecIni" id="dFecIni" class="form-control" value="<?= set_value('dFecIni', $fecIni) ?>">
                <input type="date" name="dFecFin" id="dFecFin" class="form-control" value="<?= set_value('dFecFin', $fecFin) ?>">
            </div>
        </div>
        <div class="col-sm-8 col-lg-3 mb-2">
            <div class="input-group">
                <span class="input-group-text">Ordenado por</span>
                <select name="nOrden" id="nOrden" class="form-select">
                    <option value="1" <?= $select == '1' ? 'selected' : '' ?>>Vendido</option>
                    <option value="2" <?= $select == '2' ? 'selected' : '' ?>>Facturado</option>
                </select>
            </div>
        </div>
        <div class="col-sm-4 col-lg-3 mb-2">
            <div class="input-group">
                <button type="submit" class="btn btn-outline-primary" style="z-index:auto;">Filtrar</button>
                <button type="button" class="btn btn-outline-primary" style="z-index:auto;" id="btnExportar">Exportar</button>
            </div>
        </div>
    </form>

    <div class="row border rounded">
        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th class="w-75">Descripción</th>
                        <th class="text-center">Cantidad Vendida<br>en el Período</th>
                        <th class="text-center">Cantidad Facturada<br>en el Período</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="9" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdArticulo'] ?></td>
                                <td><?= $r['sDescripcion'] ?></td>
                                <td class="text-end pe-4"><?= number_format($r['porCant'], 2) ?></td>
                                <td class="text-end pe-4"><?= number_format($r['cantFac'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const appRemisiones = {
            init: function() {
                $('#btnExportar').on('click', appRemisiones.exportar);
            },

            exportar: function() {
                window.open('generaProdMayorMenor?' + $('#frmFiltro').serialize());
            },

            onInput: function(e) {
                miGlobal.valNumero(e, appRemisiones, {
                    re: /^\d*$/g
                })
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
        appRemisiones.init();
    });
</script>