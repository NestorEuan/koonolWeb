<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h5>Articulos Disponibles</h5>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <table class="table">
                    <thead>
                        <th>Sucursal</th>
                        <th>Disponible</th>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $r) : ?>
                            <?php if ($idSucursal == $r['nIdSucursal']) continue; ?>
                            <tr>
                                <td><?= $r['cNomSuc'] ?></td>
                                <td class="text-end pe-4"><?= number_format(round(floatval($r['fReal']), 3), 3) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12">
                <div class="input-group mb-1">
                    <span class="input-group-text fw-bold flex-fill">Disponibles en esta sucursal:</span>
                    <span class="input-group-text text-danger fs-5 fw-bold flex-fill justify-content-end"><?= number_format($disponible, 3) ?></span>
                </div>
            </div>
            <div class="col-12">
                <div class="input-group">
                    <span class="input-group-text fw-bold flex-fill">TOTAL:</span>
                    <span class="input-group-text <?= $textoColor ?> fs-5 fw-bold flex-fill justify-content-end"><?= number_format($total, 3) ?></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="text-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>