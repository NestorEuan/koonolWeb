<?php // parte php 
// en forma de
$nCont = 0;
$cCampoAenfocar = null;  // primer elemento a tomer el enfoque

$nContFila = 0;

?>

<div class="container" id="cntEntrega">
    <h4 class="border-bottom ">Entrega de Producto<span class="ms-4 fs-4 fw-bold text-primary">Folio Remisión: <?= intval($cliente['folRemision']) ?></span></h4>
    <div class="row mb-2 px-1">
        <div class="col">
            <div class="input-group input-group-sm rounded">
                <div class="input-group-text py-1">Entregar en / Enviar desde</div>
                <select class="form-select py-1" id="nIdSucursalEntrega" name="nIdSucursalEntrega">
                    <?php foreach ($regSucursales as $f) : ?>
                        <option value="<?= $f['nIdSucursal'] ?>" <?= $nIdSucursalActual == $f['nIdSucursal'] ? 'selected' : '' ?>><?= $f['sDescripcion'] ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($bOtraSucursal) : ?>
                    <select class="form-select py-1 ms-2" id="nQuienRecoge" name="nQuienRecoge">
                        <option value="1" selected>Recoge Cliente (solo entrega)</option>
                        <option value="2">Recoge Chofer (solo entrega)</option>
                    </select>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row mb-2 px-1">
        <div class="col">
            <table class="table table-sm table-light table-hover mb-0" id="tblLstProdEntrega">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Descripción</th>
                        <th scope="col" class="text-center">Cantidad<br>Comprada</th>
                        <th scope="col">Existencia</th>
                        <th scope="col">Disponible</th>
                        <th scope="col">Entregado</th>
                        <th scope="col" class="text-center">Otros<br>Almacenes</th>
                        <th class="text-center" scope="col">Entrega</th>
                        <th class="text-center" scope="col">Para<br>Envio</th>
                        <th class="text-center" scope="col">A Surtir</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $r) : ?>
                        <?php $bConDetalle = $r[12] !== false; ?>
                        <?php $cCampoAenfocar = $cCampoAenfocar === null && !$bConDetalle ? '#' . 'inpEntM' . $r[0] : $cCampoAenfocar; ?>
                        <tr class="filaTipoPrincipal <?= $bConDetalle ? '' : 'tipoSinDetalle' ?>">
                            <td class="text-center" scope="row">
                                <?= ++$nContFila ?>
                            </td>
                            <td>
                                <?= $r[1] ?>
                            </td>
                            <td class="text-center">
                                <?= number_format($r[2])  ?>
                            </td>
                            <td class="text-center">
                                <?= number_format($r[11]) ?>
                            </td>
                            <td class="text-center">
                                <?= number_format($r[4]) ?>
                            </td>
                            <td class="text-center">
                                <?= number_format($r[3]) ?>
                            </td>
                            <td class="text-center">
                                <?= number_format($r[5]) ?>
                            </td>
                            <td class="text-center">
                                <input type="text" value="<?= $r[6] ?>" id="<?= 'inpEntM' . $r[0] ?>" <?= $bConDetalle ? 'disabled readonly' : '' ?> class="form-control text-end capturables mx-auto py-0" style="width:80px;">
                            </td>
                            <td class="text-center">
                                <input type="text" value="<?= $r[7] ?>" id="<?= 'inpEnvM' . $r[0] ?>" <?= $bConDetalle ? 'disabled readonly' : '' ?> class="form-control text-end capturables mx-auto py-0" style="width:80px;">
                            </td>
                            <td class="text-center">
                                <?= round($r[8], 3) ?>
                            </td>
                            <td>
                                <?php if ($bConDetalle) : ?>
                                    <button class="accordion-button collapsed p-0 pt-1" type="button" data-bs-toggle="collapse" data-bs-target="#colapsaProd<?= $r[0] ?>"></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($bConDetalle) : ?>
                            <tr>
                                <td colspan="12" class="p-0 m-0">
                                    <div class="accordion-collapse collapse" id="<?= 'colapsaProd' . $r[0] ?>">
                                        <div class="accordion-body pt-1">
                                            <div class="row mb-2" id="agrega-detalle<?= $r[0] ?>">
                                                <div class="col">
                                                    <label class="form-label form-label-sm">Medida</label>
                                                    <select class="form-select form-select-sm" data-idm="<?= 'inpEnvM' . $r[0] ?>" name="nIdArticulo">
                                                        <option value="0" data-ds="0;0;0;0" selected>...</option>
                                                        <?php foreach ($artRelacionados[$r[0]] as $artR) : ?>
                                                            <option value="<?= $artR[1] ?>" data-ds="<?= $artR[1] . ';' . $artR[2] . ';' . $artR[3] . ';' . $artR[4] . ';' ?>"><?= $artR[0] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label form-label-sm">Existencia</label>
                                                    <input type="text" class="form-control form-control-sm text-center" disabled readonly data-idm="<?= 'inpEnvM' . $r[0] ?>" data-clase="exis">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label form-label-sm">Disponible</label>
                                                    <input type="text" class="form-control form-control-sm text-center" disabled readonly data-idm="<?= 'inpEnvM' . $r[0] ?>" data-clase="disp">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label form-label-sm">Otros Almacenes</label>
                                                    <input type="text" class="form-control form-control-sm text-center" disabled readonly data-idm="<?= 'inpEnvM' . $r[0] ?>" data-clase="otros">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label form-label-sm">Entrega</label>
                                                    <input type="text" class="form-control form-control-sm text-center" data-idm="<?= 'inpEnvM' . $r[0] ?>" name="nEntrega">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label form-label-sm">Para Envio</label>
                                                    <input type="text" class="form-control form-control-sm text-center" data-idm="<?= 'inpEnvM' . $r[0] ?>" name="nEnvio">
                                                </div>
                                                <div class="col d-flex">
                                                    <button type="button" class="btn btn-outline-secondary b bi-plus fw-bolder btnAgregarDetEntrega" style="cursor:pointer;" data-idm="<?= $r[0] ?>" data-clase="boton">
                                                        agregar
                                                    </button>
                                                </div>
                                            </div>
                                            <table class="table table-sm table-striped table-hover table-secondary mb-0" id="tabla-detalle<?= $r[0] ?>">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">Medida</th>
                                                        <th class="text-center">Existencia</th>
                                                        <th class="text-center">Disponible</th>
                                                        <th class="text-center">Otros<br>Almacenes</th>
                                                        <th class="text-center">Entrega</th>
                                                        <th class="text-center">Envia</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($r[12] as $rr) : ?>
                                                        <tr class="<?= 'linea-detalle' . $rr[0] ?>">
                                                            <td class="text-center"><?= number_format($rr[2], 2) ?></td>
                                                            <td class="text-center"><?= number_format($rr[3], 0) ?></td>
                                                            <td class="text-center"><?= number_format($rr[4], 0) ?></td>
                                                            <td class="text-center"><?= number_format($rr[5], 0) ?></td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control form-control-sm text-end capturables mx-auto" style="width:80px;" readonly disabled value="<?= number_format($rr[6], 0) ?>">
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control form-control-sm text-end capturables mx-auto" style="width:80px;" readonly disabled value="<?= number_format($rr[7], 0) ?>">
                                                            </td>
                                                            <td>
                                                                <i class="bi bi-trash-fill text-secondary me-1 fs-6" data-llamar="/remisiones/borraArtD/<?= $r[0] ?>/<?= $rr[0] ?>" style="cursor:pointer;"></i>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row px-1">
        <div class="col">
            <h5 class="border">
                <button <?= $hayEnvio ? '' : 'disabled' ?> class="accordion-button collapsed p-1 fw-bold <?= $hayEnvio ? '' : 'opacity-50' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#colapsaEnvio" id="btnParaEnvio">
                    Datos para el envío
                </button>
            </h5>
            <div id="colapsaEnvio" class="accordion-collapse collapse">
                <div class="accordion-body py-0 pb-2">
                    <?php if ($cliente['tipoCli'] != 'P') : ?>
                        <div class="row mb-2">
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-text">Dirección de Envío</div>
                                    <select id="nIdDirEntrega" name="nIdDirEntrega" class="form-select">
                                        <?php foreach ($direcciones as $f) : ?>
                                            <?php if ($cliente['idDirEnt'] == '0') $cliente['idDirEnt'] = $f['id']; ?>
                                            <option value="<?= $f['id'] ?>" <?= $cliente['idDirEnt'] == $f['id'] ? 'selected' : '' ?>><?= $f['direccion'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline-secondary ms-2 bi bi-arrow-clockwise" type="button" id="btnRefreshDireccion"></button>
                                    <button class="btn btn-outline-secondary ms-2" type="button" id="btnAddDireccion">Agrega Direccion</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row mb-2">
                        <div class="col">
                            <input type="text" class="form-control form-control-sm col-12" aria-label="Lugar de entrega" placeholder="Se entrega a" name="sEnvEntrega" id="sEnvEntrega" value="<?= $cliente['envEnt'] ?>">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <textarea class="form-control form-control-sm" name="sEnvDireccion" id="sEnvDireccion" rows="1" aria-label="Direccion" placeholder="Direccion"><?= esc($cliente['envDir']) ?></textarea>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" aria-label="Colonia" placeholder="Colonia" name="sEnvColonia" id="sEnvColonia" value="<?= ($cliente['envCol']) ?>">
                        </div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" aria-label="Telefono" placeholder="Telefono" name="sEnvTelefono" id="sEnvTelefono" value="<?= ($cliente['envTel']) ?>">
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <textarea class="form-control form-control-sm" name="sEnvReferencia" id="sEnvReferencia" rows="1" aria-label="Referencia" placeholder="Referencia"><?= esc($cliente['envRef']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col border-top mt-2 pt-2 d-flex justify-content-between" id="botonesEntrega">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary me-2" id="btnEntregaTodo">Entregar Todo</button>
                <button type="button" class="btn btn-outline-primary me-2" id="btnEnviaTodo">Enviar Todo</button>
            </div>
            <div class="btn-group <?= $hayEntregaPublico == '0' ? 'd-none' : '' ?>" id="gpoNomEntrega">
                <span class="input-group-text">Nombre a quien se le vende</span>
                <input type="text" name="cNomEntrega" id="cNomEntrega" class="form-control">
            </div>
            <div class="btn-group" id="gpoCompletarVenta">
                <button type="button" class="btn btn-outline-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                <button type="button" class="btn btn-outline-success" id="btnCompletaVenta">Guardar</button>
            </div>
            <div class="btn-group d-none" id="gpoDireccion">
                <button type="button" class="btn btn-outline-primary me-3" id="btnGuardaDireccionEnvio">Guardar Dirección</button>
                <button type="button" class="btn btn-outline-secondary me-3" id="btnCancelarDireccionEnvio">Cancelar Captura de Dirección</button>
            </div>
        </div>
    </div>
</div>
<form action="" method="post" id="frmGuardarVentaEntrega"></form>
<script type="text/javascript">
    $(document).ready(function() {
        miGlobal.entregaCampo = "<?= $cCampoAenfocar ?>";
        // mdata = $('select[data-idm="inpEnvM90"]')[0].options[$('select[data-idm="inpEnvM90"]')[0].selectedIndex];
        let appEntrega = {
            lstDirecciones: <?= json_encode($direcciones) ?>,
            bndPermitirNoDisponibles: false,
            bndHayEnvio: '<?= $hayEnvio ? '1' : '0' ?>',
            bndHayEntrega: '<?= $hayEntrega ? '1' : '0' ?>',
            bndOtraSucursal: '<?= $bOtraSucursal ? '1' : '0' ?>',
            valorAnterior: '',
            elementoActual: null,
            bndEnProceso: false, // indica si existe un proceso activo de solicitud al server
            tipoCli: '<?= $cliente['tipoCli'] ?>',
            idVenta: '<?= $idventa ?>',
            init: function() {
                $("#tblLstProdEntrega")
                    .on('focusout', 'input[id^="inpEntM"], input[id^="inpEnvM"]', appEntrega.onFocusoutProducto)
                    .on('keydown', 'input[id^="inpEntM"], input[id^="inpEnvM"]', appEntrega.onKeydownProducto)
                    .on('focus', 'input[id^="inpEntM"], input[id^="inpEnvM"], select', appEntrega.onFocusInputsTabla)
                    .on('input', 'input', appEntrega.onInputsTabla);
                $("#tblLstProdEntrega button.btnAgregarDetEntrega").on('click', appEntrega.agregaMedida);
                $('#tblLstProdEntrega select[name="nIdArticulo"]').on('change', appEntrega.seleccionaMedida);
                $("#tblLstProdEntrega").find('[id^="tabla-detalle"]').on('click', appEntrega.borraLineaDetalle);
                $('#nIdDirEntrega').on('change', appEntrega.onChangeDireccionEnvio);
                appEntrega.asignaDireccion($('#nIdDirEntrega').val());
                $('#btnAddDireccion').on('click', appEntrega.guardaDireccion);
                $('#btnRefreshDireccion').on('click', {
                    'refresh': true
                }, appEntrega.onChangeDireccionEnvio);
                // los select usados se les pone un id btnAgregarDetEntrega
                $('#btnEntregaTodo').on('click', appEntrega.entregaTodo);
                $('#btnEnviaTodo').on('click', appEntrega.enviaTodo);
                $("#btnCompletaVenta").on('click', appEntrega.onClickCompletaVenta);
                $('#nIdSucursalEntrega').on('change', appEntrega.onChangeSucursal);
                if (appEntrega.bndHayEnvio == '1') {
                    $('#btnParaEnvio').removeClass('opacity-50')[0].disabled = false;
                    if (!$('#colapsaEnvio').hasClass('show')) $('#btnParaEnvio').click();
                }
                $(miGlobal.entregaCampo).select();
            },

            seleccionaMedida: function(e) {
                let ele = e.target;
                let op = $(ele).parent().parent();
                let arr = $(ele.options[ele.selectedIndex]).data('ds').split(';');
                $(op).find('[data-clase="exis"]').val(arr[1]);
                $(op).find('[data-clase="disp"]').val(arr[2]);
                $(op).find('[data-clase="otros"]').val(arr[3]);
            },
            agregaMedida: function(e) {
                let id = $(e.target).data('idm');
                let jOb = $('#agrega-detalle' + id);
                if (appEntrega.bndEnProceso) return;
                appEntrega.bndEnProceso = true;
                $.post(baseURL + '/remisiones/entregaArtD/' + id, $.param(
                        $('#cntEntrega').find('#nIdSucursalEntrega, ' + '#agrega-detalle' + id + ' [name]')
                        .filter('#nIdSucursalEntrega, select[name="nIdArticulo"], input[name="nEntrega"], input[name="nEnvio"]')
                    ), null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        appEntrega.bndEnProceso = false;

                        if (data.ok == '1') {
                            // se agrega en el listado directamente
                            appEntrega.agregaLineaDeDetalle(id, data, $(jOb).find('[name]'));
                        } else {
                            miGlobal.muestraAlerta(data.msj, 'frmModal', 3);
                            // $('#selMedida1').select();
                            $('[data-idm="inpEnvM' + id + '"]').focus();
                        }
                    })
                    .fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                        appEntrega.bndEnProceso = false;
                    });
            },
            agregaLineaDeDetalle: function(id, data, campos) {
                let objValores = data.valores;
                let tbl = $('#tabla-detalle' + id);
                let linea = tbl.find('tr.linea-detalle' + objValores.nIdArticulo);
                let cad = '<tr class="linea-detalle' + objValores.nIdArticulo + '">' +
                    '<td class="text-center">' + objValores.medida + '</td>' +
                    '<td class="text-center">' + objValores.exis + '</td>' +
                    '<td class="text-center">' + objValores.dis + '</td>' +
                    '<td class="text-center">' + objValores.otr + '</td>' +
                    '<td class="text-center">' +
                    '<input type="text" class="form-control form-control-sm text-end capturables mx-auto" style="width:80px;" readonly disabled value="' + objValores.nEnt + '">' +
                    '</td>' +
                    '<td class="text-center">' +
                    '<input type="text" class="form-control form-control-sm text-end capturables mx-auto" style="width:80px;" readonly disabled value="' + objValores.nEnv + '">' +
                    '</td>' +
                    '<td>' +
                    '<i class="bi bi-trash-fill text-secondary me-1 fs-6" style="cursor:pointer;" data-llamar="/remisiones/borraArtD/' + id + '/' + objValores.nIdArticulo + '"></i>' +
                    '</td>' +
                    '</tr>';
                if (linea.length == 0) {
                    tbl.find('tbody').append(cad);
                } else {
                    linea.replaceWith(cad);
                }
                campos.eq(0).val('0');
                campos.eq(1).val('');
                campos.eq(2).val('');
                campos.eq(3).val('');
                campos.eq(4).val('');
                campos.eq(5).val('');
                appEntrega.actualizaCabecero(data);
            },
            borraLineaDetalle: function(e) {
                let jo = $(e.target);
                if (jo.hasClass('bi-trash-fill') === false) return;
                if (appEntrega.bndEnProceso) return;
                appEntrega.bndEnProceso = true;
                // si no se clikea en el basurero, se sale
                $.post(baseURL + jo.data('llamar'), {}, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        appEntrega.bndEnProceso = false;
                        // se borra del listado
                        jo.parent().parent().remove();
                        appEntrega.actualizaCabecero(data);
                    })
                    .fail(function(jqxhr, textStatus, err) {
                        appEntrega.bndEnProceso = false;
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },
            actualizaCabecero: function(data) {
                // actualizar cabeceros
                $('#inpEntM' + data.id).val(data.nEntrega);
                $('#inpEnvM' + data.id).val(data.nEnvio);
                $('#inpEnvM' + data.id).parent().next().text(data.porSurtir);
                if (data.hayEntregaPublico == '0') {
                    $('#gpoNomEntrega').addClass('d-none');
                } else {
                    $('#gpoNomEntrega').removeClass('d-none');
                }
                appEntrega.bndHayEnvio = data.hayEnvio;
                appEntrega.bndHayEntrega = data.hayEntrega;
                obj = $('#colapsaEnvio');
                if (data.hayEnvio == '1') {
                    $('#btnParaEnvio').removeClass('opacity-50')[0].disabled = false;
                    if (!obj.hasClass('show')) $('#btnParaEnvio').click();
                } else {
                    if (obj.hasClass('show')) $('#btnParaEnvio').click();
                    $('#btnParaEnvio').addClass('opacity-50')[0].disabled = true;
                }
            },

            onChangeDireccionEnvio: function(e) {
                if (e.target.selectedIndex == -1) return;
                let id = (e.data) ? $('#nIdDirEntrega').val() : e.target.value;
                appEntrega.asignaDireccion(id);
            },
            onChangeSucursal: function(e) {
                miGlobal.toggleBlockPantalla('');
                $.post(baseURL + '/remisiones/entregaparcial/' + appEntrega.idVenta, $.param($('#cntEntrega').find('[name]')), null, 'html')
                    .done(function(data, textStatus, jqxhr) {
                        miGlobal.toggleBlockPantalla('');
                        $('#frmModal .modal-body').html(data);
                    }).fail(function(jqxhr, textStatus, err) {
                        miGlobal.toggleBlockPantalla('');
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },

            onFocusInputsTabla: function(e) {
                let t = e.target;
                appEntrega.valorAnterior = t.value;
                appEntrega.elementoActual = t;
            },
            onKeydownProducto: function(e) {
                let tipoMov = e.target.id.substr(0, 6) == 'inpEnt' ? 'ent' : 'env';
                let obj;
                if (e.which == 13 || e.which == 9) {
                    if (e.shiftKey === true) return;
                    if (tipoMov == 'ent') {
                        obj = $(e.target).parent().next().find('input');
                    } else {
                        obj = $(e.target).parent().parent().nextAll('.tipoSinDetalle')
                            .first().find('input[id^="inpEntM"]');
                    }
                    if (obj.length > 0) {
                        e.preventDefault();
                        obj.select();
                    } else {
                        e.target.blur();
                    }
                }
            },
            onFocusoutProducto: function(e) {
                let tipoMov = e.target.id.substr(0, 6) == 'inpEnt' ? 'entrega' : 'envio';
                let id = e.target.id.substr(7);
                if (appEntrega.valorAnterior == e.target.value) return;
                if (appEntrega.bndEnProceso) {
                    e.target.value = appEntrega.valorAnterior;
                    return;
                }
                appEntrega.bndEnProceso = true;
                miGlobal.toggleBlockPantalla('');
                e.target.value = e.target.value.trim() == '' ? '0' : e.target.value.trim();
                $.post(baseURL + '/remisiones/entregaArt/' + id + '/' + tipoMov + '/' + e.target.value, null, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        appEntrega.bndEnProceso = false;
                        miGlobal.toggleBlockPantalla('');
                        if (data.ok == '1') {
                            appEntrega.actualizaCabecero(data);
                        } else {
                            // e.target.value = appEntrega.valorAnterior;
                            e.target.value = data.cantActual;
                            miGlobal.muestraAlerta(data.msj, 'frmModal', 1);
                            e.target.select();
                        }
                    }).fail(function(jqxhr, textStatus, err) {
                        miGlobal.toggleBlockPantalla('');
                        appEntrega.bndEnProceso = false;
                        console.log('fail', jqxhr,
                            textStatus, err);
                    });
            },

            asignaDireccion: function(id) {
                id = parseInt(id ?? 0);
                let o = appEntrega.lstDirecciones.filter((val) => {
                    return val.id == id;
                })[0];
                if (id == 0) {
                    $('#colapsaEnvio  [name^=sEnv]').val('');
                } else {
                    $('#sEnvEntrega').val(o.sEnvEntrega);
                    $('#sEnvReferencia').val(o.sEnvReferencia);
                    $('#sEnvDireccion').val(o.sEnvDireccion);
                    $('#sEnvColonia').val(o.sEnvColonia);
                    $('#sEnvTelefono').val(o.sEnvTelefono);
                }
            },
            esDireccionCompleta: function() {
                let r = 0;
                if ($('#sEnvEntrega').val().trim() == '') {
                    r = 1;
                } else if ($('#sEnvDireccion').val().trim() == '') {
                    r = 2;
                } else if ($('#sEnvColonia').val().trim() == '') {
                    r = 3;
                } else if ($('#sEnvTelefono').val().trim() == '') {
                    r = 4;
                } else if ($('#sEnvReferencia').val().trim() == '') {
                    r = 5;
                }
                if (r > 0) {
                    miGlobal.muestraAlerta('Se deben capturar todos los datos para el envio', 'frmModal', 4000);
                    $('#sEnvEntrega, #sEnvDireccion, #sEnvColonia, #sEnvTelefono, #sEnvReferencia')
                        .eq(r - 1).select();
                }
                return (r == 0);
            },
            esDireccionDistinta: function(id) {
                id = parseInt(id ?? 0);
                if (id == 0) return true;
                let o = appEntrega.lstDirecciones.filter((val) => {
                    return val.id == id;
                })[0];
                return (
                    $('#sEnvReferencia').val().trim() != o.sEnvReferencia.trim() ||
                    $('#sEnvDireccion').val().trim() != o.sEnvDireccion.trim() ||
                    $('#sEnvColonia').val().trim() != o.sEnvColonia.trim() ||
                    $('#sEnvTelefono').val().trim() != o.sEnvTelefono.trim());
            },
            guardaDireccion: function() {
                if (appEntrega.bndEnProceso) return;
                appEntrega.bndEnProceso = true;
                if (appEntrega.esDireccionCompleta() === false) {
                    appEntrega.bndEnProceso = false;
                    return;
                }
                if (appEntrega.esDireccionDistinta($('#nIdDirEntrega').val())) {
                    if (confirm('Confirma que desea agregar la direccion?') === false) {
                        appEntrega.bndEnProceso = false;
                        return;
                    }
                    let jOb = $('#colapsaEnvio');
                    $.post(baseURL + '/remisiones/agregarDireccion', $.param($(jOb).find('[name]')), null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            appEntrega.bndEnProceso = false;
                            if (data.ok == '1') {
                                appEntrega.asignaLstDirecciones(data);
                            }
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            appEntrega.bndEnProceso = false;
                            console.log('fail', jqxhr, textStatus, err);
                        });
                }
            },
            asignaLstDirecciones: function(data) {
                let a = '';
                appEntrega.lstDirecciones = data.reg;
                data.reg.forEach((v, i) => {
                    a += '<option value="' + v.id + '" ' +
                        (data.id == v.id ? 'selected' : '') + '>' +
                        v.direccion +
                        '</option>';
                });
                $('#nIdDirEntrega').html(a);
            },

            entregaTodo: function(e) {
                miGlobal.toggleBlockPantalla('');
                $.post(baseURL + '/remisiones/entregaTodo', $.param($('#cntEntrega').find('[name]')), null, 'html')
                    .done(function(data, textStatus, jqxhr) {
                        miGlobal.toggleBlockPantalla('');
                        $('#frmModal .modal-body').html(data);
                    }).fail(function(jqxhr, textStatus, err) {
                        miGlobal.toggleBlockPantalla('');
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },
            enviaTodo: function(e) {
                miGlobal.toggleBlockPantalla('');
                $.post(baseURL + '/remisiones/enviaTodo', $.param($('#cntEntrega').find('[name]')), null, 'html')
                    .done(function(data, textStatus, jqxhr) {
                        miGlobal.toggleBlockPantalla('');
                        $('#frmModal .modal-body').html(data);
                    }).fail(function(jqxhr, textStatus, err) {
                        miGlobal.toggleBlockPantalla('');
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },

            onInputsTabla: function(e) {
                miGlobal.valNumero(e, appEntrega, {
                    re: /^\d*(?:\.?\d{0,2})?$/g,
                    propValorAnterior: 'valorAnterior',
                    validacionEnBlur: true
                });
            },
            onClickCompletaVenta: function(e) {
                if (appEntrega.bndEnProceso) return;
                appEntrega.bndEnProceso = true;
                if (appEntrega.bndHayEnvio == '1' && appEntrega.esDireccionCompleta() === false) {
                    appEntrega.bndEnProceso = false;
                    return;
                }
                if (appEntrega.bndHayEntrega == '1' && appEntrega.tipoCli == 'P' && $('#cNomEntrega').val().trim() == '') {
                    miGlobal.muestraAlerta('Falta a quién se le entrega el producto.', 'frmModal', 4000);
                    appEntrega.bndEnProceso = false;
                    $('#cNomEntrega').select();
                    return;
                }
                if (appEntrega.bndHayEnvio == '0' && appEntrega.bndHayEntrega == '0') {
                    miGlobal.muestraAlerta('No se asignó producto para surtir.', 'frmModal', 4000);
                    appEntrega.bndEnProceso = false;
                    $('#cNomEntrega').select();
                    return;
                }
                $("#btnCompletaVenta")[0].disabled = true;
                let bAquienSeEntrega = '0';
                if (appEntrega.bndOtraSucursal == '1') {
                    bAquienSeEntrega = $('#nQuienRecoge').val();
                }
                // #nIdSucursalEntrega, #nIdDirEntrega, #sEnvEntrega, #sEnvDireccion, #sEnvColonia,
                // #sEnvTelefono, #sEnvReferencia, #nIdUsoCFDI, #cIdMetodoPago, #cNomEntrega
                let oF = $('#frmGuardarVentaEntrega');
                $('#cntEntrega [name]').each((i, el) => {
                    oF.append(
                        '<input type="hidden" name="' + el.name +
                        '" value="' + el.value + '">'
                    );
                });
                oF.append('<input type="hidden" name="hayEntrega" value="' + appEntrega.bndHayEntrega + '">');
                oF.append('<input type="hidden" name="hayEnvio" value="' + appEntrega.bndHayEnvio + '">');
                oF.append('<input type="hidden" name="quienRecoge" value="' + bAquienSeEntrega + '">');
                $.post(baseURL + '/remisiones/guardarEntrega', oF.serialize(), null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        if (data.ok == '1') {
                            if (data.imprimir == '1') {
                                let a = $('#frmGuardarVentaEntrega')[0];
                                a.action = baseURL + '/ventas/imprimeRemision/' + data.id + '/1/entrega/' + data.envioAotraSuc;
                                a.submit();
                            } else {
                                location.href = baseURL + '/remisiones';
                            }
                        } else {
                            location.href = baseURL + '/remisiones';
                        }
                    })
                    .fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr,
                            textStatus, err);
                        $("#btnCompletaVenta")[0].disabled = false;
                    });
                // oF[0].action = baseURL + '/remisiones/guardarEntrega';
                // oF[0].submit();
            }
        };
        appEntrega.init();
    });
</script>