<div class="container-fluid position-relative">
    <div id="viajeswAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <div class="row" id="cntTitulo">
        <div class="col">
            <h4><?= $titulo ?></h4>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-9" id="cntTablaEnvios">
            <table class="table table-borderless">
                <thead>
                    <tr class="border-3 border-dark border-top-0 border-end-0 border-start-0">
                        <th></th>
                        <th class="text-center">Folio<br>Envio</th>
                        <th class="text-center">Origen</th>
                        <th class="text-center">Folio<br>Origen</th>
                        <th class="text-center" style="width:190px;">Fechas:<br>De Alta/Solicitada</th>
                        <th class="text-center" style="width:150px;">Peso</th>
                        <th class="text-center border" style="width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cntTablaEnviosBody">
                    <?php $nPesoViaje = 0; ?>
                    <?php $nPar = true; ?>
                    <?php $nContReg = 0; ?>
                    <?php foreach ($registros as $k => $r) : ?>
                        <?php
                        $nPesoViaje += round(floatval($r['peso']), 3);
                        $nPar = !$nPar;
                        $nContReg++;
                        ?>
                        <tr class="<?= $nPar ? 'bg-secondary bg-opacity-10' : '' ?>">
                            <td><input type="checkbox" class="form-check-input" id="chkBxMarca<?= $k ?>" disabled <?= $r['marca'] == '1' ? 'checked' : '' ?>></td>
                            <td class="text-center"><?= $k ?></td>
                            <td class="text-center"><?= $r['cOrigen'] == 'ventas' ? 'Remision' : 'Traspaso' ?></td>
                            <td class="text-center"><?= $r['cOrigen'] == 'ventas' ? $r['nFolioRemision'] : $r['nFolioTraspaso'] ?></td>
                            <td class="text-center"><?= $r['fechas'] ?></td>
                            <td class="text-center"><?= round(floatval($r['peso']) / 1000, 3) > 0.49 ? sprintf('%01.2f Tons', floatval($r['peso']) / 1000) : round(floatval($r['peso']), 3) . 'Kg.'   ?></td>
                            <td class="text-center">
                                <i class="bi bi-truck text-primary me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viaje/envio/' . $modoAccionEnEnvio . '/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Seleccionar productos para viaje"></i>
                                <?php if ($tipoAccion != 's') : ?>
                                    <i class="bi bi-entrega-mano-fill text-primary me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viaje/envio/n/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Asignar para envio en otra sucursal"></i>
                                    <i class="bi bi-entrega-mano-fill3 text-primary me-2 fw-bold fs-5" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-mod-msj="Reasignar productos restantes del envio a ventas?" data-mod-titulo="Confirmar acción" data-llamar="<?= 'viaje/envio/r/' . $k . ($idViaje == '' ? '/0' : '/' . $idViaje) . '/' . ($r['cOrigen'] == 'ventas' ? $r['nIdOrigen'] : '0') ?>" style="cursor:pointer;" title="Para reasignar en ventas"></i>
                                <?php endif; ?>

                            </td>
                        </tr>
                        <tr class="border-bottom border-dark <?= $nPar ? 'bg-secondary bg-opacity-10' : '' ?>">
                            <td class="py-0"></td>
                            <td colspan="6" class="py-0">
                                <div class="row">
                                    <div class="col">
                                        <?= $r['nomCli'] . ' / ' . $r['sEnvEntrega'] ?>
                                    </div>
                                    <div class="col">
                                        <?= $r['sEnvDireccion'] . ' ' . $r['sEnvColonia'] . ' ' . $r['sEnvReferencia'] ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-3 rounded bg-light p-3">
            <div class="mb-1">
                <label class="form-sm-label">Nombre Chofer</label>
                <label class="col-form-label text-center bg-secondary rounded-2 bg-opacity-25 d-block" id="cNomChofer"><?= $cNomChofer ?></label>
            </div>

            <div class="mb-1">
                <label for="fechaProg" class="form-label">Fecha Programada</label>
                <input type="date" name="fechaProg" id="fechaProg" class="form-control" value="<?= $fechaProg ?>" <?= $modoAccionEnEnvio == 'e' ? '' : 'readonly disabled' ?>>
            </div>
            <div class="mb-1">
                <label for="sObservacionViaje" class="form-label">Observaciones</label>
                <textarea name="sObservacionViaje" id="sObservacionViaje" cols="15" rows="2" class="form-control" <?= $modoAccionEnEnvio == 'e' ? '' : 'readonly disabled' ?>><?= $sObservacionViaje ?></textarea>
            </div>
            <div class="pb-1 text-center border-bottom border-dark">
                <label class="form-label text-start pe-2">Peso</label>
                <label class="col-form-label py-1 px-2 text-center bg-secondary rounded-2 bg-opacity-25" style="width:100px;" id="txtPesoTotalViaje">
                    <?= round($nPesoViaje / 1000, 3) > 0.49 ? sprintf('%01.2f Tons', $nPesoViaje / 1000) : round($nPesoViaje, 3) . 'Kg.'   ?>
                </label>
            </div>
            <?php if ($tipoAccion == 'e' || $tipoAccion == 'a') : ?>
                <div class="d-flex justify-content-around align-items-center pt-2">
                    <button type="button" class="btn btn-outline-secondary" id="btnBack">Regresar</button>
                    <button type="button" class="btn btn-outline-primary" id="btnGuardarViaje">Guardar Viaje</button>
                </div>
            <?php elseif ($tipoAccion == 'c') : ?>
                <div class="d-flex justify-content-around align-items-center pt-2">
                    <button type="button" class="btn btn-outline-primary" id="btnBack">Regresar</button>
                </div>
            <?php elseif ($tipoAccion == 's') : ?>
                <div class="row pt-2">
                    <?php if ($nContReg > 0) : ?>
                        <button type="button" class="col-12 btn btn-outline-primary mb-2" id="btnImprimirAsignarViaje">
                            Imprimir y Asignar Viaje Para Cargar
                        </button>
                    <?php endif; ?>
                    <button type="button" class="col-12 btn btn-outline-secondary" id="btnBack">Regresar</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?= generaModalGeneral('frmModal', 'modal-xl modal-dialog-scrollable') ?>
<div class="modal fade" id="mdConfirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Confirmar</h3>
                <hr>
                <p>mensaje</p>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnNoConfirmar">No</button>
                    <button type="button" class="btn btn-primary" id="btnSiConfirmar">Si</button>
                </div>
            </div>
        </div>
    </div>
</div>
<form action="" method="post"></form>

<script type="text/javascript">
    $(document).ready(function() {
        let appViaje = {
            arrEnvio: [],
            init: function() {
                $('#frmModal').on('show.bs.modal', appViaje.procesaEnvio);
                $('#mainCnt > div.row').addClass('h-100');

                $('#btnBack').on('click', () => {
                    history.back();
                });
                $('#btnGuardarViaje').on('click', appViaje.validaDatos);
                $('#btnImprimirAsignarViaje').on('click', appViaje.asignaViaje);

                let ele = $('#cntTablaEnvios')[0];
                ele.style.height = ($('#mainCnt').height() - $('#cntTitulo').height()).toString() + 'px';
                ele.style.overflow = "auto";
            },

            procesaEnvio: function(e) {
                let sTarget = $(e.relatedTarget).data('bs-target') + ' .modal-body';
                $.ajax({
                    url: baseURL + '/' + $(e.relatedTarget).data('llamar'),
                    method: 'GET',
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $(sTarget).html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },

            procesando: false,
            validaDatos: function() {
                let conta = $('#cntTablaEnvios input[type="checkbox"]:checked').length;
                if (conta == 0) {
                    miGlobal.muestraAlerta('No se ha seleccionado ningun envio para el viaje', 'viajes', 1500);
                    return false;
                }
                if ($('#fechaProg').val() == '') {
                    miGlobal.muestraAlerta('Falta la fecha del viaje', 'viajes', 1500);
                    return false;
                }

                if (appViaje.procesando) return;
                appViaje.procesando = true;
                $('#btnGuardarViaje')[0].disabled = true;
                $.post('<?= $baseURL ?>', {
                        'observacion': $('#sObservacionViaje').val(),
                        'fecha': $('#fechaProg').val()
                    }, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        if (data.nuevo == '1')
                            location.assign(data.url + '?page=200');
                        else
                            $('#btnBack').click();
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                        appViaje.procesando = true;
                        $('#btnGuardarViaje')[0].disabled = true;
                    });

            },

            asignaViaje: function() {
                window.open('<?= $baseURLimp ?>');
                history.back();
            }

        };
        // se realiza la confirmacion pero no enviando por submit sino por ajax post
        // cuando se confirma, se recarga el detalle, 
        let appConfirma = {
            url: '',
            frmsubmit: '',
            init: function() {
                $('#mdConfirma').on('show.bs.modal', appConfirma.initDialogo);
                $('#btnSiConfirmar').on('click', appConfirma.enviar);
            },

            initDialogo: function(e) {
                let a = $(e.relatedTarget);
                let titulo = a.data('mod-titulo') ?? '';
                let msj = a.data('mod-msj') ?? '';
                let qBody = $('#mdConfirma div.modal-body');
                $('#btnSiConfirmar')[0].disabled = false;

                appConfirma.url = a.data('llamar');
                appConfirma.frmsubmit = a.data('frmsubmit') ?? '';
                if (titulo != '') qBody.find('> h3').html(titulo);
                if (msj != '') qBody.find('> p').html(msj);
            },
            enProceso: false,
            enviar: function(e) {
                if (appConfirma.enProceso) return;
                appConfirma.enProceso = true;
                e.target.disabled = true;

                $.post(baseURL + '/' + appConfirma.url, {}, null, 'html').
                done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 4) === 'nooK') {
                        if (data.substr(4, 3) == 'msj') {
                            miGlobal.muestraAlerta(data.substr(7), 'viajes', 4500);
                        } else {
                            miGlobal.muestraAlerta('Error desconocido', 'viajes', 1500);
                        }
                        e.target.disabled = false;
                        appConfirma.enProceso = false;
                    } else {
                        $('#cntTablaEnviosBody').html(data);
                        $('#btnNoConfirmar').click();
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    e.target.disabled = false;
                    appConfirma.enProceso = false;
                });
                // // se realiza un submit     appConfirma.frmsubmit
                // miGlobal.agregaCamposHidden('#frmBuscaProveedor [name]', appConfirma.frmsubmit);
                // let f = $(appConfirma.frmsubmit)[0];
                // f.action = appConfirma.url;
                // miGlobal.toggleBlockPantalla('Agregando artículo...');
                // f.submit();
            }
        };

        appViaje.init();
        appConfirma.init();
    });
</script>