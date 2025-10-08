<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h4 class="text-center">Facturar Remisiones del Corte</h4>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h6 class="text-center" id="progresoBarrita"><?= $sProceso ?></h6>
            </div>
        </div>
        <div class="progress">
            <div id="prgBarra" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
        </div>
        <div class="row">
            <div class="col">
                <div class="text-danger" id="textoerror"></div>
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardar">Facturar</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let appCorteCaja = {
            intervalo: null,
            init: function() {
                $('#btnGuardar').on('click', appCorteCaja.enviar);
                $('#btnCancelar').on('click', () => {
                    clearInterval(appCorteCaja.intervalo);
                });
            },
            enProceso: false,
            enviar: function(e) {
                if (appCorteCaja.enProceso) return;
                appCorteCaja.enProceso = true;
                $('#btnGuardar')[0].disabled = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: {},
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    clearInterval(appCorteCaja.intervalo);
                    appCorteCaja.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;
                    if (data.ok == '1') {
                        location.reload();
                    } else {
                        $('#textoerror').html(data.msj);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    appCorteCaja.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
                appCorteCaja.intervalo = setInterval(appCorteCaja.consulta, 1000);
            },
            /*
            enviar2: function(e) {
                if (appCorteCaja.enProceso) return;
                appCorteCaja.enProceso = true;
                $('#btnGuardar')[0].disabled = true;
                // leo listado de facturas a procesar
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: {},
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    clearInterval(appCorteCaja.intervalo);
                    appCorteCaja.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;
                    if (data.ok == '1') {
                        location.reload();
                    } else {
                        $('#textoerror').html(data.msj);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    appCorteCaja.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
                appCorteCaja.intervalo = setInterval(appCorteCaja.consulta, 1000);
            },
            */
            consulta: function() {
                $.ajax({
                    url: '<?= $frmURLedo ?>',
                    method: 'POST',
                    data: {},
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    let msj = data.msj;
                    let nTotal = Number.parseInt(data.tot);
                    let actual = Number.parseInt(data.act);
                    let porcen;
                    if (nTotal == actual) clearInterval(appCorteCaja.intervalo);
                    if (nTotal == 0) {
                        porcen = 0;
                    } else {
                        porcen = Math.round((actual / nTotal) * 100, 0);
                    }
                    $('#prgBarra')[0].style = 'width:' + porcen.toString() + '%;';
                    $('#progresoBarrita').text(msj);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail2222', jqxhr, textStatus, err);
                });
            },
            /*
            leeListado: function() {
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: {},
                    dataType: 'json'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.ok == '1') {
                        appCorteCaja.procesaListado(data.lst);
                    } else {
                        $('#textoerror').html(data.msj);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    appCorteCaja.enProceso = false;
                    $('#btnGuardar')[0].disabled = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
            procesaListado: function(lst) {
                let t = lst.length;
                for(i = 0; i < t; i++){

                }
            }
            */
        };
        appCorteCaja.init();
    });
</script>