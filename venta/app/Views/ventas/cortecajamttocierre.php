<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h4 class="text-center">Cierre de Corte de Caja</h4>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col text-center">
                <div>
                    Usuario:<span class="fw-bold pe-4"><?= $nombreUsu ?></span>
                    Caja:<span class="fw-bold"><?= $numCaja ?></span>
                </div>
                <div>
                    Fecha Apertura: <span class="fw-bold"><?= $dtApertura ?></span>
                </div>
            </div>
        </div>
        <hr class="">
        <h6 class="">Arqueo Efectivo</h6>
        <hr>
        <div class="row">
            <div class="col-6 px-5">
                <table class="table caption-top">
                    <caption>Billetes</caption>
                    <thead>
                        <tr>
                            <th>Valor</th>
                            <th>Cantidad</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody id="tbl_arqueocaja">
                        <tr>
                            <td>1000</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="b-1000" name="b-1000">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>500</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="b-500" name="b-500">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>200</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="b-200" name="b-200">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>100</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="b-100" name="b-100">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>50</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="b-50" name="b-50">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>20</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="b-20" name="b-20">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-6 border-start px-5">
                <table class="table caption-top">
                    <caption>Monedas</caption>
                    <thead>
                        <tr>
                            <th>Valor</th>
                            <th>Cantidad</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody id="tbl_arqueocaja2">
                        <tr>
                            <td>20</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="m-20" name="m-20">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="m-10" name="m-10">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="m-5" name="m-5">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="m-2" name="m-2">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="m-1" name="m-1">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>.50</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="m-050" name="m-050">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td>.10</td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-end" id="m-010" name="m-010">
                            </td>
                            <td class="text-end">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-4">
                <label for="nSaldoFin" class="form-label">Saldo de efectivo en Caja</label>
                <?php generaCampoTexto('nSaldoFin', $error ?? false, null, $registro ?? null, $modo, 'text-end', 'readonly'); ?>
            </div>
            <div class="col"></div>
        </div>
        <div class="row">
            <div class="col-12">
                <label for="sObservaciones" class="form-label">Observaciones</label>
                <?php generaCampoTexto('sObservaciones', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let appCorteCaja = {
            cantAnt: '',
            montoTotal: 0,

            init: function() {
                $('#btnGuardar').on('click', appCorteCaja.enviar);
                $('#tbl_arqueocaja, #tbl_arqueocaja2').on('focusout', appCorteCaja.calcula)
                $('#tbl_arqueocaja, #tbl_arqueocaja2').on('input', appCorteCaja.onInput)
                $('#frmModal>div.modal-dialog').addClass('modal-lg');
                $('#b-1000').select();
            },

            bndGuardado: false,
            enviar: function(e) {
                if (appCorteCaja.bndGuardado) return;
                appCorteCaja.bndGuardado = true;
                $('#btnGuardar')[0].disabled = true;

                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: {
                        'nSaldoFin': $('#nSaldoFin').val().replace(',', ''),
                        'sObservaciones': $('#sObservaciones').val(),
                        'dat': appCorteCaja.prepara()
                    },
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    appCorteCaja.bndGuardado = false;
                    $('#btnGuardar')[0].disabled = false;
                });
            },

            calcula: function(e) {
                let nTotal = 0;
                $('#tbl_arqueocaja, #tbl_arqueocaja2').find('input').each(function(i, e) {
                    let cValor = $(this).val() ?? '';
                    if (cValor == '.') cValor = '';
                    let nValor = parseFloat(cValor ? $(this).val() : '0');
                    let nval = 0;
                    let c = e.id.substring(0, 3)
                    if (c == 'm-0')
                        nval = parseFloat('0.' + e.id.substring(3)) * nValor;
                    else
                        nval = parseFloat(e.id.substring(2)) * nValor;
                    $(this).parent().next().text(nval.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'));
                    nTotal += nval;
                });
                appCorteCaja.montoTotal = nTotal;
                $('#nSaldoFin').val(nTotal.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'));
            },

            prepara: function() {
                let a = [];
                $('#tbl_arqueocaja, #tbl_arqueocaja2').find('input').each(function(i, e) {
                    let cValor = $(this).val();
                    if (cValor == '' || cValor == '.') return;

                    let nValor = parseFloat(cValor);
                    if (nValor == 0) return;

                    let nval = 0;
                    let c = e.id.substring(0, 3)
                    if (c == 'm-0')
                        nval = parseFloat('0.' + e.id.substring(3));
                    else
                        nval = parseFloat(e.id.substring(2));
                    a.push([c, nval, nValor]);
                });
                return a;
            },

            onInput: function(e) {
                miGlobal.valNumero(e, appCorteCaja, {
                    re: /^\d*(?:\.\d{0,2})?$/g
                })
            }
        };
        appCorteCaja.init();
    });
</script>