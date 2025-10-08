<?php

function imprime(&$dat, $tipoDocumento, $tipoImpresion)
{

    $dat['bImprimirPiePagina'] = false;
    $lineasPorPagina = $dat['lineasPorPagina'];
    $nContLineas = 10;
    $sum = 0;
    foreach ($dat['det'] as $v) {
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
    imprimePie($dat, $tipoDocumento, $tipoImpresion);
}

function imprimeCabecero(&$dat, $tipoDocumento, $tipoImpresion)
{

    if ($dat['bImprimirPiePagina'] && $tipoImpresion == 'normal') imprimePie($dat, $tipoDocumento, $tipoImpresion);

    if ($dat['bDocumentoImpreso']) {
        echo '<p class="d-none page-break d-sm-block"></p>';
        echo '<div class="d-sm-none border-top w-100 border-dark my-3 border-4"></div>';
    }

    if ($tipoDocumento == 'enviobod')  $titulo = 'ENVIO  ( BODEGA )';
    elseif ($tipoDocumento == 'enviocli')  $titulo = 'ENVIO  ( CLIENTE )';
    elseif ($tipoDocumento == 'enviocaj')  $titulo = 'ENVIO  ( CAJA )';
    elseif ($tipoDocumento == 'enviocho')  $titulo = 'ENVIO  ( CHOFER )';
    elseif ($tipoDocumento == 'envioord')  $titulo = 'ORDEN DE COMPRA';
    if ($dat['cOrigen'] == 'ventas') {
        $cFolio = 'Remisión: ';
        $nFolio = $dat['nFolioRemision'];
    } else {
        $cFolio = 'Traspaso: ';
        $nFolio = $dat['nFolioTraspaso'];
    }
    $rzLeyenda = $dat['rzLeyenda'];
    $datSuc = $dat['datSuc'];
    $datCli = $dat['datCli'];
    $nIdEnvio = $dat['nIdEnvio'];
    $enviarA = $dat['enviarA'];

?>
    <div class="container-fluid border-bottom border-dark">
        <div class="row d-none d-sm-block mt-1 fw-normal">
            <div class="col align-text-top text-center pb-2">
                <img src="<?= base_url() ?>/assets/img/ferromat.png" alt="" width="84" class="float-start">
                <h6><?= $titulo ?></h6>
                <span class="me-4 fs-8">Folio <?= $cFolio ?>: <strong><?= sprintf('%07d', intval($nFolio)) ?></strong></span>
                <span class="fs-8"><?= (new DateTime())->format('d/m/Y H:i:s') ?></span>
                <span class="fs-8">Folio Envio: <strong><?= sprintf('%07d', intval($nIdEnvio)) ?></strong></span>
            </div>
        </div>
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
            <div class="col-12 col-sm-2 text-center fs-7 fw-bold lh-1">ENVIAR A >></div>
            <div class="col-12 text-center col-sm-5 fs-sm-7 fs-9 lh-1 fw-bold"><?= strtoupper(str_replace(array("\r\n", "\r", "\n"), " ", $enviarA)) ?></div>
        </div>
        <div class="row fw-normal">
            <div class="col">
                <table>
                    <thead>
                        <tr id="cabDet">
                            <?php if ($tipoDocumento == 'enviocli') : ?>
                                <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Cantidad</th>
                                <th style="width:70%;" class="fw-bold fs-8 py-0">Descripción</th>
                                <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Precio</th>
                                <th style="width:10%;" class="text-center fw-bold fs-8 py-0">Entregado</th>
                            <?php elseif ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
                                <th style="width:10%;" class="text-center fw-bold fs-8 py-1">Cantidad</th>
                                <th style="width:70%;" class="fw-bold fs-8 py-0 ps-3">Descripción</th>
                                <th style="width:20%;" class="text-center fw-bold fs-8 py-0">Entregado</th>
                            <?php endif; ?>
                        </tr>
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

function imprimeDetalle(&$dat, &$v, $tipoImpresion, &$sum, $tipoDocumento, &$nContLineas)
{
    if ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocli' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') {
        if ($v['capturada'] <= 0) {
            $nContLineas--;
            return;
        }
    }
?>
    <tr>
        <?php if ($tipoDocumento == 'enviocli') : ?>
            <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v['capturada'], 3) ?></td>
            <td style="width:70%;" class=" fs-9 py-0"><?= esc($v['nomArt']) ?></td>
            <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v['nPrecioT'], 2) ?></td>
            <td style="width:10%;" class="text-start fs-9 py-0">=> <span class="fw-bold ms-2 ps-2 <?= $v['cModoEnv'] == '1' ? '' : 'd-none' ?>">ENV</span></td>
        <?php elseif ($tipoDocumento == 'enviobod' || $tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho' || $tipoDocumento == 'envioord') : ?>
            <td style="width:10%;" class="text-end fs-9 py-0"><?= number_format($v['capturada'], 3) ?></td>
            <td style="width:70%;" class=" fs-9 py-0"><?= esc($v['nomArt']) ?></td>
            <td style="width:20%;" class="text-start fs-9 py-0">=> <span class="fw-bold ms-2 ps-2 <?= $v['cModoEnv'] == '1' ? '' : 'd-none' ?>">ENV</span></td>
        <?php endif; ?>
    </tr>
<?php
}

function imprimePie(&$dat, $tipoDocumento, $tipoImpresion)
{
    echo '</tbody></table>';      // cerramos tabla del contenedor detalle
?>
    <table class="table table-borderless">
        <tbody>
            <?php if ($tipoDocumento == 'enviocaj' || $tipoDocumento == 'enviocho') : ?>
                <tr>
                    <td class="py-0 fw-bold fs-8 border-top border-dark">** DOCUMENTO NO VALIDO PARA RETIRO DE MERCANCIA **</td>
                    <td class="py-0 fw-bold fs-8 border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                </tr>
                <?php if ($tipoDocumento == 'enviocho') : ?>
                    <tr>
                        <td class="py-0 fw-bold fs-6 border-top" colspan="2">
                            Observaciones: <?= $dat['observa'] ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php elseif ($tipoDocumento == 'enviobod') : ?>
                <tr>
                    <td class="fw-bold fs-8 border-top border-dark">** ESTE DOCUMENTO NO ES UN COMPROBANTE FISCAL **</td>
                </tr>
            <?php elseif ($tipoDocumento == 'enviocli') : ?>
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
        </tbody>
    </table>
<?php
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
</head>

<body>
    <?php $dat['bDocumentoImpreso'] = false; ?>
    <div class="row m-4 " id="btnImprimir">
        <div class="col"></div>
        <div class="col text-center p-4 border border-dark rounded">
            <button class="btn btn-primary"> Imprimir </button>
        </div>
        <div class="col"></div>
    </div>
    <div class="imprimir d-sm-block pt-2" style="width: 100%;">
        <?php
        $bDocumentoImpreso = false;
        foreach ($det as $d) {
            $d['datSuc'] = $dat['datSuc'];
            $d['rzLeyenda'] = $dat['rzLeyenda'];
            $d['lineasPorPagina'] = 10;
            $d['bDocumentoImpreso'] = $bDocumentoImpreso;
            imprime($d, 'enviocli', 'normal');
            $bDocumentoImpreso = $d['bDocumentoImpreso'];
        }
        foreach ($det as $d) {
            $d['datSuc'] = $dat['datSuc'];
            $d['rzLeyenda'] = $dat['rzLeyenda'];
            $d['lineasPorPagina'] = 10;
            $d['bDocumentoImpreso'] = $bDocumentoImpreso;
            imprime($d, 'enviocho', 'normal');
            $bDocumentoImpreso = $d['bDocumentoImpreso'];
        }
        ?>
    </div>
</body>
<script>
    $(document).ready(function() {
        $('#btnImprimir').on('click', () => {
            $('#btnImprimir').addClass('d-none');
            window.print();
            window.close();
        })
    });
</script>

</html>