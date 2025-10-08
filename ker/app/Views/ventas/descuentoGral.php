<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h5>Descuento en <?= $cTitulo ?></h5>
                <hr>
                <?php if ($cTitulo == 'Producto') : ?>
                    <h6><?= $nomProd ?></h6>
                    <hr>
                <?php endif; ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text">Porcentaje Descuento</span>
                    <input type="text" class="form-control text-end" placeholder="" aria-label="% a descontar en la remision" id="nDescGralPorce" value="<?= $descuento ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarDescGral">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        miGlobal.entregaCampo = '#nDescGralPorce';
        let artDispo = {
            cantAnt: '',

            init: function() {
                $('#btnGuardarDescGral').on('click', artDispo.enviar);
                //document.getElementById('btnGuardarDescGral').addEventListener('click', artDispo.enviar);
                $('#nDescGralPorce')
                    .on('input', artDispo.onInput)
                    .on('keydown', artDispo.onKeydown);
                $(miGlobal.entregaCampo).select();
            },

            onInput: function(e) {
                miGlobal.valNumero(e, artDispo, {
                    min: 0,
                    max: 100,
                    re: /^\d*$/g
                });
            },

            onKeydown: function(e) {
                if (e.which == 13 && e.target.value >= 0) {
                    artDispo.enviar(e);
                }
            },

            enviar: function(e) {
                let v = $('#nDescGralPorce').val();
                e.preventDefault();
                if (v.trim() != '' && Number(v) >= 0) {
                    let f = $('#frmEnvio');
                    f.empty();
                    f[0].action = baseURL + '<?= $url ?>';
                    f.append('<input type="hidden" name="valDesc" value="' + v + '">');
                    f[0].submit();
                }
            }
        };
        artDispo.init();
    });
</script>