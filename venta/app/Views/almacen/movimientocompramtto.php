<?php
$bPrint = isset($printerMode) ? 'true' : 'false';
$nCont = 0;
$fTotal = 0;
$fSubTotal = 0;
$fIVA = 0;
$pago['completar'] = 0;
$registro['fSubTotal'] = 0;
$registro['fIVA'] = 0;
$registro['fTotal'] = 0;
$registro['fTotal2'] = 0;
$deshabilitaCampos = $modo === 'A' || $modo === 'E' ? ($proveedor['nIdProveedor'] == 0 ? (in_array($operacion, array('entrada', 'salida')) ? '' : 'disabled') : '') : 'disabled';
//$attrBtnCompletar = 'data-bs-toggle="mdConfirma" data-bs-target="#frmModal" id="btnPagar" data-llamar="' . $frmURL . '" ';

$dlProveedor =  $proveedor['sNombre'] ?? '';
$nIdSucursal = $registro['nIdSucursal'] ?? 0;

$aKeys = [
    'salida' => ['nIdSalida', 'dSalida', ''],
    'entrega' => ['nIdEntrega', 'dEntrega', 'Entrega'],
    'compra'  => ['nIdCompra', 'dcompra', 'del proveedor'],
    'entrada' => ['nIdEntrada', 'dEntrada', ''],
    'traspaso' => ['nIdTraspaso', 'dTraspaso', 'de la sucursal a solicitar'],
    'envio' => ['nIdEnvio', 'dEntrega', 'del cliente'],
];
$masterkey = $aKeys[$operacion][0];
$datefld = 'dSolicitud'; //$aKeys[$operacion][1];
$campoproveedor = $aKeys[$operacion][2];

$containerClass = "container-fluid h-100";
$tblClass = "table table-striped table-hover table-sm";
$borderClass = "col bg-light px-4 pt-3 border rounded";
if (isset($printerMode)) {
    $containerClass = "overflow-auto container-fluid imprimir";
    $tblClass = "table table-sm";
    $borderClass = "col px-4 pt-3";
}
generaCssImpresion();
?>

<style>
    #tbl tbody tr:hover {
        background-color: rgba(161, 191, 226, 0.501) !important;
    }
</style>

<div class="<?= $containerClass ?>">
    <?php if (isset($printerMode)) : ?>
        <div class="row">
            <div class="col-3">
                <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain'] ?>" alt="" width="60" class="align-text-top">
            </div>

            <div class="col-6">
                <h4 class="text-center"><?= $titulo ?></h4>
            </div>

            <div class="col-3">
                <?= $registro['sDescripcion'] ?>
            </div>
        </div>
        <?php if (!in_array($operacion, array('entrada', 'salida'))) : ?>
            <div class="row px-4">
                <div class="col">
                    <?= ($operacion === 'traspaso' ? 'Solicitar a' : 'Proveedor') . ' <B>' . $proveedor['sNombre'] . '</B>' ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="row px-4 pt-3">
            <div class="col-6 ">
                Fecha de solicitud <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . (new DateTime($registro["$datefld"]))->format('d-m-Y') . '</b>' ?>
            </div>
            <div class="col text-center">
                Folio <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . $id . '</b>' ?><span class="px-2"></span>
                <?= $operacion === 'traspaso' ? 'Folio Envio: <b class="fs-sm-7 fw-bold">' . $registro['nIdEnvio'] . '</b>' : '' ?>
            </div>
        </div>
        <div class="row px-4 border-bottom">
            <div class="col">
                Observaciones: <B> <?= $registro['sObservacion'] ?> </B>
            </div>
        </div>
    <?php else : ?>
        <div class="row">
            <div class="col">
                <h4><?= $titulo ?></h4>
                <hr>
            </div>
        </div>
    <?php endif; ?>
    <div class="row h-75">
        <div class="<?= $borderClass ?>">
            <?php if (!isset($printerMode)) : ?>

                <div class="position-relative">
                    <div id="movtomttowAlert">
                        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
                        </div>
                    </div>
                </div>
                <form action="<?= base_url('/movimiento/agregaArticuloCompra/' . $sufijoURL) ?>" method="get" id="frm00" autocomplete="off">
                    <input type="hidden" name="nIdArticulo" id="nIdArticulo" value="">
                    <input type="hidden" name="nCosto" id="nCosto" value="">
                    <input type="hidden" name="nIva" id="nIva" value="">
                    <input type="hidden" name="nIdViaje" id="nIdViaje" value="">
                    <input type="hidden" name="nIdViajeEnvio" id="nIdViajeEnvio" value="">
                    <div class="row">
                        <div class="input-group ">
                            <input class="form-control" id="dlArticulos0" name="dlArticulos0" list="dlArticulos" placeholder="Escriba nombre o el codigo del producto" aria-label="codigo/nombre del producto" tabindex="2" <?= $deshabilitaCampos ?> />
                            <datalist id="dlArticulos"></datalist>
                            <input class="form-control text-end" type="text" style="max-width:90px;" placeholder="Costo" aria-label="Costo" id="fImporte" name="fImporte" tabindex="3" <?= $deshabilitaCampos ?> />
                            <input class="form-control text-end" type="text" style="max-width:100px;" placeholder="IVA total" title="IVA aplicado total al importe" aria-label="IVA" id="fIVA" name="fIVA" tabindex="4" <?= $deshabilitaCampos ?> />
                            <input class="form-control " type="text" style="max-width:90px;" placeholder="Envio" aria-label="Envio" id="nIdEnvio" name="nIdEnvio" tabindex="5" <?= $deshabilitaCampos ?> />
                            <i class="bi bi-search m-1 <?= $deshabilitaCampos ? 'text-secondary' : 'text-primary' ?>" style="font-size: 1rem; <?= $deshabilitaCampos ? '' : ' cursor:pointer; ' ?>" id="BuscaEnv" <?= $deshabilitaCampos ?>></i>
                            <input class="form-control text-end" type="text" style="max-width:90px;" placeholder="Cantidad" aria-label="Cantidad del producto" id="nCant" name="nCant" tabindex="6" data-llamar="ventas/" <?= $deshabilitaCampos ?> />
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <div class="mt-3 " style="z-index:1;">
                <table class="<?= $tblClass ?>" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <th class="text-center" scope="col">Cantidad</th>
                            <th class="text-center" scope="col">Precio</th>
                            <th class="text-center" scope="col">Importe</th>
                            <th class="text-center" scope="col">IVA del importe</th>
                            <th class="text-center" scope="col">Envio</th>
                            <th class="text-center" scope="col">Viaje</th>
                            <?php if (!(isset($printerMode) || !in_array($modo, ['A', 'E']))) : ?>
                                <th scope="col">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="9" class="fs-5 text-center">No hay registros</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php
                                $nImp = (floatval($r['fImporte']) * floatval($r['fCantidad']));
                                $registro['fSubTotal'] += $nImp;
                                $nIva = floatval($r['fIVA']);
                                $registro['fIVA'] += $nIva;
                                $registro['fTotal'] = $registro['fSubTotal'] + $registro['fIVA'];
                                ?>
                                <tr class="<?= isset($printerMode) ? 'fs-7' : '' ?>">
                                    <td scope="row"> <?= ++$nCont ?> </td>
                                    <td> <?= $r['sDescripcion'] ?> </td>
                                    <td class="text-end pe-5"> <?= $r['fCantidad'] ?> </td>
                                    <td class="text-center" scope="col"> <?= $r['fImporte'] ?> </td>
                                    <td class="text-center" scope="col"> <?= $nImp ?> </td>
                                    <td class="text-center" scope="col"> <?= $r['fIVA'] ?> </td>
                                    <td class="text-center" scope="col"> <?= $r['nIdEnvio'] == '0' ? '' : $r['nIdEnvio'] ?> </td>
                                    <td class="text-center" scope="col"> <?= $r['nIdViaje'] == '0' ? '' : $r['nIdViaje'] ?> </td>
                                    <?php if (!(isset($printerMode) || !in_array($modo, ['A', 'E']))) : ?>
                                        <td>
                                            <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="movimiento/borraArticuloCompra/<?= $r['nIdArticulo'] . '/' . $r['nIdEnvio'] . '/' . $sufijoURL  ?>" data-mod-msj="Confirma borrar el registro?" style="cursor:pointer;"></i>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach;
                            $registro['fSubTotal'] = number_format($registro['fSubTotal'], 2);
                            $registro['fIVA'] = number_format($registro['fIVA'], 2);
                            $registro['fTotal2'] = $registro['fTotal'];
                            $registro['fTotal'] = number_format($registro['fTotal'], 2);
                            ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <?php if (!isset($printerMode)) : ?>
            <div class="col" style="max-width: 350px;">
                <div class="container-fluid border rounded pt-3  bg-light">
                    <div class="position-relative">
                        <div id="provmovttowAlert">
                            <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-1">
                        <span class="col-12 fw-bold text-center">
                            <?= $registro['sDescripcion'] ?>
                        </span>
                    </div>
                    <form action='<?= base_url("movimiento/agregaProveedor/" . $sufijoURL) ?>' method="post" id="frmBuscaProveedor" autocomplete="off">
                        <?php generaCampoTexto('nIdProveedor', $error ?? false, 'hidden', $proveedor ?? null, $modo); ?>
                        <?php generaCampoTexto('nIdCliente', $error ?? false, 'hidden', $proveedor ?? null, $modo); ?>
                        <?php generaCampoTexto('nIdDirEntrega', $error ?? false, 'hidden', $proveedor ?? null, $modo); ?>
                        <?php if (!in_array($operacion, array('entrada', 'salida'))) : ?>
                            <div class="col">
                                <label for="dlProveedores" class="form-label">Proveedor</label>
                                <div class="input-group ">
                                    <input class="form-control w-75" list="dlProveedores" placeholder="Escriba nombre del proveedor" aria-label="codigo/nombre de <?= $campoproveedor ?>" id="dlProveedores0" name="dlProveedores0" value="<?= $dlProveedor ?>" />
                                    <datalist id="dlProveedores" value="<?= $dlProveedor ?>"></datalist>
                                </div>
                            </div>
                        <?php endif ?>
                        <div class="col">
                            <label for="<?= $datefld ?>" class="form-label">F. de solicitud de <?= $operacion ?></label>
                            <div class="input-group ">
                                <?php generaCampoTexto("$datefld", $error ?? false, 'date', $registro ?? null, $modo); ?>
                            </div>
                        </div>
                        <div class="col">
                            <label for="sObservacion" class="form-label">Observaciones</label>
                            <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                            <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
                        </div>
                        <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                        <div class="row mb-1">
                            <div class="col-7">Artículos solicitados</div><span class="col-5 text-end fw-bold"><?= $nCont ?></span>
                        </div>
                        <div class="row mb-1">
                            <div class="col">SubTotal</div>
                            <div class="col-8">
                                <?php generaCampoTexto('fSubTotal', $error ?? false, 'input ', $registro ?? null, 'B', 'text-end'); ?>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col">IVA</div>
                            <div class="col-8">
                                <?php generaCampoTexto('fIVA', $error ?? false, 'input ', $registro ?? null, 'B', ' text-end'); ?>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col">Total</div>
                            <div class="col-8">
                                <?php generaCampoTexto('fTotal', $error ?? false, 'input ', $registro ?? null, 'B', ' text-end'); ?>
                                <?php generaCampoTexto('fTotal2', $error ?? false, 'hidden', $registro ?? null, 'B', ' text-end'); ?>
                            </div>
                        </div>

                        <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                    </form>

                    <div class="text-center my-1">
                        <button class="btn btn-sm btn-outline-secondary me-3" id="btnBack">
                            Regresar
                        </button>
                        <?php if ($nCont > 0 && in_array($modo, ['A', 'E'])) : ?>
                            <button class="btn btn-outline-primary btn-sm me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="/movimiento/limpiaOperacion/<?= $sufijoURL ?>" data-mod-msj="Desea reiniciar la captura?">
                                Limpiar
                            </button>
                            <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!">
                                Guardar
                            </button>
                        <?php endif; ?>
                        <?php if ($nCont > 0 && $modo === 'B') : ?>
                            <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Confirma la cancelacion de la compra?">
                                Cancelar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="mdConfirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Confirmar</h3>
                <hr>
                <p>Confirma borrar el registro?</p>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar00">No</button>
                    <button type="button" class="btn btn-primary" id="btnGuardar00">Si</button>
                </div>
            </div>
        </div>
    </div>
</div>


<form action="" method="post" id="frmEnvio" style="display:none">
    <input type="hidden" name="idDummyEnvio" value="" />
</form>

<?= generaModalGeneral('frmModal', 'modal-xl modal-dialog-scrollable') ?>
<?= generaModalGeneral('frmModalDis', 'modal-md') ?>

<script type="text/javascript">
    $(document).ready(function() {

        const selArticulo = {
            buscar: true,
            init: function() {
                $('#dlArticulos0')
                    .on('focus', () => {
                        selArticulo.buscar = true;
                    })
                    .on('input', selArticulo.onInput)
                    .on('keydown', selArticulo.onKeydown);
            },

            onInput: function(e) {
                if (selArticulo.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/articulo/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let js = '';
                            for (x of data.registro) {
                                js += '<option value="' + x.sDescripcion +
                                    '" data-id="' + x.nIdArticulo +
                                    '" data-precio="' + x.nCosto +
                                    '">';
                            }
                            $('#dlArticulos').html(js);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdArticulo').val('');
                    $('#fImporte').val('');
                    $('#nCosto').val('');
                }
            },

            onKeydown: function(e) {
                function asignaArticulo(d) {
                    selArticulo.buscar = false;
                    $('#nIdArticulo').val(d.nIdArticulo);
                    $('#fImporte').val(d.nCosto);
                    $('#nCosto').val(d.nCosto);
                    $('#dlArticulos0').val(d.sDescripcion);
                    $('#fImporte').select();
                };

                function buscaCodArticulo(id) {
                    //miGlobal.toggleBlockPantalla('Espere ...');
                    $.post(baseURL + '/articulo/leeRegistro/' + id, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Artículo no existente', "movtomtto", 1100);
                        } else {
                            //console.log(data.registro);
                            asignaArticulo(data.registro);
                        }
                        //miGlobal.toggleBlockPantalla();
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                let valor = e.target.value.trim();
                if ((e.which == 13 || e.which == 9) && valor.length > 0) {
                    e.preventDefault();
                    if (/^\d+$/.test(valor) === true) {
                        // solo numero
                        buscaCodArticulo(valor);
                    } else {
                        let elementoEncontrado = false;
                        $('#dlArticulos option').each((i, el) => {
                            if (elementoEncontrado === false) {
                                elementoEncontrado = el;
                            } else {
                                if (el.value == e.target.value) {
                                    elementoEncontrado = el;
                                    return false;
                                }
                            }
                        });
                        if (elementoEncontrado) {
                            selArticulo.buscar = false;
                            $('#dlArticulos0').val(elementoEncontrado.value);
                            $('#nIdArticulo').val($(elementoEncontrado).data('id'));
                            $('#fImporte').val($(elementoEncontrado).data('precio'));
                            $('#nCosto').val($(elementoEncontrado).data('precio'));
                            $('#fImporte').select();
                        } else {
                            miGlobal.muestraAlerta('Articulo no existente', "movtomtto", 1100);
                        }
                    }
                }
            },
        };

        const valCantidad = {
            cantAnt: '',
            init: function() {
                $('#nCant')
                    .on('input', valCantidad.onInput)
                    .on('keydown', valCantidad.onKeydown);
            },
            onInput: function(e) {
                miGlobal.valNumero(e, valCantidad, {
                    re: /^\d*(?:\.\d{0,3})?$/g,
                    numeroMax: 90999999,
                })
            },
            bandCantidad: false,
            onKeydown: function(e, porEnvio) {
                if (((e.which == 13 || e.which == 9) || (porEnvio ?? false)) && e.target.value > 0) {
                    e.preventDefault();
                    if (valCantidad.bandCantidad) return;
                    valCantidad.bandCantidad = true;
                    if ($('#nIdArticulo').val() <= 0) {
                        miGlobal.muestraAlerta('Articulo no Seleccionado', 'movtomtto', 1100);
                        valCantidad.bandCantidad = false;
                        return;
                    }
                    miGlobal.toggleBlockPantalla('Agregando artículo...');
                    miGlobal.agregaCamposHidden('#<?= $datefld ?>, #sObservacion', '#frm00');
                    $('#frm00')[0].submit();
                }
            }
        };

        const valImporte = {
            init: function() {
                $("#fImporte")
                    .on('input', valImporte.onImporteInput)
                    .on('keydown', {
                        'tipo': 'imp'
                    }, valImporte.onImporteKeyDown);
                $("#fIVA")
                    .on('input', valImporte.onImporteInput)
                    .on('keydown', {
                        'tipo': 'iva'
                    }, valImporte.onImporteKeyDown);
            },
            onImporteInput: function(e) {
                miGlobal.valNumero(e, valImporte, {
                    re: /^\d*(?:\.\d{0,2})?$/g
                })
            },
            onImporteKeyDown: function(e) {
                if ((e.which == 13 || e.which == 9) && e.target.value > 0) {
                    e.preventDefault();
                    if (e.data.tipo == 'imp') {
                        $('#fIVA').select();
                    } else {
                        $('#nIdEnvio').select();
                    }
                }
            },
        };

        const movTabla = {
            cmd: '',
            frmDestino: '',
            init: function() {
                $('#mdConfirma').on('show.bs.modal', movTabla.confirmar);
                $('#btnGuardar00').on('click', movTabla.enviar);
            },
            confirmar: function(e) {
                let a = $(e.relatedTarget);
                movTabla.cmd = a.data('llamar');
                movTabla.frmDestino = a.data('frmDestino');
                if (movTabla.frmDestino == null)
                    movTabla.frmDestino = '#frmEnvio';
                $('#mdConfirma div.modal-body > p').html(a.data(
                    'mod-msj'));
            },
            bndGuardar: false,
            enviar: function(e) {
                if (movTabla.bndGuardar) return;
                movTabla.bndGuardar = true;
                $('#btnGuardar00')[0].disabled = true;
                miGlobal.agregaCamposHidden('#frmBuscaProveedor [name]', movTabla.frmDestino);
                // se realiza un submit
                let f = $(movTabla.frmDestino)[0];
                f.append("#fTotal");
                f.append("#fTotal2");
                f.action = baseURL + '/' + movTabla.cmd;
                miGlobal.toggleBlockPantalla('Agregando compra...');
                f.submit();
            }
        };

        const buscaEnv = {
            init: function() {
                $('#BuscaEnv').on('click', buscaEnv.buscaClick);
            },
            buscaClick: function(e) {
                let idEnvio = $('#nIdEnvio').val();
                let idArticulo = $('#nIdArticulo').val();
                if (idArticulo == '') {
                    $('#dlArticulos0').select();
                    return;
                }
                if (idEnvio == '') {
                    $('#nIdEnvio').select();
                    return;
                }

                miGlobal.toggleBlockPantalla('Espere ...');
                $.post(baseURL + '/viaje/buscaArticuloDeEnvio/' + idEnvio + '/' + idArticulo, {}, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    miGlobal.toggleBlockPantalla();
                    if (data.Ok == '0') {
                        miGlobal.muestraAlerta(data.msj, "movtomtto", 5000);
                        $('#nIdEnvio').select();
                    } else if (data.Ok == '2') {
                        $('#frmModalDis .modal-body').html(data.registro);
                        $('#frmModalDis').modal('show');
                    } else {
                        $('#nIdViaje').val(data.registro['nIdViaje']);
                        $('#nIdViajeEnvio').val(data.registro['nIdViajeEnvio']);
                        $('#nCant').val(data.registro['fPorRecibir']);
                        $('#nCant').trigger('keydown', ['porEnvio']);
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    miGlobal.toggleBlockPantalla();
                });
            }
        };

        const appSelProveedor = {
            buscar: true,
            observacion: '',
            fecha: '',
            init: function() {
                $('#dlProveedores0')
                    .on('focus', () => {
                        appSelProveedor.buscar = true;
                    })
                    .on('input', appSelProveedor.onInput)
                    .on('keydown', appSelProveedor.onKeydown);
            },

            onInput: function(e) {
                if (appSelProveedor.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    let js = '';
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/proveedor/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            for (x of data.registro) {
                                js += '<option value="' + x.sNombre + '" ' +
                                    'data-id="' + x.nIdProveedor + '" ' +
                                    'data-cli="0"' +
                                    '>';
                            }
                            $('#dlProveedores').html(js);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdProveedor').val('');
                }
            },

            onKeydown: function(e) {
                function asignaProveedor(d) {
                    appSelProveedor.buscar = false;
                    $('#nIdProveedor').val(d.nIdSucursal);
                    $('#nIdCliente').val(d.nIdCliente);
                    $('#nIdDirEntrega').val(d.nIdDirEntrega);
                    $('#dlProveedores0').val(d.sDescripcion);
                };

                function buscaCodProveedor(id) {
                    $.post(baseURL + '/proveedor/leeRegistro/' + id, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Proveedor no encontrado', 'provmovtto', 1100);
                        } else {
                            nId = data.registro.nIdProveedor;
                            nCli = 0;
                            sDes = data.registro.sNombre;
                            nDir = 0;
                            asignaProveedor({
                                nIdSucursal: nId,
                                nIdCliente: nCli,
                                sDescripcion: sDes,
                            });
                            $('#frmBuscaProveedor')[0].submit();
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                if ((e.which == 13 || e.which == 9) && e.target.value) {
                    e.preventDefault();
                    let valor = e.target.value.trim();
                    if (/^\d+$/.test(valor) === true) {
                        // solo numero
                        buscaCodProveedor(valor);
                    } else {
                        let elementoEncontrado = false;
                        $('#dlProveedores option').each((i, el) => {
                            if (elementoEncontrado === false) {
                                elementoEncontrado = el;
                            } else {
                                if (el.value == e.target.value) {
                                    elementoEncontrado = el;
                                    return false;
                                }
                            }
                        });
                        if (elementoEncontrado) {
                            asignaProveedor({
                                nIdSucursal: $(elementoEncontrado).data('id'),
                                nIdCliente: $(elementoEncontrado).data('cli'),
                                sDescripcion: $(elementoEncontrado).val(),
                            });
                            $("#frmBuscaProveedor")[0].submit();
                        }
                    }
                }
            },
        };

        const appmovto = {
            urlAnterior: '<?= $hisBack ?>',
            init: function() {
                $('#btnBack').on('click', function(e) {
                    if (appmovto.urlAnterior == '0')
                        history.back();
                    else
                        location.replace(appmovto.urlAnterior);
                });
            }

        };

        let imprime = <?= $bPrint  ?>;
        if (imprime == true) {
            $('#mainCnt').addClass('container-fluid').removeClass('container')[0].style.height = 'auto';
            $('#mainCnt > div').removeClass('row');
            $('footer').remove()
            window.print();
            history.back();
        } else {
            appSelProveedor.init();
            valCantidad.init();
            valImporte.init();
            selArticulo.init();
            //chkModoEnv.init();
            movTabla.init();
            buscaEnv.init();
            appmovto.init();
        }
        $('#dlArticulos0').select();
    });
</script>