<?php $nPesoViaje = 0; ?>
<?php $nPar = true; ?>
<?php $sizeIcons = $esMobil ? 'fs-1' : 'fs-5'; ?>
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
        <td class="text-center"><?= round(floatval($r['peso']) / 1000, 3) > 1.00 ? sprintf('%01.2f Tons', floatval($r['peso']) / 1000) : round(floatval($r['peso']), 3) . 'Kg.'   ?></td>
        <td class="text-center">
            <?php if (isset($esCtrl)) : ?>
                <i class="bi bi-eye-fill text-primary me-2 fw-bold <?= $sizeIcons ?>" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viaje/envio/c/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Consultar productos para viaje"></i>
                <?php if ($modoAccionEnEnvio == 'e' && $permisoCapDevoluciones) : ?>
                    <i class="bi bi-box-return2 text-primary me-2 fw-bold <?= $sizeIcons ?>" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viajectrl/devolucion/e/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Devolucion productos del viaje"></i>
                <?php else : ?>
                    <?php if ($r['conDevolucion'] == '1') : ?>
                        <i class="bi bi-box-return2 text-primary me-2 fw-bold <?= $sizeIcons ?>" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viajectrl/devolucion/c/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Productos Devueltos"></i>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else : ?>
                <i class="bi bi-truck text-primary me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viaje/envio/e/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Seleccionar productos para viaje"></i>
                <?php if ($tipoAccion != 's') : ?>
                    <i class="bi bi-entrega-mano-fill text-primary me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="<?= 'viaje/envio/n/' . $k . ($idViaje == '' ? '' : '/' . $idViaje) ?>" style="cursor:pointer;" title="Asignar para envio en otra sucursal"></i>
                    <i class="bi bi-entrega-mano-fill3 text-primary me-2 fw-bold fs-5" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-mod-msj="Reasignar productos restantes del envio a ventas?" data-mod-titulo="Confirmar acción" data-llamar="<?= 'viaje/envio/r/' . $k . ($idViaje == '' ? '/0' : '/' . $idViaje) . '/' . ($r['cOrigen'] == 'ventas' ? $r['nIdOrigen'] : '0') ?>" style="cursor:pointer;" title="Para reasignar en ventas"></i>
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
<?php $cPesoViaje = round(floatval($nPesoViaje) / 1000, 3) > 1.00 ? sprintf('%01.2f Tons', floatval($nPesoViaje) / 1000) : round(floatval($nPesoViaje), 3) . 'Kg.'; ?>
<script type="text/javascript">
    $(function() {
        let pesotot = '<?= $cPesoViaje ?>';
        $('#txtPesoTotalViaje').text(pesotot);
    });
</script>