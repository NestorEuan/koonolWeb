<div class="container-fluid h-100">
    <div class="row">
        <div class="col">
            <h4>Buscar artículos</h4>
            <hr>
        </div>
    </div>
    <div class="position-relative">
        <div class="input-group w-50 mx-auto">
            <input type="text" class="form-control" id="busArtDlgIdArt" name="busArtDlgIdArt" placeholder="Escriba parte de la descripcion" aria-label="nombre del producto" />
            <button type="button" class="btn btn-primary ms-4" id="busArtDlgBtnBuscar">Buscar</button>
        </div>
        <hr>
        <table class="table table-striped w-75 mx-auto">
            <thead>
                <tr>
                    <th class="text-center">Codigo</th>
                    <th>Descripcion</th>
                    <th class="text-center">Disponibilidad<br>en Sucursales</th>
                    <th class="text-center">Precio</th>
                    <th class="text-center">Seleccionar</th>
                </tr>
            </thead>
            <tbody id="busArtDlgTbody">
            </tbody>
        </table>

        <hr>
        <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelar00">Cerrar</button>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

            let busArtDlg = {
                lastHtml: '',
                init: function() {
                    $('#busArtDlgBtnBuscar').on('click', busArtDlg.onClick);
                    $('#busArtDlgIdArt')
                        .on('keydown', busArtDlg.onKeydown)
                        .focus();

                    new bootstrap.Popover($('#busArtDlgTbody')[0], {
                        selector: '[data-bs-toggle="popover"]',
                        placement: 'left',
                        trigger: 'focus'
                    });
                    $('#busArtDlgTbody').on('inserted.bs.popover', function(e) {
                        let o = $(e.target);
                        let id = o.data('id');
                        let idPop = o.attr('aria-describedby');
                        busArtDlg.cargaDatos(id, idPop,
                            o.parent().parent().find('.descriArticulo').text())
                    }).on('click', function(e) {
                        let aDat = $(e.target).data('idartcod');
                        if (aDat) {
                            $('#dlArticulos0').val(aDat);
                            $('#dlArticulos0').select();
                        }
                    });
                },

                onClick: function(e) {
                    let id = $('#busArtDlgIdArt').val();
                    if (id == '') return;
                    miGlobal.toggleBlockPantalla('Consultando...');
                    $.post(baseURL + '/articulo/buscaNombre/' + id + '/1', {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        miGlobal.toggleBlockPantalla('');
                        $('#busArtDlgTbody').empty();
                        if (data.ok == '0') {
                            busArtDlg.muestraAlerta('Artículo no encontrado');
                        } else {
                            busArtDlg.muestraArticulos(data.registro);
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                        miGlobal.toggleBlockPantalla('');
                    });
                },

                muestraArticulos: function(regs) {
                    let b = $('#busArtDlgTbody');
                    for (let r of regs) {
                        b.append(
                            '<tr>' +
                            '<td>' + r.sCodigo + '</td>' +
                            '<td class="descriArticulo">' + r.sDescripcion + '</td>' +
                            '<td><button type="button" class="btn btn-outline-primary fw-bold py-1" style="width:90px;"  data-bs-toggle="popover" title="Existencias en Sucursales" data-id="E' + r.nIdArticulo + '" data-bs-content="Cargando...">' + r.fExistencias + '</button></td>' +
                            '<td><button type="button" class="btn btn-outline-primary py-1" data-bs-toggle="popover" title="Lista de Precios" data-id="P' + r.nIdArticulo + '" data-bs-content="cargando...">Precios</button></td>' +
                            '<td class="text-center"><button type="button" class="btn btn-outline-success py-1 bi bi-check-circle-fill" data-bs-dismiss="modal" data-idartcod="' + r.sCodigo + '"></button></td>' +
                            '</tr>'
                        );
                    }
                },

                onKeydown: function(e) {
                    let valor = e.target.value.trim();
                    if ((e.which == 13 || e.which == 9) && valor.length > 0) {
                        e.preventDefault();
                        $('#busArtDlgBtnBuscar').trigger('click');
                    }
                },

                cargaDatos: function(d, idPop, descriArticulo) {
                    let tipo = d.substr(0, 1);
                    let id = d.substr(1);
                    let url;
                    if (tipo == 'E') {
                        url = baseURL + '/existencias/muestraExistencias/' + id;
                    } else {
                        url = baseURL + '/artprecios/muestraPrecios/' + id;
                    }
                    $('#' + idPop).css({
                        'max-width': 'none'
                    });
                    $('#' + idPop).addClass('border border-dark');
                    let oTitulo = $('#' + idPop + ' .popover-header');
                    oTitulo.html(descriArticulo);
                    oTitulo.css({
                        'font-size': '.95rem',
                        'text-align': 'center',
                    });
                    $.post(url, {}, null, 'html').
                    done(function(data, textStatus, jqxhr) {
                        $('#' + idPop + ' .popover-body').html(data);
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                },
            };

            busArtDlg.init();
        });
    </script>