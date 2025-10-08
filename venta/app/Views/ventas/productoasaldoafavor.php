<div class="col">
    <div class="row position-relative">
        <div id="pasowAlert">
            <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
            </div>
        </div>
        <h4>Paso de Producto a Saldo a Favor</h4>
        <hr>
    </div>
    <div class="row mt-4">
        <div class="col-12 col-md"></div>
        <div class="col-12 col-md-5">
            <div class="container-fluid p-4 bg-light rounded">
                <label for="folio" class="d-inline">Folio Remision</label>
                <div class="d-inline-block">
                    <input type="text" id="folio" class="form-control" placeholder="Folio" aria-label="Folio">
                </div>
                <button type="button" class="btn btn-primary bg-gradient ms-4 d-inline" id="btnbuscafolio">Buscar</button>
            </div>
        </div>
        <div class="col-12 col-md"></div>
    </div>
</div>
<?= generaModalGeneral('frmModal', 'modal-dialog-scrollable modal-lg') ?>
<script>
    $(document).ready(function() {
        let appPasoProd = {
            modal: null,
            init: function() {
                appPasoProd.modal = new bootstrap.Modal(document.getElementById('frmModal'), {});
                $('#frmModal').on('hide.bs.modal', appPasoProd.escondeModal);
                $('#btnbuscafolio').on('click', appPasoProd.envia);
                $('#folio').focus();
            },
            proceso: false,
            envia: function() {
                let a = $('#folio').val();
                if (a.trim() == '') {
                    miGlobal.muestraAlerta('Falta el numero de folio de remision.', 'paso', 2000);
                    return;
                }
                if (appPasoProd.proceso) return;
                appPasoProd.proceso = true;
                $('#btnbuscafolio')[0].disabled = true;
                miGlobal.toggleBlockPantalla('Consultando...');

                $.ajax({
                    url: baseURL + '/remisiones/productoAsaldoAfavor/' + a,
                    method: 'POST',
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    miGlobal.toggleBlockPantalla();
                    if (data.substr(0, 4) == 'noOk') {
                        miGlobal.muestraAlerta(data.substr(4), 'paso', 3000);
                        appPasoProd.proceso = false;
                        $('#btnbuscafolio')[0].disabled = false;
                        return;
                    }
                    appPasoProd.modal.show();
                    $('#frmModal .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    miGlobal.toggleBlockPantalla();
                    appPasoProd.proceso = false;
                    $('#btnbuscafolio')[0].disabled = false;
                });
            },
            escondeModal: function (e) {
                e.preventDefault();
                miGlobal.toggleBlockPantalla();
                location.reload();
            }
        };

        appPasoProd.init();

    });
</script>