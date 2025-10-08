<div class="container bg-light mt-4 border position-relative">
    <div id="cardexwAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h4>Movimiento en Inventario de productos</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col">
            <form action="<?php base_url('cardex') ?>" method="get" id="frmFiltro">
                <div class="row">
                    <div class="col-5">
                        <div class="input-group">
                            <span class="input-group-text">Rango de Fechas</span>
                            <input type="date" name="dFecIni" id="dFecIni" class="form-control" value="<?= set_value('dFecIni', $fecIni) ?>">
                            <input type="date" name="dFecFin" id="dFecFin" class="form-control" value="<?= set_value('dFecFin', $fecFin) ?>">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="input-group ">
                            <input type="text" class="form-control" id="sProducto" name="sProducto" list="dlArticulos" placeholder="Escriba nombre, id, *codigo para buscar el producto" aria-label="id/codigo/nombre del producto" value="<?= set_value('sProducto', '') ?>" />
                            <input type="hidden" name="idProducto" id="idProducto" value="<?= set_value('idProducto', '') ?>">
                            <datalist id="dlArticulos"></datalist>
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="input-group">
                            <button type="submit" class="btn btn-primary" style="z-index:auto;" id="btnFiltro">Filtrar</button>
                            <button type="button" class="btn btn-primary" style="z-index:auto;" id="btnExportaCardex">Exportar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row border rounded">
        <div class="col-2"></div>
        <div class="col-8">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Tipo Docto</th>
                        <th class="text-center">Id Docto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $saldo = null; ?>
                    <?php foreach ($registros as $r) : ?>
                        <?php if ($saldo === null) : ?>
                            <?php $saldo = round(floatval($r['fSaldoInicial']), 3);  ?>
                            <tr>
                                <td class="text-center"><?= date("d-m-Y", strtotime($r['dtAlta'])) ?></td>
                                <td class="text-center">Saldo Inical</td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"><?= $saldo ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php $saldo += round(floatval($r['fCantidad']), 3); ?>
                        <tr>
                            <td class="text-center"><?= date("d-m-Y", strtotime($r['dtAlta'])) ?></td>
                            <td class="text-center"><?= $r['cOrigen'] ?></td>
                            <td class="text-center"><?= $r['nIdOrigen'] ?></td>
                            <td class="text-center"><?= round(floatval($r['fCantidad']), 3) ?></td>
                            <td class="text-center"><?= $saldo ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-2"></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let conCardex = {
            sProductoActual: '',
            init: function() {
                $('#sProducto')
                    .on('input', conCardex.onInput)
                    .on('keydown', conCardex.onKeydown);
                $('#btnExportaCardex')
                    .on( 'click', conCardex.exportaCSV);
            },
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
                                    conCardex.sProductoActual = x.sDescripcion;
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
                    $('#btnFiltro').click();

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
                            $('#sProducto').val(conCardex.sProductoActual);
                            $('#btnFiltro').click();
                        }
                    }

                }

            },
            exportaCSV: function(e) {
                window.open(baseURL + '/cardex/exporta');
            }
        }
        conCardex.init();
    });
</script>