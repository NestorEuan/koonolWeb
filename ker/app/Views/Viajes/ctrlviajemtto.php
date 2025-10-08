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
                        <th class="text-center" style="width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cntTablaEnviosBody">
                    <?php $nPesoViaje = 0; ?>
                    <?php $nPar = true; ?>
                    <?php foreach ($registros as $k => $r) : ?>
                        <?php
                        $nPesoViaje += round(floatval($r['peso']), 3);
                        $nPar = !$nPar;
                        ?>
                        <tr class="<?= $nPar ? 'bg-secondary bg-opacity-10' : '' ?>">
                            <td><input type="checkbox" class="form-check-input" id="chkBxMarca<?= $k ?>" disabled <?= $r['marca'] == '1' ? 'checked' : '' ?>></td>
                            <td class="text-center"><?= $k ?></td>
                            <td class="text-center"><?= $r['cOrigen'] == 'ventas' ? 'Remision' : 'Traspaso' ?></td>
                            <td class="text-center"><?= $r['cOrigen'] == 'ventas' ? $r['nFolioRemision'] : $r['nFolioTraspaso'] ?></td>
                            <td class="text-center"><?= $r['fechas'] ?></td>
                            <td class="text-center"><?= round(floatval($r['peso']) / 1000, 3) > 0.49 ? sprintf('%01.2f Tons', floatval($r['peso']) / 1000) : round(floatval($r['peso']), 3) . 'Kg.'   ?></td>
                            <td class="text-center">
                                <i class="bi bi-eye-fill text-primary me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viaje/envio/c/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Consultar productos para viaje"></i>
                                
                                <?php if($modoAccionEnEnvio == 'e' && $permisoCapDevoluciones): ?>
                                    <i class="bi bi-box-return2 text-primary me-2 fw-bold fs-5" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viajectrl/devolucion/e/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Devolucion productos del viaje"></i>
                                <?php else: ?>
                                    <?php if($r['conDevolucion'] == '1'): ?>
                                        <i class="bi bi-box-return2 text-primary me-2 fw-bold fs-5" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viajectrl/devolucion/c/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Productos Devueltos"></i>
                                    <?php endif; ?>
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
                <?php if ($tipoAccion == 's') : ?>
                    <select id="nIdChofer" name="nIdChofer" class="form-select form-select-sm">
                        <option value="0" <?= $nIdChofer == '0' ? 'selected' : '' ?>>Seleccione Chofer</option>
                        <?php foreach ($listChofer as $f) : ?>
                            <option value="<?= $f['nIdChofer'] ?>" <?= $nIdChofer == $f['nIdChofer'] ? 'selected' : '' ?>><?= $f['sChofer'] ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <label class="col-form-label text-center bg-secondary rounded-2 bg-opacity-25 d-block" id="cNomChofer"><?= $cNomChofer ?></label>
                <?php endif; ?>
            </div>

            <div class="mb-1">
                <label for="fechaProg" class="form-label">Fecha Programada</label>
                <input type="date" name="fechaProg" id="fechaProg" class="form-control" value="<?= $fechaProg ?>" readonly disabled>
            </div>
            <div class="mb-1">
                <label for="sObservacionViaje" class="form-label">Observaciones</label>
                <textarea name="sObservacionViaje" id="sObservacionViaje" cols="15" rows="2" class="form-control" readonly disabled><?= $sObservacionViaje ?></textarea>
            </div>
            <div class="pb-1 text-center border-bottom border-dark">
                <label class="form-label text-start pe-2">Peso</label>
                <label class="col-form-label py-1 px-2 text-center bg-secondary rounded-2 bg-opacity-25" style="width:100px;" id="txtPesoTotalViaje">
                    <?= round($nPesoViaje / 1000, 3) > 0.49 ? sprintf('%01.2f Tons', $nPesoViaje / 1000) : round($nPesoViaje, 3) . 'Kg.'   ?>
                </label>
            </div>
            <?php if ($tipoAccion == 's') : ?>
                <div class="d-flex justify-content-around align-items-center pt-2">
                    <button type="button" class="btn btn-outline-secondary" id="btnBack">Regresar</button>
                    <button type="button" class="btn btn-outline-primary" id="btnGuardarViaje">Viaje Cargado</button>
                </div>
            <?php elseif ($tipoAccion == 'f') : ?>
                <div class="d-flex justify-content-around align-items-center pt-2">
                    <button type="button" class="btn btn-outline-secondary" id="btnBack">Regresar</button>
                    <button type="button" class="btn btn-outline-primary" id="btnGuardarViaje">Viaje Finalizado</button>
                </div>
            <?php elseif ($tipoAccion == 'c') : ?>
                <div class="d-flex justify-content-around align-items-center pt-2">
                    <button type="button" class="btn btn-outline-primary" id="btnBack">Regresar</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?= generaModalGeneral('frmModal', 'modal-xl modal-dialog-scrollable') ?>
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
                if ($('#nIdChofer').val() == '' || $('#nIdChofer').val() == '0') {
                    miGlobal.muestraAlerta('Falta seleccionar el chofer', 'viajes', 1500);
                    $('#nIdChofer').focus();
                    return false;
                }
                if (appViaje.procesando) return;
                appViaje.procesando = true;
                $('#btnGuardarViaje')[0].disabled = true;
                $.post('<?= $baseURL ?>', {
                        'nIdChofer': $('#nIdChofer').val()
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

            }
        };
        appViaje.init();
    });
</script>