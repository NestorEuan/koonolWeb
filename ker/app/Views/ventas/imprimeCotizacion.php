<?php
$ln = '<br>';   // chr(10)

$datSuc = strtoupper($suc['sDescripcion'] . $ln .
    $suc['sDireccion']);
$datCli = 'Cliente: ' . $rv['sNombre'] . '<br>' .
    'Vigencia de la cotizacion hasta el dia: ' .
    $vigencia->format('d-m-Y');
generaCssImpresion();
$nIVAtapado = round($sumaSubTotalTapado * 0.16, 2);

?>

<div class="overflow-auto container-fluid imprimir">
    <table class="tblImpresion">
        <tbody>
            <tr class="border-bottom border-dark">
                <td style="width:33%;"><?= $fechaImp->format('d/m/Y H:i:s') ?></td>
                <td class="text-center fs-6 fw-bold" style="width:33%;">COTIZACION</td>
                <td class="text-end">Folio Cotización: <?= sprintf('%07d', intval($rv['nFolioCotizacion'])) ?></td>
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
                <td colspan="3"><?= $datCli ?></td>
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
                            <?php foreach ($lst as $k => $v) : ?>
                                <tr style="line-height:15px;">
                                    <td class="text-end py-0"><?= number_format($v[2], 3) ?></td>
                                    <td class=" py-0"><?= esc($v[0]) ?></td>
                                    <td class="text-end py-0"><?= number_format($v[4], 2) ?></td>
                                    <td class="text-end py-0"><?= number_format(round($v[4] * $v[2], 2), 2) ?></td>
                                </tr>
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
                            <tr class="" style="line-height:15px;">
                                <td colspan="3" class="text-end py-0">IVA:</td>
                                <td class="text-end py-0">
                                    <?= number_format($nIVAtapado, 2) ?>
                                </td>
                            </tr>
                            <tr class="" style="line-height:15px;">
                                <td colspan="3" class="text-end py-0">TOTAL:</td>
                                <td class="text-end py-0">
                                    <?= number_format($sumaSubTotalTapado + $nIVAtapado, 2) ?>
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

</div>

<script>
    $(document).ready(function() {
        $('#mainCnt').addClass('container-fluid').removeClass('container')[0].style.height = 'auto';
        $('#mainCnt > div').removeClass('row');
        $('footer').remove()
        window.print();
        location.href = "<?= base_url() ?>/ventas/nVenta";
    });
</script>