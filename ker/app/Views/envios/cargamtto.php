<?php
$nCont = 0;
$nRecibidos = 0;
$nSolicitados = 0;
$modoedicion = !($modo == 'A' || $modo == 'E') ?? 'disabled';
//$deshabilitaCampos = $modo === 'A' ? ($cliente['nIdCliente'] == 0 ? ($operacion === 'entrada' ? '' : 'disabled') : '') : 'disabled';
//$attrBtnCompletar = 'data-bs-toggle="mdConfirma" data-bs-target="#frmMdlCarga" id="btnPagar" data-llamar="' . $frmURL . '" ';

$aKeys = [
    'envio'  => ['nIdEnvio', 'dAsignacion', 'del envio'],
];
$masterkey = $aKeys[$operacion][0];
$datefld = $aKeys[$operacion][1];
$campoproveedor = $aKeys[$operacion][2];
$fPesoTotal = 0;
?>

<style>
    #tblcarga tbody tr:hover {
        background-color: rgba(161, 191, 226, 0.501) !important;
    }
</style>
<!-- ?= var_dump($registro) ? -->

<div class="container-fluid h-100 px-1">
    <div class="row">
        <div class="col">
            <h4><?= $titulo . ' ' . $registro['nIdEnvio']  ?> <?= ($registro['cOrigen'] === 'ventas') ? ' - Remisión ' . $registro['nFolioRemision'] : ' - Transferencia ' . $registro['nFolioTraspaso'] ?> </h4>
            <hr>
        </div>
    </div>
    <!-- ?= var_dump($registros) ? -->
    <div class="row h-75 px-1">
        <div class="col bg-light px-1 pt-3 border rounded">
            <div class="position-relative">
                <div id="wAlert">
                    <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    </div>
                </div>
            </div>
            <div class="mt-3 " style="z-index:1;">
                <table class="table table-sm table-striped" id="tblcarga">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Env</th>
                            <th scope="col">Descripción</th>
                            <th class="text-center" scope="col">Solicitado</th>
                            <th class="text-center" scope="col">Enviado</th>
                            <th class="text-center" scope="col">Disponible</th>
                            <th class="text-center" scope="col">Por enviar</th>
                            <th class="text-center" scope="col">Peso</th>
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
                                $nSolicitados += $r['fCantidad'];
                                $nRecibidos += ($r['fPorRecibir']);
                                $fDisponible = ($r['fExistencia']) - ($r['fComprometido']) + $r['fPorRecibir'];
                                $fMinXRecibir = $fDisponible < ($r['fPorRecibir']) ? $fDisponible : ($r['fPorRecibir']);
                                $fPeso = 0;
                                $nKey = $r['nIdArticulo'];
                                if ($r['fXRecibir'] !== NULL)
                                    //if($r['fXRecibir'] > 0)
                                    $fMinXRecibir = $r['fXRecibir'];
                                $fPeso = $r['fPeso'] * $fMinXRecibir;
                                $fPesoTotal += $fPeso;
                                //$r['fPeso'] = $fPeso;
                                ?>
                                <tr>
                                    <th scope="row"> <?= ++$nCont ?> </th>
                                    <td>
                                        <input class="form-check-input" type="checkbox" data-id="modEnv-<?= $nKey ?>" id="chk-<?= $nKey ?>" <?= $modoedicion ?> <?= $r['cModoEnv'] == '1' ? 'checked' : '' ?> value="<?= $nKey ?>">
                                    </td>
                                    <td class="col-4"> <?= $r['sDescripcion'] ?> </td>
                                    <td class="text-end "> <?= sprintf("%0.0f", $r['fCantidad']) ?> </td>
                                    <td class="text-end "> <?= sprintf("%0.0f", $r['fRecibido']) ?> </td>
                                    <td class="text-end "> <?= sprintf("%0.0f", $fDisponible) ?> </td>
                                    <td class="col-2">
                                        <?php if ($modo === 'D') : ?>
                                            <input type="text" data-id="<?= $nKey ?>" data-cantidad="<?= sprintf("%0.0f", $r['fDevolver']) ?? 0 ?>" id="arte-<?= $nCont ?>" data-peso="<?= $r['fPeso'] ?>" class="form-control text-end input-sm py-0" name="<?= $operacion ?>[<?= $nKey ?>]" value="<?= sprintf("%0.0f", $r['fDevolver'] ?? 0)  ?>" <?= $fDisponible > 0 ? ($modo == 'B' ? 'readonly' : '') : ($modo === 'D' ? '' : 'readonly') ?>>
                                            <input type="hidden" id="pesosumar-<?= $nCont ?>" class="form-control text-end input-sm py-0" name="pesosumar[<?= $nKey ?>]" value="<?= $fPeso  ?>" readonly>
                                        <?php else : ?>
                                            <input type="text" data-id="<?= $nKey ?>" data-cantidad="<?= sprintf("%0.0f", $r['fCantidad']  - $r['fRecibido']) ?>" id="arte-<?= $nCont ?>" data-peso="<?= $r['fPeso'] ?>" class="form-control text-end input-sm py-0" name="<?= $operacion ?>[<?= $nKey ?>]" value="<?= sprintf("%0.0f", $fMinXRecibir)  ?>" <?= $fDisponible > 0 ? ($modo == 'B' ? 'readonly' : '') : ($modo === 'D' ? '' : 'readonly') ?>>
                                            <input type="hidden" id="pesosumar-<?= $nCont ?>" class="form-control text-end input-sm py-0" name="pesosumar[<?= $nKey ?>]" value="<?= $fPeso  ?>" readonly>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div id="peso-<?= $nCont ?>">
                                            <?= $fPeso / 1000 > 0.49 ? sprintf('%01.2f Tons', $fPeso / 1000) : $fPeso . 'Kg.' ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col" style="max-width: 300px;">
            <div class="row mb-1">
                <span class="col-12 fw-bold text-center">
                    <?= $registro['sDescripcion'] ?>
                </span>
            </div>
            <form action='<?= base_url("$frmURL") ?>' method="post" id="frmRecepcionEnvio" autocomplete="off">
                <?php generaCampoTexto("$masterkey", $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                <?php generaCampoTexto('nIdSucursal', $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                <?php generaCampoTexto('nIdCliente', $error ?? false, 'hidden', $cliente ?? null, $modo); ?>
                <?php generaCampoTexto('nSolicitados', $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                <?php generaCampoTexto('nRecibidos', $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                <div class="col">
                    <label for="nIdCliente" class="form-label">Enviar a</label>
                    <div class="input-group ">
                        <textarea class="form-control" readonly><?= $cliente['sNombre'] . '  ' . $cliente['sDireccion'] ?></textarea>
                    </div>
                </div>
                <div class="col">
                    <label for="<?= $datefld ?>" class="form-label">Fecha de <?= $operacion ?></label>
                    <div class="input-group ">
                        <?= generaCampoTexto("$datefld", $error ?? false, 'date', $registro ?? null, 'B'); ?>
                    </div>
                </div>
                <div class="col">
                    <label for="sObservacion" class="form-label">Observaciones</label>
                    <?= generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo, '', $modoedicion); ?>
                </div>
                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1">
                    <label class="col">Artículos solicitados</label>
                    <span class="col-2 text-end fw-bold"><?= $nCont ?></span>
                </div>
                <div class="row mb-1">
                    <?php $pesotot = $fPesoTotal / 1000 > 0.49 ? sprintf('%01.2f', $fPesoTotal / 1000) . ' Tons' : $fPesoTotal . ' Kg.'; ?>
                    <label class="col">Peso total</label><span class="col text-end fw-bold">
                        <div id="pesototal"> <?= $pesotot ?> </div>
                    </span>
                    <input type="hidden" id="fPesoTotal" name="fPesoTotal" value=<?= $fPesoTotal ?>>
                </div>
            </form>
            <div class="text-center my-1">
                <button class="btn btn-sm btn-outline-danger me-3" data-bs-dismiss="modal" id="btnBack00">
                    Regresar
                </button>
                <?php if ($modo !== 'V') : ?>
                    <button class="btn btn-sm btn-outline-success me-3" data-mod-msj="Guardar la información!!" id='btnCargaMttoSave'>
                        Guardar
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let appTblCarga = {
            init: function() {
                $("#tblcarga").on("keydown", appTblCarga.tblKeyDown);
                $("#tblcarga input[id^='arte-']")
                    .on("focus", appTblCarga.tblCellOnFocus)
                    .on("input", appTblCarga.tblCellInput)
                    .on("focusout", appTblCarga.tblCellOutFocus);

            },
            tblKeyDown: function(e) {
                function siguiente(e) {
                    var sumax = 0;
                    $('#tblcarga input[id^="pesosumar-"]').each(function() {
                        sumax += parseFloat($(this).val().trim() == '' || $(this).val().trim() == '.' ? 0 : $(this).val());
                    });
                    $("#pesototal").text(sumax / 1000 > 0.49 ? sumax / 1000 + " tons" : sumax + " Kg.");
                    $("#fPesoTotal").val(sumax);
                    let t = $(e.target);
                    let arrId = t.attr("id").split("-");
                    let act = arrId[1];
                    let nNext = parseInt(act) + 1
                    if (nNext > <?= $nCont ?>)
                        nNext = 1;
                    t.blur();
                    $("#arte-" + nNext).select();
                };
                let t = $(e.target);
                //console.log(t);
                if ((e.which == 13 || e.which == 9)) {
                    e.preventDefault();
                    let arPeso = t.attr('id').split("-");
                    let peso = t.val() * t.data("peso");
                    console.log(arPeso);
                    console.log(peso);
                    $("#pesosumar-" + arPeso[1]).val(peso);
                    $("#peso-" + arPeso[1]).text(peso / 1000 > 0.49 ? peso / 1000 + " tons" : peso + " Kg.");
                    siguiente(e);
                };

            }

        };

        let appCargaMtto = {
            cmd: '',
            frmDestino: '',
            cnt: 0,
            btnGuardar: false,
            init: function() {
                $('#btnCargaMttoSave').on('click', appCargaMtto.enviar);
            },
            enviar: function(e) {
                if (appCargaMtto.btnGuardar) return;
                miGlobal.toggleBlockPantalla('Guardando viaje...');
                appCargaMtto.btnGuardar = true;
                let a = $('#frmRecepcionEnvio');
                cnt = 0;
                $("#nSolicitados").val("<?= $nSolicitados; ?>");
                $("#nRecibidos").val(appCargaMtto.cnt.toString());
                appCargaMtto.cmd = baseURL + '/' + a.data('llamar');
                appCargaMtto.frmDestino = '#frmRecepcionEnvio';
                let f = $('#frmRecepcionEnvio')[0];
                miGlobal.agregaCamposHidden('#tblcarga [name]', appCargaMtto.frmDestino);
                $('#tblcarga input[id^="chk-"]').each(function() {
                    //$(appCargaMtto.frmDestino).append('<input type="checkbox" name="' + $(this).name +
                    //    '" value="' + $(this).val() + '">' );
                    window.alert($(this).name);
                    //$('#tblcarga').find('input').each(function() {

                    //appCargaMtto.cnt += parseFloat($(this).val().trim() == '' || $(this).val().trim() == '.' ? 0 : $(this).val());
                });
                $('#tblcarga input[id^="pesosumar-"]').each(function() {
                    //$('#tblcarga').find('input').each(function() {
                    appCargaMtto.cnt += parseFloat($(this).val().trim() == '' || $(this).val().trim() == '.' ? 0 : $(this).val());
                });
                $.ajax({
                    url: baseURL + '/' + '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#frmRecepcionEnvio').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    //window.alert(data);
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmRecepcionEnvio .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
        };

        appCargaMtto.init();
        appTblCarga.init();
    });
</script>