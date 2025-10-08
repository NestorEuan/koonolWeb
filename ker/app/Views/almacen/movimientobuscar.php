<div class="container-fluid h-100">
    <div class="row">
        <div class="col">
            <h4><?= $titulo ?></h4>
            <hr>
        </div>
    </div>
    <div class="row h-75">
        <div class="col bg-light px-4 pt-3 py-3 border rounded">
            <div class="position-relative">
                <div id="wAlert">
                    <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    </div>
                </div>
            </div>
            <div class="col-5 ">
                <form action="<?= base_url()?>/movimiento/buscar/<?= $operacion?>" method="post" id="frm00" autocomplete="off">
                    <div class="row">
                        <div class = "col">

                            <div class="input-group ">
                                <label for="nIdCompra" class="col-7 col-form-label"><?= $label ?></label>
                                <?php generaCampoTexto('nIdCompra', $error ?? false, 'text', null, null); ?>
                                <!-- input class="form-control" id="nIdCompra" name="nIdCompra" placeholder="Folio a buscar" 
                                aria-label="Folio" tabindex="2" required-->
                            </div>
                        </div>
                        <div class="col-1 d-flex align-items-center">
                            <button class="btn btn-success bg-gradient">Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
