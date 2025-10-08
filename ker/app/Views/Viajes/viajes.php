<?php
$nCont = 0;
$select = set_value('inputGroupSelectOpciones', '0');
$placeholderFolio = $cOpciones[intval($select)][2];
$estatusViajes = [
    '0' => 'Programado',
    '1' => 'Asignado Para Cargar',
    '2' => 'En Transito',
    '3' => 'Finalizado'
];
?>

<div class="container bg-light mt-4 border">
    <div id="viajeswAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h4>Viajes</h4>
    <form class="row border rounded mb-3 py-2" action="<?php base_url('viajes/viaje') ?>" method="get" id="frmFiltro">
        <div class="col">
            <div class="row">
                <div class="col-4">
                    <div class="input-group">
                        <label class="input-group-text" for="inputGroupSelectOpciones">Filtrar por</label>
                        <select class="form-select" id="inputGroupSelectOpciones" name="inputGroupSelectOpciones">
                            <?php foreach ($cOpciones as $v) : ?>
                                <option value="<?= $v[1] ?>" <?= $v[1] == $select ? 'selected' : '' ?>><?= $v[0] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-5">
                    <div class="input-group <?= in_array($select, ['3', '7']) ? '' : 'd-none' ?> " id="grpRangoFec">
                        <span class="input-group-text">Rango de Fechas</span>
                        <input type="date" name="dFecIni" id="dFecIni" class="form-control" value="<?= set_value('dFecIni', $fecIni) ?>">
                        <input type="date" name="dFecFin" id="dFecFin" class="form-control" value="<?= set_value('dFecFin', $fecFin) ?>">
                    </div>
                    <input type="text" class="form-control <?= in_array($select, ['4', '5', '6']) ? '' : 'd-none' ?> w-auto" name="nFolio" id="nFolio" value="<?= set_value('nFolio', '') ?>" placeholder="<?= $placeholderFolio ?>">
                </div>
                <div class="col-3">
                    <div class="input-group justify-content-between">
                        <button type="submit" class="btn btn-primary" style="z-index:auto;" id="btnFiltrarViajes">Filtrar</button>
                        <a type="button" class="btn btn-primary" style="z-index:auto;" href="<?= base_url('viaje/a') ?>">Crear Viaje</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="row border rounded">
        <div class="table-responsive-lg">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">ID Viaje</th>
                        <th class="text-center">Fecha<br>Alta</th>
                        <th class="text-center">Fecha<br>Programada</th>
                        <th>Chofer</th>
                        <th class="text-center">Estado</th>
                        <th class="">Observacion</th>
                        <th class="text-center" style="width:160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="7" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php $nSum = 0; ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td class="text-center"><?= $r['nIdViaje'] ?></td>
                                <td class="text-center"><?= (new DateTime($r['dtAlta']))->format('d-m-Y')  ?></td>
                                <td class="text-center"><?= (new DateTime($r['dViaje']))->format('d-m-Y')  ?></td>
                                <td><?= $r['nomChofer'] ?></td>
                                <td class="text-center"><?= $estatusViajes[$r['cEstatus']] ?></td>
                                <td><?= $r['sObservacion'] ?></td>
                                <td class="text-center">
                                    <?php if ($r['cEstatus'] == '3') : ?>
                                        <a class="bi bi-eye-fill text-primary me-2 " style="cursor:pointer;" title="Consultar Viaje" href="<?= base_url('viajectrl/c/' . $r['nIdViaje']) ?>"></a>
                                    <?php else : ?>
                                        <a class="bi bi-eye-fill text-primary me-2 " style="cursor:pointer;" title="Consultar Viaje" href="<?= base_url('viaje/c/' . $r['nIdViaje']) ?>"></a>
                                    <?php endif; ?>

                                    <?php if ($r['cEstatus'] == '0') : ?>
                                        <a class="bi bi-pencil-fill text-primary me-2 " style="cursor:pointer;" title="Editar Viaje" href="<?= base_url('viaje/e/' . $r['nIdViaje']) ?>"></a>
                                    <?php else : ?>
                                        <a class="bi bi-pencil-fill text-secondary me-2 " title="Editar Viaje"></a>
                                    <?php endif; ?>
                                    <?php if (in_array($r['cEstatus'], ['0', '1']) && $r['nEnvios'] != '0') : ?>
                                        <a class="bi bi-fork-lift2 text-primary me-2 fs-5" style="cursor:pointer;" title="Imprimir/Asignar para Cargar" href="<?= base_url('viaje/s/' . $r['nIdViaje']) ?>"></a>
                                    <?php else : ?>
                                        <a class="bi bi-fork-lift2 text-secondary me-2 fs-5" title="Imprimir/Asignar para Cargar"></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const appViajes = {
            init: function() {
                // $('#frmModal').on('show.bs.modal', appViajes.agregar);
                // $('#frmModaldf').on('show.bs.modal', appViajes.agregar);
                // $('#frmModal').on('shown.bs.modal', () => {
                //     $(miGlobal.entregaCampo).select();
                // });
                $('#inputGroupSelectOpciones').on('change', appViajes.aplicaSeleccion);
                $('#frmFiltro').on('submit', appViajes.onSubmit);
                $('#nFolio').on('input', appViajes.onInput);

            },

            aplicaSeleccion: function(e) {
                $('#nFolio, #grpRangoFec').toggleClass('d-none', true);
                if (e.target.selectedIndex == -1) return;
                let v = e.target.options[e.target.selectedIndex].value;
                let textPlace = {
                    '4': 'Viaje',
                    '5': 'Remisión',
                    '6': 'Envio'
                };
                switch (v) {
                    case '0':
                    case '1':
                    case '2':
                        $('#btnFiltrarViajes').click();
                        break;
                    case '3':
                    case '7':
                        $('#grpRangoFec').removeClass('d-none');
                        break;
                    case '4':
                    case '5':
                    case '6':
                        $('#nFolio').removeClass('d-none');
                        $('#nFolio')[0].placeholder = 'Folio de ' + textPlace[v];
                        break;
                }
            },

            onSubmit: function(e) {
                let v = $('#inputGroupSelectOpciones').val();
                if (v == '4' || v == '5' || v == '6') {
                    if ($('#nFolio').val() == '') {
                        e.preventDefault();
                        miGlobal.muestraAlerta('Falta el folio', 'viajes', 1500);
                        $('#nFolio').focus();
                        return;
                    }
                } else if (v == '3' || v == '7') {
                    let s = 0;
                    if ($('#dFecIni').val() != '') s++;
                    if ($('#dFecFin').val() != '') s++;
                    if (s < 2) {
                        e.preventDefault();
                        miGlobal.muestraAlerta('Falta el rango de fechas', 'viajes', 1500);
                        $('#dFecIni').focus();
                        return;
                    }
                }
            },

            onInput: function(e) {
                miGlobal.valNumero(e, appViajes, {
                    re: /^\d*$/g
                })
            },

            agregar: function(e) {
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
            }
        };
        appViajes.init();
    });
</script>