<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Ferromat</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url() ?>/assets/img/ferromat.ico" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>

<body style="background-color: white ; height:100%;">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="card" style="width:25rem;">
                <img src="<?= base_url('assets/img/' . $aInfoSis['bannerlogin']) ?>" alt="imagen" class="mt-2 img-fluid border-bottom">
                <h1 class="fs-4 fw-bolder pt-2 mt-1 text-center">INICIAR SESION</h1>
                <form action="<?= base_url() . '/' ?>" method="post" class="p-4 text-light">
                    <input type="hidden" name="lngitud" id="lngitud" value="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control text-light bg-dark bg-gradient" id="usario" name="usuario" placeholder="  ">
                        <label for="usuario">Usuario</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control text-light bg-dark bg-gradient" id="password" name="password" placeholder="  ">
                        <label for="password">Contraseña</label>
                    </div>
                    <div class="text-danger fs-6 fw-bold text-center">
                        <?= $msjErr ?? '' ?>
                    </div>
                    <button class="btn btn-primary bg-gradient mt-5 mb-2 w-100 ">Aceptar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

</body>
<script>
    $(document).ready(function() {
        $('#lngitud').val(window.history.length);
    });
</script>
</html>
