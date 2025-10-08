<?php
$nIdProveedor = 0;
$dlProveedor = '';
?>
<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h4>Entradas/Salidas Caja</h4>
                <hr>
            </div>
        </div>
        <form action="" method="post" class="row" id="addESCajafrm" onsubmit="return false;">
            <div class="col-6 mb-3">
                <select class="form-select" aria-label="Selecciona" id="cTipoMov" name="cTipoMov">
                    <option value="E" selected>Ingreso a Caja</option>
                    <option value="S">Egreso de Caja</option>
                    <option value="C">Abono de clientes</option>
                    <!-- <option value="P">Pago proveedores</option> -->
                </select>
            </div>
            <div class="col-2">

            </div>
            <div class="w-100"></div>
            <div class="col">
                <div class="collapse" id="collapseExample">
                    <div class="card card-body">
                        <?php generaCampoTexto('nIdCompra', $error ?? false, 'hidden', $registro ?? null); ?>
                        <?php generaCampoTexto('nIdVentas', $error ?? false, 'hidden', $registro ?? null); ?>
                        <?php generaCampoTexto('dtPago', $error ?? false, 'hidden', $registro ?? null); ?>
                        <?php generaCampoTexto('fPago', $error ?? false, 'hidden', $registro ?? null); ?>
                        <div class="input-group input-group-sm mb-1">
                            <div class="input-group-text">Origen</div>
                            <select class="form-select py-1" id="nIdSucursalEntrega" name="nIdSucursalEntrega">
                                <?php foreach ($regSucursales as $f) : ?>
                                    <option value="<?= $f['nIdSucursal'] ?>" <?= $nIdSucursalActual == $f['nIdSucursal'] ? 'selected' : '' ?>><?= $f['sDescripcion'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group input-group-sm mb-1">
                            <label for="nIdFolio" id="lblFolio" class="input-group-text">Folio de ____</label>
                            <?php generaCampoTexto('nIdFolio', $error ?? false, null, $registro ?? null, $modo, 'text-center mx-2 p-0'); ?>
                            <button class="btn btn-outline-secondary" style="cursor:pointer;" id="btnSearch">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <hr class="m-0 p-0">
                        <div class="container p-0">
                            <div id="infopago" class="row">
                                <div class="col"><span class="fw-bold">Nombre: </span><span id="sNombre"></span> </div>
                            </div>
                            <div class="row">
                                <div class="col"><span class="fw-bold">Importe: </span><span id="fImporte"></span></div>
                                <div class="col"><span class="fw-bold">Saldo: </span><span id="fSaldo"></span></div>
                            </div>
                        </div>
                        <hr class="m-0 p-0">
                        <div class="input-group input-group-sm mt-1">
                            <div class="input-group-text">Tipo Pago</div>
                            <?php generaCampoTexto('nIdTipoPago', $error ?? false, 'select', $registro ?? null, $modo ?? '', 'form-select-sm', '', $regTipoPago, 'sDescripcion'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-100"></div>
            <div class="col-12 mb-3">
                <label for="sMotivo" class="form-label">Motivo</label>
                <?php generaCampoTexto('sMotivo', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
            </div>
            <div class="col-5">
                <label for="nImporte" class="form-label">Importe</label>
                <?php generaCampoTexto('nImporte', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
            </div>
            <div class="col-7">
                <label for="sPersona" class="form-label" id="idEtiqMovto">Entrega</label>
                <?php generaCampoTexto('sPersona', $error ?? false, null, $registro ?? null, $modo); ?>
            </div>
        </form>
        <hr>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let appBuscar = {
            buscar: true,
            init: function() {
                $('#btnSearch')
                    .on('click', appBuscar.onClick)
            },

            onClick: function(e) {
                if (appBuscar.buscar === false) return;
                let idFolio = $("#nIdFolio").val();
                if (idFolio && idFolio > 0) {
                    let val = idFolio.trim();
                    let mov = $('#cTipoMov').val();
                    if (mov == 'C')
                        mov = 'cobrar';
                    else
                        mov = 'pagar';
                    $.get(baseURL + '/cuentas/leeMovimiento/' + mov + '/' + val, {
                        'nIdSucursal' : $('#nIdSucursalEntrega').val(),
                    }, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            if (data.res == 1) {
                                $("#sNombre").html(data.reg['sNombre']);
                                $("#fImporte").html(data.reg['nTotalVenta']);
                                $("#fSaldo").html(data.reg['fSaldo']);
                                $("#fSaldo").data('saldo', data.reg['fSaldo']);
                                $("#btnGuardar").prop('disabled', false);
                                if (mov == 'cobrar')
                                    $("#nIdVentas").val(data.reg['nIdVentas']);
                                else
                                    $("#nIdCompra").val(data.reg['nIdCompra'])
                            } else {
                                miGlobal.muestraAlerta(data.msj ? data.msj : 'No encontrado', 'frmModal', 2700);
                                $("#sNombre, #fImporte, #fSaldo").html('');
                                $("#btnGuardar").prop('disabled', true);
                            }
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    //$('#nIdProveedor').val('');
                }
            },
        };

        let addEscaja = {
            cantAnt: '',

            init: function() {
                $('#btnGuardar').on('click', addEscaja.enviar);
                $('#nImporte').on('input', addEscaja.onInput);
                $('#cTipoMov').on('input', addEscaja.onChange);
            },
            onChange: function(e) {
                let m = '';
                let n = '';
                $("#collapseExample").hide();
                switch (e.target.selectedIndex) {
                    case 0:
                        m = 'Entrega';
                        $("#btnGuardar").prop("disabled", false);
                        break;
                    case 1:
                        m = 'Recibe';
                        $("#btnGuardar").prop("disabled", false);
                        break;
                    case 2:
                        m = 'Entrega';
                        n = "Folio de Remisión";
                        $("#collapseExample").show();
                        $("#btnGuardar").prop("disabled", true);
                        break;
                    case 3:
                        m = 'Entrega';
                        n = "Folio de Compra";
                        $("#collapseExample").show();
                        $("#btnGuardar").prop("disabled", true);
                        break;
                }
                /*
                if (e.target.selectedIndex == 0)
                    m = 'Entrega';
                else
                    m = 'Recibe';
                */
                $('#lblFolio').html(n);
                $('#idEtiqMovto').html(m);
            },
            onInput: function(e) {
                miGlobal.valNumero(e, addEscaja);
            },
            bndGuardado: false,
            enviar: function(e) {
                if (addEscaja.bndGuardado) return;
                // validamos el tipo de pago
                if ($('#cTipoMov').val() == 'C') {
                    if ($('#nIdTipoPago')[0].selectedIndex < 1) {
                        miGlobal.muestraAlerta('Falta seleccionar el tipo de pago', 'frmModal', 1700);
                        return;
                    }
                    let saldo = parseFloat($("#fSaldo").data('saldo') ?? '0');
                    if(saldo == 'NaN') saldo = 0;
                    let importe = $('#nImporte').val() == '' ? 0 : parseFloat($('#nImporte').val());
                    if(importe > saldo) {
                        miGlobal.muestraAlerta('El importe del pago no puede ser mayor al saldo', 'frmModal', 2700);
                        return;
                    }
                }
                addEscaja.bndGuardado = true;
                $('#btnGuardar')[0].disabled = true;
                //$('#nIdCompra').val($('#nIdFolio').val());
                //$('#nIdVentas').val($('#nIdFolio').val());
                $('#fPago').val($('#fImporte').val());
                $('#dtPago').val(Date());
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addESCajafrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        <?php if ($esMobil) : ?>
                            window.open(baseURL + '/movtoCajas/imprimeRecibo', '_blank');
                            location.reload();
                        <?php else : ?>
                            location.href = baseURL + '/movtoCajas/imprimeRecibo';
                        <?php endif; ?>
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };

        addEscaja.init();
        appBuscar.init();

        $("cTipoMov").trigger('change');
    });
</script>