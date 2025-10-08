<div class="container bg-light mt-4 border position-relative">
    <div id="resvenwAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h4>Reporte Resumen de Cortes en Ventas</h4>
    <form action="" class="row border rounded m-md-3 p-md-3 mx-0 px-1 py-2" onsubmit="return false;" id="frmFiltro">
        <div class="col mx-auto">
            <div class="input-group">
                <span class="input-group-text">Rango de Fechas</span>
                <input type="date" name="dFecIni" id="dFecIni" class="form-control">
                <input type="date" name="dFecFin" id="dFecFin" class="form-control">
                <button type="button" class="btn btn-outline-primary ms-3" style="z-index:auto;" id="btnFiltrarPorUsuarios">Mostrar Usuarios</button>
                <select name="lstUsu" id="lstUsu" class="form-select ms-2 d-none">
                    <option value="0" selected>Todos</option>
                </select>
                <button type="button" class="btn btn-outline-primary ms-3" style="z-index:auto;" id="btnExportar">Exportar</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        const appResVenta = {
            init: function() {
                $('#btnExportar').on('click', appResVenta.exportar);
                $('#btnFiltrarPorUsuarios').on('click', appResVenta.mostrarUsuarios);
            },

            exportar: function() {
                if(appResVenta.validaRango() === false) return;
                if($('#lstUsu').hasClass('d-none')) $('#lstUsu')[0].disabled = true;
                console.log(baseURL + '/ventas/rep/resumenVentasXLS?' + $('#frmFiltro').serialize());
                window.open(baseURL + '/ventas/rep/resumenVentasXLS?' + $('#frmFiltro').serialize());
                $('#lstUsu')[0].disabled = false;
            },

            mostrarUsuarios: function() {
                let fIni = $('#dFecIni').val();
                let fFin = $('#dFecFin').val();
                if(appResVenta.validaRango('Seleccione el rango de fechas para mostrar usuarios que hicieron corte.') === false) return;
                $.post(baseURL + '/ventas/rep/getusuarios', {
                    'dIni': fIni,
                    'dFin': fFin
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    appResVenta.llenaUsuarios(data.lst);
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },

            llenaUsuarios: function(lst) {
                let t = lst.length;
                let o = $('#lstUsu');
                o.empty();
                o.append('<option value="0">Todos</option>');
                for (let i = 0; i < t; i++) {
                    o.append('<option value="' + lst[i].nIdUsuario + '">' + lst[i].sNombre + '</option>');
                }
                o.val('0');
                o.removeClass('d-none');
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
        appResVenta.init();
    });
</script>