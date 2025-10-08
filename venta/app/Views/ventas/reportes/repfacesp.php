<div class="container bg-light mt-4 border position-relative">
    <div id="resvenwAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h4>Reporte Facturas Especiales</h4>
    <form action="" class="row border rounded m-md-3 p-md-3 mx-0 px-1 py-2" onsubmit="return false;" id="frmFiltro">
        <div class="col mx-auto">
            <div class="input-group">
                <span class="input-group-text">Rango de Fechas</span>
                <input type="date" name="dFecIni" id="dFecIni" class="form-control">
                <input type="date" name="dFecFin" id="dFecFin" class="form-control">
                <button type="button" class="btn btn-outline-primary ms-3" style="z-index:auto;" id="btnExportar">Exportar</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        const appFacEsp = {
            init: function() {
                $('#btnExportar').on('click', appFacEsp.exportar);
            },

            exportar: function() {
                if(appFacEsp.validaRango() === false) return;
                console.log(baseURL + '/ventas/rep4/exportaXLS?' + $('#frmFiltro').serialize());
                window.open(baseURL + '/ventas/rep4/exportaXLS?' + $('#frmFiltro').serialize());
            },

            validaRango: function(msj) {
                let fIni = $('#dFecIni').val();
                let fFin = $('#dFecFin').val();
                let nVal = 0;
                if (fIni !== '') nVal++;
                if (fFin !== '') nVal++;
                if (nVal < 2) {
                    miGlobal.muestraAlerta((msj ? msj : 'Falta el rango de fechas'), 'resven', 2000);
                    return false;
                }
                return true;
            }
        };
        appFacEsp.init();
    });
</script>