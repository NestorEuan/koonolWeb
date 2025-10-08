<div class="col">
    <div class="row">
        <div class="col">
            <h4>Imprimir Entrega de Otra Sucursal</h4>
            <hr>
        </div>
    </div>
    <div class="row bg-light px-4 pt-3 py-3 border rounded position-relative">
        <div class="col-sm-4"></div>
        <div class="col">
            <div id="entregaimpwAlert">
                <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
                </div>
            </div>
            <div class="input-group">
                <label for="idFolio" class="col-form-label">Entrega a Imprimir: </label>
                <input type="text" name="idFolio" id="idFolio" class="form-control mx-3">
                <button class="btn btn-success bg-gradient" id='btnOk'>Imprimir</button>
            </div>
        </div>
        <div class="col-sm-4"></div>
    </div>
</div>
<form method="post" id="frmimp"></form>
<script>
    $(document).ready(function() {
        let appEnt = {
            init: function() {
                $('#btnOk').on('click', () => {
                    let v = $('#idFolio').val().trim();
                    if (v == '') return;
                    $.post('<?= base_url('imprimeentrega') ?>/' + v, {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            debugger;
                            if (data.ok == '0') {
                                miGlobal.muestraAlerta(data.msj, 'entregaimp', 1700);
                                $('#idFolio').val('');
                            } else {
                                let f = $('#frmimp')[0];
                                f.action = 'ventas/imprimeRemision/' + data.idVentas + '/0/entregaos/' + v;
                                console.log(f.action);
                                f.submit();
                            }
                        }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });

                });
                $('#idFolio').on('input', (e) => {
                    miGlobal.valNumero(e, appEnt, {
                        re: /^\d*$/g
                    })
                });
                $('#idFolio').focus();
            }
        };
        appEnt.init();
    });
</script>