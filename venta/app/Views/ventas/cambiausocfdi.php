<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded" id="mdlFrmUsoC">
        <div class="row">
            <div class="col">
                <h5>Modificar Datos Factura</h5>
                <h5 class="text-primary">Remision: <?= $folRemision ?></h5>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="cIdUsoCfdi" class="form-label">Uso CFDI</label>
                <?php generaCampoTexto('cIdUsoCfdi', $error ?? false, 'select', $registro ?? null, $modo, 'form-select-sm', '', $lstUso, 'sDescripcion'); ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="cPPD" class="form-label">Forma de Pago</label>
                <?php generaCampoTexto('cPPD', $error ?? false, 'select', $registro ?? null, $modo, 'form-select-sm', '', $lstFormaPago, 'sDescripcion'); ?>
            </div>
        </div>
        <?php if ($bCambioCliente == '1') : ?>
            <div class="row mb-3">
                <div class="col">
                    <label for="sClienteUso" class="form-label">Cliente</label>
                    <div class="input-group ">
                        <?php generaCampoTexto('sClienteUso', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm', 'list="dlClientes" placeholder="Escriba nombre, id"'); ?>
                        <input type="hidden" name="idClienteUso" id="idClienteUso" value="<?= set_value('idClienteUso', $registro['idClienteUso']) ?>">
                        <input type="hidden" name="cTipoCliente" id="cTipoCliente" value="<?= set_value('cTipoCliente', $registro['cTipoCliente']) ?>">
                        <datalist id="dlClientes"></datalist>
                    </div>
                </div>
            </div>
            <div class="row mb-3 d-none" id="esPublicoGral">
                <div class="col">
                    <label for="cNombrePublico" class="form-label">Otro Nombre para Publico General</label>
                    <?php generaCampoTexto('cNombrePublico', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm', 'placeholder="PUBLICO GENERAL"'); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="row mb-3">
            <div class="col">
                <label for="cIdTipoRelacion" class="form-label">Tipo Relacion</label>
                <?php generaCampoTexto('cIdTipoRelacion', $error ?? false, 'select', $registro ?? null, $modo, 'form-select-sm', '', $lstTipoRel, 'sDescripcion'); ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="cUUIDrelacionado" class="form-label">UUID's relacionados (poner en forma de listado para varios)</label>
                <?php generaCampoTexto('cUUIDrelacionado', $error ?? false, 'textarea', $registro ?? null, $modo, 'form-control-sm'); ?>
            </div>
        </div>
        <?php if ($bCambioFecha == '1') : ?>
            <div class="row mb-3">
                <div class="col">
                    <label for="dFechaFactura" class="form-label">Fecha de Emision de la factura</label>
                    <?php generaCampoTexto('dFechaFactura', $error ?? false, 'date', $registro ?? null, $modo, 'form-control-sm'); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($bTieneVariasFacturas == '0') : ?>
            <div class="row mb-3">
                <div class="col">
                    <input class="form-check-input" type="checkbox" value="1" id="bDividirEnVarias" name="bDividirEnVarias" <?= $registro['bDividirEnVarias'] == '1' ? 'checked' : '' ?>>
                    <label for="bDividirEnVarias" class="form-check-label">Dividir en varias facturas</label>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($soloEfectivoAnticipado) : ?>
            <div class="row mb-3">
                <div class="col">
                    <label for="nIdTipoPago" class="form-label">Cambia tipo de pago</label>
                    <?php generaCampoTexto('nIdTipoPago', $error ?? false, 'select', $registro ?? null, $modo, 'form-select-sm', '', $lstTipoPago, 'sDescripcion'); ?>
                </div>
            </div>

        <?php endif; ?>
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarUso">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let artDispo = {
            cTipoPublicoGral: '<?= $esPublicoGral ?>',
            init: function() {
                $('#btnGuardarUso').on('click', artDispo.enviar);
                $('#esPublicoGral').toggleClass('d-none', artDispo.cTipoPublicoGral == '0');
                $('#cNombrePublico').on('blur', (e) => {
                    let a = e.target.value;
                    e.target.value = a.toUpperCase()
                });
                $('#cIdTipoRelacion').on('change', artDispo.onChangeTipo);
                $('#cIdTipoRelacion').trigger('change');
                $('#cIdUsoCfdi').focus();
            },

            enviar: function(e) {
                $.ajax({
                    url: '<?= $url ?>',
                    method: 'POST',
                    data: $('#mdlFrmUsoC [name]').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModaldf .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },

            onChangeTipo:function(e){
                var v = $(e.currentTarget).val();
                if(!v || v === '' || v === '0'){
                    $('#cUUIDrelacionado')[0].readOnly = true;
                } else {
                    $('#cUUIDrelacionado')[0].readOnly = false;
                }

            }
        };

        let buscaCliente = {
            sClienteActual: '',
            idClienteActual: '',
            cTipoActual: '',
            init: function() {
                $('#sClienteUso')
                    .on('input', buscaCliente.onInput)
                    .on('keydown', buscaCliente.onKeydown)
                    .on('blur', buscaCliente.onBlur);
            },
            onInput: function(e) {
                let val = (e.target.value ?? '').trim();
                if (val != '') {
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/cliente/buscaNombre/' + val, {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let a = '',
                                id = '',
                                nom = '',
                                tipo = '';
                            for (const x of data.registro) {
                                a += '<option value="' + x.sNombre +
                                    '" data-id="' + x.nIdCliente +
                                    '" data-tipo="' + x.cTipoCliente + '" >';
                                if (id == '') {
                                    id = x.nIdCliente;
                                    buscaCliente.sClienteActual = x.sNombre;
                                    buscaCliente.idClienteActual = x.nIdCliente;
                                    buscaCliente.cTipoActual = x.cTipoCliente;
                                    console.log(buscaCliente.sClienteActual, buscaCliente.idClienteActual);
                                }
                            }
                            $('#dlClientes').html(a);
                            $('#idClienteUso').val(id);
                            $('#cTipoCliente').val(tipo);
                            console.log('val1: ', $('#idClienteUso')[0].value);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#idClienteUso').val('');
                    $('#cTipoCliente').val('');
                    console.log('val2: ', $('#idClienteUso')[0].value);
                }
            },
            onKeydown: function(e) {
                function asignaCliente(d) {
                    $('#dlClientes').html('<option ' +
                        'value="' + d.sNombre + '" ' +
                        'data-id="' + d.nIdCliente + '" ' +
                        'data-tipo="' + d.cTipoCliente + '" ' +
                        '></option>'
                    );
                    $('#sClienteUso').val($('#dlClientes option').val());
                    $('#idClienteUso').val(d.nIdCliente);
                    $('#cTipoCliente').val(d.cTipoCliente);

                };

                function buscaCodCliente(id, par) {
                    $.post(baseURL + '/cliente/leeRegistro/' + id + '/1' + par, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Cliente no encontrado', 'cardex', 1700);
                            $('#idClienteUso').val('');
                            $('#cTipoCliente').val('');
                            $(e.target)[0].select();
                        } else {
                            asignaCliente(data.registro);
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                let valor = (e.target.value ?? '').trim();
                if ((e.which == 13 || e.which == 9) && valor != '') {
                    if (/^\d+$/.test(valor)) {
                        let par1 = valor;
                        let par2 = '';
                        buscaCodCliente(par1, par2);
                    } else {
                        if ($('#idClienteUso').val() != '') {
                            $('#sClienteUso').val(buscaCliente.sClienteActual);
                            $('#cTipoCliente').val(buscaCliente.cTipoActual);
                        }
                        console.log('val3: ', $('#idClienteUso')[0].value);

                        if (e.wich == 13) e.target.blur();
                        console.log(e.target);
                    }
                }

            },
            onBlur: function(e) {
                let p = $('#cTipoCliente').val();
                artDispo.cTipoPublicoGral = (p == 'P' ? '1' : '0');
                $('#esPublicoGral').toggleClass('d-none', artDispo.cTipoPublicoGral == '0');
            }
        };
        artDispo.init();
        buscaCliente.init();
    });
</script>