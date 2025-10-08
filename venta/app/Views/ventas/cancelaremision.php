<?php
$esCancelable = $tieneComprometido == '0';
$permiteCerrarOpcion2 = (round($impPagadoAlChofer, 2) > 1) && (round($impPagadoAlChofer, 2) < round(floatval($datos['nTotal']), 2)) ? '1' : '0';
$permiteCerrarOpcion3 = (round($impSaldoAFavor, 2) == 0 || $esPublicoGral == '1') ? '0' : '1';
?>
<div class="container bg-light mt-4 border">
    <h4>Cancelar/Cerrar Remision (<?= $esCancelable ? '<span class="text-danger opacity-75">CANCELAR</span>' : '<span class="text-primary opacity-75">CERRAR</span>' ?>)</h4>
    <div class="row border rounded mb-1">
        <div class="col text-center">
            <strong>Cliente: </strong><?= $datos['sNombre'] ?>
        </div>
    </div>
    <div class="row border rounded mb-1">
        <div class="col-12 d-flex ">
            <div class="px-2">
                <strong>Folio Remision:</strong>
                <span class="fs-5 text-primary"><?= $datos['nFolioRemision'] ?></span>
            </div>
            <div class="px-2">
                <strong>Importe Remision:</strong>
                <span class="fs-5 text-primary"><?= number_format(floatval($datos['nTotal']), 2) ?></span>
            </div>
            <div class="px-2">
                <strong>Factura Timbrada:</strong>
                <span class="fs-5 text-primary"><?= $facturaTimbrada == '0' ? 'NO' : 'SI' ?></span>
            </div>

        </div>
    </div>
    <?php if (!$esCancelable) : ?>
        <div class="row border rounded mb-1">
            <div class="col">
                <select id="selOpcion" class="form-select form-select-sm">
                    <option value="-1" selected>Selecciona una opcion</option>
                    <option value="2">Cliente devuelve producto, solo pagó lo que recibió</option>
                    <option value="3">Cliente paga completa la remision, pero quiere otro producto en lugar del devuelto</option>
                </select>
            </div>
        </div>
    <?php else : ?>
        <div class="row border rounded mb-1">
            <div class="col">
                <select id="selOpcion" class="form-select form-select-sm">
                    <option value="-1" selected>Selecciona una opcion</option>
                    <option value="1">Se cancela la remision.</option>
                    <option value="3" >Cliente paga completa la remision, pero quiere otro producto</option>
                    <?php if ($cancelaFacturaEspecial) : ?>
                        <option value="4">Se cancela la remision con factura especial.</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <div class="row border rounded mb-2">
        <div style="height:200px;max-height:200px;width:100%;overflow-y:auto;">
            <div class="table-responsive-lg">
                <table class="table table-striped table-sm" id="tblDatosRemi">
                    <thead>
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Descripción</th>
                            <th class="text-end" scope="col">Precio Uni.</th>
                            <th class="text-end" scope="col">Cantidad</th>
                            <th class="text-center" scope="col">Por<br>Surtir</th>
                            <th class="text-end" scope="col">Importe</th>
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
                                    <td><?= $r['id'] ?></td>
                                    <td><?= $r['des'] ?></td>
                                    <td class="text-end"><?= number_format(floatval($r['precio']), 2) ?></td>
                                    <td class="text-end"><?= number_format(floatval($r['cant']), 2) ?></td>
                                    <td class="text-center"><?= intval($r['porsurtir']) ?></td>
                                    <td class="text-end"><?= number_format(floatval($r['imp']), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row border rounded mb-2">
        <?php if ($esCancelable) : ?>
            <div class="col opcion4 d-none">
                <div class="row">
                    <div class="col-3">
                        <label for="dtFecha" class="form-label">Fecha cancelacion</label>
                        <input type="text" class="form-control text-center" id="dtFecha" value="<?= (new DateTime())->format('d-m-Y') ?>" disabled readnoly>
                    </div>
                    <div class="col">
                        <label for="sMotivo" class="form-label">Motivo Cancelacion</label>
                        <textarea class="form-control" id="sMotivo" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <div class="col opcion3 d-none">
                <div class="fw-bold fs-4 h-100 d-flex align-items-center">
                    <div class="px-2 border rounded border-2 border-primary text-center">
                        <span class="text-secondary">Saldo a Favor:</span> <span class="text-dark"><?= number_format($impSaldoAFavor, 2) ?></span>
                    </div>
                    <div class="mensajes px-2 fs-5">
                        <?php if ($permiteCerrarOpcion3 == '0') : ?>
                            El saldo debe ser mayor de cero y no se puede asignar saldo a favor a publico general.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="col">
                <div class="fw-bold fs-4 h-100 d-flex align-items-center opcion2 d-none">
                    <div class="px-2 border rounded border-2 border-primary text-center">
                        <span class="text-secondary">Importe pagado:</span> <span class="text-dark"><?= number_format($impPagadoAlChofer, 2) ?></span>
                    </div>
                    <div class="mensajes px-2 fs-5">
                        <?php if ($permiteCerrarOpcion2 == '0') : ?>
                            El importe pagado debe ser menor que el importe de la remision.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fw-bold fs-4 h-100 d-flex align-items-center opcion3 d-none">
                    <div class="px-2 border rounded border-2 border-primary text-center">
                        <span class="text-secondary">Saldo a Favor:</span> <span class="text-dark"><?= number_format($impSaldoAFavor, 2) ?></span>
                    </div>
                    <div class="mensajes px-2 fs-5">
                        <?php if ($permiteCerrarOpcion3 == '0') : ?>
                            El saldo debe ser mayor de cero y no se puede asignar saldo a favor a publico general.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col py-2" id="barraBotonesCancel">
            <div class="h-100 d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-primary d-none" id="btnGuardar">Cerrar Remision</button>
                <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Salir</button>
            </div>
        </div>
    </div>

</div>
<script>
    $(document).ready(function() {
        let addCancel = {
            bOpc2: '<?= $permiteCerrarOpcion2 ?>',
            bOpc3: '<?= $permiteCerrarOpcion3 ?>',
            bCancelable: '<?= $esCancelable ? '1' : '0' ?>',
            init: function() {
                $('#btnGuardar').on('click', addCancel.enviar);
                $('#selOpcion').on('change', addCancel.onChange);
                $('#selOpcion').focus();
            },

            pintaBotonGuardar: function(op) {
                let b = $('#btnGuardar');
                if (addCancel.bCancelable == '1') {
                    if (op == 3) {
                        b.removeClass('btn-danger btn-warning');
                        b.addClass('btn-primary')
                        b.text('Cerrar Remision');
                    }
                    if (op == 1) {
                        b.removeClass('btn-primary btn-warning');
                        b.addClass('btn-danger')
                        b.text('Cancelar Remision');
                    }
                    if (op == 4) {
                        b.removeClass('btn-primary btn-danger');
                        b.addClass('btn-warning')
                        b.text('Cancelar Remision');
                    }
                }
                let bb = $('#barraBotonesCancel');
                let bb2 = $('#barraBotonesCancel>div').eq(0);
                if (op <= 0) {
                    bb.removeClass('col-3');
                    bb.addClass('col');
                    bb2.removeClass('justify-content-between');
                    bb2.addClass('justify-content-end');
                } else {
                    bb.removeClass('col');
                    bb.addClass('col-3');
                    bb2.removeClass('justify-content-end');
                    bb2.addClass('justify-content-between');
                }
            },

            onChange: function(e) {
                let op = e.target.selectedIndex;
                let val = parseInt(op > -1 ? e.target.value : '0');
                if (op == -1 || op == 0 || (val == 2 && addCancel.bOpc2 == '0') || (val == 3 && addCancel.bOpc3 == '0')) {
                    $('#btnGuardar').toggleClass('d-none', true);
                } else {
                    $('#btnGuardar').toggleClass('d-none', false);
                }
                switch (val) {
                    case 2:
                        $('div.opcion2').toggleClass('d-none', false);
                        $('div.opcion3').toggleClass('d-none', true);
                        $('div.opcion4').toggleClass('d-none', true);
                        break;
                    case 3:
                        $('div.opcion2').toggleClass('d-none', true);
                        $('div.opcion3').toggleClass('d-none', false);
                        $('div.opcion4').toggleClass('d-none', true);
                        break;
                    case 1:
                    case 4:
                        $('div.opcion2').toggleClass('d-none', true);
                        $('div.opcion3').toggleClass('d-none', true);
                        $('div.opcion4').toggleClass('d-none', false);
                        break;

                    default:
                        $('div.opcion2').toggleClass('d-none', true);
                        $('div.opcion3').toggleClass('d-none', true);
                        $('div.opcion4').toggleClass('d-none', true);
                        break;
                }
                addCancel.pintaBotonGuardar(val);

            },
            enProceso: false,
            enviar: function(e) {
                if (addCancel.enProceso) return;
                let c = $('#sMotivo').length == 0 ? '' : ($('#sMotivo').val()).trim();
                let valOpc;
                valOpc = $('#selOpcion').val();
                if (valOpc == '-1') {
                    miGlobal.muestraAlerta('Falta seleccionar la opcion correspondiente', 'frmModal', 1700);
                    return;
                }
                if (addCancel.bCancelable == '1' && c == '' && valOpc == '1') {
                    miGlobal.muestraAlerta('Escriba el motivo de la cancelacion', 'frmModal', 1700);
                    return;
                }
                // if (addCancel.bCancelable == '0') {
                //     valOpc = $('#selOpcion').val();
                // } else {
                //     valOpc = '1';
                // }
                addCancel.enProceso = true;
                $('#btnGuardar')[0].disabled = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: {
                        'opcion': valOpc,
                        'sMotivo': c
                    },
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.ok == '1') {
                        location.reload();
                    } else {
                        miGlobal.muestraAlerta(data.msj, 'frmModal', 1700);
                    }
                    addCancel.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;

                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    addCancel.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;
                });
            },
        };
        addCancel.init();
    });
</script>