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
                    <th class="text-center">Cancela<br>pago</th>
                </tr>
            </thead>
            <tbody id="tblbodpagos">
                <?php $nCont = 0; ?>
                <?php foreach ($regs as $r) : ?>
                    <tr>
                        <td class="text-center"><?= (new DateTime($r['dtPago']))->format('d/m/Y') ?></td>
                        <td class=""><?= $r['sLeyenda'] ?></td>
                        <td class="text-end"><?= number_format(round(floatval($r['fPago']), 2), 2) ?></td>
                        <td class="text-center">
                            <?php if ($puedeCancelar) : ?>
                                <?php if ($operacion == 'cobrar' && $r['nIdEScaja'] != 0 && $r['cierreCorte'] == null) : ?>
                                    <i class="bi bi-x-circle-fill text-primary me-3" style="cursor:pointer;" data-llamar="/cancelapago/venta/<?= $r['nIdCreditoPago'] ?>"></i>
                                <?php elseif ($operacion == 'pagar' && $nCont == 0) : ?>
                                    <i class="bi bi-x-circle-fill text-primary me-3" style="cursor:pointer;" data-llamar="/cancelapago/compra/<?= $r['nIdCompraPago'] ?>"></i>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php $nCont++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="row">
    <div class="col text-end">
        <button type="button" class="btn btn-primary me-3" data-bs-dismiss="modal" id="btnCerrarConsultaPagos">Cerrar</button>
    </div>
</div>
<script>
    $(document).ready(function() {
        appLstPagos = {
            frmQueLlama: '<?= $frmURL ?>',
            init: function() {
                $('#tblbodpagos').on('click', appLstPagos.onClick);
            },

            onClick: function(e) {
                let f = $(e.target).data('llamar') ?? '';
                if (f == '') return;
                $.get(baseURL + f, {
                    urlant: appLstPagos.frmQueLlama,
                }, null, 'html')
                    .done(function(data, textStatus, jqxhr) {
                        $('#frmModal .modal-body').html(data);
                    })
                    .fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },
        }
        appLstPagos.init();
    });
</script>