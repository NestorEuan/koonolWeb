<?php
$nCont = 0;
$fTotal = 0;
$fSubTotal = 0;
$fIVA = 0;
$pago['completar'] = 0;


$dlProveedor =  $proveedor['sNombre'] ?? '';
$nIdSucursal = $registro['nIdSucursal'] ?? 0;

$aKeys = [
    'salida' => ['nIdSalida', 'dSalida', ''],
    'entrega' => ['nIdEntrega', 'dEntrega', 'Entrega'],
    'compra'  => ['nIdCompra', 'dcompra', 'del proveedor'],
    'entrada' => ['nIdEntrada', 'dEntrada', ''],
    'traspaso' => ['nIdTraspaso', 'dTraspaso', 'de la sucursal a solicitar'],
    'envio' => ['nIdEnvio', 'dEntrega', 'del cliente'],
];
$masterkey = $aKeys[$operacion][0];
$datefld = 'dSolicitud'; //$aKeys[$operacion][1];
$campoproveedor = $aKeys[$operacion][2];

$containerClass = "overflow-auto container-fluid imprimir";
$tblClass = "table table-sm";
$borderClass = "col px-4 pt-3";

?>
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
    <div class="container p-4 relative">
        <div class="row m-4 " id="btnImprimir">
            <div class="col"></div>
            <div class="col text-center p-4 border border-dark rounded">
                <button class="btn btn-primary"> Imprimir </button>
            </div>
            <div class="col"></div>
        </div>
        <div class="container-fluid overflow-auto imprimir">
            <div class="row">
                <div class="col-3">
                    <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain'] ?>" alt="" width="60" class="align-text-top">
                </div>

                <div class="col-6">
                    <h4 class="text-center"><?= $titulo ?></h4>
                </div>

                <div class="col-3">
                    <?= $registro['sDescripcion'] ?>
                </div>
            </div>
            <?php if (!in_array($operacion, array('entrada', 'salida'))) : ?>
                <div class="row px-4">
                    <div class="col">
                        <?= ($operacion === 'traspaso' ? 'Solicitar a' : 'Proveedor') . ' <B>' . $proveedor['sNombre'] . '</B>' ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row px-4 pt-3">
                <div class="col-6 ">
                    Fecha de solicitud <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . (new DateTime($registro["$datefld"]))->format('d-m-Y') . '</b>' ?>
                </div>
                <div class="col text-center">
                    Folio <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . $id . '</b>' ?><span class="px-2"></span>
                    <?= $operacion === 'traspaso' ? 'Folio Envio: <b class="fs-sm-7 fw-bold">' . $registro['nIdEnvio'] . '</b>' : '' ?>
                </div>
            </div>
            <div class="row px-4 border-bottom">
                <div class="col">
                    Observaciones: <B> <?= $registro['sObservacion'] ?> </B>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="mt-3 " style="z-index:1;">
                        <table class="<?= $tblClass ?>" id="tbl">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Descripción</th>
                                    <th class="text-center" scope="col">Cantidad</th>
                                    <?php if ($operacion === 'compra' || $operacion === 'entrada') : ?>
                                        <th class="text-center" scope="col">Precio</th>
                                        <th class="text-center" scope="col">Importe</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($registros)) : ?>
                                    <tr>
                                        <td colspan="7" class="fs-5 text-center">No hay registros</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($registros as $r) : ?>
                                        <?php
                                        //echo $r[0] . '\r\n' . $r[1] . '\r\n' . $r[2] . '\r\n' . $r[3];
                                        //var_dump($r);
                                        $nImp = (floatval($r['fImporte']) * floatval($r['fCantidad']));
                                        $fTotal += $nImp;
                                        $registro['fTotal'] = $fTotal;
                                        $fSubTotal = round($fTotal / 1.16, 2);
                                        $fIVA = round($fSubTotal * 0.16, 2);
                                        ?>
                                        <tr class="<?= isset($printerMode) ? 'fs-7' : '' ?>">
                                            <th scope="row"> <?= ++$nCont ?> </th>
                                            <td> <?= $r['sDescripcion'] ?> </td>
                                            <td class="text-end pe-5"> <?= $r['fCantidad'] ?> </td>
                                            <?php if ($operacion === 'compra' || $operacion === 'entrada') : ?>
                                                <td class="text-center" scope="col"> <?= $r['fImporte'] ?></td>
                                                <td class="text-center" scope="col"> <?= $nImp ?> </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="container <?= $borderClass ?>">
                    <div class="row justify-content-end text-end">
                        <div class="col-4 ">Artículos solicitados</div>
                        <div class="col-2 fw-bold"><?= $nCont ?></div>
                    </div>
                    <div class="row justify-content-end text-end">
                        <div class="col-4">Total</div>
                        <div class="col-2">
                            <?php generaCampoTexto('fTotal', $error ?? false, 'input ', $registro ?? null, 'B'); ?>
                        </div>
                    </div>
                    <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                    <div class="col">
                        <label for="sObservacion" class="form-label">Observaciones</label>
                        <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
                    </div>
                </div>
            </div>
                
            <p class="page-break d-sm-block"></p>
            <?php $nCont = 0; ?>
            <div class="row">
                <div class="col-3">
                    <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain'] ?>" alt="" width="60" class="align-text-top">
                </div>

                <div class="col-6">
                    <h4 class="text-center"><?= $titulo ?> (Sucursal)</h4>
                </div>

                <div class="col-3">
                    <?= $registro['sDescripcion'] ?>
                </div>
            </div>
            <?php if (!in_array($operacion, array('entrada', 'salida'))) : ?>
                <div class="row px-4">
                    <div class="col">
                        <?= ($operacion === 'traspaso' ? 'Solicitar a' : 'Proveedor') . ' <B>' . $proveedor['sNombre'] . '</B>' ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row px-4 pt-3">
                <div class="col-6 ">
                    Fecha de solicitud <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . (new DateTime($registro["$datefld"]))->format('d-m-Y') . '</b>' ?>
                </div>
                <div class="col text-center">
                    Folio <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . $id . '</b>' ?><span class="px-2"></span>
                    <?= $operacion === 'traspaso' ? 'Folio Envio: <b class="fs-sm-7 fw-bold">' . $registro['nIdEnvio'] . '</b>' : '' ?>
                </div>
            </div>
            <div class="row px-4 border-bottom">
                <div class="col">
                    Observaciones: <B> <?= $registro['sObservacion'] ?> </B>
                </div>
            </div>
            <div class="row">
                <div class="col-2"></div>
                <div class="col">
                    <div class="mt-3 " style="z-index:1;">
                        <table class="<?= $tblClass ?>" id="tbl">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Descripción</th>
                                    <th class="text-center" scope="col">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($registros)) : ?>
                                    <tr>
                                        <td colspan="7" class="fs-5 text-center">No hay registros</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($registros as $r) : ?>
                                        <tr class="<?= isset($printerMode) ? 'fs-7' : '' ?>">
                                            <th scope="row"> <?= ++$nCont ?> </th>
                                            <td> <?= $r['sDescripcion'] ?> </td>
                                            <td class="text-end pe-5"> <?= $r['fCantidad'] ?> </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-2"></div>
            </div>
            <div class="row">
                <div class="container <?= $borderClass ?>">
                    <div class="row justify-content-end text-end">
                        <div class="col-4 ">Artículos solicitados</div>
                        <div class="col-2 fw-bold"><?= $nCont ?></div>
                    </div>
                    <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                    <div class="col">
                        <label for="sObservacion" class="form-label">Observaciones</label>
                        <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnImprimir').on('click', () => {
            $('#btnImprimir').addClass('d-none');
            window.print();
            window.close();
        })
    });
</script>

</html>