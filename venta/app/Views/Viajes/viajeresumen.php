<div class="container-fluid position-relative">
    <div class="row" id="cntTitulo">
        <div class="col">
            <h4 class="position-relative">Resumen envios
                <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close" title="Cerrar"></button>
            </h4>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col" id="cntTablaEnvios">
            <table class="table table-borderless">
                <thead>
                    <tr class="border-3 border-dark border-top-0 border-end-0 border-start-0">
                        <th class="text-center">Folio<br>Envio</th>
                        <th class="text-center">Origen</th>
                        <th class="text-center">Folio<br>Origen</th>
                        <th class="text-center" style="width:190px;">Fechas:<br>De Alta/Solicitada</th>
                        <th class="text-center" style="width:150px;">Peso</th>
                    </tr>
                </thead>
                <tbody id="cntTablaEnviosBody">
                    <?php $nPesoViaje = 0; ?>
                    <?php $nPar = true; ?>
                    <?php $nContReg = 0; ?>
                    <?php foreach ($registros as $k => $r) : ?>
                        <?php
                        $nPesoViaje += round(floatval($r['peso']), 3);
                        $nPar = !$nPar;
                        $nContReg++;
                        ?>
                        <tr class="">
                            <td class="text-center"><?= $k ?></td>
                            <td class="text-center"><?= $r['cOrigen'] == 'ventas' ? 'Remision' : 'Traspaso' ?></td>
                            <td class="text-center"><?= $r['cOrigen'] == 'ventas' ? $r['nFolioRemision'] : $r['nFolioTraspaso'] ?></td>
                            <td class="text-center"><?= $r['fechas'] ?></td>
                            <td class="text-center fw-bold"><?= round(floatval($r['peso']) / 1000, 3) > 1.00 ? sprintf('%01.2f Tons', floatval($r['peso']) / 1000) : round(floatval($r['peso']), 3) . 'Kg.'   ?></td>
                        </tr>
                        <tr class=" border-bottom">
                            <td colspan="5" class="py-0">
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
                        <tr class="border-bottom border-dark">
                            <td colspan="5" class="bg-primary bg-opacity-10">
                                <table class="table table-sm">
                                    <thead>
                                        <th class="text-center" style="width:80px;">Id</th>
                                        <th>Descripcion</th>
                                        <th class="text-center" style="width:100px;">Cantidad</th>
                                        <th class="text-end" style="width:150px;">Peso</th>
                                        <th class="text-center">ENV</th>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($r['det'] as $kk => $rr) : ?>
                                            <tr>
                                                <td class="text-center"><?= $rr[0] ?></td>
                                                <td><?= $rr[5] ?></td>
                                                <td class="text-center"><?= $rr[2] ?></td>
                                                <td class="text-end"><?= round(floatval($rr[6]) / 1000, 3) > 1.00 ? number_format(floatval($rr[6]) / 1000, 2) . '<span class="text-start ps-2 d-inline-block" style="width:40px;">Tons.</span>' : number_format(floatval($rr[6]), 2) . '<span class="text-start ps-2 d-inline-block" style="width:40px;">Kg.</span>'   ?></td>
                                                <td class="text-center">
                                                    <i class="bi bi-check-square-fill text-success me-2 <?= $rr[4] == '1' ? '' : 'd-none' ?>"></i>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="d-flex justify-content-around align-items-center pt-3">
                <button type="button" class="btn btn-outline-secondary w-50 mx-2" data-bs-dismiss="modal" id="btnSalirDeResumir">Salir</button>
            </div>
        </div>
    </div>
</div>