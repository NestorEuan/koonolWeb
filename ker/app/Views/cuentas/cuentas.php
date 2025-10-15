<?php
$aKeys = [
    'pagar'  => ['nIdCompra', 'dcompra', 'F.Compra', 'fSaldo', 'dSolicitud', 'Proveedor'],
    'cobrar' => ['nIdVentas', 'dtAlta', 'F.Venta', 'fSaldo', 'dtAlta', 'Cliente'],
];
$sKey = $aKeys[$operacion][0];
$sMov = $aKeys[$operacion][1];
$fMov = $aKeys[$operacion][2];
$nTotal = $aKeys[$operacion][3];
$fSolicitud = $aKeys[$operacion][4];
$clienteproveedor = $aKeys[$operacion][5];
$bOcultaRecepcion = $operacion === "cobrar";
// $cEdo = '0';
if (isset($aWhere)) {
    $cEdo = $aWhere['Edo'] ?? '0';
    $dIni = $aWhere['dIni'] ?? '';
    $dFin = $aWhere['dFin'] ?? '';
}
?>
<div class="container bg-light mt-4 position-relative">
    <div id="cuentaswAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h5><?= $titulo ?></h5>
    <form class="row border rounded mb-3 py-1" action="<?= base_url('cuentas/' . $operacion) ?>" method="get" id="formFiltro" autocomplete="off">
        <div class="col-12 mb-1 col-md-4 col-lg-2">
            <div class="input-group">
                <span class="input-group-text">Estado</span>
                <select class="form-select" name="cEstado" id="cEstado">
                    <option value="0" <?= $cEdo == '0' ? 'selected' : '' ?>>Con saldo</option>
                    <option value="1" <?= $cEdo == '1' ? 'selected' : '' ?>>Pagados</option>
                    <option value="2" <?= $cEdo == '2' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
        </div>
        <div class="col-12 mb-1 col-md-8 col-lg-4">
            <div class="input-group">
                <span class="input-group-text w-25"><?= $fMov ?></span>
                <input type="date" name="dIni" id="dIni" class="form-control text-center" value="<?= $dIni ?>" <?= $cEdo == '0' ? 'disabled' : '' ?>>
                <input type="date" name="dFin" id="dFin" class="form-control text-center" value="<?= $dFin ?>" <?= $cEdo == '0' ? 'disabled' : '' ?>>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="input-group ">
                <span class="input-group-text"><?= $clienteproveedor ?></span>
                <input type="text" class="form-control" id="sCliente" name="sCliente" list="dlClientes" placeholder="Escriba nombre o id" aria-label="id/nombre del cliente" value="<?= set_value('sCliente', '') ?>" />
                <input type="hidden" name="idCliente" id="idCliente" value="<?= set_value('idCliente', '') ?>">
                <datalist id="dlClientes"></datalist>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg d-flex flex-column flex-md-row">
            <button type="submit" class="btn btn-secondary bg-gradient mb-1 me-md-auto">Filtrar</button>
            <?php if ($operacion == 'cobrar') : ?>
                <div class="form-check-inline mt-2">
                    <input type="checkbox" name="chkSoloCliente" id="chkSoloCliente" class="form-check-input">
                    <label class="form-check-label" for="chkSoloCliente">Para el cliente</label>
                </div>
            <?php endif; ?>
            <button type="button" class="btn btn-primary bg-gradient mb-1" id="btnExportar">Exportar</button>
        </div>
    </form>

    <div class="row border rounded">

        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th><?= $operacion === 'pagar' ? '#' : 'Folio Remision' ?></th>
                    <th><?= $fMov ?></th>
                    <th>Sucursal</th>
                    <th><?= $operacion === 'pagar' ? 'Proveedor' : 'Cliente' ?></th>
                    <th>Saldo</th>
                    <?= $operacion === 'pagar' ? '<th>F.Solicitud</th>' : '' ?>
                    <th>Estado</th>
                    <th>
                        <!-- Acción -->
                    </th>
                </tr>
            </thead>
            <tbody id="bodyTabla">
                <?php if (empty($registros)) : ?>
                    <tr>
                        <td colspan="5" class="fs-5 text-center">No hay registros</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($registros as $r) : ?>
                        <?php
                        $nKey = $r[$sKey];
                        $dMov = $r[$sMov] === null ? '' : date("Y-m-d", strtotime($r[$sMov]));
                        $dSolicita = $r[$fSolicitud] === null ? '' : date("Y-m-d", strtotime($r[$fSolicitud]));
                        ?>
                        <tr>
                            <td><?= $operacion === 'pagar' ? $r[$sKey] : $r['nFolioRemision'] ?></td>
                            <td><?= $dMov ?></td>
                            <td><?= $r['nIdSucursal'] . ' ' . $r['sDescripcion'] ?></td>
                            <?php if ($operacion !== 'entrada') : ?>
                                <td><?= $r[$operacion == 'cobrar' ? 'nIdCliente' : 'nIdProveedor'] . ' ' . $r['sNombre'] ?></td>
                            <?php endif ?>
                            <td class="text-end pe-3"><?= number_format($r[$nTotal], 2); ?></td>
                            <?= $operacion === 'pagar' ? '<td>' . $dSolicita . '</td>' : '' ?>
                            <td>
                                <?php
                                switch ($r['cEdoEntrega']) {
                                    case '2':
                                        echo 'Surtido parcial';
                                        break;
                                    case '3':
                                        echo 'Surtido total';
                                        break;
                                    default:
                                        echo 'Pendiente';
                                }
                                ?></td>
                            <!--td>< ?= $r['nProductos']? ></td -->
                            <td>
                                <?php if (!$bOcultaRecepcion) : ?>
                                    <i class="bi bi-cash-coin <?= round(floatval($r[$nTotal]), 2) == 0 ? 'text-secondary' : 'text-primary' ?> me-3" <?= round(floatval($r[$nTotal]), 2) == 0 ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#frmModal" style="cursor:pointer;"' ?> data-llamar="cuentas/<?= $operacion ?>/p/<?php echo $nKey ?>"></i>
                                <?php endif; ?>
                                <i class="bi bi-eye-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#frmModal" style="cursor:pointer;" data-llamar="cuentas/<?= $operacion ?>/d/<?php echo $nKey ?>"></i>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $pager->links() ?>
</div>

<?= generaModalGeneral() ?>

<script type="text/javascript">
    $(document).ready(function() {
        const appCuentas = {
            init: function() {
                $("#dIni").on('change', appCuentas.valFin);
                $("#dFin").on('change', appCuentas.valIni);
                $('#frmModal').on('show.bs.modal', appCuentas.agregar);
                $('#btnExportar').on('click', appCuentas.exportar);
                $('#cEstado').on('change', appCuentas.onChange);
                $('#formFiltro').on('submit', appCuentas.onSubmit);
                $('#sCliente')
                    .on('input', appCuentas.onInput)
                    .on('keydown', appCuentas.onKeydown);
            },
            sClienteActual: '',
            idClienteActual: '',
            onInput: function(e) {
                let val = (e.target.value ?? '').trim();
                if (val != '') {
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/<?= $operacion == 'cobrar' ? 'cliente' : 'proveedor' ?>/buscaNombre/' + val, {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let a = '',
                                id = '';
                            for (const x of data.registro) {
                                a += '<option value="' + x.sNombre +
                                    '" data-id="' + x.nID + '" >';
                                if (id == '') {
                                    id = x.nID;
                                    appCuentas.idClienteActual = x.nID;
                                    appCuentas.sClienteActual = x.sNombre;
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
                    $.post(baseURL + '/<?= $operacion == 'cobrar' ? 'cliente' : 'proveedor' ?>/leeRegistro/' + id + '/1' + par, {}, null, 'json').
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
                            $('#sCliente').val(appCuentas.sClienteActual);
                            $('#formFiltro').submit();
                        }
                    }
                }
            },

            onChange: function(e) {
                if (e.target.value == '0') {
                    $('#dIni')[0].disabled = true;
                    $('#dFin')[0].disabled = true;
                } else {
                    $('#dIni')[0].disabled = false;
                    $('#dFin')[0].disabled = false;
                };
            },
            agregar: function(e) {
                let d = $(e.relatedTarget).data('llamar');
                $.ajax({
                    url: baseURL + '/' + d,
                    method: 'GET',
                    data: {},
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModal .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
            valFin: function(e) {
                $("#dFin").attr('min', e.target.value);
                $("#dFin").attr('value', e.target.value);
                //window.alert(e.target.value);
            },
            valIni: function(e) {
                $("#dIni").attr('max', e.target.value);
                if ($("#dIni").value == null || $("#dIni").value == '')
                    $("#dIni").attr('value', e.target.value);
            },
            exportar: function() {
                let t = $('#formFiltro')[0].target;
                let a = $('#formFiltro')[0].action;
                $('#formFiltro')[0].action = "<?= base_url('cuentas/exporta/' . $operacion) ?>";
                $('#formFiltro')[0].target = "_blank";
                $('#formFiltro')[0].submit();
                $('#formFiltro')[0].action = a;
                $('#formFiltro')[0].target = t;
            },
            onSubmit: function(e) {
                if ($('#cEstado').val() != '0' && $('#dIni').val().trim() == '') {
                    e.preventDefault();
                    miGlobal.muestraAlerta('Falta el rango de fechas', 'cuentas', 2000);
                    return;
                }
            }
        };

        appCuentas.init();
    });
</script>