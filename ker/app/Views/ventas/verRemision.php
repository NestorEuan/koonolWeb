<?php

$bPrint = isset($printerMode) ? 'true' : 'false';

$dlProveedor =  $cliente['sNombre'] ?? '';
$nIdSucursal = $registro['nIdSucursal'] ?? 0;

$operacion = 'ventadevolucion';
$aKeys = [
    'ventadevolucion' => ['nIdVentas', 'dtAlta', 'del cliente'],
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
$nCont = 0;
$modo = 'B';
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
                <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain'] ?>" alt="" width="50" class="d-inline-block align-text-top position-absolute start-0 top-0">
            </div>

            <div class="col-6">
                <h4><?= $titulo ?></h4>
            </div>

            <div class="col-3">
                <?= $registro['sNombre'] ?>
            </div>
        </div>
        <div class="col">
            <div class="<?= $borderClass ?>">
                <div>
                    <?php if (!in_array($operacion, array('entrada', 'salida'))) : ?>
                        <div class="col">
                            <?= ' <B>' . $cliente['sNombre'] . '</B>' ?>
                        </div>
                    <?php endif ?>
                    <div class="col">
                        Fecha de venta <B> <?= $operacion . ' ' . $registro["$datefld"] ?> </B>
                    </div>
                    <div class="col">
                        Observaciones: <B> <?= $cliente['sNombre'] ?> </B>
                    </div>
                </div>

                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1">
                    <label class="col-7">Artículos solicitados</label><span class="col-5 text-end fw-bold"><?= $nCont ?></span>
                </div>
                <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
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
                    <div id="devolucionwAlert">
                        <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!-- ?= var_dump($registros); ? -->
            <?php
            $nDiferenciaCantcontrafDevuelto = 0;
            ?>
            <div class="mt-3 " style="z-index:1;">
                <table class="<?= $tblClass ?>" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <th class="text-center" scope="col">Cantidad</th>
                            <th class="text-center" scope="col">Devuelto</th>
                            <th class="text-center" scope="col">Por devolver</th>
                            <th class="text-center" scope="col">Precio unitario</th>
                            <th class="text-center" scope="col">Importe a devolver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="7" class="fs-5 text-center">No hay
                                    registros</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php
                                $nImp = 0; // round(floatval($r[3]) * floatval($r[2]), 2);
                                $nKey = $r['nIdArticulo'];
                                $nDevolver = 0 * $r['nPrecio']; /* $r['nPorEntregar'] */
                                ?>
                                <tr>
                                    <th scope="row"> <?= ++$nCont ?> </th>
                                    <td> <?= $r['nomArt'] ?> </td>
                                    <td class="text-end col-1"> <?= $r['nCant'] ?> </td>
                                    <td class="text-end col-1"> <?= $r['fDevuelto'] ?> </td>
                                    <?php $nDiferenciaCantcontrafDevuelto += round(floatval($r['nCant']) - floatval($r['fDevuelto']), 3); ?>
                                    <td class="text-end col-1">
                                        <input type="text" class="form-control input-sm py-0" id="fDevuelto-<?= $nCont ?>" data-cantidad="<?= $r['nCant'] ?>" data-devuelto="<?= $r['fDevuelto'] ?>" data-idx=<?= $nCont ?> data-fuente="cantidad" name="devolucion[<?= $r['nIdArticulo'] ?>]" value="0" <?= $r['nCant'] - $r['fDevuelto'] > 0  ? '' : 'disabled' ?>>
                                        <!-- value="< ?= $r['nCant'] - $r['fDevuelto'] ? >" -->
                                        <input type="hidden" class="form-control input-sm py-0" id="nPrecio-<?= $nCont ?>" data-cantidad="<?= $r['nCant'] ?>" data-devuelto="<?= $r['fDevuelto'] ?>" data-idx=<?= $nCont ?> data-fuente="precio" name="devolprecio[<?= $r['nIdArticulo'] ?>]" value="<?= $r['nPrecio'] ?>">

                                        <!-- < ?php generaCampoTexto('fDevuelto-^fDevuelto' . $nCont, $error ?? false, null, $r ?? null, 'E', "form-control py-0", "data-idx = $nCont data-cantidad = {$r['nCant']} "); ?>
                                        -->
                                    </td>
                                    <td class="text-end col-1"> <?= $r['nPrecio'] ?> </td>
                                    <td class="text-end col-1"> <?= $nDevolver ?> </td>
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
                    <div class="row mb-1">
                        <span class="col-12 fw-bold text-center">
                            <?= $registro['sNombre'] ?>
                        </span>
                    </div>

                    <?php generaCampoTexto('nIdCliente', $error ?? false, 'hidden', $cliente ?? null, $modo); ?>

                    <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                    <div class="row mb-1">
                        <label class="col-7">Artículos solicitados</label><span class="col-5 text-end fw-bold"><?= $nCont ?></span>
                    </div>
                    <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                </div>
                <div class="container-fluid border rounded  bg-light">
                    <form action='<?= base_url("devolucion/$registro[$masterkey]"); ?>' method="post" id="frmInfoCancela" autocomplete="off">

                        <?php generaCampoTexto("nIdVentas", $error ?? false, 'input', $registro ?? null, 'E', '', 'readonly'); ?>
                        <div class="col">
                            <label for="dtDevolucion" class="form-label">F. de devolución</label>
                            <div class="input-group ">
                                <?php generaCampoTexto("dtDevolucion", $error ?? false, 'date', $registro ?? null, 'E', '', 'readonly'); ?>
                            </div>
                        </div>
                        <div class="col">
                            <label for="fADevolver" class="form-label">Ingreso a saldo a favor</label>
                            <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                            <?php generaCampoTexto('fADevolver', $error ?? false, 'input', $registro ?? null, 'E', '', 'readonly'); ?>
                        </div>
                        <div class="col">
                            <label for="sObservacion" class="form-label">Motivo de devolución</label>
                            <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                            <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, 'E'); ?>
                        </div>
                        <div class="text-center my-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-3" id="btnRegresar">Regresar</button>
                            <!-- button class="btn btn-sm btn-outline-success" < ?= $attrBtnCompletar ?> style="width:80px;" < ?= count($registros) ? '' : 'disabled' ?> -->
                            <?php if ($nDiferenciaCantcontrafDevuelto > 0): ?>
                                <button type="button" class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!">
                                    Guardar
                                </button>
                            <?php endif; ?>
                            <!-- button class="btn btn-sm btn-outline-success me-3" id="btnGuardar" data-mod-msj="Guardar la información!!">
                                < !-- button class="btn btn-sm btn-outline-success me-3" id="btnGuardar" -- >
                                Aceptar
                            </button .-->
                        </div>
                    </form>

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
                <p>Confirma guardar el registro?</p>
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

        const appDevolverVenta = {
            buscar: true,
            observacion: '',
            fecha: '',
            cmd: '',
            frmDestino: '',
            valant: '',
            btnRegresarPulsado: false,
            init: function() {
                $('#tbl').on('keydown', appDevolverVenta.tblKeyDown);
                $('#tbl input[id^="fDevuelto-"]')
                    .on('focus', appDevolverVenta.recepcionOnFocus)
                    .on('input', appDevolverVenta.recepcionInput)
                    .on('focusout', appDevolverVenta.recepcionFocusOut);
                $('#mdConfirma').on('show.bs.modal', appDevolverVenta.confirmar);
                $("#btnGuardar00").on('click', appDevolverVenta.enviar);
                $('#btnRegresar').on('click', () => {
                    if (appDevolverVenta.btnRegresarPulsado) return;
                    appDevolverVenta.btnRegresarPulsado = true;
                    history.back();
                });
            },
            tblKeyDown: function(e) {
                function siguiente(e) {
                    let t = $(e.target);
                    let siguiente = "#fDevuelto-";
                    let f = (Number(t.data('idx')) + 1).toString();
                    if (f > <?= $nCont ?>) f = 1;
                    t.blur();
                    $(siguiente + f).select();
                };
                let t = e.target;
                if ((e.which == 13 || e.which == 9) /*&& (t.value != appDevolverVenta.valant )*/ ) {
                    e.preventDefault();
                    siguiente(e);
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                        siguiente(e);
                    }
                }
            },
            recepcionInput: function(e) {
                miGlobal.valNumero(e, appDevolverVenta, {
                    re: /^\d*(?:\.\d{0,3})?$/g
                });
            },
            recepcionFocusOut: function(e) {
                let t = e.target;

                //console.log( t.value );
                //console.log($(t).data('cantidad'));
                //console.log($(t).data('devuelto'));

                if (parseFloat(t.value) > parseFloat($(t).data('cantidad')) - parseFloat($(t).data('devuelto'))) {
                    miGlobal.muestraAlerta('La cantidad a devolver no debe ser mayor al saldo  de la venta', 'devolucion', 4000);
                    t.value = '0.00';
                }

            },
            confirmar: function(e) {
                let a = $(e.relatedTarget);
                appDevolverVenta.cmd = a.data('llamar');
                appDevolverVenta.frmDestino = a.data('frmDestino');
                if (appDevolverVenta.frmDestino == null)
                    appDevolverVenta.frmDestino = '#frmEnvio';
                $('#mdConfirma div.modal-body > p').html(a.data(
                    'mod-msj'));
            },
            btnGuardar: false,
            enviar: function(e) {
                e.preventDefault();
                if (appDevolverVenta.btnGuardar) return;
                miGlobal.toggleBlockPantalla('Cancelando ...');
                appDevolverVenta.btnGuardar = true;
                appDevolverVenta.frmDestino = '#frmInfoCancela';
                let f = $(appDevolverVenta.frmDestino)[0];
                $('#tbl').find('input').appendTo(appDevolverVenta.frmDestino);
                //f.action = appCarga.cmd;
                f.submit();
            },

        };

        let imprime = <?= $bPrint  ?>;
        if (imprime == true) {
            $('#mainCnt').addClass('container-fluid').removeClass('container')[0].style.height = 'auto';
            $('#mainCnt > div').removeClass('row');
            $('footer').remove()
            window.print();
            history.back();
        } else {
            appDevolverVenta.init();
        }
    });
</script>