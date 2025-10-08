<?php
  if( !isset($nIdUsuario) )
    $nIdUsuario = null;
?>
<script>
  miGlobal.nomSucursal = '<?= $sucursal ?? '' ?>';
</script>
<nav class="navbar navbar-expand-lg navbar-light bg-light position-relative" id="mainMenu" style="z-index:2;">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= base_url()?>/desktop">
        <img src="<?= base_url()?>/assets/img/<?= $aInfoSis['bannernavbrand'] ?>" alt="" width="30" height="24" class="d-inline-block align-text-top">
        <?= $aInfoSis['nomempresa'] ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php 
          if(isset($navbar))
          {
            echo $navbar;
          }

        ?>
      </ul>
      <span class="col-sm d-lg-none col-xl navbar-text fw-bold overflow-hidden text-nowrap me-auto" style="color:rgb(232, 87, 55);"><?= $sucursal ?? '' ?></span>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= $slogin ?? 'Usuario no registrado'?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
            <li><a class="dropdown-item" href="<?php echo base_url('usuario/changepasswd/logeado/' . $nIdUsuario ?? ''); ?>">Reiniciar contraseña</a></li>
            <li><a class="dropdown-item" href="<?= base_url('login')?>">Salir</a></li>
          </ul>
        </li>
      </ul>

    </div>
  </div>
</nav>
