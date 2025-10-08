<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<div class="row">
    <div class="col">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">Fecha Pago</th>
                    <th>Forma Pago</th>
                    <th class="text-center">Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($regs as $r) : ?>
                    <tr>
                        <td class="text-center"><?= (new DateTime($r['dtPago']))->format('d/m/Y') ?></td>
                        <td class=""><?= $r['sLeyenda'] ?></td>
                        <td class="text-end"><?= number_format(round(floatval($r['fPago']), 2), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="row">
    <div class="col text-end">
        <button type="button" class="btn btn-primary me-3" data-bs-dismiss="modal" id="btnCancelar">Cerrar</button>
    </div>
</div>