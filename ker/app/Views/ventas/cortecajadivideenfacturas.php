<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="row border rounded" style="width:100%;">
        <div class="col">
            <div class="row">
                <div class="col">
                    <h5 class="text-center">Facturar remision <?= $venta['nFolioRemision'] ?> en Varias Facturas</h5>
                    <span class="me-2">Cliente:</span><span class="fw-bold"><?= $venta['sNombre'] ?></span>
                    <hr class="my-0">
                </div>
            </div>
            <div class="row">
                <div class="col-6 border-end">
                    <h6 class="text-center fw-bolder">Remision</h6>
                    <div class="row border-bottom">
                        <div class="col-3 px-0 mb-1">
                            <button type="button" class="btn-sm btn-primary" id="btnCreaFactura">Crea Factura</button>
                        </div>
                        <div class="col-3 pe-0">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">ID</span>
                                <input type="text" class="form-control" id="txtIdArt" name="txtIdArt">
                            </div>
                        </div>
                        <div class="col-4 pe-0">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Cant</span>
                                <input type="text" class="form-control" id="txtCantArt" name="txtCantArt">
                            </div>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnCreaFilaFactura">
                                <i class="bi bi-plus-square-fill"></i>
                            </button>
                        </div>
                    </div>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Precio</th>
                                <th scope="col">Producto</th>
                                <th scope="col">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sumaTotal = 0; ?>
                            <?php foreach ($regRemision as $r) : ?>
                                <tr>
                                    <td class="text-center"><?= $r['id'] ?></td>
                                    <td class="text-center"><?= round($r['cant'], 3) ?></td>
                                    <td class="text-end"><?= number_format(round($r['precio'], 2), 2) ?></td>
                                    <td><?= $r['des'] ?></td>
                                    <td class="text-end"><?= number_format(round($r['cant'] * $r['precio'], 2), 2) ?></td>
                                </tr>
                                <?php $sumaTotal += round($r['cant'] * $r['precio'], 2); ?>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="fw-bold text-end" colspan="5">
                                    Total: <?= number_format($sumaTotal, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-6">
                    <h6 class="text-center fw-bolder">Facturas</h6>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $nFila = 1; ?>
                            <?php foreach ($regFacturas as $r) : ?>
                                <tr>
                                    <td class="fw-bold">
                                        <span>Factura <?= $nFila ?></span>
                                        <i class="bi bi-x-circle-fill ms-3 text-primary btnborrafactura" data-llamar="cortecaja/delFactura/<?= $nFila - 1 ?>" style="cursor:pointer;" title="Borra la Factura"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Producto</th>
                                                    <th>Cant</th>
                                                    <th>Importe</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $sumaTotal = 0; ?>
                                                <?php foreach ($r as $rArticulo) : ?>
                                                    <tr>
                                                        <td class="text-center"><?= $rArticulo['id'] ?></td>
                                                        <td><?= $rArticulo['des'] ?></td>
                                                        <td class="text-center"><?= round($rArticulo['cant'], 3) ?></td>
                                                        <td class="text-end"><?= number_format(round($rArticulo['cant'] * $rArticulo['precio'], 2), 2) ?></td>
                                                    </tr>
                                                    <?php $sumaTotal += round($rArticulo['cant'] * $rArticulo['precio'], 2); ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="fw-bold text-end" colspan="4">Importe Factura: <?= number_format($sumaTotal, 2) ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </td>
                                </tr>
                                <?php $nFila++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr class="mt-1">
            <div class="d-flex justify-content-end mb-2">
                <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar">Guardar Facturas</button>
            </div>
        </div>
    </div>

</div>
<form action="" method="POST" id="frmCrtFact">
    <input type="hidden" name="id">
    <input type="hidden" name="cant">
</form>

<script type="text/javascript">
    $(document).ready(function() {

        let appCorteCajaDiv = {
            mensajeError: '<?= $msjError ?>',
            intervalo: null,
            origen: '<?= $origen ?>',
            init: function() {
                if(appCorteCajaDiv.origen == 'C') $('#frmModal .modal-dialog')[0].style.maxWidth = '900px';
                $('#btnCreaFactura').on('click', appCorteCajaDiv.creaFactura);
                $('#btnCreaFilaFactura').on('click', appCorteCajaDiv.creaFilaFactura);
                $('#txtIdArt').on('input', appCorteCajaDiv.onInputId)
                    .on('keydown', appCorteCajaDiv.onKeydownId);
                $('#txtCantArt').on('input', appCorteCajaDiv.onInputCant)
                    .on('keydown', appCorteCajaDiv.onKeydownCant);
                $('#frmModal .btnborrafactura').on('click', appCorteCajaDiv.borraFactura);
                $('#btnGuardar').on('click', appCorteCajaDiv.guardarFacturas);
                if (appCorteCajaDiv.mensajeError != '') miGlobal.muestraAlerta(appCorteCajaDiv.mensajeError, 'frmModal', 5000);
                $('#txtIdArt').select();
            },
            //  /^\d*(?:\.\d{0,3})?$/g
            onInputId: function(e) {
                miGlobal.valNumero(e, appCorteCajaDiv, {
                    re: /^\d*$/g
                });
                miGlobal.muestraAlerta('frmModal');
            },
            onInputCant: function(e) {
                miGlobal.valNumero(e, appCorteCajaDiv, {
                    re: /^\d*(?:\.\d{0,3})?$/g
                });
                miGlobal.muestraAlerta('frmModal');
            },

            onKeydownId: function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    $('#txtCantArt').select();
                }
            },
            onKeydownCant: function(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    console.log($('#btnCreaFilaFactura'));
                    $('#btnCreaFilaFactura').focus();
                }
            },

            creaFactura: function() {
                $.get(baseURL + '/cortecaja/addFactura/' + appCorteCajaDiv.origen, null, 'html')
                    .done((data, textStatus, jqxhr) => {
                        $('#frmModal .modal-body').html(data);
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },

            borraFactura: function(e) {
                $.get(baseURL + '/' + $(e.target).data('llamar') + '/' + appCorteCajaDiv.origen, null, 'html')
                    .done((data, textStatus, jqxhr) => {
                        $('#frmModal .modal-body').html(data);
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },

            creaFilaFactura: function() {
                if ($('#txtIdArt').val() == '' || $('#txtCantArt').val() == '') {
                    return;
                }
                $.get(baseURL + '/cortecaja/addFilaFactura/' +
                        $('#txtIdArt').val() + '/' + $('#txtCantArt').val() + '/' + appCorteCajaDiv.origen, null, 'html')
                    .done((data, textStatus, jqxhr) => {
                        $('#frmModal .modal-body').html(data);
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },
            procesa: false,
            guardarFacturas: function() {
                if (appCorteCajaDiv.procesa) return;
                appCorteCajaDiv.procesa = true;
                $('#btnGuardar')[0].disabled = true;
                $.post(baseURL + '/cortecaja/divideFacturas', null, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        if (data.ok == '1') {
                            location.reload();
                        } else {
                            miGlobal.muestraAlerta(data.msj, 'frmModal', 4000);
                        }
                        appCorteCajaDiv.procesa = false;
                        $('#btnGuardar')[0].disabled = false;
                    })
                    .fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                        appCorteCajaDiv.procesa = false;
                        $('#btnGuardar')[0].disabled = false;
                    });
            }
        };
        appCorteCajaDiv.init();

    });
</script>