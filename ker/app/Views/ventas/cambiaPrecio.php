<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h5>Cambia Precio</h5>
                <hr>
                <h6><?= $nomProd ?></h6>
                <hr>
            </div>
        </div>
        <?php if ($bNoRecalcular == '1') : ?>
            <div class="row mb-3">
                <div class="col">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="chkNoAplicaComisionesDescuentos" <?= $bNoAplicaComisionesDescuentos == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="chkNoAplicaComisionesDescuentos">
                            No aplicar en el precio comisiones y descuentos
                        </label>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="row mb-3">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text">Precio</span>
                    <input type="text" class="form-control text-end" placeholder="" aria-label="Nuevo Precio" id="nImportePrecio" value="<?= $importe ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarPrecio">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        miGlobal.entregaCampo = '#nImportePrecio';
        let artDispo = {
            cantAnt: '',

            init: function() {
                $('#btnGuardarPrecio').on('click', artDispo.enviar);
                //document.getElementById('btnGuardarDescGral').addEventListener('click', artDispo.enviar);
                $('#nImportePrecio')
                    .on('input', artDispo.onInput)
                    .on('keydown', artDispo.onKeydown);
                $(miGlobal.entregaCampo).select();
            },

            onInput: function(e) {
                miGlobal.valNumero(e, artDispo);
            },

            onKeydown: function(e) {
                if (e.which == 13 && e.target.value >= 0) {
                    artDispo.enviar(e);
                }
            },

            enviar: function(e) {
                let v = $('#nImportePrecio').val();
                let chk = $('#chkNoAplicaComisionesDescuentos');
                if (chk.length > 0) {
                    chk = chk[0].checked ? 'on' : '';
                    $('#chkNoAplicaComisionesDescuentos')[0].checked
                } else {
                    chk = '';
                }
                e.preventDefault();
                if (v.trim() != '' && Number(v) >= 0) {
                    // frmEnvio para cambio de precio en el listado
                    // frm00 para agregar articulo con el precio capturado
                    let f = $('#frmEnvio');
                    f.empty();
                    f[0].action = baseURL + '<?= $url ?>';
                    f.append('<input type="hidden" name="valImporte" value="' + v + '">');
                    f.append('<input type="hidden" name="chkNoAplicaComisionesDescuentos" value="' + chk + '">');
                    f[0].submit();
                }
            }
        };
        artDispo.init();
    });
</script>