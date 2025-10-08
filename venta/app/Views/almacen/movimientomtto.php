<?php
$bPrint = isset($printerMode) ? 'true' : 'false';
$nCont = 0;
$fTotal = 0;
$fSubTotal = 0;
$fIVA = 0;
$pago['completar'] = 0;
$deshabilitaCampos = $modo === 'A' ? ($proveedor['nIdProveedor'] == 0 ? (in_array($operacion, array('entrada', 'salida')) ? '' : 'disabled') : '') : 'disabled';
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
// solo para salidas
if (isset($printerMode))
    $lstTipoSalida = [
        '0' => 'Sin Tipificacion',
        '1' => 'Conversion',
        '2' => 'Merma',
        '3' => 'Consumo',
    ];
else {
    if ($modo == 'C')
        $lstTipoSalida = [
            ['cTipoSalida' => '0', 'sDes' => 'Sin Tipificacion'],
            ['cTipoSalida' => '1', 'sDes' => 'Conversion'],
            ['cTipoSalida' => '2', 'sDes' => 'Merma'],
            ['cTipoSalida' => '3', 'sDes' => 'Consumo'],
        ];
    else
        $lstTipoSalida = [
            ['cTipoSalida' => '1', 'sDes' => 'Conversion'],
            ['cTipoSalida' => '2', 'sDes' => 'Merma'],
            ['cTipoSalida' => '3', 'sDes' => 'Consumo'],
        ];
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
                <?php if ($operacion == 'salida') : ?>
                    <div class="row">
                        <div class="col text-center">
                            <span class="fw-bold pe-2">Tipo Salida:</span><?= $lstTipoSalida[$registro['cTipoSalida']] ?>
                        </div>
                    </div>
                <?php endif; ?>
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
                <form action="<?= base_url('/movimiento/agregaArticulo/' . $sufijoURL) ?>" method="get" id="frm00" autocomplete="off">
                    <input type="hidden" name="nIdArticulo" id="nIdArticulo" value="">
                    <input type="hidden" name="nCosto" id="nCosto" value="">
                    <div class="input-group ">
                        <input class="form-control w-75" id="dlArticulos0" name="dlArticulos0" list="dlArticulos" placeholder="Escriba nombre o el codigo del producto" aria-label="codigo/nombre del producto" tabindex="2" <?= $deshabilitaCampos ?> />
                        <datalist id="dlArticulos"></datalist>
                        <input class="form-control text-end" type="text" placeholder="Costo" aria-label="Costo" id="fImporte" name="fImporte" tabindex="4" <?= $deshabilitaCampos ?> />
                        <input class="form-control text-end" type="text" placeholder="Cantidad" aria-label="Cantidad del producto" id="nCant" name="nCant" tabindex="3" data-llamar="ventas/" <?= $deshabilitaCampos ?> />
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
                            <?php if ($operacion === 'compra' || $operacion === 'entrada') : ?>
                                <th class="text-center" scope="col">Precio</th>
                                <th class="text-center" scope="col">Importe</th>
                            <?php endif; ?>
                            <?php if (!(isset($printerMode) || $modo !== 'A')) : ?>
                                <th scope="col">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="7" class="fs-5 text-center">No hay registros</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php
                                //echo $r[0] . '\r\n' . $r[1] . '\r\n' . $r[2] . '\r\n' . $r[3];
                                //var_dump($r);
                                $nImp = (floatval($r['fImporte']) * floatval($r['fCantidad']));
                                $fTotal += $nImp;
                                $registro['fTotal'] = $fTotal;
                                $fSubTotal = round($fTotal / 1.16, 2);
                                $fIVA = round($fSubTotal * 0.16, 2);
                                ?>
                                <tr class="<?= isset($printerMode) ? 'fs-7' : '' ?>">
                                    <th scope="row"> <?= ++$nCont ?> </th>
                                    <td> <?= $r['sDescripcion'] ?> </td>
                                    <td class="text-end pe-5"> <?= $r['fCantidad'] ?> </td>
                                    <?php if ($operacion === 'compra' || $operacion === 'entrada') : ?>
                                        <td class="text-center" scope="col"> <?= $r['fImporte'] ?></td>
                                        <td class="text-center" scope="col"> <?= $nImp ?> </td>
                                    <?php endif; ?>
                                    <?php if (!(isset($printerMode) || $modo !== 'A')) : ?>
                                        <td>
                                            <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="movimiento/borraArticulo/<?= $r['nIdArticulo'] . '/' . $sufijoURL ?>" data-mod-msj="Confirma borrar el registro?" style="cursor:pointer;"></i>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
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
                                <div for="dlProveedores" class="form-label"><?= $operacion === 'traspaso' ? 'Solicitar a' : 'Proveedor' ?></div>
                                <div class="input-group ">
                                    <input class="form-control w-75" list="dlProveedores" placeholder="Escriba nombre de la sucursal" aria-label="codigo/nombre de <?= $campoproveedor ?>" id="dlProveedores0" name="dlProveedores0" value="<?= $dlProveedor ?>" <?= $operacion === 'traspaso' && $modo == 'B'  ? 'readonly' : '' ?> />
                                    <datalist id="dlProveedores" value="<?= $dlProveedor ?>"></datalist>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($operacion == 'salida') : ?>
                            <div class="col">
                                <label for="cTipoSalida" class="form-label">Tipo de Salida</label>
                                <div class="input-group ">
                                    <?php generaCampoTexto("cTipoSalida", $error ?? false, 'select', $registro ?? null, $modo, '', $modo == 'C' ? 'disabled' : '', $lstTipoSalida); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col">
                            <label for="<?= $datefld ?>" class="form-label">F. de solicitud de <?= $operacion ?></label>
                            <div class="input-group">
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
                        <!-- 
                        <div class="row mb-1">
                            <label class="col-7">SubTotal</label><span class="col-5 text-end fw-bold">< ?= $fSubTotal ? ></span>
                        </div>
                        <div class="row mb-1">
                            <label class="col-7">I.V.A.</label><span class="col-5 text-end fw-bold">< ?= $fIVA ? ></span>
                        </div>
                        -->
                        <div class="row mb-1">
                            <div class="col-2">Total</div>
                            <div class="col-8">
                                <?php generaCampoTexto('fTotal', $error ?? false, 'input ', $registro ?? null, 'B'); ?>
                            </div>
                        </div>
                        <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                    </form>

                    <div class="text-center my-1">
                        <button class="btn btn-sm btn-outline-secondary me-3" id="btnBack">
                            Regresar
                        </button>
                        <?php if ($nCont > 0 && $modo === 'A') : ?>
                            <button class="btn btn-outline-primary btn-sm me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="/movimiento/limpiaOperacion/<?= $sufijoURL ?>" data-mod-msj="Desea reiniciar la captura?">
                                Limpiar
                            </button>
                            <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!">
                                Guardar
                            </button>
                        <?php endif; ?>
                        <?php if ($nCont > 0 && $modo === 'B') : ?>
                            <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Confirma la cancelacion del traspaso ?">
                                Cancelar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="row h-75">
                <div class="container <?= $borderClass ?>">
                    <div class="row justify-content-end text-end">
                        <div class="col-4 ">Artículos solicitados</div>
                        <div class="col-2 fw-bold"><?= $nCont ?></div>
                    </div>
                    <div class="row justify-content-end text-end">
                        <div class="col-4">Total</div>
                        <div class="col-2">
                            <?php generaCampoTexto('fTotal', $error ?? false, 'input ', $registro ?? null, 'B'); ?>
                        </div>
                    </div>
                    <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                    <div class="col">
                        <label for="sObservacion" class="form-label">Observaciones</label>
                        <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                        <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
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
                    .on('keydown', selArticulo.onKeydown)
                    .on('focusout', selArticulo.onFocusOut);
            },

            seleccionaOpcion: function(valor) {
                let b = false;
                if (valor == undefined || valor == '') {
                    return;
                }
                $('#dlArticulos option').each((i, el) => {
                    if (el.value == valor) {
                        b = el;
                        return false;
                    }
                });
                if (b) {
                    console.log(b);
                    $('#dlArticulos0').val(b.value);
                    $('#nIdArticulo').val($(b).data('id'));
                    $('#fImporte').val($(b).data('precio'));
                    $('#nCosto').val($(b).data('precio'));
                    $('#nCant').focus();
                    // $('#idProducto').val($(b).data('id'));
                } else {
                    miGlobal.muestraAlerta('Articulo no existente', "movtomtto", 1100);
                };
            },

            onFocusOut: function(e) {
                if (selArticulo.buscar === false) return;
                selArticulo.seleccionaOpcion(e.target.value);
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
                    $('#nPrecio').val('');
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
                    $('#nCant').focus();
                };

                function buscaCodArticulo(id) {
                    miGlobal.toggleBlockPantalla('Espere ...');
                    $.post(baseURL + '/articulo/leeRegistro/' + id + '/1', {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Artículo no encontrado', "movtomtto", 1100);
                            $(e.target)[0].select();
                        } else {
                            asignaArticulo(data.registro);
                        }
                        miGlobal.toggleBlockPantalla();
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                function buscaCodArticuloCad(id) {
                    miGlobal.toggleBlockPantalla('Espere ...');
                    $.post(baseURL + '/articulo/leeRegistro/' + id + '/1/1', {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Articulo no encontrado', 'movtomtto', 1100);
                            $(e.target)[0].select();
                        } else {
                            asignaArticulo(data.registro);
                        }
                        miGlobal.toggleBlockPantalla();
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                let valor = e.target.value.trim();
                if ((e.which == 13 || e.which == 9) && valor.length > 0) {
                    e.preventDefault();
                    if (valor.trim().substr(0, 1) == '*') {
                        buscaCodArticuloCad(valor.trim().substr(1));
                    } else if (/^\d+$/.test(valor) === true) {
                        // solo numero
                        buscaCodArticulo(valor);
                    } else {
                        selArticulo.seleccionaOpcion(e.target.value);
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
                });
            },
            bandCantidad: false,
            onKeydown: function(e) {
                if ((e.which == 13 || e.which == 9) && e.target.value > 0) {
                    e.preventDefault();
                    if (valCantidad.bandCantidad) return;
                    valCantidad.bandCantidad = true;
                    if ($('#nIdArticulo').val() <= 0) {
                        miGlobal.muestraAlerta('Articulo no Seleccionado', 'movtomtto', 1100);
                        valCantidad.bandCantidad = false;
                        return;
                    }
                    miGlobal.toggleBlockPantalla('Agregando artículo...');
                    let operacion = '<?= $operacion ?>';
                    if (operacion == 'traspaso' || operacion == 'salida') {
                        let sucursalida = '<?= $proveedor["nIdProveedor"] ?>';
                        if (operacion == 'salida')
                            sucursalida = '<?= $nIdSucursal ?>';
                        if (sucursalida == '') {
                            miGlobal.toggleBlockPantalla();
                            miGlobal.muestraAlerta('Falta seleccionar la sucursal', 'movtomtto', 1700);
                            $('#nCant').select();
                            valCantidad.bandCantidad = false;
                            return;
                        }

                        let id = $('#nIdArticulo').val();
                        $.post(baseURL + '/existencia/' + sucursalida + '/' + id, {}, null, 'json').
                        done(function(data, textStatus, jqxhr) {
                            //console.log(data.registro[0]);
                            if (data.ok === '0') {
                                miGlobal.muestraAlerta('No hay artículos en existencia', 'movtomtto', 1100);
                                $('#nCant').select();
                                valCantidad.bandCantidad = false;

                            } else {
                                miGlobal.agregaCamposHidden('#<?= $datefld ?>, #sObservacion', '#frm00');
                                $('#frm00')[0].submit();
                            }
                            miGlobal.toggleBlockPantalla();
                        }).
                        fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr,
                                textStatus, err);
                            valCantidad.bandCantidad = false;
                        });
                    } else {
                        miGlobal.agregaCamposHidden('#<?= $datefld ?>, #sObservacion', '#frm00');
                        $('#frm00')[0].submit();
                    }
                    //miGlobal.toggleBlockPantalla();
                }
            }
        };

        const valImporte = {
            init: function() {
                $("#fImporte")
                    .on('input', valImporte.onImporteInput)
                    .on('keydown', valImporte.onImporteKeyDown);
            },
            onImporteInput: function(e) {
                miGlobal.valNumero(e, valImporte, {
                    re: /^\d*(?:\.\d{0,3})?$/g
                })
            },
            bandCosto: false,
            onImporteKeyDown: function(e) {
                if ((e.which == 13 || e.which == 9) && e.target.value > 0) {
                    e.preventDefault();
                    // if( valImporte.bandCosto ) return;
                    valImporte.bandCosto = true;
                    $('#nCant').focus();
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
                <?php if ($operacion == 'salida') : ?>
                    if (movTabla.cmd.indexOf('movimiento/salida/') != -1) {
                        let aP1 = $('#cTipoSalida').val();
                        if (aP1 == '' || aP1 == '0' || aP1 == '-1') {
                            miGlobal.muestraAlerta('Falta seleccionar el tipo de salida', 'movtomtto', 1700);
                            movTabla.bndGuardar = false;
                            e.preventDefault();
                            return;
                        }
                    }
                <?php endif; ?>
                $('#mdConfirma div.modal-body > p').html(a.data(
                    'mod-msj'));
            },
            bndGuardar: false,
            enviar: function(e) {
                if (movTabla.bndGuardar) return;
                movTabla.bndGuardar = true;
                $('#btnGuardar00')[0].disabled = true;
                <?php if (in_array($operacion, array('entrada', 'salida')) == false) : ?>
                    let aP = $('#nIdProveedor').val();
                    if (aP == '' || aP == '0') {
                        miGlobal.muestraAlerta('Falta <?= $operacion === 'traspaso' ? 'Quien solicita' : 'el proveedor' ?>', 'movtomtto', 1700);
                        movTabla.bndGuardar = false;
                        return;
                    }
                <?php endif; ?>
                // valido a que proceso se envia
                miGlobal.agregaCamposHidden('#frmBuscaProveedor [name]', movTabla.frmDestino);
                // se realiza un submit
                let f = $(movTabla.frmDestino)[0];
                f.action = baseURL + '/' + movTabla.cmd;
                miGlobal.toggleBlockPantalla('Procesando...');
                f.submit();
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
                    $.get(baseURL + '/<?= $operacion === 'traspaso' ? 'sucursal' : 'proveedor' ?>/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            for (x of data.registro) {
                                <?php if ($operacion === 'compra') : ?>
                                    js += '<option value="' + x.sNombre + '" ' +
                                        'data-id="' + x.nIdProveedor + '" ' +
                                        'data-cli="0"' +
                                        '>';
                                <?php else : ?>
                                    js += '<option value="' + x.sDescripcion + '" ' +
                                        'data-id="' + x.nIdSucursal + '" ' +
                                        'data-cli="' + x.nIdCliente + '" ' +
                                        'data-dir="' + x.nIdDirEntrega + '" ' +
                                        '>';
                                <?php endif; ?>
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
                    <?php $deshabilitaCampos = '' ?>
                    $('#dlArticulos0').removeAttr('disabled');
                    $('#nCant').removeAttr('disabled');
                    $('#dlArticulos0').focus();
                };

                function buscaCodProveedor(id) {
                    $.post(baseURL + '/<?= $operacion === 'compra' ? 'proveedor' : 'sucursal' ?>/leeRegistro/' + id, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        console.log(data);
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Proveedor no encontrado', 'provmovtto', 1100);
                        } else {
                            <?php if ($operacion === 'compra') : ?>
                                nId = data.registro.nIdProveedor;
                                nCli = 0;
                                sDes = data.registro.sNombre;
                                nDir = 0;
                            <?php else : ?>
                                nId = data.registro.nIdSucursal;
                                nCli = data.registro.nIdCliente;
                                sDes = data.registro.sDescripcion;
                                nDir = data.registro.nIdDirCliente;
                            <?php endif; ?>
                            $('#nIdProveedor').val(nId);
                            $('#nIdCliente').val(nCli);
                            $('#nIdDirCliente').val(nDir);
                            $('#dlProveedores0').val(sDes);
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

            /*

            onObsFocusOut: function(e) {
                let valor = e.target.value.trim();
                if (e.target.value && e.target.value.trim().length > 1) {
                    if (valor == appSelProveedor.observacion) return;
                    $("#frmBuscaProveedor")[0].submit();
                }
            },

            onDateFocusOut: function(e) {
                //console.log('outfocus');
                let valor = e.target.value.trim();
                if (e.target.value && e.target.value.trim().length > 1) {
                    if (valor == appSelProveedor.fecha) return;
                    $("#frmBuscaProveedor")[0].submit();
                }
            }
            */
        };

        const appmovto = {
            modo: '<?= $modo ?>',
            init: function() {
                $('#btnBack').on('click', function(e) {
                    if (appmovto.modo == 'A')
                        location.assign('<?= $frmURListado ?>');
                    else
                        history.back();
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
            selArticulo.init();
            movTabla.init();
            appmovto.init();
        }
        /*
        appSelSucursal.init();
        */

        $('#dlArticulos0').select();
    });
</script>