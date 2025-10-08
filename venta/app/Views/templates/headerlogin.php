<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $aInfoSis['nomempresa'] ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url()?>/assets/img/<?= $aInfoSis['icono'] ?>" />
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url();?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url();?>/assets/css/bootstrap-icons.css">
    
    <!-- CSS Personalizado del Sistema -->
    <link rel="stylesheet" href="<?= base_url();?>/assets/css/login.css">

    <!-- jQuery y Bootstrap JS -->
    <script src="<?= base_url();?>/assets/js/jquery.js"></script>
    <script src="<?= base_url();?>/assets/js/bootstrap.bundle.min.js"></script>
    
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
<body class="login-page">

<!-- Reloj Digital - Esquina Superior Derecha -->
<div class="digital-clock">00:00:00</div>

<script>
    // Función para actualizar el reloj
    function clockUpdate() {
        const date = new Date();
        
        function addZero(x) {
            return x < 10 ? '0' + x : x;
        }
        
        function twelveHour(x) {
            if (x > 12) return x - 12;
            if (x === 0) return 12;
            return x;
        }
        
        const h = addZero(twelveHour(date.getHours()));
        const m = addZero(date.getMinutes());
        const s = addZero(date.getSeconds());
        
        $('.digital-clock').text(h + ':' + m + ':' + s);
    }
    
    // Inicializar reloj al cargar la página
    $(document).ready(function() {
        clockUpdate();
        setInterval(clockUpdate, 1000);
    });
</script>