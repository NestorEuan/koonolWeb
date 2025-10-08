<?php
$bPrint = isset($printerMode) ? 'true' : 'false';

$dlProveedor =  $cliente['sNombre'] ?? '';
$nIdSucursal = $registro['nIdSucursal'] ?? 0;

$operacion = 'ventacancela';
$aKeys = [
    'ventacancela' => ['nIdVentas', 'dtAlta', 'del cliente'],
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
                <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" width="50" class="d-inline-block align-text-top position-absolute start-0 top-0">
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
                    <div id="wAlert">
                        <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-3 " style="z-index:1;">
                <table class="<?= $tblClass ?>" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <th class="text-center" scope="col">Solicitado</th>
                            <th class="text-center" scope="col">Surtido</th>
                            <th class="text-center" scope="col">Por surtir</th>
                            <th class="text-center" scope="col">Precio unitario</th>
                            <th class="text-center" scope="col">Importe a devolver</th>
                            <th class="text-center" scope="col"></th>
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
                                $nDevolver = $r['nPorEntregar'] * $r['nPrecio'];
                                ?>
                                <tr>
                                    <th scope="row"> <?= ++$nCont ?> </th>
                                    <td> <?= $r['nomArt'] ?> </td>
                                    <td class="text-end pe-5"> <?= $r['nCant'] ?> </td>
                                    <td class="text-end pe-5"> <?= $r['nCant'] - $r['nPorEntregar'] ?> </td>
                                    <td class="text-end pe-5"> <?= $r['nPorEntregar'] ?> </td>
                                    <td class="text-end pe-5"> <?= $r['nPrecio'] ?> </td>
                                    <td class="text-end pe-5"> <?= $nDevolver ?> </td>
                                    <td>
                                        <input class="form-check-input" type="checkbox" data-id="chk-<?= $nKey ?>" data-devolver="<?= $nDevolver ?>" id="chk-<?= $nKey ?>" value=<?= $nKey ?> name="devolver[<?= $nCont - 1 ?>]"  checked>
                                    </td>
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
                    <form action='<?= base_url("ventas/verRemision/$registro[$masterkey]"); ?>' method="post" id="frmInfoCancela" autocomplete="off">

                        <?php generaCampoTexto("nIdVentas", $error ?? false, 'input', $registro ?? null, 'E', '','readonly'); ?>
                        <div class="col">
                            <label for="dtCancela" class="form-label">F. de cancelación</label>
                            <div class="input-group ">
                                <?php generaCampoTexto("dtCancela", $error ?? false, 'date', $registro ?? null, 'E', '','readonly'); ?>
                            </div>
                        </div>
                        <div class="col">
                            <label for="fADevolver" class="form-label">Ingreso a saldo a favor</label>
                            <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                            <?php generaCampoTexto('fADevolver', $error ?? false, 'input', $registro ?? null, 'E', '','readonly'); ?>
                        </div>
                        <div class="col">
                            <label for="sMotivoCancela" class="form-label">Motivo de cancelación</label>
                            <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                            <?php generaCampoTexto('sMotivoCancela', $error ?? false, 'textarea', $registro ?? null, 'E'); ?>
                        </div>
                        <div class="text-center my-1">
                            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Salir</button>
                            <!-- button class="btn btn-sm btn-outline-success" < ?= $attrBtnCompletar ?> style="width:80px;" < ?= count($registros) ? '' : 'disabled' ?> -->
                            <button class="btn btn-sm btn-outline-success me-3" id="btnGuardar" data-mod-msj="Guardar la información!!">
                                <!-- button class="btn btn-sm btn-outline-success me-3" id="btnGuardar" -->
                                Aceptar
                            </button>
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

        const appInfoCancel = {
            buscar: true,
            observacion: '',
            fecha: '',
            cmd: '',
            frmDestino: '',
            init: function() {
                $('#tbl input[id^="chk-"]')
                    .on('click', appInfoCancel.chkclic);
                $("#btnGuardar").on('click', appInfoCancel.enviar);
            },
            btnGuardar: false,
            enviar: function(e) {
                e.preventDefault();
                if (appInfoCancel.btnGuardar) return;
                miGlobal.toggleBlockPantalla('Cancelando ...');
                appInfoCancel.btnGuardar = true;
                appInfoCancel.frmDestino = '#frmInfoCancela';
                let f = $(appInfoCancel.frmDestino)[0];
                $('#tbl').find('input').appendTo(appInfoCancel.frmDestino);
                //window.alert('bebere');
                //f.action = appCarga.cmd;
                f.submit();
            },

            chkclic: function(e) {
                $aDevolver = 0;
                $('#tbl input[id^="chk-"]').each(function() {
                    if ($(this).is(':checked'))
                        $aDevolver += parseFloat($(this).data('devolver'));
                    //$('#tblcarga').find('input').each(function() {
                    //fPesoTotal += parseFloat($(this).val().trim() == '' || $(this).val().trim() == '.' ? 0 : $(this).val());
                });
                $("#fADevolver").val($aDevolver);
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
            appInfoCancel.init();
        }
    });
</script>