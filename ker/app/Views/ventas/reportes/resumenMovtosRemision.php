<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="row border rounded">
                <div class="col fs-5 text-center position-relative">
                    <span>Folio Remision: </span><span class="fw-bold text-primary"><?= $regVenta[0]['nFolioRemision'] ?></span>
                    <span class="ms-4">Fecha: </span><span><?= (new DateTime($regVenta[0]['dtAlta']))->format('d-m-Y') ?></span>
                    <span class="ms-4 me-1">Capturó:</span><span><?= $regVenta[0]['nomUsuario'] ?></span>
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="row">
                <div class="col-3"></div>
                <div class="col">
                    <button class="accordion-button py-1 border rounded fw-bold text-center collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tblDetRem"><span class="flex-grow-1">Detalle Remisión</span></button>
                    <div id="tblDetRem" class="accordion-collapse collapse">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:20%;">Cantidad Vendida</th>
                                    <th>Producto</th>
                                    <th class="text-center" style="width:20%;">Por Surtir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($regVentadet as $r) : ?>
                                    <tr>
                                        <td class="text-center"><?= round(floatval($r['nCant']), 3) ?></td>
                                        <td><?= $r['sDescripcion'] ?></td>
                                        <td class="text-center"><?= round(floatval($r['nPorEntregar']), 3) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-3"></div>
            </div>
            <?php if ($conEntregas) : ?>
                <div class="container-fluid px-0">
                    <div class="row">
                        <div class="col-3"></div>
                        <div class="col">
                            <button class="accordion-button border rounded fs-5 fw-bold text-center py-1 mt-2 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secEntregas">
                                <span class="flex-grow-1">Entregas</span>
                            </button>
                        </div>
                        <div class="col-3"></div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div id="secEntregas" class="accordion-collapse collapse">
                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col">
                                        <?php
                                        $folActual = '0';
                                        // $pie = '</tbody></table></div></div><div class="col-3"></div></div>';
                                        $pie = '</tbody></table></div></div>';
                                        ?>
                                        <?php foreach ($registros2 as $r) : ?>
                                            <?php if ($folActual != $r['id']) : ?>
                                                <?php
                                                if ($folActual != '0') echo $pie;
                                                $folActual = $r['id'];
                                                ?>
                                                <div class="row">
                                                    <div class="col fs-6 text-center bg-info bg-opacity-10">
                                                        <span>Folio Entrega: </span><strong><?= $r['id'] ?></strong>
                                                        <span class="ms-4">Fecha: </span><span><?= (new DateTime($r['dtAlta']))->format('d-m-Y') ?></span>
                                                        <span class="ms-4">Sucursal donde se entregó:</span><span><?= $r['sucEntrega'] ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                echo '<div class="row">' .
                                                    '<div class="col">' .
                                                    '<table class="table table-sm table-striped">' .
                                                    '<thead><tr><th class="text-center">Cantidad</th><th>Producto</th><th class="text-center">Surtido</th></tr></thead>' .
                                                    '<tbody>';
                                                ?>
                                            <?php endif; ?>
                                            <tr>
                                                <td class="text-center" style="width:15%;"><?= round(floatval($r['cant']), 3) ?></td>
                                                <td><?= $r['sDescripcion'] ?></td>
                                                <td class="text-center" style="width:15%;"><?= round(floatval($r['fRecibido']), 3) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if ($folActual != '0') echo $pie; ?>
                                    </div>
                                    <div class="col-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($conEnvios) : ?>
                <div class="container-fluid px-0">
                    <div class="row">
                        <div class="col-3"></div>
                        <div class="col">
                            <button class="accordion-button border rounded fs-5 fw-bold text-center py-1 mt-2 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secEnvios">
                                <span class="flex-grow-1">Envios</span>
                            </button>
                        </div>
                        <div class="col-3"></div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div id="secEnvios" class="accordion-collapse collapse">


                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col">
                                        <?php
                                        $folActual = '0';
                                        $pie = '</tbody></table></div></div>';
                                        ?>
                                        <?php foreach ($registros as $r) : ?>
                                            <?php if ($folActual != $r['id']) : ?>
                                                <?php if ($folActual != '0') : ?>
                                                    <?php echo $pie; ?>
                                                    <?php foreach (($regviajes[$folActual] ?? []) as $vk => $v) : ?>
                                                        <div class="row px-4">
                                                            <div class="col fs-6 text-center bg-warning bg-opacity-50">
                                                                <span>Folio Viaje: </span><strong><?= $vk ?></strong>
                                                            </div>
                                                        </div>
                                                        <div class="row px-4">
                                                            <div class="col">
                                                                <table class="table table-sm table-success table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Producto</th>
                                                                            <th class="text-center">Cantidad Surtida</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($v['det'] as $dd) : ?>
                                                                            <tr>
                                                                                <td><?= $dd[1] ?></td>
                                                                                <td class="text-center" style="width:20%;"><?= round(floatval($dd[2]), 3) ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php $folActual = $r['id']; ?>
                                                <div class="row">
                                                    <div class="col fs-6 text-center bg-warning bg-opacity-10">
                                                        <span>Folio Envio: </span><strong><?= $r['id'] ?></strong>
                                                        <span class="ms-4">Fecha: </span><span><?= (new DateTime($r['dtAlta']))->format('d-m-Y') ?></span>
                                                        <span class="ms-4">Sucursal de envio:</span><span><?= $r['sucEntrega'] ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                echo '<div class="row">' .
                                                    '  <div class="col">' .
                                                    '   <table class="table table-sm table-striped">' .
                                                    '   <thead><tr><th class="text-center">Cantidad</th><th>Producto</th><th class="text-center">Surtido</th><th class="text-center">Modo ENV</th></tr></thead>' .
                                                    '    <tbody>';
                                                ?>
                                            <?php endif; ?>
                                            <tr>
                                                <td style="width:15%;" class="text-center"><?= round(floatval($r['cant']), 3) ?></td>
                                                <td><?= $r['sDescripcion'] ?></td>
                                                <td style="width:15%;" class="text-center"><?= round(floatval($r['fRecibido']), 3) ?></td>
                                                <td style="width:10%;" class="text-center"><?= $r['cModoEnv'] == '1' ? '**' : '' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if ($folActual != '0') : ?>
                                            <?php echo $pie; ?>
                                            <?php foreach (($regviajes[$folActual] ?? []) as $vk => $v) : ?>
                                                <div class="row px-4">
                                                    <div class="col fs-6 text-center bg-warning bg-opacity-50">
                                                        <span>Folio Viaje: </span><strong><?= $vk ?></strong>
                                                    </div>
                                                </div>
                                                <div class="row px-4">
                                                    <div class="col">
                                                        <table class="table table-sm table-success table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Producto</th>
                                                                    <th class="text-center">Cantidad Surtida</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($v['det'] as $dd) : ?>
                                                                    <tr>
                                                                        <td><?= $dd[1] ?></td>
                                                                        <td class="text-center" style="width:20%;"><?= round(floatval($dd[2]), 3) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-3"></div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>