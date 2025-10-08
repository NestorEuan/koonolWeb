<div class="container bg-light mt-4 border">
    <h4>Existencias</h4>
    <div class="row">
        <form class="col-12 col-md-10 border rounded py-2" action="<?= base_url('existencias') ?>" method="get" id="frmInventario">
            <div class="input-group">

                <span class="input-group-text">Sucursal</span>
                <select class="form-select" name="nIdSucursal" id="nIdSucursal" onchange="this.submit();">
                    <?php foreach ($regSucursales as $f) : ?>
                        <option value="<?= $f['nIdSucursal'] ?>" <?= $nIdSucursalActual == $f['nIdSucursal'] ? 'selected' : '' ?>><?= $f['sDescripcion'] ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-group-text">Contiene en Descripción</span>
                <input type="text" name="sDescri" id="sDescri" class="form-control" value="<?= set_value('sDescri', '') ?>">
                <input type="submit" value="Filtrar"  class="btn btn-primary" style="z-index:auto;">
            </div>
        </form>
        <div class="col-12 col-md-2 border rounded py-2">
            <div class="input-group justify-content-end">
                <button class="btn btn-primary bg-gradient" style="z-index:auto;"  id="btnExportaExistencias">Exportar</button>
            </div>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 15%;">Codigo</th>
                        <th class='col-sm-6'>Descripción</th>
                        <th class='col-sm-1 text-center'>Existencia</th>
                        <th class='col-sm-1 text-center'>Existencia<br>Global</th>
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
                                <td><?= $r['sCodigo'] ?></td>
                                <td><?= $r['nomArt'] ?></td>
                                <td class="text-center"><?= round(floatval($r['fExistencia']), 2) ?></td>
                                <td class="text-center"><?= round(floatval($r['nGlobal']), 2) ?></td>
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
            init: function () {
                $('#nIdSucursal').on('change', appLstInv.envio);
                $('#btnExportaExistencias').on('click', appLstInv.exportaExistencias);
            },
            envio: function (e) {
                $('form')[0].submit();
            },
            exportaExistencias: function(e) {
                window.open(baseURL + '/existencias/exportarExistencias?' + $('#frmInventario').serialize());
            }
        };
        appLstInv.init();
    });
</script>