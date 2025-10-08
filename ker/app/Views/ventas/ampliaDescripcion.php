<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h5>Amplia Descripcion</h5>
                <hr>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Descripción Producto</label>
                <textarea class="form-control" name="nomProd" id="nomProd" cols="40" rows="10"><?= $nomProd ?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarDescri">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let artDescri = {
            init: function() {
                $('#btnGuardarDescri').on('click', artDescri.enviar);
            },

            enviar: function(e) {
                let v = $('#nomProd').val();
                e.preventDefault();
                if (v.trim() != '') {
                    // frmEnvio para cambio de precio en el listado
                    // frm00 para agregar articulo con el precio capturado
                    let f = $('#frmEnvio');
                    f.empty();
                    f[0].action = baseURL + '<?= $url ?>';
                    f.append('<input type="hidden" name="nomProd" value="' + v + '">');
                    f[0].submit();
                }
            }
        };
        artDescri.init();
    });
</script>