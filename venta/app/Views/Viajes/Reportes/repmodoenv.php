<div class="container bg-light mt-4 border position-relative">
    <div id="cardexwAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h4>Envios con modo ENV</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col">
            <form action="<?php base_url('rep6') ?>" method="get" id="frmFiltro" autocomplete="off">
                <div class="row">
                    <div class="col-5">
                        <div class="input-group">
                            <span class="input-group-text">Rango de Fechas</span>
                            <input type="date" name="dFecIni" id="dFecIni" class="form-control" value="<?= set_value('dFecIni', $fecIni) ?>">
                            <input type="date" name="dFecFin" id="dFecFin" class="form-control" value="<?= set_value('dFecFin', $fecFin) ?>">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group ">
                            <input type="text" class="form-control" id="sProducto" name="sProducto" list="dlArticulos" placeholder="Todos o nombre, id, *codigo para buscar el producto" aria-label="id/codigo/nombre del producto" value="<?= set_value('sProducto', '') ?>" autocomplete="off" />
                            <input type="hidden" name="idProducto" id="idProducto" value="<?= set_value('idProducto', '') ?>">
                            <datalist id="dlArticulos"></datalist>
                        </div>
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
</div>

<script>
    $(document).ready(function() {
        let rep6 = {
            consultando: false,
            init: function() {
                $('#sProducto')
                    .on('input', rep6.onInput)
                    .on('keydown', rep6.onKeydown);
                $('#btnExportar').on('click', rep6.exportar);
            },
            onInput: function(e) {
                let val = (e.target.value ?? '').trim();
                if (val != '') {
                    if (/^\d+$/.test(val) === true || val.substring(0, 1) == '*') return;
                    if(val.length < 2) return;
                    $.get(baseURL + '/articulo/buscaNombre/' + val, {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let a = '';
                            for (const x of data.registro) {
                                a += '<option value="' + x.sDescripcion +
                                    '" data-id="' + x.nIdArticulo + '" >';
                            }
                            $('#dlArticulos').html(a);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
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
                    $('#btnFiltro').click();

                };

                function buscaArticulo(id, par) {
                    miGlobal.toggleBlockPantalla('Consultando...');
                    rep6.consultando = true;
                    $.post(baseURL + '/articulo/leeRegistro/' + id + '/1' + par, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.toggleBlockPantalla();
                            rep6.consultando = false;
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
                        $('#btnFiltro').click();
                    }
                } else {
                    if(e.which == 8 || e.which == 46) $('#idProducto').val('');
                }
            },
            seleccionaOpcion: function (valor) {
                let b = false;
                $('#dlArticulos option').each((i, el) => {
                    if (el.value == valor) {
                        b = el;
                        return false;
                    }
                });
                if (b) {
                    $('#idProducto').val($(b).data('id'));
                }
            },
            onSubmit: function(e) {
                rep6.seleccionaOpcion($('#sProducto').val());
                let v = $('#idProducto').val();
                if (v == '') {
                    e.preventDefault();
                    miGlobal.muestraAlerta('Articulo no Seleccionado', 'cardex', 1500);
                    return;
                }
                if (rep6.consultando === false) miGlobal.toggleBlockPantalla('Consultando...');
            },
            exportar: function() {
                if (rep6.validaRango() === false) return;
                window.open(baseURL + '/viaje/rep6/exportaXLS?' + $('#frmFiltro').serialize());
            },

            validaRango: function(msj) {
                let fIni = $('#dFecIni').val();
                let fFin = $('#dFecFin').val();
                let nVal = 0;
                if (fIni !== '') nVal++;
                if (fFin !== '') nVal++;
                if (nVal < 2) {
                    miGlobal.muestraAlerta((msj ? msj : 'Falta el rango de fechas'), 'cardex', 2000);
                    return false;
                }
                
                let sProd = $('#sProducto').val();
                rep6.seleccionaOpcion(sProd);

                let idProd = $('#idProducto').val();
                // if (idProd == '' || sProd == '') {
                //     miGlobal.muestraAlerta(('Falta seleccionar el producto'), 'cardex', 2000);
                //     return false;
                // }

                return true;
            }
        }
        rep6.init();
    });
</script>