<?php
$permiteCerrarOpcion3 = (round($impSaldoAFavor, 2) == 0 || $esPublicoGral == '1') ? '0' : '1';
?>
<div class="container bg-light mt-4 border">
    <h4>Pasar Producto No Asignado a Saldo a Favor</h4>
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
    <div class="row border rounded mb-2">
        <div style="width:100%;overflow-y:auto;">
            <div class="table-responsive-lg">
                <table class="table table-striped table-sm" id="tblDatosRemi">
                    <thead>
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Descripción</th>
                            <th class="text-end" scope="col">Precio Uni.</th>
                            <th class="text-end" scope="col">Cantidad</th>
                            <th class="text-center" scope="col">Por<br>Surtir</th>
                            <th class="text-end" scope="col">Cantidad Para<br>Saldo a Favor</th>
                            <th class="text-end" scope="col">Importe</th>
                        </tr>
                    </thead>
                    <tbody id="bodyTablaCancel">
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="8" class="fs-5 text-center">No hay registros</td>
                            </tr>
                        <?php else : ?>
                            <?php $cCampoAenfocar = null; ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php $cCampoAenfocar = $cCampoAenfocar === null ? '#' . 'inpEntM' . $r['id'] : $cCampoAenfocar; ?>
                                <tr>
                                    <td><?= $r['id'] ?></td>
                                    <td><?= $r['des'] ?></td>
                                    <td class="text-end"><?= number_format(floatval($r['precio']), 2) ?></td>
                                    <td class="text-end"><?= number_format(floatval($r['cant']), 2) ?></td>
                                    <td class="text-center"><?= intval($r['porsurtir']) ?></td>
                                    <td class="text-end">
                                        <input type="text" value="0" id="<?= 'inpEntM' . $r['id'] ?>" class="form-control text-end mx-auto py-0" style="width:80px;">
                                    </td>
                                    <td class="text-end"><?= number_format(floatval($r['imp']), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row border rounded mb-2 mt-4">
        <div class="col">
            <div class="px-2 border rounded border-2 border-primary text-center fw-bold fs-4">
                <span class="text-secondary">Saldo a Favor:</span> <span class="text-dark" id="totalSaldoFav"><?= number_format(0, 2) ?></span>
            </div>
        </div>
        <div class="col text-end" id="barraBotonesCancel">
            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cerrar</button>
            <button type="button" class="btn btn-primary ms-3" id="btnGuardar">Aplicar Saldo a Favor</button>
        </div>
    </div>

</div>
<script>
    $(document).ready(function() {
        let addCancel = {
            lista: <?= json_encode($registros) ?>,
            valorAnterior: '',
            importeRemision: parseFloat('<?= round(floatval($datos['nTotal']), 2) ?>'),
            importeTotal: 0,
            init: function() {
                $('#btnGuardar').on('click', addCancel.enviar);
                $('#bodyTablaCancel')
                    .on('focusout', 'input[id^="inpEntM"]', addCancel.onFocusoutProducto)
                    // .on('keydown', 'input[id^=inpEntM]', addCancel.onKeydownProducto)
                    .on('focus', 'input[id^="inpEntM"]', addCancel.onFocusInputsTabla)
                    .on('input', 'input', addCancel.onInputsTabla);
            },

            onFocusoutProducto: function(e) {
                let id = e.target.id.substr(7);
                if (e.target.value == '') {
                    addCancel.lista[id].capCant = 0;
                } else {
                    let n = parseFloat(e.target.value);
                    if (n > addCancel.lista[id].porsurtir) {
                        addCancel.lista[id].capCant = addCancel.lista[id].porsurtir;
                        e.target.value = addCancel.lista[id].porsurtir;
                    } else
                        addCancel.lista[id].capCant = n;
                }
                addCancel.calcularImporteTotal();
            },

            onFocusInputsTabla: function(e) {
                let t = e.target;
                addCancel.valorAnterior = t.value;
                miGlobal.muestraAlerta('frmModal');
            },

            onInputsTabla: function(e) {
                miGlobal.valNumero(e, addCancel, {
                    re: /^\d*(?:\.?\d{0,2})?$/g,
                    propValorAnterior: 'valorAnterior'
                });
            },

            calcularImporteTotal: function() {
                let n = 0;
                for (let i in addCancel.lista) {
                    n += addCancel.lista[i].capCant * addCancel.lista[i].precio;
                }
                $('#totalSaldoFav').text(Number(n.toFixed(2)).toLocaleString('es-MX', {
                    minimumFractionDigits: 2
                }));
                addCancel.importeTotal = Number(n.toFixed(2));
            },

            enProceso: false,
            enviar: function(e) {
                if (addCancel.enProceso) return;
                if (addCancel.importeRemision <= addCancel.importeTotal) {
                    miGlobal.muestraAlerta('El importe del saldo a favor no puede ser el importe de la remision.<br>' +
                        'Para cancelar la remision y pasarlo todo como saldo a favor,<br>' +
                        'se usa la opcion cancelar remision en consulta de remisiones.', 'frmModal');
                    return;
                }
                if (addCancel.importeTotal == 0) {
                    miGlobal.muestraAlerta('No se capturo ningun saldo a favor.', 'frmModal', 2700);
                    return;
                }

                addCancel.enProceso = true;
                $('#btnGuardar')[0].disabled = true;

                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: {
                        'lista': addCancel.lista,
                        'importesaldo': addCancel.importeTotal

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
            }
        };
        addCancel.init();
    });
</script>