<div class="container h-100 d-flex align-items-center justify-content-center bg-light">
    <div class="p-2 border rounded">
        <div class="row">
            <div class="col">
                <h4> Entradas/Salidas Caja </h4>
                <hr>
            </div>
        </div>
        <form action="" method="post" class="row" id="addESCajafrm" onsubmit="return false;">
            <div class="col-5 mb-3">
                <select class="form-select" aria-label="Selecciona" id="cTipoMov" name="cTipoMov">
                    <?php if($ingresoacaja !== "NO") : ?>
                    <option value="E" selected>Ingreso a Caja</option>
                    <?php endif; ?>
                    <option value="S">Egreso de Caja</option>
                </select>
            </div>
            <div class="w-100"></div>
            <div class="col-12 mb-3">
                <label for="sMotivo" class="form-label">Motivo</label>
                <?php generaCampoTexto('sMotivo', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
            </div>
            <div class="col-5">
                <label for="nImporte" class="form-label">Importe</label>
                <?php generaCampoTexto('nImporte', $error ?? false, null, $registro ?? null, $modo, 'text-end'); ?>
            </div>
            <div class="col-7">
                <label for="sPersona" class="form-label" id="idEtiqMovto">Entrega</label>
                <?php generaCampoTexto('sPersona', $error ?? false, null, $registro ?? null, $modo); ?>
            </div>
        </form>
        <hr>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let addEscaja = {
            cantAnt: '',

            init: function() {
                $('#btnGuardar').on('click', addEscaja.enviar);
                $('#nImporte').on('input', addEscaja.onInput);
                $('#cTipoMov').on('input', addEscaja.onChange);
            },
            onChange: function(e) {
                let m = '';
                if (e.target.selectedIndex == 0)
                    m = 'Entrega';
                else
                    m = 'Recibe';
                $('#idEtiqMovto').html(m);
            },
            onInput: function(e) {
                miGlobal.valNumero(e, addEscaja);
            },
            bndGuardado: false,
            enviar: function(e) {
                if (addEscaja.bndGuardado) return;
                addEscaja.bndGuardado = true;
                $('#btnGuardar')[0].disabled = true;
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: $('#addESCajafrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.href = baseURL + '/movtoCajas/imprimeRecibo' ;
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };
        addEscaja.init();
    });
</script>