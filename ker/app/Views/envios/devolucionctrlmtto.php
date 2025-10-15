<?php
$nCont = 0;
$nRecibidos = 0;
$nSolicitados = 0;
//$deshabilitaCampos = $modo === 'A' ? ($cliente['nIdCliente'] == 0 ? ($operacion === 'entrada' ? '' : 'disabled') : '') : 'disabled';
//$attrBtnCompletar = 'data-bs-toggle="mdConfirma" data-bs-target="#frmMdlCarga" id="btnPagar" data-llamar="' . $frmURL . '" ';

$aKeys = [
    'envioctrl'  => ['nIdEnvio', 'dAsignacion', 'del envio'],
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
            <h4><?= $titulo ?></h4>
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
                            <th class="text-center" scope="col">Por devolver</th>
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
                                $fDisponible = /*($r['fExistencia']) - */ ($r['fComprometido']);
                                $fMinXRecibir = $fDisponible < ($r['fPorRecibir']) ? $fDisponible : ($r['fPorRecibir']);
                                $fPeso = 0;
                                $nKey = $r['nIdArticulo'];
                                if ($r['fXRecibir'] !== NULL)
                                    //if($r['fXRecibir'] > 0)
                                    $fMinXRecibir = $r['fXRecibir'];
                                $fPeso = $r['fPeso'] * $fMinXRecibir;
                                $fPesoTotal += $fPeso;
                                $r['fPeso'] += $fPesoTotal;
                                ?>
                                <tr>
                                    <th scope="row"> <?= ++$nCont ?> </th>
                                    <td>
                                        <input class="form-check-input" type="checkbox" data-id="Chk-<?= $nKey ?>" id="chk-<?= $nKey ?>" disabled>
                                    </td>
                                    <td class="col-4"> <?= $r['sDescripcion'] ?> </td>
                                    <td class="text-end "> <?= sprintf("%0.0f", $r['fCantidad']) ?> </td>
                                    <td class="text-end "> <?= sprintf("%0.0f", $r['fRecibido']) ?> </td>
                                    <td class="text-end "> <?= sprintf("%0.0f", $fDisponible) ?> </td>
                                    <td class="col-2">
                                        <input type="text" data-id="<?= $nKey ?>" data-cantidad="<?= sprintf("%0.0f", $r['fDevolver']) ?? 0 ?>" id="arte-<?= $nCont ?>" data-peso="<?= $r['fPeso'] ?>" class="form-control text-end input-sm py-0" name="<?= $operacion ?>[<?= $nKey ?>]" value="<?= sprintf("%0.0f", $r['fDevolver'] ?? 0)  ?>" <?= $fDisponible > 0 ? ($modo == 'B' ? 'readonly' : '') : ($modo === 'D' ? '' : 'readonly') ?>>
                                        <input type="hidden" id="pesosumar-<?= $nCont ?>" class="form-control text-end input-sm py-0" name="pesosumar[<?= $nKey ?>]" value="<?= $fPeso  ?>" readonly>
                                    </td>
                                    <td class="text-end" id="peso-<?= $nCont ?>"><?= $fPeso / 1000 > 1.00 ? sprintf('%01.2f Tons', $fPeso / 1000) : $fPeso . 'Kg.' ?> </td>
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
                    <label for="sObsEnvio" class="form-label">Observaciones</label>
                    <?= generaCampoTexto('sObsEnvio', $error ?? false, 'textarea', $registro ?? null, $modo, '', $modo == 'V' ? 'disabled' : ''); ?>
                </div>
                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1">
                    <label class="col">Artículos solicitados</label><span class="col-2 text-end fw-bold"><?= $nCont ?></span>
                </div>
                <div class="row mb-1">
                    <label class="col">Peso total</label>
                    <span class="col text-end fw-bold">
                        <div id="pesototal"> <?= $fPesoTotal ?> </div>
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
                //miGlobal.toggleBlockPantalla('Agregando artículo...');
                appCargaMtto.btnGuardar = true;
                let a = $('#frmRecepcionEnvio');
                cnt = 0;
                $('#tblcarga input[id^="pesosumar-"]').each(function() {
                    //$('#tblcarga').find('input').each(function() {
                    appCargaMtto.cnt += parseFloat($(this).val().trim() == '' || $(this).val().trim() == '.' ? 0 : $(this).val());
                });
                $("#nSolicitados").val("<?= $nSolicitados; ?>");
                $("#nRecibidos").val(appCargaMtto.cnt.toString());
                appCargaMtto.cmd = baseURL + '/' + a.data('llamar');
                appCargaMtto.frmDestino = '#frmRecepcionEnvio';
                let f = $('#frmRecepcionEnvio')[0];
                miGlobal.agregaCamposHidden('#tblcarga [name]', appCargaMtto.frmDestino);
                //window.alert(baseURL + '/' + '<?= $frmURL ?>');
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
    });
</script>