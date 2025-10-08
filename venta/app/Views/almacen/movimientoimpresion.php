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
// solo para salidas
$lstTipoSalida = [
    '0' => 'Sin Tipificacion',
    '1' => 'Conversion',
    '2' => 'Merma',
    '3' => 'Consumo',
];
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
    <div class="container-fluid p-2">
        <div class="row m-4 " id="rowImprimir">
            <div class="col"></div>
            <div class="col text-center p-4 border border-dark rounded">
                <div>
                    <select class="form-select" aria-label="Default select example" id="fldSelect01">
                        <?php
                        $nnn = 0;
                        foreach ($aRegLis as $r) {
                            echo '<option value="seccionAimprimir' . $nnn++ . '">' . $r[0] . '  ' . (new DateTime($r[1]['dtAlta']))->format('d-m-Y H:i') . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-primary mt-4" id="btnImprimir"> Imprimir </button>
            </div>
            <div class="col"></div>
        </div>
        <?php $nnn = 0; ?>
        <?php $registro0 = $aRegLis[0][1]; ?>
        <?php foreach ($aRegLis as $r) : ?>
            <?php
            $registro = $r[1];
            $registrosdet = $r[2];
            $nCont = 0;
            $fTotal = 0;
            ?>
            <div class="container-fluid overflow-auto imprimir d-none" id="seccionAimprimir<?= $nnn ?>">
                <div class="row">
                    <div class="col-3">
                        <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain'] ?>" alt="" width="60" class="align-text-top">
                    </div>

                    <div class="col-6">
                        <h4 class="text-center"><?= $r[0] . ' ' . $titulo ?></h4>
                        <?php if ($operacion == 'salida') : ?>
                            <div class="row">
                                <div class="col text-center">
                                    <span class="fw-bold pe-2">Tipo Salida:</span><?= $lstTipoSalida[$registro0['cTipoSalida']] ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-3">
                        <?= $registro0['sDescripcion'] ?>
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
                        Fecha de solicitud <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . (new DateTime($registro0["$datefld"]))->format('d-m-Y') . '</b>' ?>
                    </div>
                    <div class="col text-center">
                        Folio <?= $operacion . ': <b class="fs-sm-7 fw-bold">' . $id . '</b>' ?><span class="px-2"></span>
                        <?= $operacion === 'traspaso' ? 'Folio Envio: <b class="fs-sm-7 fw-bold">' . $registro0['nIdEnvio'] . '</b>' : '' ?>
                    </div>
                </div>
                <div class="row px-4 border-bottom">
                    <div class="col">
                        Observaciones: <B> <?= $registro0['sObservacion'] ?> </B>
                    </div>
                </div>

                <div class="container">
                    <div class="row px-4 pt-3">
                        <div class="mt-3 " style="z-index:1;">
                            <table class="<?= $tblClass ?>" id="tbl">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Descripción</th>
                                        <th class="text-center" scope="col">Cantidad</th>
                                        <?php if ($r[0] == 'Captura') : ?>
                                            <?php if ($operacion === 'compra' || $operacion === 'entrada') : ?>
                                                <th class="text-end" scope="col">Precio</th>
                                                <th class="text-end" scope="col">Importe</th>
                                                <?php if ($operacion === 'compra') : ?>
                                                    <th class="text-end" scope="col">IVA del Importe</th>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($registros)) : ?>
                                        <tr>
                                            <td colspan="7" class="fs-5 text-center">No hay registros</td>
                                        </tr>
                                    <?php else : ?>
                                        <?php foreach ($aRegLis[0][2] as $rd) : ?>
                                            <?php
                                            if ($r[0] == 'Captura') {
                                                $nCantidad = floatval($rd['fCantidad']);
                                                $nImp = floatval($rd['fImporte']) * $nCantidad;
                                                $fSubTotal += $nImp;
                                                $nIva = floatval($rd['fIVA'] ?? '0');
                                                $fIVA += $nIva;
                                            } else {
                                                if ($operacion == 'compra') {
                                                    if ($rd['nIdViajeEnvio'] != '0') continue;
                                                }
                                                $existe = false;
                                                foreach ($registrosdet as $rdet) {
                                                    if (($rd['nIdArticulo'] == $rdet['nIdArticulo'])  && $rdet['fCantidad'] != 0) {
                                                        $existe = true;
                                                        $nCantidad = floatval($rdet['fCantidad']);
                                                        break;
                                                    }
                                                }
                                                if (!$existe) continue;
                                            }
                                            ?>
                                            <tr class="fs-7">
                                                <th scope="row"> <?= ++$nCont ?> </th>
                                                <td> <?= $rd['sDescripcion'] ?> </td>
                                                <td class="text-center"> <?= $nCantidad ?> </td>
                                                <?php if ($r[0] == 'Captura') : ?>
                                                    <?php if ($operacion === 'compra' || $operacion === 'entrada') : ?>
                                                        <td class="text-end" scope="col"> <?= number_format(floatval($rd['fImporte']), 2) ?></td>
                                                        <td class="text-end" scope="col"> <?= number_format($nImp, 2)  ?> </td>
                                                        <?php if ($operacion === 'compra') : ?>
                                                            <td class="text-end" scope="col"> <?= number_format($nIva, 2)  ?> </td>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col <?= $borderClass ?>">
                            <div class="row">
                                <div class="col text-end">Artículos solicitados</div>
                                <div class="col-2 fw-bold text-center"><?= $nCont ?></div>
                            </div>
                            <?php if ($r[0] == 'Captura') : ?>
                                <?php if ($operacion === 'compra') : ?>
                                    <div class="row mb-1">
                                        <div class="col text-end">SubTotal</div>
                                        <div class="col-3">
                                            <input type="input " class="form-control text-end py-0" id="fTotal" name="fTotal" value="<?= number_format($fSubTotal, 2) ?>" readonly="">
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col text-end">IVA</div>
                                        <div class="col-3">
                                            <input type="input " class="form-control text-end py-0" id="fTotal" name="fTotal" value="<?= number_format($fIVA, 2) ?>" readonly="">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($operacion === 'entrada' || $operacion === 'compra') : ?>
                                    <div class="row">
                                        <div class="col text-end">Total</div>
                                        <div class="col-3">
                                            <input type="input " class="form-control text-end py-0" id="fTotal" name="fTotal" value="<?= number_format($fSubTotal + $fIVA, 2) ?>" readonly="">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>


                            <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                            <div class="col">
                                <div class="form-label d-flex justify-content-between">
                                    <span>Observaciones <?= $r[0] == 'Recepcion' ? 'Recepcion' : '' ?></span>
                                    <?php if ($r[0] == 'Recepcion') : ?>
                                        <span>Usuario: <?= $registro['nomUsu'] ?></span>
                                        <span>Fecha Recepcion: <?= (new DateTime($registro['dtAlta']))->format('d-m-Y H:i') ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
                            </div>
                        </div>
                    </div>

                </div>

                <?php if ($operacion == 'compra' && $nnn == 0) : ?>

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

                <?php endif; ?>

            </div>
            <?php $nnn++; ?>
        <?php endforeach; ?>

    </div>
</body>

<script type="text/javascript">
    $(document).ready(function() {
        let oPrint = $('.imprimir')
        $('#btnImprimir').on('click', () => {
            $('#rowImprimir').addClass('d-none');
            window.print();
            window.close();
        })
        $('#fldSelect01').on('change', function(e) {
            let id = e.target.value;
            oPrint.each(function(i, e) {
                if (id == e.id)
                    $(e).removeClass('d-none');
                else
                    $(e).addClass('d-none');
            });
        });
        $('#fldSelect01').trigger('change');
        $('#fldSelect01').focus();
    });
</script>

</html>