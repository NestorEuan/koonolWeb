<?php
if ($reiniciaVenta) {
    $cmd = base_url() . '/ventas/nVenta';
} else {
    $cmd = base_url() . '/ventas';
}
$ln = '<br>';   // chr(10)
$datSuc = strtoupper($suc['sDescripcion'] . $ln . $suc['sDireccion']);
$datCli = 'Código: ' . sprintf('%06d', intval($cli['nIdCliente'])) .
    '  RFC: ' . strtoupper(trim($cli['sRFC'])) .
    '  C.P.: ' . $cli['cCP'] . chr(10) .
    $cli['sNombre'] . chr(10) . $cli['sDireccion'];
generaCssImpresion();
?>
<div class="overflow-auto container-fluid imprimir">
    <?php $bPrint = false; ?>

    <?php if ($destino == 'venta' || $destino == 'entrega') : ?>
        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="text-center position-relative" colspan="3">
                        <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" width="50" class="d-inline-block align-text-top position-absolute start-0 top-0">
                        <div class="d-inline-block align-text-top text-center fs-6 ms-3 lh-1">
                            COPIA BODEGA <br>
                            <span class="me-4">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                            <span><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <?=
                        'FERROMAT MOTUL S.A. DE C.V. <br>' .
                            'CALLE 26 TAB. CAT. 4229 MOTUL DE CARRILLO PUERTO <br>' .
                            'MOTUL YUCATAN C.P. 97430 <br>' .
                            'FMO131114PT6'
                        ?>
                    </td>
                    <td></td>
                    <td><?= $datSuc ?></td>
                </tr>
                <tr>
                    <td><?= $datCli ?></td>
                    <td style="min-width:30%;" class="text-center fs-6 fw-bold">ENVIAR A >></td>
                    <td><?= $enviarA ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="px-5 ">
                        <table class="table table-borderless border-bottom border-dark">
                            <thead>
                                <tr id="cabDet">
                                    <th class="text-end">Cantidad<br>a entregar</th>
                                    <th class="">Descripción</th>
                                    <th class="text-end">Entregado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lst as $k => $v) : ?>
                                    <?php if ($v[3] <= 0) continue; ?>
                                    <tr style="line-height:15px;">
                                        <td class="text-end py-0"><?= number_format($v[3], 3) ?></td>
                                        <td class=" py-0"><?= $v[0] ?></td>
                                        <td class="text-start py-0 fw-bold fs-6">=></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="mt-4">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php $bPrint = true; ?>
    <?php endif; ?>


    <?php if ($destino == 'venta' || $destino == 'back') : ?>
        <?php if ($bPrint) : ?>
            <p class="page-break"></p>
        <?php endif; ?>

        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="text-center position-relative" colspan="3">
                        <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" width="50" class="d-inline-block align-text-top position-absolute start-0 top-0">
                        <div class="d-inline-block align-text-top text-center fs-6 ms-3 lh-1">
                            COPIA CAJA <br>
                            <span class="me-4">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                            <span><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <?=
                        'FERROMAT MOTUL S.A. DE C.V. <br>' .
                            'CALLE 26 TAB. CAT. 4229 MOTUL DE CARRILLO PUERTO <br>' .
                            'MOTUL YUCATAN C.P. 97430 <br>' .
                            'FMO131114PT6'
                        ?>
                    </td>
                    <td></td>
                    <td><?= $datSuc ?></td>
                </tr>
                <tr>
                    <td><?= $datCli ?></td>
                    <td style="min-width:30%;" class="text-center fs-6 fw-bold">ENVIAR A >></td>
                    <td><?= $enviarA ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="px-5">
                        <table class="table table-borderless">
                            <thead>
                                <tr id="cabDet">
                                    <th class="text-end">Cantidad</th>
                                    <th class="">Descripción</th>
                                    <th class="text-end">Precio Unitario</th>
                                    <th class="text-end">Importe</th>
                                    <th class="text-end">Descuento</th>
                                    <th class="text-end">Importe Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sum = 0; ?>
                                <?php foreach ($lst as $k => $v) : ?>
                                    <tr style="line-height:15px;">
                                        <td class="text-end py-0"><?= number_format($v[2], 3) ?></td>
                                        <td class=" py-0"><?= esc($v[0]) ?></td>
                                        <td class="text-end py-0"><?= number_format($v[1], 2) ?></td>
                                        <td class="text-end py-0"><?= number_format(round($v[1] * $v[2], 2), 2) ?></td>
                                        <td class="text-end py-0"><?= number_format($v[6], 2) ?></td>
                                        <td class="text-end py-0"><?= number_format(round(($v[1] * $v[2]) - $v[6], 2), 2) ?></td>
                                    </tr>
                                    <?php $sum += round($v[1] * $v[2], 2); ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="border-top" style="line-height:15px;">
                                    <td colspan="5" class="text-end py-0">SUBTOTAL:</td>
                                    <td class="text-end py-0"><?= number_format($sumaSubTotalReal - $descuentoEnProducto, 2) ?></td>
                                </tr>
                                <?php if ($descuentoRemision > 0) : ?>
                                    <tr class="" style="line-height:15px;">
                                        <td colspan="5" class="text-end py-0">DESCUENTO REMISION:</td>
                                        <td class="text-end py-0"><?= number_format($descuentoRemision, 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="" style="line-height:15px;">
                                    <td colspan="5" class="text-end py-0">IVA:</td>
                                    <td class="text-end py-0">
                                        <?= number_format($sumaIVA, 2) ?>
                                    </td>
                                </tr>
                                <tr class="" style="line-height:15px;">
                                    <td colspan="5" class="text-end py-0">TOTAL:</td>
                                    <td class="text-end py-0">
                                        <?= number_format($impTotal, 2) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end py-1 fw-bold">Forma de Pago:</td>
                                    <td></td>
                                </tr>
                                <?php foreach ($lstpagos as $k => $v) : ?>
                                    <tr>
                                        <td colspan="5" class="text-end py-0"><?= $v['nomPago'] ?></td>
                                        <td class="text-end py-0"><?= number_format(floatval($v['nImporte']), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php $bPrint = true; ?>
    <?php endif; ?>


    <?php if ($destino == 'venta' || $destino == 'back') : ?>
        <?php if ($bPrint) : ?>
            <p class="page-break"></p>
        <?php endif; ?>

        <table class="tblImpresion">
            <tbody>
                <tr class="border-bottom border-dark">
                    <td class="text-center position-relative" colspan="3">
                        <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" width="50" class="d-inline-block align-text-top position-absolute start-0 top-0">
                        <div class="d-inline-block align-text-top text-center fs-6 ms-3 lh-1">
                            ORIGINAL CLIENTE<br>
                            <span class="me-4">Folio Remisión: <?= sprintf('%07d', intval($nFolio)) ?></span>
                            <span><?= $fechaImp->format('d/m/Y H:i:s') ?></span>
                        </div>
                    </td>
                </tr>
                <tr class="border-bottom border-dark">
                    <td>
                        <?=
                        'FERROMAT MOTUL S.A. DE C.V. <br>' .
                            'CALLE 26 TAB. CAT. 4229 MOTUL DE CARRILLO PUERTO <br>' .
                            'MOTUL YUCATAN C.P. 97430 <br>' .
                            'FMO131114PT6'
                        ?>
                    </td>
                    <td></td>
                    <td><?= $datSuc ?></td>
                </tr>
                <tr>
                    <td ><?= $datCli ?></td>
                    <td style="min-width:30%;" class="text-center fs-6 fw-bold">ENVIAR A >></td>
                    <td ><?= $enviarA ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="px-5">
                        <table class="table table-borderless border-bottom border-dark">
                            <thead>
                                <tr id="cabDet">
                                    <th class="text-end">Cantidad</th>
                                    <th class="">Descripción</th>
                                    <th class="text-end">Precio Unitario</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sum = 0; ?>
                                <?php foreach ($lst as $k => $v) : ?>
                                    <tr style="line-height:15px;">
                                        <td class="text-end py-0"><?= number_format($v[2], 3) ?></td>
                                        <td class=" py-0"><?= esc($v[0]) ?></td>
                                        <td class="text-end py-0"><?= number_format($v[4], 2) ?></td>
                                        <td class="text-end py-0"><?= number_format(round($v[4] * $v[2], 2), 2) ?></td>
                                    </tr>
                                    <?php $sum += round($v[4] * $v[2], 2); ?>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="4" class="fw-bold">
                                        ** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-top" style="line-height:15px;">
                                    <td colspan="3" class="text-end py-0">SUBTOTAL:</td>
                                    <td class="text-end py-0"><?= number_format($sumaSubTotalTapado, 2) ?></td>
                                </tr>
                                <?php if ($bonificaciones > 0) : ?>
                                    <tr class="" style="line-height:15px;">
                                        <td colspan="3" class="text-end py-0">BONIFICACION:</td>
                                        <td class="text-end py-0"><?= number_format($bonificaciones, 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="" style="line-height:15px;">
                                    <td colspan="3" class="text-end py-0">IVA:</td>
                                    <td class="text-end py-0">
                                        <?= number_format($sumaIVA, 2) ?>
                                    </td>
                                </tr>
                                <tr class="" style="line-height:15px;">
                                    <td colspan="3" class="text-end py-0">TOTAL:</td>
                                    <td class="text-end py-0">
                                        <?= number_format($impTotal, 2) ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <p class="mt-4">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</p>
                        <p class="mt-2">
                            Distancia de acarreo hasta 10 Mts. Si requiere su
                            material a más de 10 Mts. pero menor a 20 Mts.
                            el costo del camion de 3 TN sera de $80.00
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<script>
    $(document).ready(function() {
        let destino = '<?= $destino ?>';
        $('#mainCnt').addClass('container-fluid').removeClass('container')[0].style.height = 'auto';
        $('#mainCnt > div').removeClass('row');
        $('footer').remove()
        window.print();
        if (destino == 'venta') {
            location.href = "<?= $cmd ?>";
        } else if (destino == 'back' || destino == 'entrega') {
            history.back();
        }
    });
</script>