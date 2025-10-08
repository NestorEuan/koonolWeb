    <div class="container h-100 mt-md-5">
        <div class="row h-100 align-items-center justify-content-center">
            <div class="card" style="width:25rem;">
                <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannerlogin']?>" alt="imagen" class="mt-2 img-fluid border-bottom">
                <h1 class="fs-4 fw-bolder pt-2 mt-1 text-center">INICIAR SESION</h1>
                <form action="<?= base_url() ?>/login" method="post" class="px-4 text-light" id="appLoginFrm">
                    <div class="form-floating mb-2">
                        <?php generaCampoTexto('sLogin', $error ?? false, 'text', null, 'a', 'text-light bg-dark bg-gradient', 'placeholder="."'); ?>
                        <label for="sLogin">Login</label>
                    </div>
                    <div class="form-floating mb-2">
                        <?php generaCampoTexto('sPsw', $error ?? false, 'password', null, 'a', 'text-light bg-dark bg-gradient', 'placeholder="."'); ?>
                        <label for="sPsw">Contraseña</label>
                    </div>
                    <div class="form-floating">
                        <?php generaCampoTexto('nIdSucursal', $error ?? false, 'select', null, 'a', 'text-light bg-dark bg-gradient', '', $regSucursales, 'sDescripcion'); ?>
                        <label for="nIdSucursal">Sucursal</label>
                    </div>
                    <div class="text-danger fs-6 fw-bold text-center">
                        <?= $msjErr ?? '' ?>
                    </div>
                    <button class="btn btn-primary bg-gradient mt-2 mt-md-5 mb-2 w-100 " id="btnLogin">Aceptar</button>
                </form>
            </div>
        </div>
    </div>
