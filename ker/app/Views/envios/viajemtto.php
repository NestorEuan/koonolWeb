<?php
$aKeys = [
    'viaje' => ['nIdViaje', 'dViaje', 'del chofer'],
];
$deshabilitaCampos = ''; // ($registro['nIdChofer'] > 0 ? '' : 'disabled');
$modoedicion = $modo == 'A' || $modo == 'E' ? '' : 'readonly';
$sMov = $aKeys[$operacion][1];
?>

<style>
    #tblViaje tbody tr:hover {
        background-color: rgba(161, 191, 226, 0.501) !important;
    }
</style>

<!-- ?= var_dump($registro); ? -->
<div class="container-fluid h-100">
    <div class="row">
        <h4><?= $titulo ?></h4>
        <hr>
        <!-- ?= var_dump($registros) ? -->
    </div>
    <div class="row h-75">
        <div class="col bg-light border rounded">
            <div class="position-relative">
                <div id="wAlert">
                    <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    </div>
                </div>
            </div>
            <div class="mt-3 " style="z-index:1;">
                <div class="table-responsible">

                    <table class="table table-sm table-striped" id="tblViaje">
                        <thead>
                            <tr>
                                <th></th>
                                <th scope="col" class="text-center">ID<br>Envio/Origen/Folio<br>Fecha</th>
                                <th scope="col">F.Solicitud/<br>Venta</th>
                                <th scope="col">Solicita/Cliente</th>
                                <th scope="col">Dirección</th>
                                <th scope="col">Peso</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registros)) : ?>
                                <tr>
                                    <td colspan="7" class="fs-5 text-center">No hay
                                        registros</td>
                                </tr>
                            <?php else : ?>
                                <?php
                                $col2search = array_column($registros, 'nIdEnvio');
                                $repetidos = array_values(array_diff_assoc($col2search, array_unique($col2search)));
                                ?>
                                <?php foreach ($registros as $r) : ?>
                                    <?php
                                    $nKey = $r['nIdEnvio'];
                                    $dMov = $r['dAsignacion'];
                                    if (in_array($r['nIdEnvio'], $repetidos) && $r['nIdViaje'] != $registro['nIdViaje']) continue;
                                    $cOrigen = substr($r['cOrigen'], 0, 3);
                                    $nFolio = $cOrigen == 'ven' ? $r['nFolioRemision'] : $r['nFolioTraspaso'];
                                    $dFecDocu = $cOrigen == 'ven' ? $r['dAltaVenta'] : $r['dAltaTraspaso'];
                                    $dFecDocu = (new DateTime($dFecDocu))->format('d-m-Y');
                                    ?>
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox" data-id="<?= $nKey ?>" id="chk-<?= $nKey ?>" disabled <?= $r['selected'] === '-1' ? '' : 'checked' ?>>
                                        </td>
                                        <td class="text-center"><?= $nKey . '/' . $cOrigen . '/' . $nFolio . '<br>' . $dFecDocu ?></td>
                                        <td><?= $dMov ?></td>
                                        <td><?= $r['nIdCliente'] . ' ' . $r['sNombre'] . '<BR>' . $r['sEnvEntrega'] ?></td>
                                        <td><?= $r['sEnvDireccion'] . ' ' . $r['sEnvColonia'] ?></td>
                                        <td><?= $r['fPeso'] / 1000 > 0.49  ?  sprintf('%01.2f T', $r['fPeso'] / 1000) : $r['fPeso'] ?></td>
                                        <td>
                                            <?php if ($modo === 'V' || $modo === 'E' || $modo === 'A') : ?>
                                                <button class="btn  bi-truck  btn-link" data-bs-toggle="modal" data-bs-target="#frmMdlViaje" data-llamar="viaje/envio/<?= strtolower($modo) ?>/<?= $r['nIdEnvio'] ?? 0 ?>/<?= $r['nIdViaje'] === $registro['nIdViaje'] ? $r['nIdViaje'] : 0 ?>/<?= $r['nIdViajeEnvio'] ?? 0 ?>" data-mod-msj="Cargar al?" style="cursor:pointer;" <?= $deshabilitaCampos ?>></button>
                                                <?php if ($modo !== 'V') : ?>
                                                    <button class="btn  bi-x-lg text-danger btn-link" data-bs-toggle="modal" data-bs-target="#frmMdlViaje" data-llamar="viaje/envio/b/<?= $r['nIdEnvio'] ?? 0 ?>/<?= $r['nIdViaje'] === $registro['nIdViaje'] ? $r['nIdViaje'] : 0 ?>/<?= $r['nIdViajeEnvio'] ?? 0 ?>" data-mod-msj="Eliminar envio?" style="cursor:pointer;" <?= $deshabilitaCampos ?> <?= $r['nIdViaje'] === $registro['nIdViaje'] ? '' : 'disabled' ?>></button>
                                                <?php endif; ?>
                                            <?php elseif ($modo === 'C') : ?>
                                                <button class="btn  bi-reply-fill  text-danger btn-link" data-bs-toggle="modal" data-bs-target="#frmMdlViaje" data-llamar="viaje/envio/d/<?= $r['nIdEnvio'] ?? 0 ?>/<?= $r['nIdViaje'] === $registro['nIdViaje'] ? $r['nIdViaje'] : 0 ?>/<?= $r['nIdViajeEnvio'] ?? 0 ?>" data-mod-msj="Cargar al?" style="cursor:pointer;" <?= $deshabilitaCampos ?>></button>
                                            <?php endif; ?>
                                            <!-- button class="btn  bi-x-lg text-danger btn-link" data-bs-toggle="modal" data-bs-target="#frmMdlViaje" data-llamar="viaje/envio/b<?= '/' . $r['nIdViajeEnvio'] ?? 0 ?>/<?= $r['nIdEnvio'] ?? 0 ?>" data-mod-msj="Eliminar envio?" style="cursor:pointer;" < ?= $deshabilitaCampos ? > < ?= $r['nIdViaje'] === $registro['nIdViaje'] ? '' : 'disabled' ? > -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col" style="max-width: 300px;">
            <div class="container-fluid border rounded pt-1 bg-light">
                <div class="row mb-1">
                    <span class="col-12 fw-bold text-center">
                        <?= $registro['sDescripcion'] ?>
                    </span>
                </div>
                <form action='<?= base_url("viaje/" . strtolower($modo)) ?>' method="post" id="frmGuardaViaje" autocomplete="off">
                    <div class="col">
                        <label for="nIdChofer" class="form-label">Chofer</label>
                        <?php generaCampoTexto('nIdChofer', $error ?? false, 'select', $registro ?? null, $modo, '', $modoedicion == 'readonly' ? 'disabled' : '', $regChofer, 'sChofer'); ?>
                    </div>
                    <div class="col">
                        <label for="<?= $sMov ?>" class="form-label">Fecha de <?= $operacion ?></label>
                        <div class="input-group ">
                            <?php generaCampoTexto("$sMov", $error ?? false, 'date', $registro ?? null, $modo, '', $modoedicion); ?>
                        </div>
                    </div>
                    <div class="col">
                        <label for="sObservacion" class="form-label">Observaciones</label>
                        <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo, '', $modoedicion); ?>
                    </div>
                    <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                    <div class="row mb-1">
                        <div class="col-2">Peso</div>
                        <div class="col">
                            <input class="form-control text-end input-sm py-0" type="text" value="<?= $registro['fPeso'] / 1000 > 0.49 ? sprintf('%01.2f', $registro['fPeso'] / 1000) . ' Tons' : $registro['fPeso'] . ' Kg.' ?>" readonly>
                        </div>
                    </div>
                    <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                </form>
                <div class="text-center my-1">
                    <button class="btn btn-sm btn-outline-danger me-2 px-1" id="btnViajesBack">
                        Regresar
                    </button>
                    <?php if ($registro['nIdViaje'] === 0) : ?>
                        <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdViajeConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!" <?= empty($registros) ? 'disabled' : '' ?>>
                            Guardar viaje
                        </button>
                    <?php else : ?>
                        <?php if ($modo === 'C') : ?>
                            <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdViajeConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!">
                                Finalizar viaje
                            </button>
                        <?php elseif ($modo === 'E' || $modo === 'A') : ?>
                            <?php if ($registro['cEstatus'] === '0') : ?>
                                <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdViajeConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!">
                                    Iniciar carga
                                </button>
                                <?= $frmURL; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mdViajeConfirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Confirmar acción</h3>
                <hr>
                <p>Confirma borrar el registro?</p>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnMdlCancelar">No</button>
                    <button type="button" class="btn btn-primary" id="btnMdlConfirmar">Si</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="" method="post" id="frmViaje" style="display:none">
    <input type="hidden" name="idDummyViaje" value="" />
</form>

<?= generaModalGeneral('frmMdlViaje', 'modal-xl modal-dialog-scrollable') ?>

<script type="text/javascript">
    const tblViaje = {
        init: function() {
            $('#frmMdlViaje').on('show.bs.modal', tblViaje.modal);
            $('#frmMdlViaje').on('shown.bs.modal', tblViaje.modalF);
        },
        modal: function(e) {
            if(
                ($("#nIdChofer").val() != <?= $registro['nIdChofer']  ?> &&  $("#nIdChofer").val() > 0) ||
                $("#<?= $sMov ?>").val() !=  '<?= $registro["$sMov"] ?>' ||
                $("#sObservacion").val() !=  '<?= $registro["sObservacion"] ?>'
            )
            $.ajax({
                url: baseURL + '/viaje/updtviaje',
                method: 'POST',
                data: $("#frmGuardaViaje").serialize(),
                dataType: 'html'
            });
            /*if(< ?= $registro['nIdChofer'] ? > > 0 && $("#nIdChofer").val() <> < ?= $registro['nIdChofer'] ? >)
                window.alert($("#nIdChofer").val() + ' ' +  < ?= $registro['nIdChofer'] ? > );
                */
            //let d = $(e.relatedTarget).data('llamar');
            $.ajax({
                url: baseURL + '/' + $(e.relatedTarget).data('llamar'),
                method: 'GET',
                data: {},
                dataType: 'html'
            }).done(function(data, textStatus, jqxhr) {
                $('#frmMdlViaje .modal-body').html(data);
            }).fail(function(jqxhr, textStatus, err) {
                console.log('fail', jqxhr, textStatus, err);
            });
        },
        modalF: function(e) {
            if (miGlobal.entregaCampo && miGlobal.entregaCampo !== '') {
                document.getElementById(miGlobal.entregaCampo).select();
            }
        },
    };

    const appViaje = {
        cmd: '',
        frmViajeDestino: '',
        init: function() {
            $("#mdViajeConfirma").on("show.bs.modal", appViaje.confirmar);
            $("#btnMdlConfirmar").on("click", appViaje.enviar);
            $('#btnViajesBack').on('click', function(e) {
                history.back();
            });
        },
        confirmar: function(e) {
            if ($('#nIdChofer').val() == '-1') {
                miGlobal.muestraAlerta('Falta seleccionar el chofer', null, 1500);
                e.preventDefault();
                return;
            }
            let a = $(e.relatedTarget);
            appViaje.cmd = a.data('llamar');
            appViaje.frmViajeDestino = a.data('frmGuardaViaje');
            if (appViaje.frmViajeDestino == null)
                appViaje.frmViajeDestino = '#frmViaje';
            $('#mdViajeConfirma div.modal-body > p').html(a.data(
                'mod-msj'));
        },
        btnGuardar: false,
        enviar: function(e) {
            if (appViaje.btnGuardar) return;
            appViaje.btnGuardar = true;
            miGlobal.toggleBlockPantalla('Espere un momento...');
            let f = $(appViaje.frmViajeDestino)[0];
            // se realiza un submit
            miGlobal.agregaCamposHidden('#frmGuardaViaje [name]', appViaje.frmViajeDestino);
            f.action = baseURL + '/' + appViaje.cmd;
            f.submit();
        }
    }
    tblViaje.init();
    appViaje.init();

    /*
    $(document).ready(function() {
        let appViaje = {
            init: function() {
                $('#btnGuardar').on('click', appViaje.enviar);
            },
            enviar: function(e) {
                $.ajax({
                    url: '< ?= $frmURL ?>',
                    method: 'POST',
                    data: $('#appViaje').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
        };
        appViaje.init();
    });
    */
</script>