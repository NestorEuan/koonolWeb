<div class="container bg-light mt-4 border">
    <h4>Depósitos Clientes</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <form class="row" action="<?= base_url('pagoAdel') ?>" method="get" id="formFiltro" autocomplete="off">

                <div class="input-group">
                    <span class="input-group-text">Nombre</span>
                    <input type="text" class="form-control" id="sCliente" name="sCliente" list="dlClientes" placeholder="Escriba nombre o id" aria-label="id/nombre del cliente" value="<?= set_value('sCliente', '') ?>" />
                    <input type="hidden" name="idCliente" id="idCliente" value="<?= set_value('idCliente', '') ?>">
                    <datalist id="dlClientes"></datalist>
                    <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Filtrar</button>
                    <button class="btn btn-outline-secondary" type="button" id="button-addon3">X</button>
                </div>
            </form>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="pagoAdel/a">Agregar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Cliente</th>
                        <th class="text-end pe-4">Importe</th>
                        <th>Fecha</th>
                        <th class="text-end pe-4">Saldo</th>
                        <th>Observaciones</th>
                        <th class="text-center">Acciones</th>
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
                                <td><?= $r['nIdPagosAdelantados'] ?></td>
                                <td><?= $r['nomCli'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['nImporte']), 2), 2) ?></td>
                                <td><?= $r['fecha'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['saldoPag']), 2), 2) ?></td>
                                <td><?= $r['sObservaciones'] ?></td>
                                <td class="text-center">
                                    <a class="bi bi-filetype-xlsx text-primary me-4" title="Reimprimir Recibo" style="cursor: pointer;" href="pagoAdel/generaDesglosePagoSaldo/<?= $r['nIdPagosAdelantados'] ?>" target="_blank"></a>
                                    <a class="bi bi-printer-fill text-primary" title="Reimprimir Recibo" style="cursor: pointer;" href="pagoAdel/reimprimir/<?= $r['nIdPagosAdelantados'] ?>" target="_blank"></a>
                                    <?php if ($puedeCancelar) : ?>
                                        <?php if ($r['cierreCorte'] == null) : ?>
                                            <i class="bi bi-x-circle-fill text-primary ms-4" style="cursor:pointer;" data-llamar="cancelapago/deposito/<?= $r['nIdPagosAdelantados'] ?>" title="Cancelar Deposito" data-bs-toggle="modal" data-bs-target="#frmModal"></i>
                                        <?php endif; ?>
                                    <?php endif; ?>
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

<?= generaModalGeneral('frmModal', 'modal-lg') ?>
<div class="modal fade" id="mdConfirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Confirmar</h3>
                <hr>
                <p>mensaje</p>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnNoConfirmar">No</button>
                    <button type="button" class="btn btn-primary" id="btnSiConfirmar">Si</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        const appPagAdel = {

            init: function() {
                $('#frmModal').on('show.bs.modal', appPagAdel.agregar);

                $('#sCliente')
                    .on('input', appPagAdel.onInput)
                    .on('keydown', appPagAdel.onKeydown);
                $('#button-addon3').on('click', appPagAdel.limpiarFiltro);
            },

            sClienteActual: '',
            idClienteActual: '',
            onInput: function(e) {
                let val = (e.target.value ?? '').trim();
                if (val != '') {
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/cliente/buscaNombre/' + val, {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let a = '',
                                id = '';
                            for (const x of data.registro) {
                                a += '<option value="' + x.sNombre +
                                    '" data-id="' + x.nID + '" >';
                                if (id == '') {
                                    id = x.nID;
                                    appPagAdel.idClienteActual = x.nID;
                                    appPagAdel.sClienteActual = x.sNombre;
                                }
                            }
                            if ($('#sCliente').val().trim() != '') {
                                // se usa esta condicion porque al hacer backspace repetidamente,
                                // se dispara onInput y se llama el $.get pero el resto de las 
                                // instrucciones se sigue ejecutando asi que puede pasar que ya no haya un valor en el campo sCliente
                                // y ya no se tenga que asignar el listado y el idCliente sea vacio.
                                $('#dlClientes').html(a);
                                $('#idCliente').val(id);
                            }
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#dlClientes').html('');
                    $('#idCliente').val('');
                    $('#sCliente').val('');
                }
            },
            onKeydown: function(e) {
                function asignaCliente(d) {
                    $('#dlClientes').html('<option ' +
                        'value="' + d.sNombre + '" ' +
                        'data-id="' + d.nID + '" ' +
                        '></option>'
                    );
                    $('#sCliente').val(d.sNombre);
                    $('#idCliente').val(d.nID);
                    $('#formFiltro').submit();
                };

                function buscaCliente(id, par) {
                    $.post(baseURL + '/cliente/leeRegistro/' + id + '/1' + par, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Cliente no encontrado', 'cuentas', 1700);
                            $('#idCliente').val('');
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
                    e.preventDefault();
                    let car = valor.substring(0, 1);
                    if (car == '*' || /^\d+$/.test(valor)) {
                        let par1 = valor;
                        let par2 = '';
                        buscaCliente(par1, par2);
                    } else {
                        if ($('#idCliente').val() != '') {
                            $('#sCliente').val(appPagAdel.sClienteActual);
                            $('#formFiltro').submit();
                        }
                    }
                }
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

            filtrar: function(e) {
                e.preventDefault();
                let f = $('#frmFiltro').serialize();
                $.ajax({
                    url: baseURL + '/pagoAdel/filtro',
                    method: 'POST',
                    data: f,
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModal .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },

            limpiarFiltro: function() {
                $('#sCliente').val('');
                $('#idCliente').val('');
                $('#formFiltro').submit();
            },
        };
        appPagAdel.init();
    });
</script>