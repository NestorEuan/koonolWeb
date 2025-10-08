<?php
$aKeys = [
    'salida'  => ['nIdSalida', 'dSalida', 'F.Salida'],
    'compra'  => ['nIdCompra', 'dcompra', 'F.Compra'],
    'envio' => ['nIdEnvio', 'dEntrega', 'F.Envio'],
    'entrega' => ['nIdEntrega', 'dEntrega', 'F.Engrega'],
    'entrada' => ['nIdEntrada', 'dEntrada', 'F.Entrada'],
    'traspaso' => ['nIdTraspaso', 'dTraspaso', 'F.Traspaso'],
];
$sKey = $aKeys[$operacion][0];
$sMov = $aKeys[$operacion][1];
$fMov = $aKeys[$operacion][2];
$bOcultaRecepcion = $operacion === "compra" && $modocompra == "pagocompra";
// $cEdo = '0';
if (isset($aWhere)) {
    $cEdo = $aWhere['Edo'];
    $dIni = $aWhere['dIni'] ?? '';
    $dFin = $aWhere['dFin'] ?? '';
}
?>
<div class="container bg-light mt-4 position-relative">
    <div id="lstMovtoswAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h5><?= $titulo ?></h5>
    <form class="row border rounded mb-3 py-1" action="<?= base_url('movimiento/index/' . $operacion) . ($modocompra == "pagocompra" ? '/pagocompra' : '') ?>" method="get">
        <div class="col-12 mb-1 col-md-4 col-lg-3">
            <div class="input-group">
                <span class="input-group-text">Estado</span>
                <select class="form-select text-center" name="cEstado" id="cEstado">
                    <?php if ($modocompra === "pagocompra") : ?>
                        <option value="50" <?= $cEdo == '50' ? 'selected' : '' ?>>Con saldo</option>
                        <option value="51" <?= $cEdo == '51' ? 'selected' : '' ?>>Pagados</option>
                        <option value="52" <?= $cEdo == '52' ? 'selected' : '' ?>>Todos</option>
                    <?php else : ?>
                        <option value="0" <?= $cEdo == '0' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="1" <?= $cEdo == '1' ? 'selected' : '' ?>>Surtidos</option>
                        <option value="2" <?= $cEdo == '2' ? 'selected' : '' ?>>Todos</option>
                    <?php endif; ?>
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
            <?php if ($operacion !== 'entrega') : ?>
                <a class="btn btn-primary bg-gradient mb-1 me-md-2" href="<?= base_url('movimiento/' . $operacion . '/a') ?>">Agregar</a>
            <?php endif ?>
        </div>
    </form>

    <div class="row border rounded">

        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th><?= ($operacion == 'entrega' ? 'Fol.Entrega/<br>Fol.Remision' : '#') ?></th>
                    <th><?= $fMov ?></th>
                    <th>Sucursal</th>
                    <?php if ($operacion !== 'entrada') : ?>
                        <th>Proveedor</th>
                    <?php endif ?>
                    <th>F.Solicitud</th>
                    <?php if ($operacion == 'traspaso') : ?>
                        <th>Envio/Viaje</th>
                    <?php endif ?>
                    <th>Estado</th>
                    <th>
                        <!-- Acción -->
                    </th>
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
                        $dMov = $r[$sMov] === null ? '' : date("Y-m-d", strtotime($r[$sMov]));
                        $dSolicita = $r['dSolicitud'] === null ? '' : date("Y-m-d", strtotime($r['dSolicitud']));
                        $sClienteProveedor = $r[$operacion == 'envio' ? 'nIdCliente' : 'nIdProveedor'] . ' ' . $r['sNombre'];
                        if ($operacion == 'entrega' && $r['cTipoCliente'] == 'P')
                            $sClienteProveedor = $r['nIdCliente'] . ' ' . $r['cNomEntrega'];
                        ?>
                        <tr>
                            <td><?= ($operacion == 'entrega' ? $nKey . '/' . $r['nFolioRemision'] : $nKey) ?></td>
                            <td><?= $dMov ?></td>
                            <td><?= $r['nIdSucursal'] . ' ' . $r['sDescripcion'] ?></td>
                            <?php if ($operacion !== 'entrada') : ?>
                                <td><?= $sClienteProveedor ?></td>
                            <?php endif ?>
                            <td><?= $dSolicita; ?></td>
                            <?php if ($operacion == 'traspaso') : ?>
                                <td><?= ($r['nIdEnvio'] > 0 ? $r['nIdEnvio'] : '') . ($r['nIdViaje'] > 0 ? ' / ' . $r['nIdViaje'] : '') ?></td>
                            <?php endif ?>
                            <td>
                                <?php
                                if ($modocompra === 'pagocompra')
                                    switch ($r['cEdoPago']) {
                                        case '2':
                                            echo 'Pagado parcial';
                                            break;
                                        case '3':
                                            echo 'Pagado total';
                                            break;
                                        case '5':
                                            echo 'Cancelado';
                                            break;
                                        default:
                                            echo 'No pagado';
                                    }
                                elseif ($operacion === 'entrega')
                                    switch ($r['cEdoEntregaEntre']) {
                                        case '3':
                                            echo 'Entregado';
                                            break;
                                        default:
                                            echo 'Pendiente';
                                    }
                                else
                                    switch ($r['cEdoEntrega']) {
                                        case '2':
                                            echo 'Surtido parcial';
                                            break;
                                        case '3':
                                            echo 'Surtido total';
                                            break;
                                        case '5':
                                            echo 'Cancelado';
                                            break;
                                        default:
                                            echo 'Pendiente';
                                    }
                                ?></td>
                            <!--td>< ?= $r['nProductos']? ></td -->
                            <td>
                                <?php if ($operacion === 'compra') : ?>
                                    <?php if ($modocompra === 'pagocompra') : ?>
                                        <?php if ($r['cEdoPago'] === '1') : ?>
                                            <a href="<?php echo base_url(); ?>/movimiento/<?= $operacion ?>/p/<?php echo $nKey ?>" style="text-decoration: none;">
                                                <i class="bi bi-cash-coin text-primary me-3" style="cursor:pointer;"></i>
                                            </a>
                                        <?php elseif ($r['cEdoPago'] === '0') : ?>
                                            <a href="<?php echo base_url(); ?>/movimiento/<?= $operacion ?>/n/<?php echo $nKey ?>" style="text-decoration: none;">
                                                <i class="bi bi-clipboard2-check text-primary me-3" style="cursor:pointer;"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (!$bOcultaRecepcion) : ?>
                                    <?php if ($modocompra !== 'pagocompra' && $r['cEdoEntrega'] == '3') : ?>
                                        <i class="bi bi-entrega-mano-fill text-secondary me-2" style="font-size: 1.5rem;"></i>
                                    <?php else : ?>
                                        <a href="<?= base_url('movimiento/' . $operacion . '/r/' . $nKey); ?>" style="text-decoration: none;">
                                            <i class="bi bi-entrega-mano-fill text-primary me-2" style="cursor:pointer; font-size: 1.5rem;"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($operacion !== 'entrega') : ?>
                                    <a href="<?php echo base_url(); ?>/movimiento/<?= $operacion ?>/c/<?php echo $nKey ?>" style="text-decoration: none;">
                                        <i class="bi bi-eye-fill text-primary me-2" style="cursor:pointer; font-size: 1.5rem;"></i>
                                    </a>
                                    <a href="<?php echo base_url(); ?>/imprime/<?= $operacion ?>/<?php echo $nKey ?>" style="text-decoration: none;" target="_blank">
                                        <i class="bi bi-printer-fill text-primary me-2" style="cursor:pointer; font-size: 1.5rem;"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($operacion == 'traspaso') : ?>
                                    <?php if ($r['contEnviosViajes'] == 0 && !in_array($r['cEdoEntrega'], ['2', '3', '5'])) : ?>
                                        <a href="<?= base_url('movimiento/' . $operacion . '/b/' . $nKey) ?>" style="text-decoration: none;">
                                            <i class="bi bi-x-octagon-fill text-primary" style="cursor:pointer; font-size: 1.5rem;" title="Cancelar el traspaso"></i>
                                        </a>
                                    <?php else : ?>
                                        <i class="bi bi-x-octagon-fill text-secondary" style="font-size: 1.5rem;"></i>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($operacion == 'compra') : ?>
                                    <?php if (!in_array($r['cEdoEntrega'], ['2', '3', '5'])) : ?>
                                        <a href="<?= base_url('movimiento/' . $operacion . '/b/' . $nKey) ?>" style="text-decoration: none;">
                                            <i class="bi bi-x-octagon-fill text-primary" style="cursor:pointer; font-size: 1.5rem;" title="Cancelar la compra"></i>
                                        </a>
                                    <?php else : ?>
                                        <i class="bi bi-x-octagon-fill text-secondary" style="font-size: 1.5rem;"></i>
                                    <?php endif; ?>
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

<script type="text/javascript">
    $(document).ready(function() {
        const appMovimientos = {
            init: function() {
                $("#dIni").on('change', appMovimientos.valFin);
                $("#dFin").on('change', appMovimientos.valIni);
                // $('#frmFiltro').on('submit', appMovimientos.filtrar);
                let cMsj = "<?= $cMsjError ?? '' ?>";
                if (cMsj != '') miGlobal.muestraAlerta(cMsj, 'lstMovtos', 1700);
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

        appMovimientos.init();
    });
</script>