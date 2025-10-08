<div class="container bg-light mt-4 border position-relative">
    <div id="resvenwAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h4>Reporte Ventas de Productos Por Remision</h4>
    <div class="row border rounded m-md-3 p-md-3 mx-0 px-1 py-2" id="frmFiltro">
        <div class="col">
            <div class="input-group">
                <span class="input-group-text">Rango de Fechas</span>
                <input type="date" name="dFecIni" id="dFecIni" class="form-control">
                <input type="date" name="dFecFin" id="dFecFin" class="form-control">
            </div>
        </div>
        <div class="col">
            <input type="text" class="form-control" id="sProducto" name="sProducto" list="dlArticulos" placeholder="Escriba nombre, id, *codigo para buscar el producto" aria-label="id/codigo/nombre del producto" value="<?= set_value('sProducto', '') ?>" />
            <input type="hidden" name="idProducto" id="idProducto" value="<?= set_value('idProducto', '') ?>">
            <datalist id="dlArticulos"></datalist>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-outline-primary ms-3" style="z-index:auto;" id="btnExportar">Exportar</button>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        const appResVenta = {
            init: function() {
                $('#btnExportar').on('click', appResVenta.exportar);
                $('#sProducto')
                    .on('input', appResVenta.onInput)
                    .on('keydown', appResVenta.onKeydown);
            },

            exportar: function() {
                if (appResVenta.validaDatos() === false) return;
                console.log(baseURL + '/ventas/rep3/exportaXLS?' + $('#frmFiltro [name]').serialize());
                window.open(baseURL + '/ventas/rep3/exportaXLS?' + $('#frmFiltro [name]').serialize());
            },

            validaDatos: function(msj) {
                let fIni = $('#dFecIni').val();
                let fFin = $('#dFecFin').val();
                let nVal = 0;
                if (fIni !== '') nVal++;
                if (fFin !== '') nVal++;
                if (nVal < 2) {
                    miGlobal.muestraAlerta((msj ? msj : 'Falta el rango de fechas'), 'resven', 2000);
                    return false;
                }
                if ($('#idProducto').val() == '') {
                    miGlobal.muestraAlerta((msj ? msj : 'No se ha seleccionado un producto'), 'resven', 2000);
                    return false;
                } else {
                    if (appResVenta.sProductoActual != $('#sProducto').val()) {
                        miGlobal.muestraAlerta((msj ? msj : 'No se ha seleccionado un producto'), 'resven', 2000);
                        return false;
                    }
                }
                return true;
            },

            sProductoActual: '',
            onInput: function(e) {
                let val = (e.target.value ?? '').trim();
                if (val != '') {
                    if (/^\d+$/.test(val) === true || val.substring(0, 1) == '*') return;
                    $.get(baseURL + '/articulo/buscaNombre/' + val, {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let a = '',
                                id = '';
                            for (const x of data.registro) {
                                a += '<option value="' + x.sDescripcion +
                                    '" data-id="' + x.nIdArticulo + '" >';
                                if (id == '') {
                                    id = x.nIdArticulo;
                                    appResVenta.sProductoActual = x.sDescripcion;
                                }
                            }
                            $('#dlArticulos').html(a);
                            $('#idProducto').val(id);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#idProducto').val('');
                }
            },

            onKeydown: function(e) {
                function asignaArticulo(d) {
                    $('#dlArticulos').html('<option ' +
                        'value="' + d.sDescripcion + '" ' +
                        'data-id="' + d.nIdArticulo + '" ' +
                        '></option>'
                    );
                    $('#sProducto').val($('#dlArticulos option').val());
                    $('#idProducto').val(d.nIdArticulo);
                    $('#btnExportar').click();

                };

                function buscaArticulo(id, par) {
                    $.post(baseURL + '/articulo/leeRegistro/' + id + '/1' + par, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Articulo no encontrado', 'cardex', 1700);
                            $('#idProducto').val('');
                            $(e.target)[0].select();
                        } else {
                            asignaArticulo(data.registro);
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                let valor = (e.target.value ?? '').trim();
                if ((e.which == 13 || e.which == 9) && valor != '') {
                    e.preventDefault();
                    let car = valor.substring(0, 1);
                    if (car == '*' || /^\d+$/.test(valor)) {
                        let par1 = car == '*' ? valor.substring(1) : valor;
                        let par2 = car == '*' ? '/1' : '';
                        buscaArticulo(par1, par2);
                    } else {
                        if ($('#idProducto').val() != '') {
                            $('#sProducto').val(appResVenta.sProductoActual);
                            $('#btnExportar').click();
                        }
                    }

                }

            }


        };
        appResVenta.init();
    });
</script>