<?php
if ($reiniciaVenta) {
    $cmd = base_url() . '/ventas/nVenta';
} else {
    $cmd = base_url() . '/ventas';
}
$cmdRemisiones = base_url() . '/remisiones';
$ln = '<br>';   // chr(10)
$datSuc = strtoupper($suc['sDescripcion'] . $ln . $suc['sDireccion']);
$datCli = '<strong>CÓdigo: ' . sprintf('%06d ', intval($cli['nIdCliente'])) .
    $cli['sNombre'] . '</strong>' . $ln .
    '  RFC: ' . strtoupper(trim($cli['sRFC'])) .
    '  C.P.: ' . $cli['cCP'] . $ln .
    $cli['sDireccion'];
generaCssImpresion();
?>
<div class="overflow-auto container-fluid imprimir">
    <?php $bPrint = false; ?>

    <?php if (($destino == 'venta' || $destino == 'entrega') && intval($nIdEntrega) > 0) : ?>
        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="position-relative">
                        <div class="row d-sm-none">
                            <div class="col-12">
                                <div class="row justify-content-center">
                                    <div class="col-5">
                                        <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col fw-bold fs-6 text-center lh-1">COPIA BODEGA</div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1"><?= $fechaImp->format('d/m/Y H:i:s') ?></div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1">Folio Entrega: <?= sprintf('%07d', intval($nIdEntrega)) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block mt-3">
                            <div class="col align-text-top text-center">
                                <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" width="80" class="float-start">
                                <h6>COPIA BODEGA</h6>
                                <span class="me-4 fs-8">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                                <span class="fs-8"><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                                <span class="fs-8">Folio Entrega: <?= sprintf('%07d', intval($nIdEntrega)) ?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <div class="row justify-content-between">
                            <div class="col-12 col-sm-5 text-center fs-9 mb-1 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $rzLeyenda) ?></div>
                            <div class="col-12 col-sm-5 text-center fs-9 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $datSuc) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1">
                                <?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $datCli)) ?>
                            </div>
                            <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1">ENVIAR A >></div>
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1 fw-bold"><?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $enviarA)) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row d-sm-none">
                            <div class="col">
                                <table class="table table-sm table-borderless">
                                    <thead>
                                        <tr>
                                            <th class="my-0 py-0 lh-1">
                                                <div class="row lh-1 border-bottom border-top border-dark">
                                                    <div class="col-2 text-center fs-9 lh-1">Cantidad a entregar</div>
                                                    <div class="col-6 text-center fs-9 lh-1">Descripcion</div>
                                                    <div class="col-3 text-center fs-9 lh-1">Entregado</div>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lst as $k => $v) : ?>
                                            <?php if ($v[3] <= 0) continue; ?>
                                            <tr>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-2 text-center fs-9 lh-1"><?= number_format($v[3], 0) ?></div>
                                                        <div class="col-6 text-start fs-9 lh-1"><?= esc($v[0]) ?></div>
                                                        <div class="col-3 text-start fs-6 lh-1">=></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td class="fw-bold fs-10  border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block">
                            <table class="table table-borderless px-4">
                                <thead>
                                    <tr id="cabDet">
                                        <th class="text-end fw-bold fs-8 py-0">Cantidad a entregar</th>
                                        <th class=" fw-bold fs-8 py-0">Descripción</th>
                                        <th class="text-end fw-bold fs-8 py-0">Entregado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lst as $k => $v) : ?>
                                        <?php if ($v[3] <= 0) continue; ?>
                                        <tr>
                                            <td class="text-end fs-9 py-0"><?= number_format($v[3], 0) ?></td>
                                            <td class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                                            <td class="text-start fs-9 py-0">=></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="3" class="fw-bold fs-8 border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php $bPrint = true; ?>
    <?php endif; ?>


    <?php if ($destino == 'venta' || $destino == 'back') : ?>
        <?php if ($bPrint) : ?>
            <p class="d-none page-break d-sm-block"></p>
            <div class="d-sm-none border-top w-100 border-dark my-3 border-4"></div>
        <?php endif; ?>

        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="position-relative">
                        <div class="row d-sm-none">
                            <div class="col-12">
                                <div class="row justify-content-center">
                                    <div class="col-5">
                                        <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col fw-bold fs-6 text-center lh-1">COPIA CAJA</div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></div>
                                </div>
                                <?php if ($nomUsu != '') : ?>
                                    <div class="row">
                                        <div class="col fs-8 text-center lh-1">Vendedor: <?= $nomUsu ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1"><?= $fechaImp->format('d/m/Y H:i:s') ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block mt-3">
                            <div class="col align-text-top text-center">
                                <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" width="80" class="float-start">
                                <h6>COPIA CAJA</h6>
                                <span class="me-4 fs-8">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                                <?php if ($nomUsu != '') : ?>
                                    <span class="me-4 fs-8">Vendedor: <?= $nomUsu ?></span>
                                <?php endif; ?>
                                <span class="fs-8"><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <div class="row justify-content-between">
                            <div class="col-12 col-sm-5 text-center fs-9 mb-1 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $rzLeyenda) ?></div>
                            <div class="col-12 col-sm-5 text-center fs-9 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $datSuc) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1">
                                <?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $datCli)) ?>
                            </div>
                            <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1">ENVIAR A >></div>
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1 fw-bold"><?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $enviarA)) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row d-sm-none">
                            <div class="col">
                                <table class="table table-sm table-borderless border-bottom border-dark">
                                    <thead>
                                        <tr>
                                            <th class="my-0 py-0 lh-1">
                                                <div class="row lh-1 border-bottom border-top border-dark">
                                                    <div class="col-3 text-end fs-9 lh-1">Cantidad</div>
                                                    <div class="col-3 text-end fs-9 lh-1">Precio</div>
                                                    <div class="col-3 text-end fs-9 lh-1">Importe</div>
                                                    <div class="col"></div>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $sum = 0; ?>
                                        <?php foreach ($lst as $k => $v) : ?>
                                            <tr>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-3 text-end fs-9 lh-1"><?= number_format($v[2], 2) ?></div>
                                                        <div class="col-3 text-end fs-9 lh-1"><?= number_format($v[1], 2) ?></div>
                                                        <div class="col-3 text-end fs-9 lh-1"><?= number_format(round(($v[1] * $v[2]) - $v[6], 2), 2) ?></div>
                                                        <div class="col"></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-9 fs-9 lh-1"><?= esc($v[0]) ?></div>
                                                        <div class="col"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php $sum += round($v[1] * $v[2], 2); ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="row lh-1">
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
                                                <?php foreach ($lstpagos as $k => $v) : ?>
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block">
                            <table class="table table-borderless">
                                <thead>
                                    <tr id="cabDet">
                                        <th class="text-end fw-bold fs-8 py-0">Cantidad</th>
                                        <th class=" fw-bold fs-8 py-0">Descripción</th>
                                        <th class="text-end fw-bold fs-8 py-0">Precio Unitario</th>
                                        <th class="text-end fw-bold fs-8 py-0">Importe</th>
                                        <th class="text-end fw-bold fs-8 py-0">Descuento</th>
                                        <th class="text-end fw-bold fs-8 py-0">Importe Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sum = 0; ?>
                                    <?php foreach ($lst as $k => $v) : ?>
                                        <tr>
                                            <td class="text-end fs-9 py-0"><?= number_format($v[2], 0) ?></td>
                                            <td class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                                            <td class="text-end fs-9 py-0"><?= number_format($v[1], 2) ?></td>
                                            <td class="text-end fs-9 py-0"><?= number_format(round($v[1] * $v[2], 2), 2) ?></td>
                                            <td class="text-end fs-9 py-0"><?= number_format($v[6], 2) ?></td>
                                            <td class="text-end fs-9 py-0"><?= number_format(round(($v[1] * $v[2]) - $v[6], 2), 2) ?></td>
                                        </tr>
                                        <?php $sum += round($v[1] * $v[2], 2); ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
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
                                </tfoot>
                            </table>
                        </div>

                    </td>
                </tr>
            </tbody>
        </table>

        <?php $bPrint = true; ?>
    <?php endif; ?>


    <?php if ($destino == 'venta' || $destino == 'back') : ?>
        <?php if ($bPrint) : ?>
            <p class="d-none page-break d-sm-block"></p>
            <div class="d-sm-none border-top w-100 border-dark my-3 border-4"></div>
        <?php endif; ?>

        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="position-relative">
                        <div class="row d-sm-none">
                            <div class="col-12">
                                <div class="row justify-content-center">
                                    <div class="col-5">
                                        <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col fw-bold fs-6 text-center lh-1">ORIGINAL CLIENTE</div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></div>
                                </div>
                                <?php if ($nomUsu != '') : ?>
                                    <div class="row">
                                        <div class="col fs-8 text-center lh-1">Vendedor: <?= $nomUsu ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1"><?= $fechaImp->format('d/m/Y H:i:s') ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block mt-3">
                            <div class="col align-text-top text-center">
                                <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" width="80" class="float-start">
                                <h6>ORIGINAL CLIENTE</h6>
                                <span class="me-4 fs-8">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                                <?php if ($nomUsu != '') : ?>
                                    <span class="me-4 fs-8">Vendedor: <?= $nomUsu ?></span>
                                <?php endif; ?>
                                <span class="fs-8"><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <div class="row justify-content-between">
                            <div class="col-12 col-sm-5 text-center fs-9 mb-1 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $rzLeyenda) ?></div>
                            <div class="col-12 col-sm-5 text-center fs-9 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $datSuc) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1">
                                <?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $datCli)) ?>
                            </div>
                            <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1">ENVIAR A >></div>
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1 fw-bold"><?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $enviarA)) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row d-sm-none">
                            <div class="col">
                                <table class="table table-sm table-borderless border-bottom border-dark">
                                    <thead>
                                        <tr>
                                            <th class="my-0 py-0 lh-1">
                                                <div class="row lh-1 border-bottom border-top border-dark">
                                                    <div class="col-3 text-end fs-9 lh-1">Cantidad</div>
                                                    <div class="col-3 text-end fs-9 lh-1">Precio</div>
                                                    <div class="col-3 text-end fs-9 lh-1">Importe</div>
                                                    <div class="col"></div>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $sum = 0; ?>
                                        <?php foreach ($lst as $k => $v) : ?>
                                            <tr>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-3 text-end fs-9 lh-1"><?= number_format($v[2]) ?></div>
                                                        <div class="col-3 text-end fs-9 lh-1"><?= number_format($v[4], 2) ?></div>
                                                        <div class="col-3 text-end fs-9 lh-1"><?= number_format(round($v[4] * $v[2], 2), 2) ?></div>
                                                        <div class="col"></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-9 fs-9 lh-1"><?= esc($v[0]) ?></div>
                                                        <div class="col"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php $sum += round($v[4] * $v[2], 2); ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <table class="table table-sm table-borderless">
                                    <tbody>
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block">
                            <div class="col">
                                <table class="table table-borderless">
                                    <thead>
                                        <tr id="cabDet">
                                            <th class="text-end fw-bold fs-8 py-0">Cantidad</th>
                                            <th class=" fw-bold fs-8 py-0">Descripción</th>
                                            <th class="text-end fw-bold fs-8 py-0">Precio Unitario</th>
                                            <th class="text-end fw-bold fs-8 py-0">Importe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $sum = 0; ?>
                                        <?php foreach ($lst as $k => $v) : ?>
                                            <tr>
                                                <td class="text-end fs-9 py-0"><?= number_format($v[2], 0) ?></td>
                                                <td class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                                                <td class="text-end fs-9 py-0"><?= number_format($v[4], 2) ?></td>
                                                <td class="text-end fs-9 py-0"><?= number_format(round($v[4] * $v[2], 2), 2) ?></td>
                                            </tr>
                                            <?php $sum += round($v[4] * $v[2], 2); ?>
                                        <?php endforeach; ?>
                                        <tr class="border-top border-dark">
                                            <td colspan="3" class="text-end py-0 fs-8">TOTAL:</td>
                                            <td class="text-end py-0 fs-8"><?= number_format($sumaSubTotalTapado, 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <div class="row py-0">
                                                    <div class="col fs-8">
                                                        <div class="fw-bold">** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **</div>
                                                        <div class="fw-bold">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</div>
                                                        <div class="lh-1">
                                                            1 - Condiciones de venta: <strong>contado</strong><br>
                                                            2 - Tiempo de entrega <strong>de 48 a 72 horas.</strong><br>
                                                            3 - Condiciones de entrega de material: <strong>máximo 10 mts sujeto a condición de acceso</strong><br>
                                                            4 - Los pagos con cheques son <strong>salvo buen cobro</strong><br>
                                                            5 - Toda devolución o cancelación <strong>genera un 20% de recargo sin excepción</strong>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </td>
                </tr>
            </tbody>
        </table>
        <?php $bPrint = true; ?>
    <?php endif; ?>

    <?php if (($destino == 'venta' || $destino == 'entrega') && $bParaEnvio == '1') : ?>
        <?php if ($bPrint) : ?>
            <p class="d-none page-break d-sm-block"></p>
            <div class="d-sm-none border-top w-100 border-dark my-3 border-4"></div>
        <?php endif; ?>
        <?php $bPrint = true; ?>

        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="position-relative">
                        <div class="row d-sm-none">
                            <div class="col-12">
                                <div class="row justify-content-center">
                                    <div class="col-5">
                                        <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col fw-bold fs-6 text-center lh-1">PARA ENVIO EN BODEGA ** COPIA CAMIONERO **</div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1"><?= $fechaImp->format('d/m/Y H:i:s') ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block mt-3">
                            <div class="col align-text-top text-center">
                                <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" width="80" class="float-start">
                                <h6>PARA ENVIO EN BODEGA ** COPIA CAMIONERO **</h6>
                                <span class="me-4 fs-8">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                                <span class="fs-8"><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <div class="row justify-content-between">
                            <div class="col-12 col-sm-5 text-center fs-9 mb-1 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $rzLeyenda) ?></div>
                            <div class="col-12 col-sm-5 text-center fs-9 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $datSuc) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1">
                                <?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $datCli)) ?>
                            </div>
                            <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1">ENVIAR A >></div>
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1 fw-bold"><?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $enviarA)) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row d-sm-none">
                            <div class="col">
                                <table class="table table-sm table-borderless">
                                    <thead>
                                        <tr>
                                            <th class="my-0 py-0 lh-1">
                                                <div class="row lh-1 border-bottom border-top border-dark">
                                                    <div class="col-2 text-center fs-9 lh-1">Entregar</div>
                                                    <div class="col-6 text-center fs-9 lh-1">Descripcion</div>
                                                    <div class="col-3 text-center fs-9 lh-1">Entregado</div>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lst as $k => $v) : ?>
                                            <?php if ($v[8] <= 0) continue; ?>
                                            <tr>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-2 text-center fs-9 lh-1"><?= number_format($v[8], 0) ?></div>
                                                        <div class="col-6 text-start fs-9 lh-1"><?= esc($v[0]) ?></div>
                                                        <div class="col-3 text-start fs-6 lh-1">=></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block">
                            <table class="table table-borderless px-4">
                                <thead>
                                    <tr id="cabDet">
                                        <th class="text-end fw-bold fs-8 py-1">Cantidad a entregar</th>
                                        <th class=" fw-bold fs-8 py-0">Descripción</th>
                                        <th class="text-end fw-bold fs-8 py-0">Entregado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lst as $k => $v) : ?>
                                        <?php if ($v[8] <= 0) continue; ?>
                                        <tr>
                                            <td class="text-end fs-9 py-0"><?= number_format($v[8], 0) ?></td>
                                            <td class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                                            <td class="text-start fs-9 py-0">=></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="3" class="fs-8 border-top border-dark lh-1">
                                            <strong>** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</strong><br>
                                            1 - Condiciones de venta: <strong>contado</strong><br>
                                            2 - Tiempo de entrega <strong>de 48 a 72 horas.</strong><br>
                                            3 - Condiciones de entrega de material: <strong>máximo 10 mts sujeto a condición de acceso</strong><br>
                                            4 - Los pagos con cheques son <strong>salvo buen cobro</strong><br>
                                            5 - Toda devolución o cancelación <strong>genera un 20% de recargo sin excepción</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if ($bPrint) : ?>
            <p class="d-none page-break d-sm-block"></p>
            <div class="d-sm-none border-top w-100 border-dark my-3 border-4"></div>
        <?php endif; ?>

        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="position-relative">
                        <div class="row d-sm-none">
                            <div class="col-12">
                                <div class="row justify-content-center">
                                    <div class="col-5">
                                        <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col fw-bold fs-6 text-center lh-1">PARA ENVIO EN BODEGA ** COPIA ALMACEN **</div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col fs-8 text-center lh-1"><?= $fechaImp->format('d/m/Y H:i:s') ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block mt-3">
                            <div class="col align-text-top text-center">
                                <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" width="80" class="float-start">
                                <h6>PARA ENVIO EN BODEGA ** COPIA ALMACEN **</h6>
                                <span class="me-4 fs-8">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                                <span class="fs-8"><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <div class="row justify-content-between">
                            <div class="col-12 col-sm-5 text-center fs-9 mb-1 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $rzLeyenda) ?></div>
                            <div class="col-12 col-sm-5 text-center fs-9 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $datSuc) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1">
                                <?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $datCli)) ?>
                            </div>
                            <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1">ENVIAR A >></div>
                            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1 fw-bold"><?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $enviarA)) ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="row d-sm-none">
                            <div class="col">
                                <table class="table table-sm table-borderless">
                                    <thead>
                                        <tr>
                                            <th class="my-0 py-0 lh-1">
                                                <div class="row lh-1 border-bottom border-top border-dark">
                                                    <div class="col-2 text-center fs-9 lh-1">Entregar</div>
                                                    <div class="col-6 text-center fs-9 lh-1">Descripcion</div>
                                                    <div class="col-3 text-center fs-9 lh-1">Entregado</div>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lst as $k => $v) : ?>
                                            <?php if ($v[8] <= 0) continue; ?>
                                            <tr>
                                                <td>
                                                    <div class="row">
                                                        <div class="col-2 text-center fs-9 lh-1"><?= number_format($v[8], 0) ?></div>
                                                        <div class="col-6 text-start fs-9 lh-1"><?= esc($v[0]) ?></div>
                                                        <div class="col-3 text-start fs-6 lh-1">=></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row d-none d-sm-block">
                            <table class="table table-borderless px-4">
                                <thead>
                                    <tr id="cabDet">
                                        <th class="text-end fw-bold fs-8 py-1">Cantidad a entregar</th>
                                        <th class=" fw-bold fs-8 py-0">Descripción</th>
                                        <th class="text-end fw-bold fs-8 py-0">Entregado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lst as $k => $v) : ?>
                                        <?php if ($v[8] <= 0) continue; ?>
                                        <tr>
                                            <td class="text-end fs-9 py-0"><?= number_format($v[8], 0) ?></td>
                                            <td class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                                            <td class="text-start fs-9 py-0">=></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if ($bParaEnvioAotraSucursal == '1') : ?>
            <?php if ($bPrint) : ?>
                <p class="d-none page-break d-sm-block"></p>
                <div class="d-sm-none border-top w-100 border-dark my-3 border-4"></div>
            <?php endif; ?>
            <table class="tblImpresion">
                <tbody>
                    <tr class="border-bottom border-dark">
                        <td class="position-relative">
                            <div class="row d-sm-none">
                                <div class="col-12">
                                    <div class="row justify-content-center">
                                        <div class="col-5">
                                            <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col fw-bold fs-6 text-center lh-1">ORDEN DE COMPRA</div>
                                    </div>
                                    <div class="row">
                                        <div class="col fs-8 text-center lh-1">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col fs-8 text-center lh-1"><?= $fechaImp->format('d/m/Y H:i:s') ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row d-none d-sm-block mt-3">
                                <div class="col align-text-top text-center">
                                    <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" width="80" class="float-start">
                                    <h6>ORDEN DE COMPRA</h6>
                                    <span class="me-4 fs-8">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                                    <span class="fs-8"><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-bottom border-dark">
                        <td>
                            <div class="row justify-content-between">
                                <div class="col-12 col-sm-5 text-center fs-9 mb-1 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $rzLeyenda) ?></div>
                                <div class="col-12 col-sm-5 text-center fs-9 lh-1"><?= str_replace(array("\r\n", "\r", "\n"), " ", $datSuc) ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1">
                                    <?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $datCli)) ?>
                                </div>
                                <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1">ENVIAR A >></div>
                                <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1"><?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $enviarA)) ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row d-sm-none">
                                <div class="col">
                                    <table class="table table-sm table-borderless">
                                        <thead>
                                            <tr>
                                                <th class="my-0 py-0 lh-1">
                                                    <div class="row lh-1 border-bottom border-top border-dark">
                                                        <div class="col-2 text-center fs-9 lh-1">Entregar</div>
                                                        <div class="col-6 text-center fs-9 lh-1">Descripcion</div>
                                                        <div class="col-3 text-center fs-9 lh-1">Entregado</div>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lst as $k => $v) : ?>
                                                <?php if ($v[8] <= 0) continue; ?>
                                                <tr>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-2 text-center fs-9 lh-1"><?= number_format($v[8], 0) ?></div>
                                                            <div class="col-6 text-start fs-9 lh-1"><?= esc($v[0]) ?></div>
                                                            <div class="col-3 text-start fs-6 lh-1">=></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row d-none d-sm-block">
                                <table class="table table-borderless px-4">
                                    <thead>
                                        <tr id="cabDet">
                                            <th class="text-end fw-bold fs-8 py-1">Cantidad a entregar</th>
                                            <th class=" fw-bold fs-8 py-0">Descripción</th>
                                            <th class="text-end fw-bold fs-8 py-0">Entregado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lst as $k => $v) : ?>
                                            <?php if ($v[8] <= 0) continue; ?>
                                            <tr>
                                                <td class="text-end fs-9 py-0"><?= number_format($v[8], 0) ?></td>
                                                <td class=" fs-9 py-0"><?= esc($v[0]) ?></td>
                                                <td class="text-start fs-9 py-0">=></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
    $(document).ready(function() {
        let destino = '<?= $destino ?>';
        $('#mainCnt').addClass('container-fluid').removeClass('container')[0].style.height = 'auto';
        $('#mainCnt > div').removeClass('row');
        $('footer').remove()
        // $("#btnEnviaImpresionDoc").click();
        window.print();
        // window.print();
        if (destino == 'venta') {
            location.href = "<?= $cmd ?>";
        } else if (destino == 'back' || destino == 'entrega') {
            location.href = "<?= $cmdRemisiones ?>";
        }
    });
</script>