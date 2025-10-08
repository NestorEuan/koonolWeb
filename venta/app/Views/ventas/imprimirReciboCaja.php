<?php
$cmd = $urlret;
$ln = '<br>';   // chr(10)
$cierraVentana = $cierraVentana ?? false;

$aDias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
$aMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

generaCssImpresion();
?>

<div class="overflow-auto container-fluid imprimir">
    <table class="tblImpresion">
        <tbody>

            <tr class="border-bottom border-dark">
                <td>
                    <div class="row d-sm-none">
                        <div class="col-12">
                            <div class="row justify-content-center">
                                <div class="col-5">
                                    <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain'] ?>" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col fw-bold fs-6 text-center lh-1">RECIBO DE CAJA</div>
                            </div>
                            <div class="row">
                                <div class="col fs-8 text-center lh-1">Sucursal: <?= $sucursal ?></div>
                            </div>
                            <div class="row">
                                <div class="col fs-8 text-center lh-1">Cajero: <?= $Usuario ?></div>
                            </div>
                            <div class="row">
                                <div class="col fs-8 text-center lh-1">Fecha: <?= $fechahora ?></div>
                            </div>
                            <div class="row">
                                <div class="col fs-8 text-center lh-1">Folio: <?= $folio ?></div>
                            </div>
                            <div class="row">
                                <div class="col fs-8 text-center lh-1"><?= strtoupper($tipoDoc) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row d-none d-sm-block mt-3 position-relative">
                        <div class="col-12 align-text-top text-center">
                            <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain'] ?>" alt="" height="80" class="float-start">
                            <div class="row">
                                <div class="col fs-6 fw-bold text-center">RECIBO DE CAJA</div>
                            </div>
                            <!-- <h6>RECIBO DE CAJA</h6> -->
                            <div class="row">
                                <div class="col fs-7 text-center lh-1">Sucursal: <?= $sucursal ?></div>
                            </div>
                            <div class="row">
                                <div class="col fs-7 text-center lh-1">Cajero: <?= $Usuario ?></div>
                            </div>
                            <div class="row">
                                <div class="col fs-7 text-center lh-1">Fecha: <?= $fechahora ?><span class="ps-4"></span>Folio: <?= $folio ?></div>
                            </div>
                            <div class="row">
                                <div class="col fs-7 text-center lh-1"><?= strtoupper($tipoDoc) ?></div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="row">
                        <div class="col fs-6">
                            Recibí de <?= $recibi ?><br>
                            La cantidad de : $<?= $importe ?><br>
                            <?= $importeletras ?><br>
                            Por <?= $motivo ?>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-center col-sm-6">
                            <span class="fs-6"><?= $firma1 ?></span>
                            <div style="border-bottom: 2px dashed black;height:50px;width:60%;margin:auto;"></div>
                            <span class="fs-6"><?= $persona ?></span>
                        </div>
                        <div class="col-12 mt-4 text-center col-sm-6 mt-sm-0">
                            <span class="fs-6"><?= $firma2 ?></span>
                            <div style="border-bottom: 2px dashed black;height:50px;width:60%;margin:auto;"></div>
                        </div>
                    </div>
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
        <?php if (!$esMobil) : ?>
            <?php if ($cierraVentana) : ?>
                window.close();
            <?php else : ?>
                location.href = "<?= $cmd ?>";
            <?php endif; ?>

        <?php endif; ?>

    });
</script>