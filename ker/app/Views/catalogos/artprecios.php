<div class="container bg-light mt-4 border">
    <h4>Precios Artículos</h4>
    <div class="row border rounded mb-3 py-2">
        <form action="" class="col-sm-7" method="get" id="frmFiltro">
            <div class="input-group">
                <span class="input-group-text">Contiene en Descripción</span>
                <input type="text" name="sDescri" id="sDescri" class="form-control" value="<?= esc($_GET['sDescri'] ?? '') ?>">
                <button type="button" class="btn btn-primary" style="z-index:auto;">Filtrar</button>
            </div>
        </form>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" id="btnExpo" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="artprecios/descargaPrecios">Exportar Plantilla</button>
            <button class="btn btn-primary bg-gradient" id="btnImpo" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="artprecios/cargaPrecios">Importar Precios</button>
        </div>
    </div>
    <div class="row border rounded">
        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="3" class="border border-dark text-center pe-3">Listas Precios ---></th>
                        <?php $nContFila = 0; ?>
                        <?php foreach ($regTipos as $v) : ?>
                            <th colspan="3" class="text-center border border-dark">
                                <?= esc($v['cNombreTipo']) ?>
                            </th>
                            <?php $nContFila++; ?>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th class="border border-dark">Id</th>
                        <th class="border border-dark">Descripción Artículo</th>
                        <th class="text-center border border-dark">Precios Aplicados<br>a Partir de</th>
                        <?php for ($i = 0; $i < $nContFila; $i++) : ?>
                            <th class="border-start border-dark text-end">Remisión</th>
                            <th class="text-end">Factura</th>
                            <th class="border-end border-dark text-end">Tapado</th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="5" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <?php if($r['faPartir'] == null) continue; ?> 
                            <tr>
                                <td><?= $r['nIdArticulo'] ?></td>
                                <td><?= $r['sDescripcion'] ?></td>
                                <td class="text-center"><?= $r['faPartir'] ?? 'N/A' ?></td>
                                <?php for ($i = 0; $i < $numListas; $i++) : ?>
                                    <td class="text-end"><?= $r['L' . $regTipos[$i]['nIdTipoLista']] ?></td>
                                    <td class="text-end"><?= $r['F' . $regTipos[$i]['nIdTipoLista']] ?></td>
                                    <td class="text-end"><?= $r['T' . $regTipos[$i]['nIdTipoLista']] ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
    </div>

</div>

<?php generaModalGeneral(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        const precios = {
            init: function() {
                $('#frmModal').on('show.bs.modal', precios.modal);
                $('#frmFiltro button').on('click', precios.aplicaFiltro);
            },

            modal: function(e) {
                let d = $(e.relatedTarget).data('llamar');
                $.get(baseURL + '/' + d, {}, null, 'html')
                    .done(function(data, textStatus, jqxhr) {
                        $('#frmModal .modal-body').html(data);
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },

            aplicaFiltro: function(e) {
                let f = $('#frmFiltro')[0];
                f.action = baseURL + '/artprecios';
                f.submit();
            }
        };
        precios.init();
    });
</script>