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
        <div class="row mb-3">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text">Cantidad</span>
                    <input type="text" class="form-control text-end" placeholder="Cantidad a comprar" aria-label="Cantidad a comprar" id="nCantCapDisp">
                </div>
            </div>
        </div>
        <?php if ($bHasImporte) : ?>
            <div class="row mb-3">
                <div class="col">
                    <div class="input-group">
                        <span class="input-group-text">Importe</span>
                        <input type="text" class="form-control text-end" placeholder="Importe Manual" aria-label="Importe Manual" id="nImpCapDisp">
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let artDispo = {
            cantAnt: '',
            tieneCapturaImporte: '<?= $bHasImporte ? '1' : '0' ?>',
            init: function() {
                $('#btnGuardar').on('click', artDispo.enviar);
                $('#nCantCapDisp')
                    .on('input', artDispo.onInput)
                    .on('keydown', artDispo.onKeydown);
                $('#nImpCapDisp')
                    .on('input', artDispo.onInputImp)
                    .on('keydown', artDispo.onKeydownImp);
            },

            onInput: function(e) {
                miGlobal.valNumero(e, artDispo, {
                    re: /^\d*(?:\.\d{0,3})?$/g
                });
            },

            onInputImp: function(e) {
                miGlobal.valNumero(e, artDispo, {
                    re: /^\d*(?:\.\d{0,2})?$/g
                });
            },

            onKeydown: function(e) {
                if (e.which == 13 && e.target.value > 0) {
                    if (artDispo.tieneCapturaImporte == '1') {
                        $('#nImpCapDisp').select();
                    } else {
                        artDispo.enviar(e);
                    }
                }
            },

            onKeydownImp: function(e) {
                if (e.which == 13 && e.target.value > 0) {
                    artDispo.enviar(e);
                }
            },

            enviar: function(e) {
                let v = $('#nCantCapDisp').val();
                e.preventDefault();
                if (v.trim() != '' && Number(v) > 0) {
                    if ($('#nImpCapDisp').length > 0) {
                        let v1 = $('#nImpCapDisp').val();
                        if (v1.trim() != '' && Number(v1) > 0) {
                            $('#frm00').append('<input type="hidden" name="valImporte" value="' + v1 + '">');
                        } else {
                            return;
                        }
                    }
                    $('#nCant').val(v);
                    $('#frm00')[0].submit();
                }
            }
        };
        artDispo.init();
    });
</script>