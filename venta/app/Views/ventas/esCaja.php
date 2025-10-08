<?php
$select = set_value('inputGroupSelectOpciones', '0');
?>
<div class="container bg-light mt-4 border">
    <h4>Entradas y Salidas de Caja</h4>
    <div class="position-relative">
        <div id="escajawAlert">
            <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
            </div>
        </div>
    </div>
    <form class="row border rounded mb-3 py-2" action="<?php base_url('movtoCajas') ?>" method="get" id="frmFiltro">
        <div class="col-5">
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
                    <?php foreach ($cOpciones as $v) : ?>
                        <option value="<?= $v[1] ?>" <?= $v[1] == $select ? 'selected' : '' ?>><?= $v[0] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-3 d-flex justify-content-between">
            <button type="submit" class="btn btn-secondary bg-gradient" >Filtrar</button>
            <button type="button" class="btn btn-primary bg-gradient" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="movtoCajas/a">Agregar</button>
            <button type="button" class="btn btn-primary bg-gradient" id="btnExportar">Exportar</button>
        </div>
    </form>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th class="text-center">Fecha</th>
                        <th>Tipo Movto.</th>
                        <th>Motivo</th>
                        <th class="text-end pe-4">Importe</th>
                        <th></th>
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
                                <td><?= $r['nIdEScaja'] ?></td>
                                <td class="text-center"><?= (new DateTime($r['dtAlta']))->format('d-m-Y') ?></td>
                                <td><?= $r['cTipMov'] ?></td>
                                <td><?= $r['sMotivo'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['nImporte']), 2), 2) ?></td>
                                <td>
                                    <a class="bi bi-printer-fill text-primary me-2" title="Reimprimir Recibo" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="movtoCajas/reimprimir/<?= $r['nIdEScaja'] ?>"></a>
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
<?= generaModalGeneral('frmModal2', 'modal-dialog-scrollable') ?>

<script type="text/javascript">
    $(document).ready(function() {
        const appEscaja = {

            init: function() {
                $('#frmModal').on('show.bs.modal', appEscaja.agregar);
                $('#frmFiltro').on('submit', appEscaja.onSubmit);
                $('#btnExportar').on('click', appEscaja.exportar);
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
            },

            onSubmit: function(e) {
                if(!appEscaja.validaRango()) e.preventDefault();
            },

            exportar: function() {
                if(!appEscaja.validaRango()) return;
                window.open(baseURL + '/movtoCajas/exportaXLS?' + $('#frmFiltro').serialize());
            },

            validaRango: function() {
                let s = 0;
                if ($('#dFecIni').val() != '') s++;
                if ($('#dFecFin').val() != '') s++;
                if (s < 2) {
                    miGlobal.muestraAlerta('Falta el rango de fechas', 'escaja', 1700);
                    $('#dFecIni').focus();
                    return false;
                }
                return true;
            },
        };
        appEscaja.init();
    });
</script>