<div class="container bg-light mt-4 border position-relative">
    <div id="cardexwAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <h4>Movimiento en Inventario de productos</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col">
            <form action="<?php base_url('cardex') ?>" method="get" id="frmFiltro" autocomplete="off">
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
                            <input type="text" class="form-control" id="sProducto" name="sProducto" list="dlArticulos" placeholder="Escriba nombre, id, *codigo para buscar el producto" aria-label="id/codigo/nombre del producto" value="<?= set_value('sProducto', '') ?>" autocomplete="off" />
                            <input type="hidden" name="idProducto" id="idProducto" value="<?= set_value('idProducto', '') ?>">
                            <datalist id="dlArticulos"></datalist>
                        </div>
                    </div>
                    <div class="col-3 text-end">
                        <div class="input-group">
                            <button type="submit" class="btn btn-primary" style="z-index:auto;" id="btnFiltro">Filtrar</button>
                            <button type="button" class="btn btn-primary ms-3" style="z-index:auto;" id="btnExportar">Exportar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row border rounded">
        <div class="col-1"></div>
        <div class="col-10">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 110px;">Fecha</th>
                        <th class="text-center" style="width: 110px;">Tipo Movto</th>
                        <th class="text-center">Id Docto</th>
                        <th class="text-center">Remision Origen Entrega</th>
                        <th class="text-center">Origen del Envio</th>
                        <th class="text-center">Remision/Id Traspaso</th>
                        <th class="text-center">Sucursal</th>
                        <th class="text-center">Con Factura</th>
                        <th class="text-center">Cliente/Suc. Origen</th>
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
                                <td class="text-center">Saldo Inicial</td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"><?= $saldo ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php $saldo = round($saldo, 3) + round(floatval($r['fCantidad']), 3); ?>
                        <tr>
                            <td class="text-center"><?= date("d-m-Y", strtotime($r['dtAlta'])) ?></td>
                            <td class="text-center"><?= $r['cOrigen'] ?></td>
                            <td class="text-center"><?= $r['nIdOrigen'] ?></td>
                            <?php if ($r['cOrigen'] == 'entrega') : ?>
                                <td class="text-center"><?= $r['nFolioRemisionEntrega'] ?></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"><?= $r['sucOriEntrega'] ?></td>
                                <td class="text-center"><?= intval($r['tieneFacturaEntrega']) > 0 ? 'Si' : '' ?></td>
                                <td class="text-center"><?= $r['sClienteVEntrega'] ?></td>
                            <?php elseif ($r['cOrigen'] == 'envio') : ?>
                                <?php if ($r['nFolioRemisionEnvio'] != '0') : ?>
                                    <td class="text-center"></td>
                                    <td class="text-center">Venta</td>
                                    <td class="text-center"><?= $r['nFolioRemisionEnvio'] ?></td>
                                    <td class="text-center"><?= $r['sucOriEnvio'] ?></td>
                                    <td class="text-center"><?= intval($r['tieneFacturaEnvio']) > 0 ? 'Si' : '' ?></td>
                                    <td class="text-center"><?= $r['sClienteVEnvio'] ?></td>
                                <?php elseif ($r['nIdTraspaso'] != '0') : ?>
                                    <td class="text-center"></td>
                                    <td class="text-center">Traspaso</td>
                                    <td class="text-center"><?= $r['nIdTraspaso'] ?></td>
                                    <td class="text-center"><?= $r['sucOriTraspaso'] ?></td>
                                    <td class="text-center"></td>
                                    <td class="text-center"></td>
                                <?php endif; ?>
                            <?php else : ?>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                            <?php endif; ?>
                            <td class="text-center"><?= round(floatval($r['fCantidad']), 3) ?></td>
                            <td class="text-center"><?= $saldo ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-1"></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let conCardex = {
            consultando: false,
            init: function() {
                $('#sProducto')
                    .on('input', conCardex.onInput)
                    .on('keydown', conCardex.onKeydown);
                $('#frmFiltro').on('submit', conCardex.onSubmit);
                $('#btnExportar').on('click', conCardex.exportar);
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
                    conCardex.consultando = true;
                    $.post(baseURL + '/articulo/leeRegistro/' + id + '/1' + par, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.toggleBlockPantalla();
                            conCardex.consultando = false;
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
                conCardex.seleccionaOpcion($('#sProducto').val());
                let v = $('#idProducto').val();
                if (v == '') {
                    e.preventDefault();
                    miGlobal.muestraAlerta('Articulo no Seleccionado', 'cardex', 1500);
                    return;
                }
                if (conCardex.consultando === false) miGlobal.toggleBlockPantalla('Consultando...');
            },
            exportar: function() {
                if (conCardex.validaRango() === false) return;
                window.open(baseURL + '/cardex/exportaXLS?' + $('#frmFiltro').serialize());
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
                conCardex.seleccionaOpcion(sProd);

                let idProd = $('#idProducto').val();
                if (idProd == '' || sProd == '') {
                    miGlobal.muestraAlerta(('Falta seleccionar el producto'), 'cardex', 2000);
                    return false;
                }

                return true;
            }
        }
        conCardex.init();
    });
</script>