<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded row">
        <div class="row">
            <div class="col">
                <h4><?= $titulo ?></h4>
                <hr>
            </div>
        </div>
        <div id="wAlert">
            <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
            </div>
        </div>

        <form action="" method="post" id="frmBuscaCliente" autocomplete="off">
            <div class="input-group mb-1">
                <input type="hidden" name="nIdClienteBusca" id="nIdClienteBusca" value="<?= esc(set_value('nIdCliente')) ?>">
                <input type="text" class="form-control form-control-sm w-100" id="dlClientes0" name="dlClientes0" list="dlClientes" placeholder="Escriba nombre o el codigo del cliente" aria-label="codigo/nombre del cliente" value="<?= esc(set_value('sCliNom')) ?>" />
                <datalist id="dlClientes">
                    <?php
                    if (set_value('nIdCliente') != '') {
                        echo '<option value="' . esc(set_value('sCliNom')) .
                            '" data-id="' . esc(set_value('nIdCliente')) . '">';
                    }
                    ?>
                </datalist>
            </div>
        </form>

        <div class="w-100" id="sNombreCliente"></div>
        <form action="<?= $frmURL ?>" method="post" id="addDepAdefrm" onsubmit="return false;">
            <input type="hidden" name="nIdCliente" id="nIdCliente" value="<?= esc(set_value('nIdCliente')) ?>">
            <input type="hidden" name="sCliNom" id="sCliNom" value="<?= esc(set_value('sCliNom')) ?>">
            <div class="row mb-1">
                <div class="col-5">
                    <label for="nImporte" class="form-label">Importe</label>
                    <?php generaCampoTexto('nImporte', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
                </div>
                <div class="col-7 align-self-center text-center">
                    <?php generaCampoTexto('chkFactura', $error ?? false, 'checkbox', $registro ?? null, $modo); ?>
                    <label for="chkFactura" class="form-check-label">Facturar Depósito</label>
                </div>
            </div>

            <div class="row mb-1" id="ctrlUsoCfdi">
                <div class="col-6">
                    <label for="cIdUsoCfdi" class="form-label">Uso CFDI</label>
                    <?php generaCampoTexto('cIdUsoCfdi', $error ?? false, 'select', $registro ?? null, $modo, '', '', $regUsoCfdi, 'sDescripcion'); ?>
                </div>
                <div class="col-6">
                    <label for="nIdTipoPago" class="form-label">Tipo Pago</label>
                    <?php generaCampoTexto('nIdTipoPago', $error ?? false, 'select', $registro ?? null, $modo, '', '', $regTipoPago, 'sLeyenda'); ?>
                </div>
            </div>

            <div class="col-12 mb-1">
                <label for="sObservaciones" class="form-label">Observaciones</label>
                <?php generaCampoTexto('sObservaciones', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
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

        let autoCli = {
            cantAnt: '',
            buscar: true,
            init: function() {
                $('#dlClientes0')
                    .on('focus', () => {
                        autoCli.buscar = true;
                    })
                    .on('input', autoCli.onInput)
                    .on('keydown', autoCli.onKeydown);
            },

            onInput: function(e) {
                if (autoCli.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/cliente/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let a = '';
                            for (const x of data.registro) {
                                a += '<option value="' + x.sNombre +
                                    '" data-id="' + x.nIdCliente + '">';
                            }
                            $('#dlClientes').html(a);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdClienteBusca').val('');
                }
            },

            onKeydown: function(e) {
                function buscaCodCliente(id) {
                    $('#nIdCliente').val('');
                    $.post(baseURL + '/cliente/leeRegistro/' + id, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Cliente no encontrado');
                        } else {
                            let d = data.registro;
                            autoCli.buscar = false;
                            let js = '<option value="' + d.sNombre +
                                '" data-id="' + d.nIdCliente + '">';
                            $('#dlClientes').html(js);
                            $('#nIdClienteBusca').val(d.nIdCliente);
                            $('#nIdCliente').val(d.nIdCliente);
                            $('#dlClientes0').val(d.sNombre);
                            $('#sCliNom').val(d.sNombre);
                            $('#nImporte').focus();
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };
                if ((e.which == 13 || e.which == 9) && e.target.value) {
                    let valor = e.target.value.trim();
                    e.preventDefault();
                    if (/^\d+$/.test(valor) === true) {
                        // solo numeros
                        buscaCodCliente(valor);
                    } else {
                        $('#nIdCliente').val('');
                        let b = false;
                        $('#dlClientes option').each((i, el) => {
                            if (el.value == e.target.value) {
                                autoCli.buscar = false;
                                $('#nIdClienteBusca').val($(el).data('id'));
                                $('#nIdCliente').val($(el).data('id'));
                                $('#sCliNom').val($(el).val());
                                b = true;
                                return false;
                            }
                        });
                        if (b) {
                            $('#nImporte').focus();
                        } else {
                            miGlobal.muestraAlerta('Cliente no Seleccionado', null, 1600);
                        }
                    }
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                    }
                }
            },

        };

        let addDepAde = {
            cantAnt: '',

            init: function() {
                $('#btnGuardar').on('click', addDepAde.enviar);
                $('#nImporte').on('input', addDepAde.onInput);
                $('#chkFactura').on('change', addDepAde.onChange);
                if ($('#chkFactura')[0].checked == false) {
                    $('#ctrlUsoCfdi').hide();
                }
            },

            onInput: function(e) {
                miGlobal.valNumero(e, addDepAde);
            },
            bndGuardado: false,
            enviar: function(e) {
                if (addDepAde.bndGuardado) return;
                if ($('#nIdCliente').val() == '') {
                    miGlobal.muestraAlerta('Cliente no Seleccionado', null, 1600);
                    return;
                }
                addDepAde.bndGuardado = true;
                $('#btnGuardar')[0].disabled = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addDepAdefrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.href = baseURL + '/movtoCajas/imprimeRecibo' ;
                        // location.reload();
                    } else {
                        $('#frmModal .modal-body').html(data);
                        let cErr = '<?= isset($errorGral) ? $errorGral : '' ?>';
                        if (cErr != '') miGlobal.muestraAlerta(cErr, null, 1700);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },

            onChange: function(e) {
                if (e.target.checked) {
                    $('#ctrlUsoCfdi').slideDown();
                } else {
                    $('#ctrlUsoCfdi').slideUp();
                }
            }
        };
        addDepAde.init();
        autoCli.init();
    });
</script>