<?php
$cmd = base_url() . '/cortecaja';
$ln = '<br>';   // chr(10)

$aDias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
$aMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$fec = new DateTime($corte['dtApertura']);
$sF = $fec->format('dmYw');
$sH = $fec->format('h:i');
$fecha = $aDias[intval(substr($sF, 8))] . ', ' .
    substr($sF, 0, 2) . ' de ' . $aMeses[intval(substr($sF, 2, 2)) - 1] .
    ' del ' . substr($sF, 4, 4) . '. (' . $sH . ')';
if ($corte['dtCierre'] == null) {
    $fechacierre = 'CAJA ABIERTA';
} else {
    $fec = new DateTime($corte['dtCierre']);
    $sF = $fec->format('dmYw');
    $sH = $fec->format('h:i');
    $fechacierre = $aDias[intval(substr($sF, 8))] . ', ' .
        substr($sF, 0, 2) . ' de ' . $aMeses[intval(substr($sF, 2, 2)) - 1] .
        ' del ' . substr($sF, 4, 4) . '. (' . $sH . ')';
}

generaCssImpresion();
?>

<div class="overflow-auto container-fluid imprimir fs-7 pt-3">
    <table class="tblImpresion">
        <tbody>
            <tr class="border-bottom border-dark">
                <td class="text-center">
                    <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" width="90" class="d-inline-block align-text-top">
                    <div class="d-inline-block align-text-top text-center fs-6 ms-3 lh-1">
                        CORTE DE CAJA (<?= $corte['nomUsu'] . ' CAJA ' . $corte['nNumCaja'] ?>
                        <?= ($corte['dtCierre'] == null ? '** ACTIVA **' : '') ?> )<br>
                        <span class="textNormal12">SUCURSAL: <?= strtoupper($corte['nomSuc']) ?></span><br>
                        <span class="textNormal12">FECHA DE CORTE: <?= strtoupper($fecha) ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <table class="table table-bordered">
                        <tbody>
                            <tr >
                                <th class="text-center fs-8 fw-bold py-0"># REMISION</th>
                                <th class="text-center fs-8 fw-bold py-0">TOTAL</th>
                                <th class="text-center fs-8 fw-bold py-0"># REMISION</th>
                                <th class="text-center fs-8 fw-bold py-0"># FACTURA</th>
                                <th class="text-center fs-8 fw-bold py-0">TOTAL</th>
                            </tr>
                            <?php foreach ($corteResumen['lstventas'] as $v) : ?>
                                <tr>
                                    <td class="text-center fs-8 py-0"><?= $v[1][0] ?></td>
                                    <td class="text-end    fs-8 py-0"><?= $v[1][2] ?></td>
                                    <td class="text-center fs-8 py-0"><?= $v[0][0] ?></td>
                                    <td class="text-center fs-8 py-0"><?= $v[0][1] ?></td>
                                    <td class="text-end    fs-8 py-0"><?= $v[0][2] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td class="fs-8 fw-bold py-0">T. REMISIONES.</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format($corteResumen['sumventas'][1], 2) ?></td>
                                <td colspan="2" class="fs-8 fw-bold py-0">T. FACTURA</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format($corteResumen['sumventas'][0], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-center fs-8 fw-bold py-0">TOTAL VENTAS</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format($corteResumen['sumventas'][0] + $corteResumen['sumventas'][1], 2) ?></td>
                            </tr>

                            <?php
                            $nSumPagos = 0;
                            $nImporteEfectivo = 0.0;
                            ?>
                            <?php foreach ($corteResumen['lstPag'] as $v) : ?>
                                <tr>
                                    <td colspan="4" class="text-center py-0 fs-8 py-0"><?= $v['sDescripcion'] ?></td>
                                    <td class="text-end py-0 fs-8 py-0"><?= number_format(floatval($v['nImporte']), 2) ?></td>
                                </tr>
                                <?php
                                $nSumPagos += floatval($v['nImporte']);
                                if ($v['efectivo'] == '1') $nImporteEfectivo = round(floatval($v['nImporte']), 2);
                                ?>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">TOTAL FORMAS DE PAGO DE LAS VENTAS</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format($nSumPagos, 2) ?></td>
                            </tr>

                            <tr>
                                <td colspan="5" class="fs-7 fw-bold py-0 text-center">EFECTIVO EN CAJA</td>
                            </tr>

                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">SALDO INICIAL EN CAJA(+)</td>
                                <?php $saldoIni = floatval($corte['nSaldoIni'] ?? 0.0);  ?>
                                <td class="text-end fs-8 fw-bold py-0"><?= number_format($saldoIni, 2) ?></td>
                            </tr>

                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">EFECTIVO COBRADO(+)</td>
                                <td class="text-end fs-8 fw-bold py-0"><?= number_format($nImporteEfectivo, 2) ?></td>
                            </tr>

                            <?php $nSum1 = 0; ?>
                            <?php foreach ($corteResumen['lstES'] as $v) : ?>
                                <?php if ($v['cTipoMov'] == 'E') continue; ?>
                                <tr>
                                    <td colspan="4" class="text-center fs-8 fw-bold py-0"><?= $v['sMotivo'] ?></td>
                                    <td class="text-end fs-8 fw-bold py-0"><?= number_format(floatval($v['nImporte']), 2) ?></td>
                                </tr>
                                <?php $nSum1 += floatval($v['nImporte']); ?>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">GASTOS DEL DIA(-)</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format($nSum1, 2) ?></td>
                            </tr>

                            <?php $nSum2 = 0; ?>
                            <?php foreach ($corteResumen['lstDep'] as $v) : ?>
                                <tr>
                                    <td colspan="4" class="fs-8 fw-bold py-0 text-center">Depósito: <?= esc($v['sNombre'] . ' ' . $v['sObservaciones']) ?></td>
                                    <td class="text-end fs-8 fw-bold py-0"><?= number_format(floatval($v['nImporte']), 2) ?></td>
                                </tr>
                                <?php $nSum2 += floatval($v['nImporte']); ?>
                            <?php endforeach; ?>
                            <?php $nSum3 = 0; ?>
                            <?php foreach ($corteResumen['lstES'] as $v) : ?>
                                <?php if ($v['cTipoMov'] == 'S') continue; ?>
                                <tr>
                                    <td colspan="4" class="text-center fs-8 fw-bold py-0"><?= $v['sMotivo'] ?></td>
                                    <td class="text-end fs-8 fw-bold py-0"><?= number_format(floatval($v['nImporte']), 2) ?></td>
                                </tr>
                                <?php $nSum3 += floatval($v['nImporte']); ?>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">EXTRAS DEL DIA(+)</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format($nSum2 + $nSum3, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">TOTAL MOVTOS. (1)</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format($saldoIni + $nImporteEfectivo + $nSum3 + $nSum2 - $nSum1, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">SALDO AL CORTE EN CAJA (2)</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format(floatval($corte['nSaldoFin'] ?? 0), 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="fs-8 fw-bold py-0 text-center">DIFERENCIA: (1) - (2)</td>
                                <td class="fs-8 fw-bold py-0 text-end"><?= number_format(($saldoIni + $nImporteEfectivo - $nSum1 + $nSum2 + $nSum3) - floatval($corte['nSaldoFin'] ?? 0), 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>


    <p class="page-break"></p>
    <table class="tblImpresion">
        <tbody>
            <tr class="border-bottom border-dark">
                <td class="text-center">
                    <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" width="80" class="d-inline-block align-text-top">
                    <div class="d-inline-block align-text-top text-center fs-6 ms-3 lh-1">
                        CORTE DE CAJERO (<?= $corte['nomUsu'] . ' CAJA ' . $corte['nNumCaja'] ?>
                        <?= ($corte['dtCierre'] == null ? '** ACTIVA **' : '') ?> )<br>
                        <span class="textNormal12">SUCURSAL: <?= strtoupper($corte['nomSuc']) ?></span><br>
                        <span class="textNormal10 me-2">APERTURA: <?= strtoupper($fecha) ?></span>
                        <span class="textNormal10">CIERRE: <?= strtoupper($fechacierre) ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr style="border-bottom: 1px solid black;">
                                <th class="text-center fs-8 py-0 fw-bold">Documento</th>
                                <th class="text-center fs-8 py-0 fw-bold">Fecha</th>
                                <th class="text-center fs-8 py-0 fw-bold">Descuentos</th>
                                <th class="text-center fs-8 py-0 fw-bold">Total Docto</th>
                                <th class="text-center fs-8 py-0 fw-bold">Efectivo</th>
                                <th class="text-center fs-8 py-0 fw-bold">Tarjetas</th>
                                <th class="text-center fs-8 py-0 fw-bold">Transferencias</th>
                                <th class="text-center fs-8 py-0 fw-bold">Cheques</th>
                                <th class="text-center fs-8 py-0 fw-bold">Creditos</th>
                            </tr>
                            <?php
                            $sumDesc = 0;
                            $sumTota = 0;
                            $sumEfec = 0;
                            $sumTarj = 0;
                            $sumTran = 0;
                            $sumCheq = 0;
                            $sumCred = 0;
                            ?>
                            <?php foreach ($listadoCajero as $v) : ?>
                                <tr>
                                    <td class="text-center fs-8 py-0">R<?= sprintf('%06d', intval($v['nFolioRemision'])) ?></td>
                                    <td class="text-center fs-8 py-0"><?= $v['dtAlta'] ?></td>
                                    <?php if ($v['cEdo'] == '5') : ?>
                                        <td class="text-end fs-8 py-0" colspan="7">CANCELADO</td>
                                    <?php else : ?>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['sumaDescuento'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['nTotal'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['pagEfec'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['pagTarj'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['pagTran'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['pagCheq'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['pagCred'], 2) ?></td>
                                        <?php
                                        $sumDesc += floatval($v['sumaDescuento']);
                                        $sumTota += floatval($v['nTotal']);
                                        $sumEfec += floatval($v['pagEfec']);
                                        $sumTarj += floatval($v['pagTarj']);
                                        $sumTran += floatval($v['pagTran']);
                                        $sumCheq += floatval($v['pagCheq']);
                                        $sumCred += floatval($v['pagCred']);
                                        ?>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 1px solid black;">
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"><?= number_format($sumDesc, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"><?= number_format($sumTota, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"><?= number_format($sumEfec, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"><?= number_format($sumTarj, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"><?= number_format($sumTran, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"><?= number_format($sumCheq, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fs-8 py-0"><?= number_format($sumCred, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="page-break"></p>
    <table class="tblImpresion">
        <tbody>
            <tr class="border-bottom border-dark">
                <td class="text-center">
                    <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" width="80" class="d-inline-block align-text-top">
                    <div class="d-inline-block align-text-top text-center fs-6 ms-3 lh-1">
                        REPORTE DE VENTAS PARA (<?= $corte['nomUsu'] . ' CAJA ' . $corte['nNumCaja'] ?>
                        <?= ($corte['dtCierre'] == null ? '** ACTIVA **' : '') ?> )<br>
                        <span class="textNormal12">SUCURSAL: <?= strtoupper($corte['nomSuc']) ?></span><br>
                        <span class="textNormal10 me-3">APERTURA: <?= strtoupper($fecha) ?></span>
                        <span class="textNormal10">CIERRE: <?= strtoupper($fechacierre) ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr style="border-bottom: 1px solid black;">
                                <th class="text-center fs-8 py-0 fw-bold">Documento</th>
                                <th class="text-center fs-8 py-0 fw-bold">Fecha</th>
                                <th class="text-center fs-8 py-0 fw-bold">Total Docto</th>
                                <th class="text-center fs-8 py-0 fw-bold">Descuentos</th>
                                <th class="text-center fs-8 py-0 fw-bold">Tarjetas</th>
                                <th class="text-center fs-8 py-0 fw-bold">Creditos</th>
                                <th class="text-center fs-8 py-0 fw-bold">Edo</th>
                                <th class="text-center fs-8 py-0 fw-bold">Caja</th>
                                <th class="text-center fs-8 py-0 fw-bold">Cliente</th>
                                <th class="text-center fs-8 py-0 fw-bold">Agente</th>
                            </tr>
                            <?php
                            $sumTota = 0;
                            $sumDesc = 0;
                            $sumTarj = 0;
                            $sumCred = 0;
                            ?>
                            <?php foreach ($ventas as $v) : ?>
                                <tr>
                                    <td class="text-center fs-8 py-0">R<?= sprintf('%06d', intval($v['nFolioRemision'])) ?></td>
                                    <td class="text-center fs-8 py-0"><?= $v['dtAlta'] ?></td>
                                    <?php if ($v['cEdo'] == '5') : ?>
                                        <td class="text-center fs-8 py-0" colspan="5">CANCELADO</td>
                                    <?php else : ?>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['nTotal'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['sumaDescuento'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['pagTarj'], 2) ?></td>
                                        <td class="text-end fs-8 py-0"><?= number_format($v['pagCred'], 2) ?></td>
                                        <td class="text-end fs-8 py-0">Pagado</td>
                                        <?php
                                        $sumTota += floatval($v['nTotal']);
                                        $sumDesc += floatval($v['sumaDescuento']);
                                        $sumTarj += floatval($v['pagTarj']);
                                        $sumCred += floatval($v['pagCred']);
                                        ?>
                                    <?php endif; ?>
                                    <td class="text-center fs-8 py-0"><?= sprintf('%02d', intval($v['caja'])) ?></td>
                                    <td class="text-left fs-8 py-0"><?= $v['nomCliente'] ?></td>
                                    <td class="text-end fs-8 py-0">NO</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 1px solid black;">
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"><?= number_format($sumTota, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"><?= number_format($sumDesc, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"><?= number_format($sumTarj, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"><?= number_format($sumCred, 2) ?></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"></td>
                                <td class="text-end border-bottom-0 border-start-0 border-end-0 fw-bold fs-8 py-0"></td>
                            </tr>
                        </tfoot>
                    </table>
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
        location.href = "<?= $cmd ?>";
    });
</script>
