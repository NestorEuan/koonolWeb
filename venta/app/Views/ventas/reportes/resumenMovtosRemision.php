<div class="container-fluid position-relative">
    <div id="resRemiwAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
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
                                    <th class="text-center" style="width:20%;">Por Asignar</th>
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
            <?php if ($conEfecAnti) : ?>
                <div class="container-fluid px-0">
                    <div class="row">
                        <div class="col-3"></div>
                        <div class="col">
                            <button class="accordion-button py-1 border rounded fw-bold text-center collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tblEfecAnti"><span class="flex-grow-1">Desglose de Pagos con efectivo anticipado</span></button>
                            <div id="tblEfecAnti" class="accordion-collapse collapse">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Id Deposito</th>
                                            <th>Sucursal Origen</th>
                                            <th class="text-end">Cantidad Depositada</th>
                                            <th class="text-end">Saldo</th>
                                            <th class="text-end">Efectivo Anticipado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($regEfecAnti as $r) : ?>
                                            <tr>
                                                <td class="text-center"><?= $r['nIdPagosAdelantados'] ?></td>
                                                <td><?= $r['sucDes'] ?></td>
                                                <td class="text-end"><?= number_format(round(floatval($r['impPagAdel']), 2), 2)   ?></td>
                                                <td class="text-end"><?= number_format(round(floatval($r['nSaldoActual']), 2), 2) ?></td>
                                                <td class="text-end"><?= number_format(round(floatval($r['nImporte']), 2), 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-3"></div>
                    </div>
                </div>
            <?php endif; ?>
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
                                                        <span class="ms-4">Fecha: </span><span><?= (new DateTime($r['dtAlta']))->format('d-m-Y') ?></span><br>
                                                        <span class="ms-4">Fecha Entrega al Cliente: </span><span><?= $r['fecMovto'] == '' ? '' : (new DateTime($r['fecMovto']))->format('d-m-Y') ?></span><br>
                                                        <span class="ms-4">Sucursal donde se entregó:</span><span><?= $r['sucEntrega'] ?></span>
                                                        <?php if (($r['cEstatus'] == '1' || $r['cEstatus'] == '0')  && ($regVenta[0]['cEdo'] != '5' && $regVenta[0]['cEdo'] != '7')) : ?>
                                                            <br><button type="button" class="btn btn-primary btn-sm my-2 btncancelaentregaenvio" data-llamar="<?= '/ventas/rep2/cancelaEntrega/' . $r['id'] ?>">Cancelar Entrega</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php
                                                echo '<div class="row">' .
                                                    '<div class="col">' .
                                                    '<table class="table table-sm table-striped">' .
                                                    '<thead><tr><th class="text-center">Cantidad</th><th>Producto</th><th class="text-center">Entregado</th></tr></thead>' .
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
                                                        <span class="ms-4">Fecha: </span><span><?= (new DateTime($r['dtAlta']))->format('d-m-Y') ?></span><br>
                                                        <span class="ms-4">Sucursal de envio:</span><span><?= $r['sucEntrega'] ?></span>
                                                        <?php if ($r['cEstatus'] == '1' && ($regVenta[0]['cEdo'] != '5' && $regVenta[0]['cEdo'] != '7')) : ?>
                                                            <br><button type="button" class="btn btn-primary btn-sm me-2 btncancelaentregaenvio" data-llamar="<?= '/ventas/rep2/cancelaEnvio/' . $r['id'] . '/' . $regVenta[0]['nIdVentas']  ?>">Cancelar Envio</button>
                                                            <button type="button" class="btn btn-primary btn-sm ms-2 btnreimprimeenvio" data-llamar="<?= '/ventas/imprimeRemision/' . $regVenta[0]['nIdVentas'] . '/0/reimpenvio/' . $r['id'] ?>">Imprimir Envio</button>
                                                        <?php endif; ?>
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
<script>
    $(document).ready(function() {
        let appResMovRemision = {

            init: function() {
                $('button.btncancelaentregaenvio').on('click', appResMovRemision.clickCancelaEntregaEnvio);
                $('button.btnreimprimeenvio').on('click', appResMovRemision.clickReimprimeEnvio);
            },
            llamar: '',
            clickCancelaEntregaEnvio: function(e) {
                let btn = e.target;
                if (modAppConfirma.promptActual !== false) return;
                appResMovRemision.llamar = $(btn).data('llamar');
                modAppConfirma.agregaPrompt(btn);
            },
            clickReimprimeEnvio: function(e) {
                let btn = e.target;
                let url = $(btn).data('llamar');
                window.open(baseURL + url);
            },
            enProceso: false,
            confirmaCancelacion: function() {
                if (appResMovRemision.enProceso) return;
                appResMovRemision.enProceso = true;

                modAppConfirma.btnSi[0].disabled = true;

                $.post(baseURL + appResMovRemision.llamar, {}, null, 'html').
                done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 4) === 'nooK') {
                        if (data.substr(4, 3) == 'msj') {
                            miGlobal.muestraAlerta(data.substr(7), 'resRemi', 4500);
                        } else {
                            miGlobal.muestraAlerta('Error desconocido', 'resRemi', 1500);
                        }
                        modAppConfirma.btnSi[0].disabled = false;
                        appResMovRemision.enProceso = false;
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    modAppConfirma.btnSi[0].disabled = false;
                    appResMovRemision.enProceso = false;
                });
            }
        };

        let modAppConfirma = {
            btnCancela: false,
            promptActual: false,
            btnSi: false,
            btnNo: false,
            agregaPrompt: function(btnCancelaEntrega) {
                modAppConfirma.btnCancela = btnCancelaEntrega;
                $(btnCancelaEntrega).after(
                    '<div class="border d-flex align-items-center justify-content-around">' +
                    '<span class="fw-bold">Confirma cancelar la entrega?</span>' +
                    '<button type="button" class="btn btn-outline-success fs-4 fw-bold btn-sm"><i class="bi bi-check-circle-fill"></i></button>' +
                    '<button type="button" class="btn btn-outline-danger fs-4 fw-bold btn-sm"><i class="bi bi-x-circle-fill"></i></button>' +
                    '</div>'
                );
                modAppConfirma.promptActual = $(btnCancelaEntrega).next();
                modAppConfirma.initPrompt();
            },
            initPrompt: function() {
                modAppConfirma.btnNo = $(modAppConfirma.promptActual).find('button.btn-outline-danger');
                modAppConfirma.btnSi = $(modAppConfirma.promptActual).find('button.btn-outline-success');
                $(modAppConfirma.btnCancela).addClass('d-none');
                modAppConfirma.btnNo.on('click', modAppConfirma.limpiaPrompt);
                modAppConfirma.btnSi.on('click', appResMovRemision.confirmaCancelacion);
            },
            limpiaPrompt: function() {
                modAppConfirma.promptActual.remove();
                $(modAppConfirma.btnCancela).removeClass('d-none');
                modAppConfirma.btnCancela = false;
                modAppConfirma.promptActual = false;
            }
        };

        appResMovRemision.init();
    });
</script>