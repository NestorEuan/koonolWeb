<?php
if ($dat['reiniciaVenta']) {
    $cmd = base_url() . '/ventas/nVenta';
} else {
    $cmd = base_url() . '/ventas';
}
$cmdRemisiones = base_url() . '/remisiones';
$ln = '<br>';   // chr(10)
$dat['datSuc'] = strtoupper($dat['suc']['sDescripcion'] . $ln . $dat['suc']['sDireccion']);
$dat['datCli'] = '<strong>CÓdigo: ' . sprintf('%06d ', intval($dat['cli']['nIdCliente'])) .
    $dat['cli']['sNombre'] . '</strong>' . $ln .
    '  RFC: ' . strtoupper(trim($dat['cli']['sRFC'])) .
    '  C.P.: ' . $dat['cli']['cCP'] . $ln .
    $dat['cli']['sDireccion'];

$dat['lineasPorPagina'] = 10;
$dat['bDocumentoImpreso'] = false;

$destino = $dat['destino'];
$nIdEntrega = $dat['nIdEntrega'];
$bParaEnvio = $dat['bParaEnvio'];
$bParaEntrega = $dat['bParaEntrega'];
$bQuienRecogeEntrega = $dat['bQuienRecogeEntrega'];
$bSeEnviaTodo = $dat['bSeEnviaTodo'];
$bSeEntregaTodo = $dat['bSeEntregaTodo'];
$bEntregaEnOtraSuc = $dat['bEntregaEnOtraSuc'];

function imprime(&$dat, $tipoDocumento, $tipoImpresion)
{

    $dat['bImprimirPiePagina'] = false;
    $lineasPorPagina = $dat['lineasPorPagina'];
    $nContLineas = 10;
    $sum = 0;
    foreach ($dat['lst'] as $v) {
        if ($nContLineas == $lineasPorPagina) {
            imprimeCabecero($dat, $tipoDocumento, $tipoImpresion);
            $dat['bImprimirPiePagina'] = true;
            $dat['bDocumentoImpreso'] = true;
            $nContLineas = 0;
        }
        if ($tipoImpresion == 'normal') $nContLineas++;
        imprimeDetalle($dat, $v, $tipoImpresion, $sum, $tipoDocumento, $nContLineas);
    }
    $dat['misuma'] = $sum;
    imprimeFinal($dat, $tipoDocumento, $tipoImpresion);
}

function imprimeCabecero(&$dat, $tipoDocumento, $tipoImpresion)
{

    if ($dat['bImprimirPiePagina'] && $tipoImpresion == 'normal') imprimePie($dat, $tipoDocumento, $tipoImpresion);

    if ($dat['bDocumentoImpreso']) {
        echo '<p class="d-none page-break d-sm-block"></p>';
        echo '<div class="d-sm-none border-top w-100 border-dark my-3 border-4"></div>';
    }

    if ($tipoDocumento == 'caja') $titulo = 'COPIA CAJA';
    elseif ($tipoDocumento == 'cliente')  $titulo = 'ORIGINAL CLIENTE';
    elseif ($tipoDocumento == 'entregabod')  $titulo = 'ENTREGA  ( BODEGA )';
    elseif ($tipoDocumento == 'entregacli')  $titulo = 'ENTREGA  ( CLIENTE )';
    elseif ($tipoDocumento == 'entregacaj')  $titulo = 'ENTREGA  ( CAJA )';
    // elseif ($tipoDocumento == 'entregacho')  $titulo = 'ENVIO (CHOFER)';
    // elseif ($tipoDocumento == 'entregaord')  $titulo = 'ENVIO (ORDEN DE COMPRA DE ENTREGA)';
    elseif ($tipoDocumento == 'enviobod')  $titulo = 'ENVIO  ( BODEGA )';
    elseif ($tipoDocumento == 'enviocli')  $titulo = 'ENVIO  ( CLIENTE )';
    elseif ($tipoDocumento == 'enviocaj')  $titulo = 'ENVIO  ( CAJA )';
    elseif ($tipoDocumento == 'enviocho' || $tipoDocumento == 'entregacho')  $titulo = 'ENVIO  ( CHOFER )';
    elseif ($tipoDocumento == 'envioord' || $tipoDocumento == 'entregaord')  $titulo = 'ORDEN DE COMPRA';
    $nFolio = $dat['nFolio'];
    $nomUsu = $dat['nomUsu'];
    $fechaImp = $dat['fechaImp'];
    $rzLeyenda = $dat['rzLeyenda'];
    $datSuc = $dat['datSuc'];
    $datCli = $dat['datCli'];
    $nIdEntrega = $dat['nIdEntrega'];
    $nIdEnvio = $dat['nIdEnvio'];
    if ($tipoDocumento == 'entregabod' || $tipoDocumento == 'entregacli' || $tipoDocumento == 'entregacaj') {
        $enviarA = $dat['entregarA'];
    } else {
        $enviarA = $dat['enviarA'];
    }
?>
    <div class="container-fluid border-bottom border-dark">
        <?php if ($tipoImpresion == 'ticket') : ?>
            <div class="row d-sm-none">
                <div class="col-12">
                    <div class="row justify-content-center">
                        <div class="col-5">
                            <img src="<?= base_url() ?>/assets/img/<?= $dat['aInfoSis']['bannermain'] ?>" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col fw-bold fs-6 text-center lh-1"><?= $titulo ?></div>
                    </div>
                    <div class="row">
                        <div class="col fs-8 text-center lh-1">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></div>
                    </div>
                    <?php if ($nomUsu != '' && ($tipoDocumento == 'caja' || $tipoDocumento == 'cliente')) : ?>
                        <div class="row">
                            <div class="col fs-8 text-center lh-1">Vendedor: <?= $nomUsu ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col fs-8 text-center lh-1"><?= $fechaImp->format('d/m/Y H:i:s') ?></div>
                    </div>
                    <?php if ($tipoDocumento == 'entregabod' ||  $tipoDocumento == 'entregacli' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'entregacho' || $tipoDocumento == 'entregaord') : ?>
                        <div class="row">
                            <div class="col fs-8 text-center lh-1">Folio Entrega: <?= sprintf('%07d', intval($nIdEntrega)) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocli' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
                        <div class="row">
                            <div class="col fs-8 text-center lh-1">Folio Envio: <?= sprintf('%07d', intval($nIdEnvio)) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="row d-none d-sm-block mt-1 fw-normal">
                <div class="col align-text-top text-center pb-2">
                    <img src="<?= base_url() ?>/assets/img/<?= $dat['aInfoSis']['bannermain'] ?>" alt="" width="84" class="float-start">
                    <h6><?= $titulo ?></h6>
                    <span class="me-4 fs-8">Folio Remisión: <strong><?= sprintf('%07d', intval($nFolio)) ?></strong></span>
                    <?php if ($nomUsu != '' && ($tipoDocumento == 'caja' || $tipoDocumento == 'cliente')) : ?>
                        <span class="me-4 fs-8">Vendedor: <?= $nomUsu ?></span>
                    <?php endif; ?>
                    <?php if ($tipoDocumento == 'caja' ||  $tipoDocumento == 'cliente') : ?>
                        <span class="fs-8"><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                    <?php else : ?>
                        <span class="fs-8"><?= (new DateTime())->format('d/m/Y H:i:s') ?></span>
                    <?php endif; ?>
                    <?php if ($tipoDocumento == 'entregabod' ||  $tipoDocumento == 'entregacli' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'entregacho' || $tipoDocumento == 'entregaord') : ?>
                        <span class="fs-8">Folio Entrega: <strong><?= sprintf('%07d', intval($nIdEntrega)) ?></strong></span>
                    <?php endif; ?>
                    <?php if ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocli' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
                        <span class="fs-8">Folio Envio: <strong><?= sprintf('%07d', intval($nIdEnvio)) ?></strong></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-between fw-normal border-bottom border-dark">
            <div class="col-12 col-sm-5 text-center fs-9 mb-1 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $rzLeyenda) ?></div>
            <div class="col-12 col-sm-5 text-center fs-9 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $datSuc) ?></div>
        </div>
        <div class="row fw-normal">
            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1">
                <?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $datCli)) ?>
            </div>
            <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1"></div>
            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1 fw-bold"></div>
        </div>
        <div class="row fw-normal">
            <div class="col">
                <table>
                    <thead>
                        <?php if ($tipoImpresion == 'ticket') : ?>
                            <tr>
                                <th class="my-0 py-0 lh-1">
                                    <div class="row lh-1 border-bottom border-top border-dark">
                                        <?php if ($tipoDocumento == 'caja') : ?>
                                            <div class="col-3 text-end fs-9 lh-1">Cantidad</div>
                                            <div class="col-3 text-end fs-9 lh-1">Precio</div>
                                            <div class="col-3 text-end fs-9 lh-1">Importe</div>
                                            <div class="col"></div>
                                        <?php elseif ($tipoDocumento == 'entregabod' ||  $tipoDocumento == 'entregacli' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'entregacho' || $tipoDocumento == 'entregaord') : ?>
                                            <div class="col-6 text-center fs-9 lh-1">Descripcion</div>
                                            <div class="col-2 text-center fs-9 lh-1">Cantidad</div>
                                            <div class="col-3 text-center fs-9 lh-1">Entregado</div>
                                        <?php elseif ($tipoDocumento == 'cliente') : ?>
                                            <div class="col-3 text-end fs-9 lh-1">Cantidad</div>
                                            <div class="col-3 text-end fs-9 lh-1">Precio</div>
                                            <div class="col-3 text-end fs-9 lh-1">Importe</div>
                                            <div class="col"></div>
                                        <?php elseif ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocli' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
                                            <div class="col-6 text-center fs-9 lh-1">Descripcion</div>
                                            <div class="col-2 text-center fs-9 lh-1">Entregar</div>
                                            <div class="col-3 text-center fs-9 lh-1">Entregado</div>
                                        <?php endif; ?>
                                    </div>
                                </th>
                            </tr>
                        <?php else : ?>
                            <tr id="cabDet">
                                <?php if ($tipoDocumento == 'caja') : ?>
                                    <th style="width:50%;" class="fw-bold fs-8 py-0 align-middle">Descripción</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0 align-middle">Cantidad</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0 align-middle">Precio Unitario</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0 align-middle">Importe</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0 align-middle">Descuento</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0 align-middle">Importe Total</th>
                                <?php elseif ($tipoDocumento == 'entregabod' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'entregacho' || $tipoDocumento == 'entregaord') : ?>
                                    <th style="width:70%;" class="fw-bold fs-8 py-0">Descripción</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Cantidad</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Entregado</th>
                                <?php elseif ($tipoDocumento == 'entregacli' || $tipoDocumento == 'enviocli') : ?>
                                    <th style="width:70%;" class="fw-bold fs-8 py-0">Descripción</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Cantidad</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Precio</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Entregado</th>
                                <?php elseif ($tipoDocumento == 'cliente') : ?>
                                    <th style="width:70%;" class="fw-bold fs-8 py-0 ps-3">Descripción</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Cantidad</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Precio Unitario</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Importe</th>
                                <?php elseif ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
                                    <th style="width:70%;" class="fw-bold fs-8 py-0 ps-3">Descripción</th>
                                    <th style="width:10%;" class="text-center fw-bold fs-8 py-1">Cantidad</th>
                                    <th style="width:20%;" class="text-center fw-bold fs-8 py-0">Entregado</th>
                                <?php endif; ?>
                            </tr>
                        <?php endif; ?>
                    </thead>
                </table>
            </div>
        </div>
    </div>
<?php
    // abrimos contenedor detalle
    echo '<div class="container-fluid fw-normal">';
    echo '<div class="row"><div class="col">';
    echo '<table class="table table-borderless"><tbody>';
}

function imprimeDetalle(&$dat, $v, $tipoImpresion, &$sum, $tipoDocumento, &$nContLineas)
{
    if ($tipoDocumento == 'entregabod' || $tipoDocumento == 'entregacli' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'entregacho' || $tipoDocumento == 'entregaord') {
        if ($v[3] <= 0) {
            $nContLineas--;
            return;
        }
    }
    if ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocli' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') {
        if ($v[8] <= 0) {
            $nContLineas--;
            return;
        }
    }
    if ($tipoDocumento == 'caja') $sum += round($v[1] * $v[2], 2);
    if ($dat['conPrecioTapado']) {
        $precioCliente = $v[4];
    } else {
        $precioCliente = $v[1];
    }
    if ($tipoDocumento == 'cliente') $sum += round($precioCliente * $v[2], 2);
?>

    <?php if ($tipoImpresion == 'ticket') : ?>
        <tr>
            <td class="py-0">
                <?php if ($tipoDocumento == 'caja') : ?>
                    <div class="row">
                        <div class="row">
                            <div class="col-9 fs-9 lh-1"><?= esc($v[0]) ?></div>
                            <div class="col"></div>
                        </div>
                        <div class="col-3 text-end fs-9 lh-1"><?= number_format($v[2], 3) ?></div>
                        <div class="col-3 text-end fs-9 lh-1"><?= number_format($v[1], 2) ?></div>
                        <div class="col-3 text-end fs-9 lh-1"><?= number_format(round(($v[1] * $v[2]) - $v[6], 2), 2) ?></div>
                        <div class="col"></div>
                    </div>

                <?php elseif ($tipoDocumento == 'entregabod' ||  $tipoDocumento == 'entregacli' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'entregacho' || $tipoDocumento == 'entregaord') : ?>
                    <div class="row">
                        <div class="col-6 text-start fs-9 lh-1"><?= esc($v[0]) ?></div>
                        <div class="col-2 text-center fs-9 lh-1"><?= number_format($v[3], 3) ?></div>
                        <div class="col-3 text-start fs-6 lh-1">=></div>
                    </div>
                <?php elseif ($tipoDocumento == 'cliente') : ?>
                    <div class="row">
                        <div class="col-12 fs-9 lh-1"><?= esc($v[0]) ?></div>
                    </div>
                    <div class="row">
                        <div class="col-4 text-end fs-9 lh-1"><?= number_format($v[2], 3) ?></div>
                        <div class="col-4 text-end fs-9 lh-1"><?= number_format($precioCliente, 2) ?></div>
                        <div class="col-4 text-end fs-9 lh-1"><?= number_format(round($precioCliente * $v[2], 2), 2) ?></div>
                    </div>
                <?php elseif ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocli' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
                    <div class="row">
                        <div class="col-6 text-start fs-9 lh-1"><?= esc($v[0]) ?></div>
                        <div class="col-2 text-center fs-9 lh-1"><?= number_format($v[8], 0) ?></div>
                        <div class="col-3 text-start fs-6 lh-1">=></div>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    <?php else : ?>
        <tr>
            <?php if ($tipoDocumento == 'caja') : ?>
                <td style="width:50%;" class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[2], 3) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[1], 2) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format(round($v[1] * $v[2], 2), 2) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[6], 2) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format(round(($v[1] * $v[2]) - $v[6], 2), 2) ?></td>
            <?php elseif ($tipoDocumento == 'entregabod' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'entregacho' || $tipoDocumento == 'entregaord') : ?>
                <td style="width:70%;" class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[3], 3) ?></td>
                <td style="width:20%;" class="text-start fs-9 py-0">=></td>
            <?php elseif ($tipoDocumento == 'entregacli') : ?>
                <td style="width:70%;" class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[3], 3) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[4], 2) ?></td>
                <td style="width:10%;" class="text-start fs-9 py-0">=></td>
            <?php elseif ($tipoDocumento == 'enviocli') : ?>
                <td style="width:70%;" class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[8], 3) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[4], 2) ?></td>
                <td style="width:10%;" class="text-start fs-9 py-0">=></td>
            <?php elseif ($tipoDocumento == 'cliente') : ?>
                <td style="width:70%;" class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[2], 3) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($precioCliente, 2) ?></td>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format(round($precioCliente * $v[2], 2), 2) ?></td>
            <?php elseif ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
                <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v[8], 3) ?></td>
                <td style="width:70%;" class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                <td style="width:20%;" class="text-start fs-9 py-0">=></td>
            <?php endif; ?>
        </tr>
    <?php endif; ?>
<?php
}

function imprimePie(&$dat, $tipoDocumento, $tipoImpresion)
{
    echo '</tbody></table>';      // cerramos tabla del contenedor detalle
    $sumaSubTotalReal = $dat['sumaSubTotalReal'];
    $descuentoEnProducto = $dat['descuentoEnProducto'];
    $descuentoRemision = $dat['descuentoRemision'];
    $sumaIVA = $dat['sumaIVA'];
    $impTotal = $dat['impTotal'];
    $bonificaciones = $dat['bonificaciones'];
    $sumaSubTotalTapado = $dat['sumaSubTotalTapado'];
?>
    <table class="table table-borderless">
        <tbody>
            <?php if ($tipoImpresion == 'ticket') : ?>
                <?php if ($tipoDocumento == 'caja' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'entregacho') : ?>
                    <tr>
                        <td>
                            <div class="row lh-1">
                                <div class="col-9 fs-9 fw-bold">** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **</div>
                                <div class="col"></div>
                            </div>
                            <div class="row lh-1">
                                <div class="col-9 fs-9 fw-bold">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</div>
                                <div class="col"></div>
                            </div>
                        </td>
                    </tr>
                <?php elseif ($tipoDocumento == 'entregabod' || $tipoDocumento == 'enviobod') : ?>
                    <tr>
                        <td class="fw-bold fs-10  border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                    </tr>
                <?php elseif ($tipoDocumento == 'cliente' || $tipoDocumento == 'entregacli' || $tipoDocumento == 'enviocli') : ?>
                    <tr>
                        <td>
                            <div class="row lh-1 mb-2">
                                <div class="col-5 fs-9">TOTAL:</div>
                                <div class="col-4 text-end fs-9"><?= number_format($sumaSubTotalTapado, 2) ?></div>
                                <div class="col"></div>
                            </div>
                            <div class="row lh-1">
                                <div class="col-9 fs-9 fw-bold">** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **</div>
                                <div class="col"></div>
                            </div>
                            <div class="row lh-1">
                                <div class="col-9 fs-9 fw-bold">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</div>
                                <div class="col"></div>
                            </div>
                            <div class="row lh-1">
                                <div class="col-9 fs-9">
                                    <div class="lh-1">
                                        1 - Condiciones de venta: <strong>contado</strong><br>
                                        2 - Tiempo de entrega <strong>de 48 a 72 horas.</strong><br>
                                        3 - Condiciones de entrega de material: <strong>máximo 10 mts sujeto a condición de acceso</strong><br>
                                        4 - Los pagos con cheques son <strong>salvo buen cobro</strong><br>
                                        5 - Toda devolución o cancelación <strong>genera un 20% de recargo sin excepción</strong>
                                    </div>
                                </div>
                                <div class="col"></div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php else : ?>
                <?php if ($tipoDocumento == 'caja' || $tipoDocumento == 'entregacaj' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'entregacho') : ?>
                    <tr>
                        <td class="py-0 fw-bold fs-8 border-top border-dark">** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **</td>
                        <td class="py-0 fw-bold fs-8 border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                    </tr>
                <?php elseif ($tipoDocumento == 'entregabod' || $tipoDocumento == 'enviobod') : ?>
                    <tr>
                        <td class="fw-bold fs-8 border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                    </tr>
                <?php elseif ($tipoDocumento == 'cliente' || $tipoDocumento == 'entregacli' || $tipoDocumento == 'enviocli') : ?>
                    <tr>
                        <td class="py-0 fw-bold fs-8 border-top border-dark">** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **</td>
                        <td class="py-0 fw-bold fs-8 border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                    </tr>
                    <tr>
                        <td class="lh-1 py-0 fs-8">
                            1 - Condiciones de venta: <strong>contado</strong><br>
                            2 - Tiempo de entrega <strong>de 48 a 72 horas.</strong><br>
                            3 - Condiciones de entrega de material: <strong>máximo 10 mts sujeto a condición de acceso</strong><br>
                        </td>
                        <td class="lh-1 py-0 fs-8 border-start border-dark">
                            4 - Los pagos con cheques son <strong>salvo buen cobro</strong><br>
                            5 - Toda devolución o cancelación <strong>genera un 20% de recargo sin excepción</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

        </tbody>
    </table>
<?php
    echo '</div></div></div>';      // cerramos contenedor detalle 
}

function imprimeFinal(&$dat, $tipoDocumento, $tipoImpresion)
{
    echo '</tbody></table>';      // cerramos tabla del contenedor detalle

    $sumaSubTotalReal = $dat['sumaSubTotalReal'];
    $descuentoEnProducto = $dat['descuentoEnProducto'];
    $descuentoRemision = $dat['descuentoRemision'];
    $sumaIVA = $dat['sumaIVA'];
    $impTotal = $dat['impTotal'];
    $bonificaciones = $dat['bonificaciones'];
    $sumaSubTotalTapado = $dat['sumaSubTotalTapado'];
    $lstpagos = $dat['lstpagos'];
    $nImporteCambio = $dat['cambio'] ?? 0;
    $nImporteEntregado = $dat['efecPagado'] ?? 0;
?>
    <table class="table table-borderless">
        <tbody>
            <?php if ($tipoImpresion == 'ticket') : ?>
                <?php if ($tipoDocumento == 'caja') : ?>
                    <tr>
                        <td>
                            <div class="row lh-1 border-top">
                                <div class="col-5 fs-9">SUBTOTAL:</div>
                                <div class="col-4 text-end fs-9"><?= number_format($sumaSubTotalReal - $descuentoEnProducto, 2) ?></div>
                                <div class="col"></div>
                            </div>
                            <?php if ($descuentoRemision > 0) : ?>
                                <div class="row lh-1">
                                    <div class="col-5 fs-9">DESC. REMISION:</div>
                                    <div class="col-4 text-end fs-9"><?= number_format($descuentoRemision, 2) ?></div>
                                    <div class="col"></div>
                                </div>
                            <?php endif; ?>
                            <div class="row  lh-1">
                                <div class="col-5  fs-9">IVA:</div>
                                <div class="col-4 text-end fs-9"><?= number_format($sumaIVA, 2) ?></div>
                                <div class="col"></div>
                            </div>
                            <div class="row lh-1">
                                <div class="col-5 fs-9">TOTAL:</div>
                                <div class="col-4 text-end fs-9"><?= number_format($impTotal, 2) ?></div>
                                <div class="col"></div>
                            </div>
                            <div class="row lh-1">
                                <div class="col fw-bold fs-9">Forma de Pago:</div>
                            </div>
                            <?php foreach ($dat['lstpagos'] as $k => $v) : ?>
                                <div class="row">
                                    <div class="col-5 fs-9"><?= $v['nomPago'] ?></div>
                                    <div class="col-4 text-end fs-9"><?= number_format(floatval($v['nImporte']), 2) ?></div>
                                    <div class="col"></div>
                                </div>
                            <?php endforeach; ?>
                            <div class="row border-top">
                                <div class="col-5 fs-9">BONIFICACIONES:</div>
                                <div class="col-4 text-end fs-9"><?= number_format($bonificaciones, 2) ?></div>
                                <div class="col"></div>
                            </div>

                        </td>
                    </tr>
                <?php elseif ($tipoDocumento == 'entregacaj' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'entregacho') : ?>
                    <tr>
                        <td>
                            <div class="row lh-1">
                                <div class="col-9 fs-9 fw-bold">** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **</div>
                                <div class="col"></div>
                            </div>
                            <div class="row lh-1">
                                <div class="col-9 fs-9 fw-bold">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</div>
                                <div class="col"></div>
                            </div>
                        </td>
                    </tr>
                <?php elseif ($tipoDocumento == 'entregabod' || $tipoDocumento == 'enviobod') : ?>
                    <tr>
                        <td class="fw-bold fs-10  border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                    </tr>
                <?php elseif ($tipoDocumento == 'cliente' || $tipoDocumento == 'entregacli' || $tipoDocumento == 'enviocli') : ?>
                    <tr>
                        <td>
                            <div class="row lh-1 mb-2">
                                <div class="col-8 fs-9 text-end">TOTAL:</div>
                                <div class="col-4 text-end fs-9"><?= number_format($sumaSubTotalTapado, 2) ?></div>
                            </div>
                            <?php if ($tipoDocumento == 'cliente' && $nImporteCambio > 0) : ?>
                                <div class="row lh-1 mb-2">
                                    <div class="col-8 fs-9 text-end">ENTREGADO:</div>
                                    <div class="col-4 text-end fs-9"><?= number_format($nImporteEntregado, 2) ?></div>
                                </div>
                                <div class="row lh-1 mb-2">
                                    <div class="col-8 fs-9 text-end">CAMBIO:</div>
                                    <div class="col-4 text-end fs-9"><?= number_format($nImporteCambio, 2) ?></div>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($dat['lstpagos'] as $k => $v) : ?>
                                <div class="row lh-1">
                                    <div class="col-5 fs-9"><?= $v['nomPago'] ?></div>
                                    <div class="col-4 text-end fs-9"><?= number_format(floatval($v['nImporte']), 2) ?></div>
                                    <div class="col"></div>
                                </div>
                            <?php endforeach; ?>
                            <div class="row lh-1">
                                <div class="col-9 fs-9 fw-bold">20 DÍAS PARA CAMBIOS <strong>CONSULTE TÉRMINOS Y CONDICIONES</strong></div>
                                <div class="col"></div>
                            </div>
                        </td>
                    </tr>
                <?php elseif ($tipoDocumento == 'enviochof') : ?>
                    <tr>
                        <td class="fs-10 border-top border-dark lh-1">
                            <strong>** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</strong><br>
                            1 - Condiciones de venta: <strong>contado</strong><br>
                            2 - Tiempo de entrega <strong>de 48 a 72 horas.</strong><br>
                            3 - Condiciones de entrega de material: <strong>máximo 10 mts sujeto a condición de acceso</strong><br>
                            4 - Los pagos con cheques son <strong>salvo buen cobro</strong><br>
                            5 - Toda devolución o cancelación <strong>genera un 20% de recargo sin excepción</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php else : ?>
                <?php if ($tipoDocumento == 'caja') : ?>
                    <tr>
                        <td style="width:10%;height:0;"></td>
                        <td style="width:50%;height:0;"></td>
                        <td style="width:10%;height:0;"></td>
                        <td style="width:10%;height:0;"></td>
                        <td style="width:10%;height:0;"></td>
                        <td style="width:10%;height:0;"></td>
                    </tr>
                    <tr class="border-top border-dark lh-1">
                        <td colspan="5" class="text-end py-0 fs-8">SUBTOTAL:</td>
                        <td class="text-end py-0 fs-8"><?= number_format($sumaSubTotalReal - $descuentoEnProducto, 2) ?></td>
                    </tr>
                    <?php if ($descuentoRemision > 0) : ?>
                        <tr class=" lh-1">
                            <td colspan="5" class="text-end py-0 fs-8">DESCUENTO REMISION:</td>
                            <td class="text-end py-0 fs-8"><?= number_format($descuentoRemision, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="lh-1">
                        <td colspan="5" class="text-end py-0 fs-8">IVA:</td>
                        <td class="text-end py-0 fs-8">
                            <?= number_format($sumaIVA, 2) ?>
                        </td>
                    </tr>
                    <tr class=" lh-1">
                        <td colspan="5" class="text-end py-0 fs-8">TOTAL:</td>
                        <td class="text-end py-0 fs-8">
                            <?= number_format($impTotal, 2) ?>
                        </td>
                    </tr>
                    <tr class=" lh-1">
                        <td colspan="2" class="text-end py-0 fs-8">BONIFICACIONES:</td>
                        <td class="text-start py-0 fs-8"><?= number_format($bonificaciones, 2) ?></td>
                        <td colspan="2" class="text-end py-0 fw-bold fs-8">Forma de Pago:</td>
                        <td class="py-0"></td>
                    </tr>
                    <?php foreach ($lstpagos as $k => $v) : ?>
                        <tr class=" lh-1">
                            <td colspan="5" class="text-end py-0 fs-8"><?= $v['nomPago'] ?></td>
                            <td class="text-end py-0 fs-8"><?= number_format(floatval($v['nImporte']), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php elseif ($tipoDocumento == 'entregacaj' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'entregacho') : ?>
                    <tr>
                        <td class="py-0 fw-bold fs-8 border-top border-dark">20 DÍAS PARA CAMBIOS <strong>CONSULTE TÉRMINOS Y CONDICIONES</strong> </td>
                    </tr>
                <?php elseif ($tipoDocumento == 'cliente') : ?>
                    <tr class="border-top border-dark">
                        <td colspan="2" class="py-0">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="width:50%;" class="py-0 fs-8 pe-1">Artículos vendidos:</td>
                                        <td style="width:10%;" class="py-0 fs-8"><?= number_format(array_sum(array_column($dat['lst'], 2)), 2) ?></td>
                                        <td style="width:50%;" class="text-end py-0 fs-8 pe-1">TOTAL:</td>
                                        <td style="width:10%;" class="text-end py-0 fs-8"><?= number_format($sumaSubTotalTapado, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php foreach ($dat['lstpagos'] as $k => $v) : ?>
                                <div class="row">
                                    <div class="col-5 fs-9"><?= $v['nomPago'] ?></div>
                                    <div class="col-4 text-end fs-9"><?= number_format(floatval($v['nImporte']), 2) ?></div>
                                    <div class="col"></div>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-0 fw-bold fs-8">20 DÍAS PARA CAMBIOS <strong>CONSULTE TÉRMINOS Y CONDICIONES</strong></td>
                    </tr>
                <?php elseif ($tipoDocumento == 'enviochof' || $tipoDocumento == 'entregachof') : ?>
                    <tr>
                        <td class="py-0 fw-bold fs-8">20 DÍAS PARA CAMBIOS <strong>CONSULTE TÉRMINOS Y CONDICIONES</strong></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php
    // var_dump($dat['registros']);
    echo '</div></div></div>';      // cerramos contenedor detalle
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/js/themes/default/style.min.css') ?>">

    <script src="<?= base_url('assets/js/jquery.js') ?>"> </script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"> </script>
    <title></title>
    <?php generaCssImpresion(); ?>
    <style>
        div.imprimir * {
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>

<body>
    <div class="imprimir d-sm-none" style="width: 100%;">
        <?php
        if ($destino == 'venta' || $destino == 'back') :
            // imprime($dat, 'caja', 'ticket');
            imprime($dat, 'cliente', 'ticket');
        endif;
        // if (($destino == 'venta' || $destino == 'entrega') &&
        //     intval($nIdEntrega) > 0 && $bParaEntrega == '1'
        // ) :
        //     imprime($dat, 'entregabod', 'ticket');
        //     if ($destino == 'entrega') {
        //         imprime($dat, 'entregacli', 'ticket');
        //         imprime($dat, 'entregacaj', 'ticket');
        //     }
        // endif;

        ?>
    </div>
    <?php $dat['bDocumentoImpreso'] = false; ?>
    <div class="imprimir d-none d-sm-block pt-2" style="width: 100%;">
        <?php
        if ($destino == 'venta' || $destino == 'back') {
            // imprime($dat, 'caja', 'normal');
            imprime($dat, 'cliente', 'normal');
        }
        // if ($destino == 'venta' || $destino == 'entrega') {
        //     if ($bParaEntrega != '0') {
        //         imprime($dat, 'entregabod', 'normal');
        //         if ($bSeEntregaTodo === false || $bQuienRecogeEntrega == '2' || ($bSeEntregaTodo && $destino == 'entrega')) {
        //             imprime($dat, 'entregacli', 'normal');
        //             imprime($dat, 'entregacaj', 'normal');
        //         }
        //         if ($bQuienRecogeEntrega == '2') {
        //             imprime($dat, 'entregacho', 'normal');
        //             imprime($dat, 'entregaord', 'normal');
        //         }
        //     }

        //     if ($bParaEnvio != '0') {
        //         imprime($dat, 'enviobod', 'normal');
        //         if ($bSeEnviaTodo === false) {
        //             imprime($dat, 'enviocli', 'normal');
        //             imprime($dat, 'enviocaj', 'normal');
        //         }
        //         imprime($dat, 'enviocho', 'normal');
        //         if ($bParaEnvio == '2') {
        //             imprime($dat, 'envioord', 'normal');
        //         }
        //     }
        // }
        /*
        if ($destino == 'entregaos') {
            if ($bParaEntrega != '0') {
                imprime($dat, 'entregabod', 'normal');
                imprime($dat, 'entregacli', 'normal');
                imprime($dat, 'entregacaj', 'normal');
            }
        }
        */
        ?>
    </div>
</body>
<script>
    $(document).ready(function() {
        let destino = '<?= $destino ?>';
        let cierraVentana = <?= $cierraVentana ?>;
        window.print();
        if (destino == 'venta' && !cierraVentana) {
            location.href = "<?= $cmd ?>";
        } else if (destino == 'back' || destino == 'entrega') {
            history.back();
        } else if (destino == 'entregaos') {
            location.href = "<?= base_url('imprimeentrega') ?>";
        } else if (destino == 'venta' && cierraVentana) {
            window.close();
        }
    });
</script>

</html>