<div class="container bg-light mt-4">

    <h3>Recepci&oacute;n de Compras</h3>
    <form action="<?= base_url()?>/almacen/recepcioncompra" method="post">
        <div class="row border rounded mb-3 py-2">
            <div class="col-5">
                <div class="row">
                    <label for="nIdCompra" class="col-7 col-form-label">Cotizaci&oacute;n de compra</label>
                    <div class="col-5">
                        <input type="text" name="nIdCompra" class="form-control" id="nIdCompra" required>
                    </div>
                </div>
            </div>
            <div class="col-1 d-flex align-items-center">
                <button class="btn btn-secondary bg-gradient">Buscar</button>
            </div>
            <!--div class="col-6 d-flex align-items-center justify-content-end">
                <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#addArticulo" id="btnAgregar">Agregar</button>
                <button class="btn btn-primary bg-gradient">Exportar</button>
            </div-->
        </div>
    </form>
</div>

<div class="row border rounded">
</div>
