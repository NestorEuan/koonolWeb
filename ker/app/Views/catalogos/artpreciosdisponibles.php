<table class="table table-striped border border-dark">
    <thead>
        <tr>
            <th class="border border-dark text-center pe-3">Listas Precios ---></th>
            <?php $nContFila = 0; ?>
            <?php foreach ($regTipos as $v) : ?>
                <th colspan="2" class="text-center border border-dark">
                    <?= esc($v['cNombreTipo']) ?>
                </th>
                <?php $nContFila++; ?>
            <?php endforeach; ?>
        </tr>
        <tr>
            <th class="text-center border border-dark">Precios Aplicados<br>a Partir de</th>
            <?php for ($i = 0; $i < $nContFila; $i++) : ?>
                <th class="border-start border-dark text-end">Remisión</th>
                <th class="text-end">Factura</th>
            <?php endfor; ?>
        </tr>
    </thead>
    <tbody id="bodyTabla">
        <?php if (empty($registros)) : ?>
            <tr>
                <td colspan="5" class="fs-5 text-center">No hay registros</td>
            </tr>
        <?php else : ?>
            <?php foreach ($registros as $r) : ?>
                <?php if ($r['faPartir'] == null) continue; ?>
                <tr>
                    <td class="text-center"><?= $r['faPartir'] ?? 'N/A' ?></td>
                    <?php for ($i = 0; $i < $numListas; $i++) : ?>
                        <td class="text-end"><?= $r['L' . $regTipos[$i]['nIdTipoLista']] ?></td>
                        <td class="text-end"><?= $r['F' . $regTipos[$i]['nIdTipoLista']] ?></td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>