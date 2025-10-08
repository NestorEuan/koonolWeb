<!-- 
    <!DOCTYPE html>
<html lang="en" style="height:100%;">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>< ?= $aInfoSis['nomempresa'] ?></title>
    < !-- Favicon-- >
    <link rel="icon" type="image/x-icon" href="< ?= base_url() ?>/assets/img/< ?= $aInfoSis['icono'] ?>" />

    <link rel="stylesheet" href="< ?= base_url() ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="< ?= base_url() ?>/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="< ?= base_url() ?>/assets/js/themes/default/style.min.css">
    <link rel="stylesheet" href="< ?= base_url();?>/assets/css/main.css">

    <script src="< ?= base_url() ?>/assets/js/jquery.js"> </script>
    <script src="< ?= base_url() ?>/assets/js/bootstrap.bundle.min.js"> </script>
    <script src="< ?= base_url() ?>/assets/js/jstree.js"> </script>

</head>

<body style="height:100%;">

<div class="d-print-block">
    <div class="row row-cols-3 row-cols-sm-5 g-3">
        < ?php
        foreach ($registros as $rr) {
        ? >
            <div class="col" style="height:350px;">

                <div class="card shadow-sm">
                    <img class="card-img-top" src="< ?= ($rr['img64'] == '#') ? '#' : 'data:image/' . $rr['tipoimg'] . ';base64, ' . $rr['img64'] ?>" alt="Card image cap" style="object-fit: scale-down; max-height: 205px" >
                    < ?= $rr['sDescripcion'] ?>
                </div>
            </div>
        < ?php
        }
        ? >
    </div>
</div>

</body>
</html>
    -->

<!-- 
<div class="d-print-block">
    <div class="row row-cols-3 row-cols-sm-5 g-3">
    < ?php
        foreach ($registros as $rr) {
        ? >
            <div class="col" style="height:350px;">

                <div class="card shadow-sm">
                    <img class="card-img-top" src="< ?= ($rr['img64'] == '#') ? '#' : 'data:image/' . $rr['tipoimg'] . ';base64, ' . $rr['img64'] ?>" alt="Card image cap" style="object-fit: scale-down; max-height: 205px" >
                    < ?= $rr['sDescripcion'] ?>
                </div>
            </div>
        < ?php
        }
        ?>
    </div>
</div>    
    -->