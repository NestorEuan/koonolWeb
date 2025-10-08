<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h5>Selecciona Agente de Ventas</h5>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="idAgente" class="form-label">Agente</label>
                <?php generaCampoTexto('idAgente', $error ?? false, 'select', $registro ?? null, $modo ?? '', '', '', $lstAgentes); ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAgente">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let selAgente = {
            init: function() {
                $('#btnGuardarAgente').on('click', selAgente.enviar);
                $('#idAgente').select();
            },
            enviar: function(e) {
                let v = $('#idAgente').val();
                e.preventDefault();
                if (v.trim() != '' && Number(v) >= 0) {
                    // frmEnvio para cambio de precio en el listado
                    // frm00 para agregar articulo con el precio capturado
                    let f = $('#frmEnvio');
                    f.empty();
                    f[0].action = baseURL + '<?= $url ?>';
                    f.append('<input type="hidden" name="idAgente" value="' + v + '">');
                    f[0].submit();
                }
            }
        };
        selAgente.init();
    });
</script>