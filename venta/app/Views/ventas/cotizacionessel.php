<div class="container bg-light">
    <h6 class="text-center">Tipo de formato</h6>
    <hr>
    <div class="p-2 border rounded">
        <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-primary me-4" data-bs-dismiss="modal" id="btnPTapado">Precio Tapado</button>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="btnPNormal">Precio Normal</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const appSelCoti = {

            init: function() {
                $('#btnPTapado').on('click', function() {
                    window.open(baseURL + '/cotizaciones/exportaCotizacion/<?= $idcot ?>/1');
                });
                $('#btnPNormal').on('click', function() {
                    window.open(baseURL + '/cotizaciones/exportaCotizacion/<?= $idcot ?>/2');
                });
            }
        };
        appSelCoti.init();
    });
</script>