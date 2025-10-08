<div class="container bg-light mt-4 border">
    <h4>Existencias</h4>
    <div class="row">
        <form class="col-11 col-md-11 border rounded py-2" action="<?= base_url('existencias') ?>" method="get">
            <div class="input-group">

                <span class="input-group-text">Sucursal</span>
                <select class="form-select" name="nIdSucursal" id="nIdSucursal" onchange="this.submit();">
                    <?php foreach ($regSucursales as $f) : ?>
                        <option value="<?= $f['nIdSucursal'] ?>" <?= $nIdSucursalActual == $f['nIdSucursal'] ? 'selected' : '' ?>><?= $f['sDescripcion'] ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-group-text">Contiene en Descripción</span>
                <input type="text" name="sDescri" id="sDescri" class="form-control" value="<?= set_value('sDescri', '') ?>">
                <input type="submit" value="Filtrar" class="btn btn-primary" style="z-index:auto;">
                <button type="button" class="btn btn-primary bg-gradient ms-2" style="z-index:auto;" id="btnExportar">Exportar</button>
                <button type="button" class="btn btn-secondary bg-gradient ms-3" style="z-index:auto;" id="btnComprometidos">Exporta Comprometidos</button>
            </div>
        </form>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th class='col-sm-6'>Descripción</th>
                        <th class='col-sm-2'>C&oacute;digo</th>
                        <th class='col-sm-1 text-center'>Existencia</th>
                        <th class='col-sm-1 text-center'>Comprometido</th>
                        <th class='col-sm-1 text-center'>Sobre comprometido</th>
                        <th class='col-sm-1 text-center'>Disponibles</th>
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
                                <td><?= $r['nIdArticulo'] ?></td>
                                <td><?= $r['nomArt'] ?></td>
                                <td><?= $r['codArt'] ?></td>
                                <td><?= round(floatval($r['fExistencia']), 2) ?></td>
                                <td><?= round(floatval($r['fComprometido']), 2) ?></td>
                                <td><?= round(floatval($r['fSobreComprometido']), 2) ?></td>
                                <td><?= round(floatval($r['fExistencia']) - floatval($r['fComprometido']), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
    </div>
</div>
<script>
    $(document).ready(function() {
        let appLstInv = {
            init: function() {
                $('#nIdSucursal').on('change', appLstInv.envio);
                $('#flchkcomprometido').on('change', appLstInv.envio);
                $('#btnExportar').on('click', appLstInv.exportar);
                $('#btnComprometidos').on('click', appLstInv.exportarc);
            },
            exportar: function() {
                window.open(baseURL + '/articulo/exportaXLSinventario?' + $('form').serialize());
            },
            exportarc: function() {
                window.open(baseURL + '/articulo/exportaXLSinventario?flchkcomprometido=on&' + $('form').serialize());
            }
        };
        appLstInv.init();
    });
</script>