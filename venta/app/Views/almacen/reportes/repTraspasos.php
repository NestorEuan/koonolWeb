<div class="col">
    <div class="container bg-light mt-4 border position-relative">
        <h4>Reporte de Recepcion de Traspasos de Almacén</h4>
        <div id="repmatrepwAlert">
            <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
            </div>
        </div>
        <form action="" method="get" id="frmFilterTraspasos" autocomplete="off">
            <div class="row border rounded mb-3 py-2">
                <div class="col-5">
                    <div class="input-group">
                        <span class="input-group-text">Rango de Fechas</span>
                        <input type="date" name="dFecIni" id="dFecIni" class="form-control">
                        <input type="date" name="dFecFin" id="dFecFin" class="form-control">
                    </div>
                </div>
                <div class="col-4">
                    <?php if($verTodasSuc): ?>
                    <div class="input-group ">
                        <label class="input-group-text" for="lstSucursales">Sucursal</label>
                        <select class="form-select" id="lstSucursales" name="lstSucursales">
                            <option selected>Choose...</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-3 text-end">
                    <div class="input-group">
                        <button type="button" class="btn btn-primary ms-3" style="z-index:auto;" id="btnExportar">Exportar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        let appRepTraspasos = {
            lstSucursales: <?= json_encode($sucursales) ?>,
            init: function() {
                $('#btnExportar').on('click', appRepTraspasos.exportar);
                <?php if($verTodasSuc): ?>
                // llena el select con las sucursales
                let sSuc = '';
                let nIdSuc = <?= $idSucursal ?>;
                for (const x of appRepTraspasos.lstSucursales) {
                    sSelect = (nIdSuc == x.nIdSucursal ? 'selected' : '');
                    sSuc += '<option ' + sSelect + ' value="' + x.nIdSucursal + '">' + x.sDescripcion + '</option>';
                }
                $('#lstSucursales').html(sSuc);
                <?php endif; ?>
            },

            validaFechas: function() {
                let fIni = $('#dFecIni').val();
                let fFin = $('#dFecFin').val();
                let nVal = 0;
                if (fIni !== '') nVal++;
                if (fFin !== '') nVal++;
                if (nVal < 2) {
                    miGlobal.muestraAlerta('Falta el rango de fechas', 'repmatrep', 2000);
                    return false;
                }
                return true;
            },

            exportar: function() {
                if (appRepTraspasos.validaFechas() === false) return;
                window.open(baseURL + '/repmatrecep/exportaXLS?' + $('#frmFilterTraspasos').serialize());
            }
        };
        appRepTraspasos.init();
    });
</script>