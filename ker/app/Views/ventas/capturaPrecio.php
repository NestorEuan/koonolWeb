<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h5>Importe del producto</h5>
                <hr>
                <h6><?= esc($nomProd) ?></h6>
                <hr>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text">Importe Unitario</span>
                    <input type="text" class="form-control text-end" placeholder="" aria-label="Importe Producto" id="nImportePrecio" value="0">
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
                e.preventDefault();
                if (v.trim() != '' && Number(v) >= 0) {
                    // frmEnvio para cambio de precio en el listado
                    // frm00 para agregar articulo con el precio capturado
                    let f = $('#frm00');
                    f.append('<input type="hidden" name="valImporte" value="' + v + '">');
                    f[0].submit();
                }
            }
        };
        artDispo.init();
    });
</script>