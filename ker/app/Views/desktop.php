<?php if(isset($paraCaja)): ?>
    <script>
        miGlobal.toggleBlockPantalla('Accesando a ventas');
        location.href = '<?= base_url('ventasasp') ?>';
    </script>
<?php endif; ?>

<div style="position:absolute; top:0; bottom:0; left:0; right:0; z-index:-1;" class="bg-dark">
    <div class="container-fluid h-100">
        <div class="row h-100 justify-content-center">
            <div class="col-sm-4 col-md-8 col-lg-6 d-flex align-items-center justify-content-center">
                <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannermain']?>" alt="" class="img-fluid border border-2 border-dark rounded">
            </div>
        </div>
    </div>
    <div style="position: absolute; bottom: 75px; right: 20px;">
        <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['logobottom']?>" alt="" class="img-fluid border rounded" width="60">
    </div>
</div>
