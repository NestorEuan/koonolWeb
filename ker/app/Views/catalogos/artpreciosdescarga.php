<h5 class="bg-ligth px-2 fw-bold">Genera Plantilla</h5>
    <hr>
    <form action="" method="post" id="frmUnLoadPrecios" onsubmit="return false;" accept-charset="utf-8">
        <div class="mb-3">
            <label for="nIdTipoLista" class="form-label">Plantilla de Precios a Descargar</label>
            <?php generaCampoTexto('nIdTipoLista', $error ?? false, 'select', $registro ?? null, $modo ?? '', '', '', $regListas, 'cNombreTipo'); ?>
        </div>
        <!--
        <div class="mb-3">
            php generaCampoTexto('chkGenPrecio', $error ?? false, 'checkbox', $registro ?? null, $modo); ?>
            <label for="chkGenPrecio" class="form-check-label">Genera con precios</label>
        </div>
        -->
    </form>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardar">Generar Plantilla</button>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            let precios = {
                init: function() {
                    $('#btnGuardar').on('click', precios.enviar);
                },
                enviar: function(e) {
                    let c = $('#sDescri').val().trim();
                    if(c != '') {
                        $('#frmUnLoadPrecios').append(
                            '<input type="hidden" id="sDescriLoad" name="sDescriLoad" value="' + c + '">'
                        );
                    }
                    $.post('artprecios/descargaPrecios', $('#frmUnLoadPrecios').serialize(), null, 'html')
                        .done(function(data, textStatus, jqxhr) {
                            if (data.substr(0, 2) === 'oK') {
                                window.open('artprecios/generaArchListaPrecios?' + $('#frmUnLoadPrecios').serialize());
                                $('#btnCancelar').click();
                            } else {
                                $('#frmModal .modal-body').html(data);
                            }
                        }).fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                },
            };
            precios.init();
        });
    </script>