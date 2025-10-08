<?php
$nCont = 0;
$nRecibidos = 0;
$porpagar = 0;
//$deshabilitaCampos = $proveedor['nIdProveedor'] == 0 ? ( $operacion === 'entrada'? '' : 'disabled'): '';
$deshabilitaCampos = $modo === 'A' ? ($proveedor['nIdProveedor'] == 0 ? (in_array($operacion, array('entrada', 'salida', 'entrega')) ? '' : 'disabled') : '') : 'disabled';
$attrBtnCompletar = 'data-bs-toggle="mdConfirma" data-bs-target="#frmModal" id="btnPagar" data-llamar="' . $frmURL . '" ';

$dlProveedor =  $proveedor['sNombre'] ?? '';

$aKeys = [
    'envio'  => ['nIdEnvio', 'dAsignacion', 'del envio'],
    'salida'  => ['nIdSalida', 'dSalida', 'de la sucursal'],
    'compra'  => ['nIdCompra', 'dcompra', 'del proveedor'],
    'entrega'  => ['nIdEntrega', 'dEntrega', 'del cliente'],
    'entrada' => ['nIdEntrada', 'dEntrada', ''],
    'traspaso' => ['nIdTraspaso', 'dTraspaso', 'de la sucursal a solicitar'],
];
$masterkey = $aKeys[$operacion][0];
$datefld = $aKeys[$operacion][1];
$campoproveedor = $aKeys[$operacion][2];

?>

<style>
    #tbl tbody tr:hover {
        background-color: rgba(161, 191, 226, 0.501) !important;
    }
</style>

<div class="container-fluid h-100">
    <div class="row">
        <div class="col">
            <h4><?= $titulo ?></h4>
            <hr>
        </div>
    </div>
    <div class="row h-75">
        <div class="col bg-light px-4 pt-3 border rounded">
            <div class="position-relative">
                <div id="recepcionwAlert">
                    <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    </div>
                </div>
            </div>

            <div class="mt-3 " style="z-index:1;">
                <table class="table table-striped table-hover table-sm" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <?php if ($modo !== 'N') : ?>
                                <th class="text-center" scope="col">Solicitado</th>
                            <?php endif; ?>
                            <th class="text-center" scope="col">Recibido</th>
                            <th class="text-center" scope="col">Por recibir</th>
                            <?php if ($operacion === 'compra' && $modo === 'N') : ?>
                                <th class="text-center" scope="col">Precio</th>
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
                                $nImp = 0;
                                $nRecibidos += $r['fPorRecibir'];
                                ?>
                                <tr>
                                    <th scope="row"> <?= ++$nCont ?> </th>
                                    <td class="col-6"> <?= $r['sDescripcion'] ?> </td>
                                    <?php if ($modo !== 'N') : ?>
                                        <td class="text-end"> <?= $r['fCantidad'] ?> </td>
                                    <?php endif; ?>
                                    <td class="text-end"> <?= $r['fRecibido'] ?> </td>
                                    <td class="text-end">
                                        <input type="text" class="form-control input-sm py-0" id="art-<?= $nCont ?>" data-cantidad="<?= $r['fCantidad'] - $r['fRecibido'] ?>" data-idx=<?= $nCont ?> data-fuente="cantidad" name="<?= $operacion ?>[<?= $r['nIdArticulo'] ?>]" value="<?= $r['fPorRecibir'] ?>" <?= $r['fPorRecibir'] > 0 ? '' : 'disabled' ?>>
                                    </td>
                                    <?php if ($modo === 'N') : ?>
                                        <td class="text-end">
                                            <input type="text" class="form-control input-sm py-0" id="importe-<?= $nCont ?>" data-importe="<?= $r['fImporte'] ?>" data-idx=<?= $nCont ?> data-fuente="importe" name="<?= $operacion . 'pre' ?>[<?= $r['nIdArticulo'] ?>]" value="<?= $r['fImporte'] ?>">
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <div class="col" style="max-width: 350px;">
            <div class="container-fluid border rounded pt-3  bg-light">
                <div class="row mb-1">
                    <span class="col-12 fw-bold text-center">
                        <?= $registro['sDescripcion'] ?>
                    </span>
                </div>
                <form action='<?= base_url("$frmURL") ?>' method="post" id="frmRecepcion" autocomplete="off">
                    <?php generaCampoTexto("$masterkey", $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                    <?php generaCampoTexto('nIdSucursal', $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                    <?php generaCampoTexto('nIdProveedor', $error ?? false, 'hidden', $proveedor ?? null, $modo); ?>
                    <?php if ($operacion !== 'entrada') : ?>
                        <div class="col">
                            <label for="dlProveedores" class="form-label"><?= $operacion === 'traspaso' ? 'Solicitar a' : 'Proveedor' ?></label>
                            <div class="input-group ">
                                <input class="form-control w-75" list="dlProveedores" placeholder="Escriba nombre de la sucursal" aria-label="codigo/nombre de <?= $campoproveedor ?>" id="dlProveedores0" name="dlProveedores0" value="<?= $dlProveedor ?>" />
                                <datalist id="dlProveedores" value="<?= $dlProveedor ?>"></datalist>
                            </div>
                        </div>
                    <?php endif ?>
                    <div class="col">
                        <label for="<?= $datefld ?>" class="form-label">Fecha de <?= $operacion ?></label>
                        <div class="input-group ">
                            <?php generaCampoTexto("$datefld", $error ?? false, 'date', $registro ?? null, $modo); ?>
                        </div>
                    </div>
                    <div class="col">
                        <label for="sObservacion" class="form-label">Observaciones</label>
                        <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                        <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
                    </div>
                    <?php if ($operacion === 'compra') : ?>
                        <label for="fTotal" class="form-label">Total a pagar</label>
                        <div class="input-group ">
                            <?php generaCampoTexto("fTotal", $error ?? false, 'text', $registro ?? null, $modo); ?>
                        </div>
                    <?php endif ?>
                </form>

                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1">
                    <label class="col-7">Artículos solicitados</label><span class="col-5 text-end fw-bold"><?= $nCont ?></span>
                </div>
                <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                <div class="text-center my-1">
                    <!-- button class="btn btn-sm btn-outline-success" < ?= $attrBtnCompletar ?> style="width:80px;" < ?= count($registros) ? '' : 'disabled' ?> -->
                    <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!" <?= $nRecibidos > 0 ? '' : 'disabled' ?>>
                        <!-- button class="btn btn-sm btn-outline-success me-3" id="btnGuardar" -->
                        Guardar
                    </button>
                    <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!" <?= $nRecibidos > 0 ? '' : 'disabled' ?>>
                        <!-- button class="btn btn-sm btn-outline-success me-3" id="btnGuardar" -->
                        Finalizar recepción de nota
                    </button>
                </div>
            </div>
        </div>
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
        const movTabla = {
            init: function() {
                $('#tbl').on('keydown', movTabla.tblKeyDown);
                $('#tbl input[id^="art-"]')
                    .on('focus', movTabla.tblCellOnFocus)
                    .on('input', movTabla.tblCellInput)
                    .on('focusout', movTabla.tblCellOutFocus);
                $("#tbl input[id^='importe-']")
                    .on('focus', movTabla.tblCellOnFocus)
                    .on('input', movTabla.tblCellInput)
                    .on('focusout', movTabla.tblCellOutFocus);
            },
            modo: "<?= $modo ?>",
            tblKeyDown: function(e) {
                function siguiente(e) {
                    <?php if ($modo === 'N') : ?>
                        var sumax = 0;
                        $('#tbl input[id^="art-"]').each(function() {
                            //window.alert($("#importe-" + $(this).data("idx")).val() );
                            sumax += parseFloat($(this).val() * $("#importe-" + $(this).data("idx")).val());
                        });
                        //console.log(sumax);
                        $('#fTotal').val(sumax);
                    <?php endif; ?>
                    let t = $(e.target);

                    let suma = 1;
                    let siguiente = "#art-";

                    if (movTabla.modo == 'P') {
                        if (t.data("fuente") == "cantidad") {
                            suma = 0;
                            siguiente = "#importe-";
                        }
                    };

                    let f = (Number(t.data("idx")) + suma).toString();
                    if (f > <?= $nCont ?>)
                        f = 1;
                    //console.log(f);
                    //console.log(siguiente);
                    t.blur();
                    $(siguiente + f).select();
                };
                let t = e.target;
                if ((e.which == 13 || e.which == 9) && (t.value != movTabla.valAnt)) {
                    e.preventDefault();
                    // valida cantidad
                    if (movTabla.modo != 'N') {
                        if (parseFloat(t.value) > parseFloat($(t).data('cantidad'))) {
                            miGlobal.muestraAlerta('La cantidad a recibir no debe ser mayor a la solicitada', 'recepcion', 4000);
                            t.value = '0.00';
                        } else {
                            siguiente(e);
                        }
                    } else {
                        siguiente(e);
                    }
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                        siguiente(e);
                    }
                }
            },
            valant: '',
            tblCellOnFocus: function(e) {
                let t = e.target;
                movTabla.valant = t.value;
            },
            tblCellOutFocus: function(e) {
                let t = e.target;
                if (movTabla.modo != 'N') {
                    if (parseFloat(t.value) > parseFloat($(t).data('cantidad'))) {
                        miGlobal.muestraAlerta('La cantidad a recibir no debe ser mayor a la solicitada', 'recepcion', 4000);
                        t.value = '0.00';
                    }
                }
            },
            tblCellInput: function(e) {
                miGlobal.valNumero(e, movTabla, {
                    re: /^\d*(?:\.\d{0,3})?$/g
                });
            },
        };

        const appRecepcion = {
            cmd: '',
            frmDestino: '',
            init: function() {
                $('#mdConfirma').on('show.bs.modal', appRecepcion.confirmar);
                $('#btnGuardar00').on('click', appRecepcion.enviar);
            },
            confirmar: function(e) {
                //$(e).prop('disabled',true);
                //window.alert('shequehse');
                let a = $(e.relatedTarget);
                appRecepcion.cmd = a.data('llamar');
                appRecepcion.frmDestino = a.data('frmDestino');

                if (appRecepcion.frmDestino == null)
                    appRecepcion.frmDestino = '#frmEnvio';
                $('#mdConfirma div.modal-body > p').html(a.data(
                    'mod-msj'));
            },
            bndGuardar: false,
            enviar: function(e) {
                if (appRecepcion.bndGuardar) return;
                appRecepcion.bndGuardar = true;
                let f = $(appRecepcion.frmDestino)[0];
                $('#tbl').find('input').appendTo(appRecepcion.frmDestino);
                $('#frmRecepcion').find('input').appendTo(appRecepcion.frmDestino);
                <?php if ($operacion != 'compra') : ?>
                    $('#sObservacion').appendTo(appRecepcion.frmDestino);
                <?php endif ?>
                // se realiza un submit
                f.action = baseURL + '/' + appRecepcion.cmd;

                $.ajax({
                    url: f.action, // '< ?= $frmURL ?>',
                    method: 'POST',
                    data: $(appRecepcion.frmDestino).serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    history.back();
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
                //f.submit();
                // 
            }
        };

        appRecepcion.init();
        movTabla.init();
        /*
        valCantidad.init();
        selArticulo.init();
        appSelSucursal.init();
        */
    });

    $('#dlArticulos0').select();
</script>