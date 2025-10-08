<div class="container bg-light mt-4">
    <h5>Compras</h5>
    <div class="row border rounded mb-3 py-2">
        <div class="col-5">
            <div class="row">
                <label for="dcompra" class="col-2 col-form-label">F.Compra</label>
                <div class="col-10">
                    <input type="text" name="dcompra" class="form-control" id="dcompra">
                </div>
            </div>
        </div>
        <div class="col-1 d-flex align-items-center justify-content-between">
            <button class="btn btn-secondary bg-gradient">Filtrar</button>
        </div>
        <div class="col-6 d-flex align-items-center justify-content-end">
            <a class="btn btn-primary bg-gradient me-3" href="<?= base_url()?>/compras/registrar" role="button">Agregar</a>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>F.Compra</th>
                    <th>Id.Almacen</th>
                    <th>Id.Proveedor</th>
                    <th>Total</th>
                    <th>Productos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="bodyTabla">
                <?php if (empty($registros)) : ?>
                    <tr>
                        <td colspan="5" class="fs-5 text-center">No hay registros</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($registros as $r) : ?>
                        <tr>
                            <td><?= $r['nIdCompra'] ?></td>
                            <td><?= $r['dcompra'] ?></td>
                            <td><?= $r['nIdSucursal'] . ' ' . $r['sDescripcion'] ?></td>
                            <td><?= $r['nIdProveedor'] . ' ' . $r['sNombre'] ?></td>
                            <td><?= $r['fTotal'] ?></td>
                            <td><?= $r['nProductos']?></td>
                            <td>
                                <a  href="<?php echo base_url();?>/compras/<?php echo $r['nIdCompra']?>" style="text-decoration: none;">
                                <i class="bi bi-pencil-fill text-primary me-3"  style="cursor:pointer;"></i>
                                </a>
                                <i class="bi bi-trash-fill text-danger me-3 " data-accion-id="B<?= $r['nIdCompra'] ?>" style="cursor:pointer;"></i>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $comprapager->links('compras','catalogos') ?>
</div>
