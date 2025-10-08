<div class="container">
    <h4>Selecciona el viaje:</h4>
    <div class="row border">
        <div class="position-relative">
            <div id="SelViajewAlert">
                <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
                </div>
            </div>
        </div>
        <div class="col-2"></div>
        <div class="col">
            <?php foreach ($regs as $r) : ?>
                <?php if($r['cModoEnv'] == '0') continue; ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="nviaje<?= $r['nIdEnvio'] ?>" id="nviaje<?= $r['nIdViaje'] ?>" data-cant="<?= $r['fPorRecibir'] ?>" data-viajeenvio="<?= $r['nIdViajeEnvio'] ?>">
                    <label class="form-check-label" for="nviaje<?= $r['nIdViaje'] ?>">
                        <?= 'Viaje: ' .  $r['nIdViaje'] . ',  Cantidad: ' . $r['fPorRecibir'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-2"></div>
    </div>
    <div class="row">
        <div class="col text-center pt-2">
            <button type="button" class="btn btn-secondary me-4" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnOkSelViaje">Aceptar</button>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        let appSelViaje = {
            init: function() {
                $('#btnOkSelViaje').on('click', appSelViaje.enviar);
            },
            enviar: function() {
                // valido que se selecciono
                let bChk = false;
                if ($('input[name="nviaje<?= $r['nIdEnvio'] ?>"]:checked').length == 0) {
                    miGlobal.muestraAlerta('Debe seleccionar un viaje', 'SelViaje', 1500);
                } else {
                    bChk = $('input[name="nviaje<?= $r['nIdEnvio'] ?>"]:checked');
                    $('#nIdViaje').val(bChk[0].id.substr(6));
                    $('#nIdViajeEnvio').val(bChk.data('viajeenvio'));
                    $('#nCant').val(bChk.data('cant'));
                    $('#nCant').trigger('keydown', ['porEnvio']);
                }
            },
        }
        appSelViaje.init();
    });
</script>