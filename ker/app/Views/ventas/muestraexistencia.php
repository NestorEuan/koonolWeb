<div class="container-fluid">
    <table class="table table-striped">
        <thead class="bg-secondary bg-opacity-25">
            <th>Sucursal</th>
            <th>Existencia</th>
        </thead>
        <tbody>
            <?php foreach ($registros as $r) : ?>
                <tr>
                    <td><?= $r['cNomSuc'] ?></td>
                    <td class="text-end pe-4 border border-dark border-0 border-start"><?= number_format(round(floatval($r['fReal']), 3), 3) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>