<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title> <?= $aInfoSis['nomempresa'] ?> </title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="<?= base_url()?>/assets/img/<?= $aInfoSis['icono'] ?>" />
    <link rel="stylesheet" href="<?= base_url();?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url();?>/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url();?>/assets/css/main.css">

    <script src="<?= base_url();?>/assets/js/jquery.js"> </script>
    <script src="<?= base_url();?>/assets/js/bootstrap.bundle.min.js"> </script>
    
    <script>
        const baseURL = '<?= base_url() ?>';
        let miGlobal = {
            nomSucursal: '',
            dameMenu: function(e) {
                $("#mainMenu ul.navbar-nav > li.nav-item > ul.dropdown-menu li > a").on("click", function() {
                    miGlobal.toggleBlockPantalla('Cargando ' + $(this).text() + ' ...');
                });
            },
        };
    </script>

</head>
<body>
<header>
</header>

<div class="container overflow-auto" style="height:calc(100% - 121px);">
    <div class="row">