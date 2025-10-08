<?php

$aKeys = [
    'viaje' => ['nIdViaje', 'dViaje',],
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
    <form class="row border rounded mb-0 py-1" action="<?= base_url('viaje/') ?>" method="get">
        <!--
        <div class="col-12 mb-1 col-md-4 col-lg-3">
            <div class="input-group">
                <span class="input-group-text">Estado</span>
                <select class="form-select text-center" name="cEstado" id="cEstado">
                    <option value="2" < ?= $cEdo == '2' ? 'selected' : '' ? >>En tránsito</option>
                    <option value="1" < ?= $cEdo == '1' ? 'selected' : '' ? >>En proceso de carga</option>
                    <option value="9" < ?= $cEdo == '9' ? 'selected' : '' ? >>Todos</option>
                </select>
            </div>
        </div>
        -->
        <div class="col-2 p-1">
            <div class="input-group">
                <span class="input-group-text w-25">Del</span>
                <input type="date" name="dIni" id="dIni" class="form-control text-center" value="<?= $dIni ?>">
            </div>
        </div>
        <div class="col-2 p-1">
            <div class="input-group">
                <span class="input-group-text w-25">Al</span>
                <input type="date" name="dFin" id="dFin" class="form-control text-center" value="<?= $dFin ?>">
            </div>
        </div>
        <div class="col-4 p-1">
            <div class="input-group">
                <span class="input-group-text">Estado</span>
                <select class="form-select text-center" name="cEstado" id="cEstado">
                    <option value="1" <?= $cEdo == '1' ? 'selected' : '' ?>>En proceso de carga</option>
                    <option value="2" <?= $cEdo == '2' ? 'selected' : '' ?>>En tránsito</option>
                    <option value="3" <?= $cEdo == '3' ? 'selected' : '' ?>>Folio remisión</option>
                    <option value="4" <?= $cEdo == '4' ? 'selected' : '' ?>>Cliente</option>
                    <option value="9" <?= $cEdo == '9' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
        </div>
        <div class="col-3 p-1">
            <div class="input-group">
                <input type="text" class="form-control <?= $cEdo == '4' ? '' : 'd-none' ?>" name="sValorFiltro" id="sValorFiltro" value="<?= set_value('sValorFiltro', '') ?>" placeholder="Folio de Remision">
                <button type="btn btn-primary bg-gradient mb-1 me-md-2" class="btn btn-primary" style="z-index:auto;">Filtrar</button>
            </div>
        </div>
        <div class="col-12 text-sm-end d-flex flex-column flex-md-row col-lg">
            <a class="btn btn-primary bg-gradient mb-1 me-md-2" href="<?= base_url() ?>/viaje/a">Agregar</a>
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
                                <a href="<?php echo base_url(); ?>/viaje/v/<?php echo $nKey ?>" style="text-decoration: none;">
                                    <i class="bi bi-eye-fill" style="cursor:pointer; font-size: 1.2rem;"></i>
                                </a>
                                <?php if ($r['cEstatus'] == '0' || $r['cEstatus'] == '1') : ?>
                                    <a href="<?php echo base_url(); ?>/viaje/e/<?php echo $nKey ?>" style="text-decoration: none;">
                                        <i class="bi bi-pencil-fill text-primary me-3" style="cursor:pointer; font-size: 1.2rem;"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($r['cEstatus'] == '2') : ?>
                                    <a href="<?php echo base_url(); ?>/viaje/c/<?php echo $nKey ?>" style="text-decoration: none;">
                                        <i class="bi bi-clipboard2-check " style="cursor:pointer; font-size: 1.2rem;"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo base_url(); ?>/viaje/imprime/<?php echo $nKey ?>" style="text-decoration: none;">
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