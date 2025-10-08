<div class="container bg-light mt-4 border">
    <h4>Captura de menus y permisos del sistema</h4>
    <div class="row mb-3">
        <div class="col">
            <h5>Modulos</h5>
            <select name="lstModulos" id="lstModulos" multiple class="form-select" size="10">
                <?php foreach ($regsMod as $r) {  ?>
                    <option value="<?= $r['nIdMenu'] ?>"><?= $r['sDescripcion'] ?></option>
                <?php }; ?>
            </select>
            <div class="mt-2 btnsAcciones">
                <button class="btn btn-outline-primary bi bi-plus-square" type="button" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="modulos/a/mod/"></button>
                <button class="btn btn-outline-primary bi bi-pencil-square ms-2" type="button" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="modulos/e/mod/"></button>
                <button class="btn btn-outline-primary bi bi-dash-square ms-2" type="button" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="modulos/b/mod/"></button>
                <button class="btn btn-outline-primary bi bi-arrow-up-square ms-4" type="button" data-btnmover="m" data-llamar="modulos/u/mod/"></button>
                <button class="btn btn-outline-primary bi bi-arrow-down-square ms-2" type="button" data-btnmover="m" data-llamar="modulos/d/mod/"></button>
            </div>
        </div>
        <div class="col">
            <h5>SubModulos</h5>
            <select name="lstSubModulos" id="lstSubModulos" multiple class="form-select" size="10">
                <option value="00">Sin datos.</option>
            </select>
            <div class="mt-2 btnsAcciones">
                <button class="btn btn-outline-primary bi bi-plus-square" type="button" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="modulos/a/smod/"></button>
                <button class="btn btn-outline-primary bi bi-pencil-square ms-2" type="button" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="modulos/e/smod/"></button>
                <button class="btn btn-outline-primary bi bi-dash-square ms-2" type="button" data-bs-toggle="modal" data-bs-target="#frmModal" data-llamar="modulos/b/smod/"></button>
                <button class="btn btn-outline-primary bi bi-arrow-up-square ms-4" type="button" data-btnmover="s" data-llamar="modulos/u/smod/"></button>
                <button class="btn btn-outline-primary bi bi-arrow-down-square ms-2" type="button" data-btnmover="s" data-llamar="modulos/d/smod/"></button>
            </div>
        </div>
    </div>
</div>
<?= generaModalGeneral() ?>

<script>
    $(document).ready(() => {
        let appModulos = {
            init: function() {
                $('#frmModal').on('show.bs.modal', appModulos.onClickShowDlg);
                $('.btnsAcciones button[data-btnmover]').on('click', appModulos.onClickUpDown);
                $('#lstModulos').on('change', appModulos.onChangeSelectModulos);
                $('#lstModulos').on('click', (e) => {
                    appModulos.idPadreSeleccionado = e.target.value;
                    appModulos.idItemSeleccionado = '0';
                });
                $('#lstSubModulos').on('click', (e) => {
                    appModulos.idItemSeleccionado = e.target.value;
                });
            },
            idPadreSeleccionado: 0,
            idItemSeleccionado: 0,
            onClickShowDlg: function(e) {
                let o = e.relatedTarget;
                let c = $(o).data('llamar');

                let oSelect = appModulos.seleccionaTipoLista(c.substr(10, 1), c.substr(8, 1));
                if (oSelect === false) return;

                $.ajax({
                    url: baseURL + '/' + c + appModulos.idItemSeleccionado + '/' + appModulos.idPadreSeleccionado,
                    method: 'GET',
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModal .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
            onClickUpDown: function(e) {
                let c = $(e.target).data('llamar');
                let oSelect = appModulos.seleccionaTipoLista(c.substr(10, 1));
                if (oSelect === false) return;
                $.post(baseURL + '/' + c + appModulos.idItemSeleccionado, {}, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        // solo actuializamos el listado modificado
                        appModulos.actualizaListado(oSelect, data.listado, oSelect.options[oSelect.selectedIndex].value);
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },
            onChangeSelectModulos: function(e) {
                let oSelect = e.target;
                if (oSelect.selectedIndex == -1) return;
                let d = baseURL + '/modulos/q/mod/' + oSelect.options[oSelect.selectedIndex].value;
                $.post(d, {}, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        appModulos.actualizaListado($('#lstSubModulos')[0], data.listado);
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
            },
            actualizaListado: function(oSelect, data, id) {
                let tope = data.length;
                let c = '';
                if (id == undefined) id = '';
                for (i = 0; i < tope; i++) {
                    c += '<option value="' + data[i][1] + '"' +
                        (data[i][1] == id ? ' selected ' : '') +
                        '>' + data[i][2] + '</option>';
                }
                $(oSelect).html(c);
            },
            seleccionaTipoLista: function(tipoLista, tipoAccion) {
                if (tipoLista == 'm') {
                    oSelect = $('#lstModulos');
                } else {
                    oSelect = $('#lstSubModulos');
                }
                if (tipoAccion !== 'a') {
                    if (oSelect[0].selectedIndex == -1) {
                        alert('No hay un item seleccionado');
                        return false;
                    }
                }
                if (tipoLista == 's' && appModulos.idPadreSeleccionado === 0) {
                    alert('No hay un modulo seleccionado');
                    return false;
                }
                return oSelect[0];
            }
        };
        appModulos.init();
    });
</script>