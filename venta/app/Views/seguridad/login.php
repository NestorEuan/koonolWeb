<div class="login-container">
    <div class="login-card card">
        <!-- Header con Logo -->
        <div class="login-header">
            <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannerlogin']?>" alt="Logo" class="login-logo">
            <h1 class="login-title">Bienvenido</h1>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
        </div>

        <!-- Formulario -->
        <form action="<?= base_url() ?>/login" method="post" class="login-form" id="appLoginFrm">
            
            <!-- Campo Login -->
            <div class="form-floating">
                <?php generaCampoTexto('sLogin', $error ?? false, 'text', null, 'a', 'form-control', 'placeholder="."'); ?>
                <label for="sLogin">Usuario</label>
            </div>

            <!-- Campo Contraseña -->
            <div class="form-floating">
                <?php generaCampoTexto('sPsw', $error ?? false, 'password', null, 'a', 'form-control', 'placeholder="."'); ?>
                <label for="sPsw">Contraseña</label>
            </div>

            <!-- Campo Sucursal (condicional para ker, siempre visible para venta) -->
            <?php if( isset($regSucursales) ) : ?>
            <div class="form-floating">
                <?php generaCampoTexto('nIdSucursal', $error ?? false, 'select', null, 'a', 'form-select', '', $regSucursales, 'sDescripcion'); ?>
                <label for="nIdSucursal">Sucursal</label>
            </div>
            <?php endif; ?>

            <!-- Mensaje de Error -->
            <?php if( isset($msjErr) && $msjErr !== '' ) : ?>
            <div class="login-error">
                <?= $msjErr ?>
            </div>
            <?php endif; ?>

            <!-- Botón de Login -->
            <button type="submit" class="btn btn-login" id="btnLogin">
                Iniciar Sesión
            </button>

        </form>

        <!-- Footer -->
        <div class="login-footer">
            <p class="login-footer-text">&copy; <?= date('Y') ?> - Todos los derechos reservados</p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Focus automático en el primer campo
    $('#sLogin').focus();
    
    // Animación del formulario al cargar
    $('.login-card').css('opacity', '0').animate({opacity: 1}, 600);
    
    // Validación básica antes de enviar
    $('#appLoginFrm').on('submit', function(e) {
        const login = $('#sLogin').val().trim();
        const psw = $('#sPsw').val().trim();
        
        if(login === '' || psw === '') {
            e.preventDefault();
            $('.login-error').remove();
            $(this).prepend('<div class="login-error">Por favor completa todos los campos</div>');
            return false;
        }
    });
});
</script>