<?php
$nSub = 0.0;
$nIva = 0.0;
$nTot = 0.0;

$dcompra = date('Y-m-d');
$nIdSucursal = 0;
$nIdProveedor = 0;
$dlSucursal = '';
$dlProveedor = '';
if(isset($registro)){
    
    $dcompra = $registro['dcompra'];
    $nIdSucursal = $registro['nIdSucursal'];
    $nIdProveedor = $registro['nIdProveedor'];
    $dlSucursal = $registro['sDescripcion'];
    $dlProveedor = $registro['sNombre'];
}
?>

<div class="container-fluid h-100">
    <div class="row">
        <div class="col">
            <h3>Registrar cotizaci&oacute;n de compras</h3>
            <hr>
        </div>
        <form action="<?= base_url()?>/compras/registrar" method="post" id="addcomprafrm" autocomplete="off">
            <input type="hidden" id="nIdcompra" name="nIdcompra" value="">
            <div class="row">
                <div class="col-3">
                    <label for="dcompra">F.Compra</label>
                    <input id="dcompra" name="dcompra" class="form-control" type="date" value = "<?= $dcompra?>" />
                    <div id="dcompraFB" class="invalid-feedback"></div>
                </div>
                <div class="col-3">
                    <label for="nIdSucursal" class="form-label">Sucursal</label>
                    <input type="hidden" class="form-control" id='nIdSucursal' name="nIdSucursal" value = "<?= $nIdSucursal?>" >
                    <div id="nIdSucursalFB" class="invalid-feedback"></div>
                    <div class="input-group ">
                        <input class="form-control w-75" list="dlSucursales" placeholder="Escriba nombre de la sucursal" aria-label="codigo/nombre del producto" id="dlSucursales0" name="dlSucursales0"  value="<?= $dlSucursal?>" />
                        <datalist id="dlSucursales"></datalist>
                    </div>
                </div>
                <div class="col-3">
                    <label for="nIdProveedor" class="form-label">Proveedor</label>
                    <input type="hidden" class="form-control" id='nIdProveedor' name="nIdProveedor" value = "<?= $nIdProveedor?>" >
                    <div id="nIdProveedorFB" class="invalid-feedback"></div>
                    <div class="input-group ">
                        <input class="form-control w-75" list="dlProveedores" placeholder="Escriba nombre de la sucursal" aria-label="codigo/nombre del producto" id="dlProveedores0" name="dlProveedores0" value="<?= $dlProveedor?>" />
                        <datalist id="dlProveedores" value = "<?= $dlProveedor?>" ></datalist>
                    </div>
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row h-75">
        <div class="col bg-light px-4 pt-3 border rounded">
            <div class="position-relative">
                <div id="wAlert">
                    <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                        Artículo no encontrado
                    </div>
                </div>
                <form action="<?= base_url('/entrada/compra/agregaArticulo') ?>" method="post" id="frm00" autocomplete="off">
                    <input type="hidden" name="nIdArticulo" id="nIdArticulo" value="">
                    <input type="hidden" name="nPrecio" id="nPrecio" value="">
                    <div class="input-group ">
                        <input class="form-control w-75" list="dlArticulos" placeholder="Escriba nombre o el codigo del producto" aria-label="codigo/nombre del producto" id="dlArticulos0" name="dlArticulos0" />
                        <datalist id="dlArticulos"></datalist>
                        <input class="form-control" type="text" placeholder="Cantidad" aria-label="Cantidad del producto" id="nCant" name="nCant" />
                    </div>
                </form>
            </div>
            <!--- tabla compras --->
            <div class="mt-3 " style="z-index:1;">
                <table class="table table-striped table-hover" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <th class="text-end pe-5" scope="col">Precio Uni.</th>
                            <th class="text-center" scope="col">Cantidad</th>
                            <th class="text-end pe-5" scope="col">Importe</th>
                            <th scope="col">Descuento</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="7" class="fs-5 text-center">No hay registros</td>
                            </tr>
                        <?php else : ?>
                            <?php $nCont = 0; ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php 
                                $nImp = round(floatval($r[3]) * floatval($r[2]), 2);
                                $nSub += $nImp;
                                ?>
                                <tr>
                                    <th scope="row"><?= ++$nCont ?></th>
                                    <td><?= $r[1] ?></td>
                                    <td class="text-end pe-5"><?= $r[2] ?></td>
                                    <td class="text-center"><?= $r[3] ?></td>
                                    <td class="text-end pe-5"><?= number_format($nImp, 2) ?></td>
                                    <td>0</td>
                                    <td>
                                        <i class="bi bi-pencil-fill text-primary me-3" data-bs-toggle="modal" data-bs-target="#addArticulo" data-llamar="compras/editaCant/<?= $r[0] ?>" style="cursor:pointer;"></i>
                                        <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="compras/borraArticulo/<?= $r[0] ?>" style="cursor:pointer;"></i>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mdConfirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Confirmar</h3>
                <hr>
                <p>Confirma borrar el registro?</p>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar00">No</button>
                    <button type="button" class="btn btn-primary" id="btnGuardar00">Si</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="" method="post" id="frmEnvioBorra">
    <input type="hidden" name="idBorraArt" value="" />
</form>


<script type="text/javascript">
    const appTablaMtto = {
        cmd: '',
            init: function() {
                document.getElementById('mdConfirma').addEventListener('show.bs.modal', appTablaMtto.borrar);
                $('#btnGuardar00').on('click', appTablaMtto.enviar);
            },

            borrar: function(e) {
                appTablaMtto.cmd = $(e.relatedTarget).data('llamar');
            },

            enviar: function(e) {
                let f = $('#frmEnvioBorra')[0];
                // se realiza un submit
                f.action = baseURL + '/' + appTablaMtto.cmd;
                window.alert(f.action);
                f.submit();
            }        

    };

    const appCantidad = {
        cantAnt: '',
        init: function(){
            $('#nCant')
                    .on('input', appCantidad.onInput)
                    .on('keydown', appCantidad.onKeydown);
        },
        onInput: function(e) {
                if (e.target.value && e.target.value.trim().length > 0) {
                    let r = /^\d*(?:\.?\d*)?$/g;
                    if (r.test(e.target.value.trim())) {
                        appCantidad.cantAnt = e.target.value.trim();
                    } else {
                        e.target.value = appCantidad.cantAnt;
                    }
                } else {
                    appCantidad.cantAnt = '';
                }
            },
        onKeydown: function(e) {
            if ((e.which == 13 || e.which == 9) && e.target.value) {
                // se agrega al listado localmente 
                $('#frm00')[0].submit();
            }
        }
    };

    const appSelSucursal = {
        buscar: true,
        init: function(){
            $('#dlSucursales0')
                    .on('focus', () => {
                        appSelSucursal.buscar = true;
                    })
                    .on('input', appSelSucursal.onInput)
                    .on('keydown', appSelSucursal.onKeydown);
        },

        onInput: function(e) {
                if (appSelSucursal.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    let js = '';
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/sucursal/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            for (x of data.registro) {
                                js += '<option value="' + x.sDescripcion +
                                    '" data-id="' + x.nIdSucursal +
                                    '">';
                            }

                            $('#dlSucursales').html(js);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdSucursal').val('');
                }
        },

        onKeydown: function(e) {
            function asignaSucursal(d) {
                window.alert(d.id);
                appSelSucursal.buscar = false;
                $('#nIdSucursal').val(d.id);
                $('#dlSucursales0').val(d.des);
            };

            function muestraAlerta(m) {
                let aa = $('#wAlert .alert');
                aa.html(m);
                aa.show(700);
                setTimeout(() => {
                    aa.hide(500)
                }, 1100);
            };

            function buscaCodSucursal(id) {
                $.post(baseURL + '/sucursal/leeRegistro/' + id, {}, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    if (data.ok == '0') {
                        muestraAlerta('Sucursal no encontrado');
                    } else {
                        asignaSucursal(data);
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            };

            if ((e.which == 13 || e.which == 9) && e.target.value) {
                let valor = e.target.value.trim();
                if (/^\d+$/.test(valor) === true) {
                    // solo numero
                    buscaCodSucursal(valor);
                } else {
                    $('#dlSucursales option').each((i, el) => {
                        if (el.value == e.target.value) {
                            asignaSucursal({
                                id: $(el).data('id'),
                                des: $(el).val(),
                            });
                            return false;
                        }
                    });
                }
            }
        },
    };

    const appSelArticulo = {
        buscar: true,
        init: function(){
            $('#dlArticulos0')
                    .on('focus', () => {
                        appSelArticulo.buscar = true;
                    })
                    .on('input', appSelArticulo.onInput)
                    .on('keydown', appSelArticulo.onKeydown);
            //$('#dlArticulos0').on('input', appSelArticulo.muestalo);
        },

        muestralo: function(){
            var val = this.value;
            if($('#allNames option').filter(function(){
                return this.value.toUpperCase() === val.toUpperCase();
            }).length) {
                //send ajax request
                window.alert(this.value);
            }
        },

        onInput: function(e) {
                if (appSelArticulo.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    let js = '';
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/articulo/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            for (x of data.registro) {
                                js += '<option value="' + x.sDescripcion +
                                    '" data-id="' + x.nIdArticulo +
                                    '" data-precio="' + x.nPrecio +
                                    '">';
                            }

                            $('#dlArticulos').html(js);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdArticulo').val('');
                    $('#nPrecio').val('');
                }
        },

        onKeydown: function(e) {
            function asignaArticulo(d) {
                appSelArticulo.buscar = false;
                $('#nIdArticulo').val(d.id);
                $('#nPrecio').val(d.nPrecio);
                $('#dlArticulos0').val(d.des);
            };

            function muestraAlerta(m) {
                let aa = $('#wAlert .alert');
                aa.html(m);
                aa.show(700);
                setTimeout(() => {
                    aa.hide(500)
                }, 1100);
            };

            function buscaCodArticulo(id) {
                $.post(baseURL + '/articulo/leeRegistro/' + id, {}, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    if (data.ok == '0') {
                        muestraAlerta('Proveedor no encontrado');
                    } else {
                        window.alert(data);
                        asignaArticulo(data);
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            };

            if ((e.which == 13 || e.which == 9) && e.target.value) {
                let valor = e.target.value.trim();
                if (/^\d+$/.test(valor) === true) {
                    // solo numero
                    buscaCodArticulo(valor);
                } else {
                    $('#dlArticulos option').each((i, el) => {
                        if (el.value == e.target.value) {
                            asignaArticulo({
                                id: $(el).data('id'),
                                des: $(el).val(),
                                nPrecio: $(el).data('precio'),
                            });
                            return false;
                        }
                    });
                }
            }
        },
    };

    const appSelProveedor = {
        buscar: true,
        init: function(){
            $('#dlProveedores0')
                    .on('focus', () => {
                        appSelProveedor.buscar = true;
                    })
                    .on('input', appSelProveedor.onInput)
                    .on('keydown', appSelProveedor.onKeydown);

        },

        onInput: function(e) {
                if (appSelProveedor.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    let js = '';
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/proveedor/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            for (x of data.registro) {
                                js += '<option value="' + x.sNombre +
                                    '" data-id="' + x.nIdProveedor +
                                    '">';
                            }

                            $('#dlProveedores').html(js);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdProveedor').val('');
                }
        },

        onKeydown: function(e) {
            function asignaProveedor(d) {
                appSelProveedor.buscar = false;
                $('#nIdProveedor').val(d.id);
                $('#dlProveedores0').val(d.des);
            };

            function muestraAlerta(m) {
                let aa = $('#wAlert .alert');
                aa.html(m);
                aa.show(700);
                setTimeout(() => {
                    aa.hide(500)
                }, 1100);
            };

            function buscaCodProveedor(id) {
                $.post(baseURL + '/proveedor/leeRegistro/' + id, {}, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    if (data.ok == '0') {
                        muestraAlerta('Proveedor no encontrado');
                    } else {
                        asignaProveedor(data);
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            };

            if ((e.which == 13 || e.which == 9) && e.target.value) {
                let valor = e.target.value.trim();
                if (/^\d+$/.test(valor) === true) {
                    // solo numero
                    buscaCodProveedor(valor);
                } else {
                    $('#dlProveedores option').each((i, el) => {
                        if (el.value == e.target.value) {
                            asignaProveedor({
                                id: $(el).data('id'),
                                des: $(el).val(),
                            });
                            return false;
                        }
                    });
                }
            }
        },
    };

    const appCompra = {
        modo: '',

        init: function() {
            $('#btnAgregar').on('click', appCompra.agregaForm);
            $('#bodyTabla').on('click', appCompra.editaForm);
            $('#btnGuardar').on('click', appCompra.guarda);
        },

        agregaForm: function() {
            appCompra.modo = 'A';
            appCompra.initForm(appCompra.modo);
            $('#addcomprafrm').find('#nIdcompra').val('-1');
        },

        editaForm: function(e) {
            let at = e.target.getAttribute('data-accion-id');
            if (at === null) return;
            appCompra.modo = at.substr(0, 1);
            $('#addcomprafrm').find('#nIdcompra').val(at.substr(1));
            appCompra.leeRegistro(at.substr(1), appCompra.llenaForm);
        },

        guarda: function() {
            let url;
            if (appCompra.modo === 'B') {
                url = '<?= base_url("compras/borra") ?>';
            } else {
                url = '<?= base_url("compras/guarda") ?>';
            }
            $.ajax({
                url: url,
                method: 'POST',
                data: $('#addcompra form').serialize(),
                dataType: 'json'
            }).done(function(data, textStatus, jqxhr) {
                if (data.ok === '0') {
                    appCompra.mostrarErroresBS(data.amsj);
                    return false;
                }
                // se guardan los datos
                $('#addcompra').modal('hide');
                location.reload();
            }).fail(function(jqxhr, textStatus, err) {
                console.log('failGuarda', jqxhr, textStatus, err);
            });
        },

        leeRegistro: function(id, llenaForm) {
            $.ajax({
                url: '<?= base_url("compras/leeRegistro") ?>' + '/' + id,
                method: 'POST',
                data: {},
                dataType: 'json'
            }).done(function(data, textStatus, jqxhr) {
                if (data.ok === '0') {
                    console.log(textStatus);
                    return false;
                }
                llenaForm(data.registro);
            }).fail(function(jqxhr, textStatus, err) {
                console.log('fail', jqxhr, textStatus, err);
            });
        },

        llenaForm: function(d) {
            let f = $('#addcomprafrm');
            appCompra.initForm(appCompra.modo);
            f.find('#dcompra').val(d.dcompra);
            f.find('#nIdSucursal').val(d.nIdSucursal);
            f.find('#nIdProveedor').val(d.nIdProveedor);
            f.find('#fTotal').val(d.fTotal);
            f.find('#nProductos').val(d.nProductos);
            if (appCompra.modo === 'B') {
                $('#addcompra .form-control').each(function(i, el) {
                    el.setAttribute('readonly', true);
                });
            }
            $('#addcompra').modal('show');
        },

        mostrarErroresBS: function(o) {
            $('#addcompra .form-control').each(function(i, el) {
                if (o[el.id]) {
                    el.setAttribute('aria-describedby', el.id + 'FB');
                    el.classList.add('is-invalid');
                    $('#' + el.id + 'FB')[0].innerHTML = o[el.id];
                } else {
                    el.removeAttribute('aria-describedby');
                    el.classList.remove('is-invalid');
                    el.classList.add('is-valid');
                }
            });
        },

        resetErroresBS: function() {
            $('#addcompra .form-control').each(function(i, el) {
                el.removeAttribute('aria-describedby');
                el.removeAttribute('readonly');
                el.classList.remove('is-invalid', 'is-valid');
            });
        },

        initForm: function(m) {
            let t;
            if (m == 'A' || m == 'E') {
                $('#txtBorrar')[0].classList.add('d-none');
                $('#btnCancelar')[0].classList.add('d-none');
                $('#btnGuardar')[0].classList.remove('btn-secondary');
                $('#btnGuardar')[0].classList.add('btn-primary');
                $('#btnGuardar').text('Guardar');
                t = m == 'A' ? 'Agregar' : 'Editar';
            } else {
                $('#txtBorrar')[0].classList.remove('d-none');
                $('#btnCancelar')[0].classList.remove('d-none', 'btn-secondary');
                $('#btnCancelar')[0].classList.add('btn-primary');
                $('#btnCancelar').text('No');
                $('#btnGuardar')[0].classList.remove('btn-primary');
                $('#btnGuardar')[0].classList.add('btn-secondary');
                $('#btnGuardar').text('Si');
                t = 'Borrar';
            }
            $('h5.modal-title').text(t + ' Compra');
            $('#addcomprafrm')[0].reset();
            appCompra.resetErroresBS();
        }
    };

    $(document).ready(function() {
        appCompra.init();
        appCantidad.init();
        appTablaMtto.init();
        appSelSucursal.init();
        appSelArticulo.init();
        appSelProveedor.init();

    });
</script>