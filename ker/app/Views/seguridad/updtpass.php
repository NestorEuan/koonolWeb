<div class="container h-100 mt-md-5">
    <div class="row h-100 align-items-center justify-content-center">
        <div class="card" style="width:25rem;">
            <h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
            <form action="<?= base_url('/usuario/changepasswd/' .( isset($nIdUsuario)? 'x/' . $nIdUsuario :'login/' . $registro['nIdUsuario'] )) ?>" method="post" class="p-4 text-light" id="appUpdtFrm">
                <hr>

                <?php generaCampoTexto('nIdUsuario',  false, 'hidden', $registro ?? null, 'B', 'form-control text-light bg-dark bg-gradient'); ?>

                <div class="form-floating mb-3">
                    <?php generaCampoTexto('sLogin',  false, 'text', $registro ?? null, 'B', 'form-control text-light bg-dark bg-gradient'); ?>
                    <label for="sLogin" class="form-label">Login</label>
                </div>
                
                <div class="form-floating mb-3">
                    <?php generaCampoTexto('sNombre',  false, 'text', $registro ?? null, 'B', 'form-control text-light bg-dark bg-gradient'); ?>
                    <label for="sNombre" class="form-label">Nombre</label>
                </div>
                
                <div class="form-floating mb-3">
                    <?php generaCampoTexto('sPsw', $error ?? false, 'password', null, 'a', 'text-light bg-dark bg-gradient', 'placeholder=" "'); ?>
                    <label for="sPsw">Contrase&ntilde;a</label>
                </div>
                
                <div class="form-floating">
                    <?php generaCampoTexto('sConfirmar', $error ?? false, 'password', null, 'a', 'text-light bg-dark bg-gradient', 'placeholder=" "'); ?>
                    <label for="sConfirmar">Confirmar contrase&ntilde;a</label>
                </div>
                
                <hr>
                <div class="d-flex justify-content-end">
                    <?php if(isset($modal)) :?>
                        <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <?php else: ?>
                        <a class="btn btn-secondary" href="<?= base_url(isset($nIdUsuario)? '/': '/'); ?>" role="button">Cancelar</a>
                    <?php endif ?>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </form>
            </div>
        </div>
    </div>

