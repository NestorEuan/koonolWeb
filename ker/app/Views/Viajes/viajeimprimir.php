<?php function imprimeCab(&$dat, &$aInfoSis, $titulo)
{ ?>
    <div class="row mt-1 fw-normal border-bottom ">
        <div class="col align-text-top text-center pb-2">
            <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannernavbrand'] ?>" alt="" height="90" class="float-start">
            <h5 style="line-height:30px;margin:0;padding:0;margin-left:140px;"><?= $titulo ?></h5>
            <div class="me-4 fs-8 text-center lh-sm" style="height:30px;margin-left:140px;">
                Folio Viaje: <strong><?= sprintf('%07d', intval($dat['nIdViaje'])) ?></strong>
                <span class="ps-4"></span>
                Chofer: <strong><?= $dat['nomChofer'] ?></strong>
                <br>
                Sucursal: <strong><?= $dat['nomSuc'] ?></strong>
            </div>
            <div class="me-4 fs-8 text-center" style="height:30px;margin-left:140px;">
                Fecha y Hora Impresión: <strong><?= (new DateTime())->format('d/m/Y H:i:s') ?></strong>
                <span class="ps-4"></span>
                Peso Carga: <strong><?= round(floatval($dat['fPeso']) / 1000, 3) > 0.49 ? round(floatval($dat['fPeso']) / 1000, 2) . ' Tons' : round(floatval($dat['fPeso']), 2) . 'Kg.' ?></strong>
                <br>
                Observaciones: <strong><?= $dat['sObservacion'] ?></strong>
            </div>
        </div>
    </div>
<?php
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión <?= $aInfoSis['nomempresa'] ?></title>

    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/js/themes/default/style.min.css">

    <script src="<?= base_url() ?>/assets/js/jquery.js"> </script>
    <script src="<?= base_url() ?>/assets/js/bootstrap.bundle.min.js"> </script>

    <?php generaCssImpresion(); ?>
</head>

<body>
    <div class="container-fluid overflow-auto imprimir px-4">
        <?php imprimeCab($dat, $aInfoSis, 'ENVIOS A CLIENTES (COPIA BODEGA)') ?>
        <style>
            #detailTabla tr {
                line-height: 12px;
            }

            #detailTabla td {
                border: none;
                font-size: .85rem;
            }

            tr.pieNota {
                line-height: 16px !important;
            }
        </style>
        <div class="row">
            <div class="col">
                <table class="table table-sm table-borderless">
                    <thead>
                        <tr class="border-bottom border-dark">
                            <th class="text-center fs-8 fw-bold">Folio<br>Envio</th>
                            <th class="text-center fs-8 fw-bold">Origen</th>
                            <th class="text-center fs-8 fw-bold">Folio<br>Origen</th>
                            <th class="text-center fs-8 fw-bold">Fechas:<br>de alta/solicitada</th>
                            <th class="fs-8 fw-bold">Enviar a</th>
                            <th class="fs-8 fw-bold">Direccion</th>
                            <th class="fs-8 fw-bold">Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($det as $k => $r) : ?>
                            <tr>
                                <td class="text-center fs-8 fw-bold"><?= $r['nIdEnvio'] ?></td>
                                <td class="text-center fs-8"><?= $r['cOrigen'] ?></td>
                                <td class="text-center fs-8 fw-bold"><?= ($r['cOrigen'] == 'ventas') ? $r['nFolioRemision'] : $r['nFolioTraspaso'] ?></td>
                                <td class="text-center fs-8"><?= $r['fechaAlta'] . ' / ' . $r['fechaSol'] ?></td>
                                <td class=" fs-8"><?= $r['sEnvEntrega'] ?></td>
                                <td class=" fs-8"><?= $r['sEnvDireccion'] . ' ' . $r['sEnvColonia'] ?></td>
                                <td class=" fs-8"><?= $r['sEnvReferencia'] . ' Tel: ' . $r['sEnvTelefono'] ?></td>
                            </tr>
                            <tr class="border-bottom border-dark">
                                <td colspan="7" class="py-0">
                                    <div class="row">
                                        <div class="col-2 fw-bold fs-8 text-end">
                                            Observaciones:
                                        </div>
                                        <div class="col-10 fs-8">
                                            <?= $r['observa'] ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td colspan=5>
                                    <table class="table table-sm table-borderless w-75 border-bottom border-dark">
                                        <thead>
                                            <tr>
                                                <th class="fs-8 fw-bold text-center py-0">Id</th>
                                                <th class="fs-8 fw-bold py-0">Articulo</th>
                                                <th class="fs-8 fw-bold text-center py-0">Entregar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($r['det'] as $kk => $rr) : ?>
                                                <tr>
                                                    <td class="fs-8 text-center py-0"><?= $rr['nIdArticulo'] ?></td>
                                                    <td class="fs-8 py-0"><?= $rr['nomArt'] ?></td>
                                                    <td class="fs-8 text-center py-0"><?= $rr['capturada'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="d-none page-break d-sm-block"></p>
    <div class="container-fluid overflow-auto imprimir px-4">
        <?php imprimeCab($dat, $aInfoSis, 'ENVIOS A CLIENTES (COPIA CHOFER)') ?>
        <style>
            #detailTabla tr {
                line-height: 12px;
            }

            #detailTabla td {
                border: none;
                font-size: .85rem;
            }

            tr.pieNota {
                line-height: 16px !important;
            }
        </style>
        <div class="row">
            <div class="col">
                <table class="table table-sm table-borderless">
                    <thead>
                        <tr class="border-bottom border-dark">
                            <th class="text-center fs-8 fw-bold">Folio<br>Envio</th>
                            <th class="text-center fs-8 fw-bold">Origen</th>
                            <th class="text-center fs-8 fw-bold">Folio<br>Origen</th>
                            <th class="text-center fs-8 fw-bold">Fechas:<br>de alta/solicitada</th>
                            <th class="fs-8 fw-bold">Enviar a</th>
                            <th class="fs-8 fw-bold">Direccion</th>
                            <th class="fs-8 fw-bold">Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($det as $k => $r) : ?>
                            <tr>
                                <td class="text-center fs-8 fw-bold"><?= $r['nIdEnvio'] ?></td>
                                <td class="text-center fs-8"><?= $r['cOrigen'] ?></td>
                                <td class="text-center fs-8 fw-bold"><?= ($r['cOrigen'] == 'ventas') ? $r['nFolioRemision'] : $r['nFolioTraspaso'] ?></td>
                                <td class="text-center fs-8"><?= $r['fechaAlta'] . ' / ' . $r['fechaSol'] ?></td>
                                <td class=" fs-8"><?= $r['sEnvEntrega'] ?></td>
                                <td class=" fs-8"><?= $r['sEnvDireccion'] . ' ' . $r['sEnvColonia'] ?></td>
                                <td class=" fs-8"><?= $r['sEnvReferencia'] . ' Tel: ' . $r['sEnvTelefono'] ?></td>
                            </tr>
                            <tr class="border-bottom border-dark">
                                <td colspan="7" class="py-0">
                                    <div class="row">
                                        <div class="col-2 fw-bold fs-8 text-end">
                                            Observaciones:
                                        </div>
                                        <div class="col-10 fs-8">
                                            <?= $r['observa'] ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td colspan=5>
                                    <table class="table table-sm table-borderless w-75 border-bottom border-dark">
                                        <thead>
                                            <tr>
                                                <th class="fs-8 fw-bold text-center py-0">Id</th>
                                                <th class="fs-8 fw-bold py-0">Articulo</th>
                                                <th class="fs-8 fw-bold text-center py-0">Entregar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($r['det'] as $kk => $rr) : ?>
                                                <tr>
                                                    <td class="fs-8 text-center py-0"><?= $rr['nIdArticulo'] ?></td>
                                                    <td class="fs-8 py-0"><?= $rr['nomArt'] ?></td>
                                                    <td class="fs-8 text-center py-0"><?= $rr['capturada'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" style="font-size:0;">
                                    <div>
                                        <div class="d-inline-block text-end fs-8 me-2" style="width:100px;height:20px;">Firma de recibido:</div>
                                        <div class="d-inline-block text-end fs-8 border-bottom border-dark" style="width:200px;height:20px;">&nbsp;</div>
                                    </div>
                                    <div>
                                        <div class="d-inline-block text-end fs-8 me-2" style="width:100px;height:20px;">Observaciones:</div>
                                        <div class="d-inline-block text-end fs-8 border-bottom border-dark" style="width:600px;height:20px;">&nbsp;</div>
                                    </div>
                                    <div class="d-inline-block fs-8 border-bottom border-dark" style="width:686px;height:20px;margin-left:22px;">&nbsp;</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        window.print();
        window.close();
    });
</script>

</html>
