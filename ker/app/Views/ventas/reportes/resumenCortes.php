<div class="container bg-light mt-4 border">
    <h4>Reporte Resumen de Ventas</h4>
    <form action="" class="row border rounded m-md-3 p-md-3 mx-0 px-1 py-2" onsubmit="return false;" id="frmFiltro">
        <div class="col col-md-6 mx-auto">
            <div class="input-group">
                <span class="input-group-text">Rango de Fechas</span>
                <input type="date" name="dFecIni" id="dFecIni" class="form-control">
                <input type="date" name="dFecFin" id="dFecFin" class="form-control">
                <button type="button" class="btn btn-outline-primary ms-3" style="z-index:auto;" id="btnExportar">Exportar</button>
            </div>
        </div>
    </form>
    <div id="barraprog" class="d-none">
        <h6 class="text-center" id="nombreSucursal">Generando sucursal...</h6>
        <div class="progress">
            <div class="progress-bar bg-warning" id="progresoBarrita1" role="progressbar" style="width: 0%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="progress mt-3">
            <div class="progress-bar bg-info" id="progresoBarrita2" role="progressbar" style="width: 0%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        const appResVenta = {
            init: function() {
                $('#btnExportar').on('click', appResVenta.initExportar);
            },

            enProceso: false, 
            intervalo: null,
            archEdo: '',
            initExportar: function(e) {
                if (appResVenta.enProceso) return;
                appResVenta.enProceso = true;
                // $('#btnGuardar')[0].disabled = true;
                $('#progresoBarrita1')[0].style = 'width:0%;';
                $('#progresoBarrita2')[0].style = 'width:0%;';

                $('#barraprog').removeClass('d-none');
                $.ajax({
                    url: baseURL + '/ventas/rep/initResumenVentasXLS',
                    method: 'POST',
                    data: {},
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    appResVenta.archEdo = data.arch;
                    appResVenta.exportar(data.arch);
                }).fail(function(jqxhr, textStatus, err) {
                    appResVenta.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
            },

            exportar: function(nomArch) {
                $.ajax({
                    url: baseURL + '/ventas/rep/resumenVentasXLS?' + $('#frmFiltro').serialize(),
                    method: 'POST',
                    data: { nomArch : nomArch }, 
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    clearInterval(appResVenta.intervalo);
                    if (data.ok == '1') {
                        let nom = baseURL + '/ventas/rep/descargaVentasXLS?archivo=' + data.arch + '&nomArchEdo=' + data.archedo;
                        $('#barraprog').addClass('d-none');
                        window.open(nom);
                    } else {
                        $('#nombreSucursal').html(data.msj);
                    }
                    appResVenta.enProceso = false;
                }).fail(function(jqxhr, textStatus, err) {
                    appResVenta.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
                appResVenta.intervalo = setInterval(appResVenta.consulta, 1000);
            },

            consulta: function() {
                $.ajax({
                    url: baseURL + '/ventas/rep/consultaVentasXLS',
                    method: 'POST',
                    data: { nomArch: appResVenta.archEdo },
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    if(data.ok == 0)  return
                    let msj = data.msj;
                    let nTotal = Number.parseInt(data.tot);
                    let actual = Number.parseInt(data.act);
                    let porcen;
                    let nTotal2 = Number.parseInt(data.tot2);
                    let actual2 = Number.parseInt(data.act2);
                    let porcen2;
                    $('#nombreSucursal').text(msj);
                    if (nTotal == 0) {
                        porcen = 0;
                    } else {
                        porcen = Math.round((actual / nTotal) * 100, 0);
                    }
                    $('#progresoBarrita1')[0].style = 'width:' + porcen.toString() + '%;';
                    if (nTotal2 == 0) {
                        porcen = 0;
                    } else {
                        porcen = Math.round((actual2 / nTotal2) * 100, 0);
                    }
                    $('#progresoBarrita2')[0].style = 'width:' + porcen.toString() + '%;';
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail2222', jqxhr, textStatus, err);
                });
            },

        };
        appResVenta.init();
    });
</script>