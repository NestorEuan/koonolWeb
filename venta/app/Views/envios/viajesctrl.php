<?php

    $aKeys = [
        'viajectrl' => ['nIdViaje', 'dViaje',],
    ];
    $sKey = $aKeys[$operacion][0];
    $sMov = $aKeys[$operacion][1];
    if (isset($aWhere)) {
        $cEdo = $aWhere['Edo'];
        $dIni = $aWhere['dIni'] ?? '';
        $dFin = $aWhere['dFin'] ?? '';
    }
    if (!isset($cEdo))
        $cEdo = '1';
?>

<div class="container bg-light mt-4">
    <h5><?= $titulo ?></h5>
    <form class="row border rounded mb-0 py-1" action="<?= base_url('viajectrl/')?>" method="get">
        <div class="col-12 mb-1 col-md-4 col-lg-3">
            <div class="input-group">
                <span class="input-group-text">Estado</span>
                <select class="form-select text-center" name="cEstado" id="cEstado">
                    <option value="2" <?= $cEdo == '2' ? 'selected' : '' ?>>En tránsito</option>
                    <option value="1" <?= $cEdo == '1' ? 'selected' : '' ?>>En proceso de carga</option>
                    <option value="9" <?= $cEdo == '9' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
        </div>
        <div class="col-12 mb-1 col-md-4 col-lg-3">
            <div class="input-group">
                <span class="input-group-text w-25">Del</span>
                <input type="date" name="dIni" id="dIni" class="form-control text-center" value="<?= $dIni ?>">
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <div class="input-group">
                <span class="input-group-text w-25">Al</span>
                <input type="date" name="dFin" id="dFin" class="form-control text-center" value="<?= $dFin ?>">
            </div>
        </div>
        <div class="col-12 mt-2 text-sm-end d-flex flex-column flex-md-row col-lg mt-lg-0">
            <button type="submit" class="btn btn-secondary bg-gradient mb-1 me-md-auto">Filtrar</button>
            <button type="button" class="btn btn-primary bg-gradient mb-1">Exportar</button>
        </div>
    </form>


    <div class="row border rounded">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>F.Viaje</th>
                    <th>Chofer</th>
                    <th>Estado</th>
                    <th>Observación</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="bodyTabla">
                <?php if (empty($registros)) : ?>
                    <tr>
                        <td colspan="5" class="fs-5 text-center">No hay registros</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($registros as $r) : ?>
                        <?php
                        $nKey = $r[$sKey];
                        $dMov = $r[$sMov];
                        ?>
                        <tr>
                            <td><?= $nKey ?></td>
                            <td><?= $dMov ?></td>
                            <td><?= $r['nIdChofer'] . ' ' . $r['sChofer'] ?></td>
                            <td><?= $r['cEstatus'] == '1' ? 'En proceso de carga' : 'En tránsito' ?></td>
                            <td><?= $r['sObservacion'] ?></td>

                            <!-- td>< ?= $r['fTotal'] ? ></td>
                            <td>< ?= $r['nProductos']? ></td -->
                            <td>
                                <a href="<?php echo base_url(); ?>/viajectrl/v/<?php echo $nKey ?>" style="text-decoration: none;">
                                    <i class="bi bi-eye-fill" style="cursor:pointer; font-size: 1.2rem;"></i>
                                </a>
                                <?php if ($r['cEstatus'] == '0' || $r['cEstatus'] == '1') : ?>
                                    <a href="<?php echo base_url(); ?>/viajectrl/i/<?php echo $nKey ?>" style="text-decoration: none;">
                                        <i class="bi bi-truck-flatbed text-primary me-3" style="cursor:pointer; font-size: 1.2rem;"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($r['cEstatus'] == '2') : ?>
                                <a href="<?php echo base_url(); ?>/viajectrl/c/<?php echo $nKey ?>" style="text-decoration: none;">
                                    <i class="bi bi-clipboard2-check " style="cursor:pointer; font-size: 1.2rem;"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo base_url(); ?>/viajectrl/imprime/<?php echo $nKey ?>" style="text-decoration: none;">
                                    <i class="bi bi-printer-fill" style="cursor:pointer; font-size: 1.2rem;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $pager->links() ?>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const appViajes = {
            init: function() {
                $("#dIni").on('change', appViajes.valFin);
                $("#dFin").on('change', appViajes.valIni);
                // $('#frmFiltro').on('submit', appMovimientos.filtrar);
            },
            valFin: function(e) {
                $("#dFin").attr('min', e.target.value);
                $("#dFin").attr('value', e.target.value);
                //window.alert(e.target.value);
            },
            valIni: function(e) {
                $("#dIni").attr('max', e.target.value);
                if ($("#dIni").value == null || $("#dIni").value == '')
                    $("#dIni").attr('value', e.target.value);
            },
            filtrar: function(e) {
                e.preventDefault();
                miGlobal.toggleBlockPantalla('Consultando información...');
                this.submit();
            }
        };

        appViajes.init();
    });
</script>
