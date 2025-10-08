<?php
$aKeys = [
    'salida'  => ['nIdSalida', 'dSalida', 'F.Salida'],
    'compra'  => ['nIdCompra', 'dcompra', 'F.Compra'],
    'envio' => ['nIdEnvio', 'dEntrega', 'F.Envio'],
    'entrega' => ['nIdEntrega', 'dEntrega', 'F.Engrega'],
    'entrada' => ['nIdEntrada', 'dEntrada', 'F.Entrada'],
    'traspaso' => ['nIdTraspaso', 'dTraspaso', 'F.Traspaso'],
    'capturainv' => ['idInventarioCaptura', 'dAplicacion', 'F.Aplicación'],
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
<div class="container bg-light mt-4">
    <h5><?= $titulo ?></h5>
    <form action="<?= base_url('movimiento/index/' . $operacion) . ($modocompra == "pagocompra" ? '/pagocompra' : '') ?>" method="get">
        <div class="row border rounded mb-3 py-1">
            <div class="col-12 mb-1 col-md-12 col-xl-9">
                <div class="row p-1">
                    <div class="col-12 col-md-4">
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
                    <div class="col-12 col-md-4 my-1 my-md-0">
                        <div class="input-group">
                            <span class="input-group-text w-25">Del</span>
                            <input type="date" name="dIni" id="dIni" class="form-control text-center" value="<?= $dIni ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text w-25">Al</span>
                            <input type="date" name="dFin" id="dFin" class="form-control text-center" value="<?= $dFin ?>">
                        </div>

                    </div>
                </div>
                <?php if (in_array($operacion, array('entrada', 'salida', 'traspaso'))) : ?>
                    <div class="row p-1">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">Sucursal</span>
                                <select class="form-select" name="nIdSucursal" id="nIdSucursal">
                                    <?php foreach ($aSucursales as $suc) : ?>
                                        <option value="<?= $suc['nIdSucursal'] ?>" <?= $suc['nIdSucursal'] == $nIdSucursal ? 'selected' : '' ?>><?= $suc['sDescripcion'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if ($pager->getPageCount() > 1) : ?>
                            <div class="col-12 mt-1 mt-md-0 col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">Pagina</span>
                                    <input type="number" class="form-control" aria-label="Pagina" aria-describedby="pagina" value="<?= $pagina ?>" id="pagina" name="pagina">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12 mb-1 col-md-12 col-xl-3">
                <div class="row h-100 p-1 pe-md-3 px-xl-2 align-items-xl-center">
                    <button type="submit" class="col-12 col-md-2 ms-md-auto col-xl-auto ms-xl-0 btn btn-secondary bg-gradient">Filtrar</button>
                    <a class="col-12 col-md-2  col-xl-auto ms-xl-auto btn btn-primary bg-gradient my-1 my-md-0 mx-md-2 mx-xl-1" href="<?= base_url('movimiento/' . $operacion . '/a') ?>">Agregar</a>
                    <button type="button" class="col-12 col-md-2 col-xl-auto btn btn-primary bg-gradient">Exportar</button>
                </div>
            </div>
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
                                else
                                    switch ($r['cEdoEntrega']) {
                                        case '2':
                                            echo 'Surtido parcial';
                                            break;
                                        case '3':
                                            echo 'Surtido total';
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
                                    <?php if ($modocompra !== 'pagocompra' && ($r['cEdoEntrega'] == '3' || $r['cEdoEntrega'] == '9')) : ?>
                                        <i class="bi bi-entrega-mano-fill text-secondary" style="font-size: 1.5rem;"></i>
                                    <?php else : ?>
                                        <a href="<?php echo base_url(); ?>/movimiento/<?= $operacion ?>/r/<?php echo $nKey ?>" style="text-decoration: none;">
                                            <i class="bi bi-entrega-mano-fill text-primary" style="cursor:pointer; font-size: 1.5rem;"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($operacion !== 'entrega') : ?>
                                    <a href="<?php echo base_url(); ?>/movimiento/<?= $operacion ?>/e/<?php echo $nKey ?>" style="text-decoration: none;">
                                        <i class="bi bi-eye-fill text-primary" style="cursor:pointer; font-size: 1.5rem;"></i>
                                    </a>
                                    <a href="<?php echo base_url(); ?>/imprime/<?= $operacion ?>/<?php echo $nKey ?>" style="text-decoration: none;">
                                        <i class="bi bi-printer-fill text-primary" style="cursor:pointer; font-size: 1.5rem;"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($r['cEdoEntrega'] < '3' && $operacion == 'entrada') : ?>
                                    <a href="<?php echo base_url(); ?>/movimiento/<?= $operacion ?>/c/<?php echo $nKey ?>" style="text-decoration: none;">
                                        <i class="bi bi-x-circle text-danger" style="cursor:pointer; font-size: 1.5rem;"></i>
                                    </a>
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