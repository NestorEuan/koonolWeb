<?php
    $nCont = 0;
    $pago['completar'] = 0;
    //$deshabilitaCampos = $proveedor['nIdProveedor'] == 0 ? ( $operacion === 'entrada'? '' : 'disabled'): '';
    $deshabilitaCampos = $modo === 'A' ? ($proveedor['nIdProveedor'] == 0 ? ( $operacion === 'entrada'? '' : 'disabled'): '') : 'disabled';
    $attrBtnCompletar = 'data-bs-toggle="mdConfirma" data-bs-target="#frmModal" id="btnPagar" data-llamar="' . $frmURL . '" ';

    $dlProveedor =  $proveedor['sNombre']??'';

    $aKeys = [
        'compra'  => ['nIdCompra','dcompra','del proveedor'], 
        'entrada' => ['nIdEntrada', 'dEntrada', ''], 
        'traspaso' => ['nIdTraspaso','dTraspaso', 'de la sucursal a solicitar'],
    ];
    $masterkey = $aKeys[$operacion][0];
    $datefld = $aKeys[$operacion][1];
    $campoproveedor = $aKeys[$operacion][2];

?>

<style>
    #tbl tbody tr:hover {
        background-color: rgba(161, 191, 226, 0.501) !important;
    }
</style>
    
<div class="container-fluid h-100">
    <div class="row">
        <div class="col">
            <h4><?= $titulo ?></h4>
            <hr>
        </div>
    </div>
    <div class="row h-75">
        <div class="col bg-light px-4 pt-3 border rounded">
            <div class="position-relative">
                <div id="wAlert">
                    <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    </div>
                </div>
            </div>
            <form action="<?= base_url('/entrada/agregaArticulo/' . $operacion . '/' . strtolower($modo)) ?>" method="post" id="frm00" autocomplete="off">
                <input type="hidden" name="nIdArticulo" id="nIdArticulo" value="">
                <div class="input-group ">
                    <input class="form-control w-75" id="dlArticulos0" name="dlArticulos0" list="dlArticulos" placeholder="Escriba nombre o el codigo del producto" aria-label="codigo/nombre del producto" tabindex="2" <?= $deshabilitaCampos ?> />
                    <datalist id="dlArticulos"></datalist>
                    <input class="form-control text-end" type="text" placeholder="Cantidad" aria-label="Cantidad del producto" id="nCant" name="nCant" tabindex="3" data-llamar="ventas/" <?= $deshabilitaCampos ?> />
                </div>
            </form>

            <div class="mt-3 " style="z-index:1;">
                <table class="table table-striped table-hover" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <th class="text-center" scope="col">Cantidad</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="7" class="fs-5 text-center">No hay
                                    registros</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php
                                $nImp = 0; // round(floatval($r[3]) * floatval($r[2]), 2);
                                ?>
                                <tr>
                                    <th scope="row"><?= ++$nCont ?>
                                    </th>
                                    <td><?= $r['sDescripcion'] ?>
                                    </td>
                                    <td class="text-end pe-5"><?= $r['fCantidad'] ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-trash-fill text-danger me-3 " data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="entrada/borraArticulo/<?= $operacion . '/' . $r['nIdArticulo'] ?>" data-mod-msj="Confirma borrar el registro?" style="cursor:pointer;"></i>
                                        <!-- i class="bi bi-trash-fill text-danger me-3" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="entrada/borraArticulo/< ?= $r['nIdArticulo'] ? >" style="cursor:pointer;" title="Descuento"></i -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <div class="col" style="max-width: 350px;">            
            <div class="container-fluid border rounded pt-3  bg-light">
                <div class="row mb-1">
                    <span class="col-12 fw-bold text-center">
                        <?= $registro['sDescripcion'] ?>
                    </span>
                </div>
                <form action='<?= base_url("entrada/agregaProveedor/$operacion/" . strtolower( $modo )) ?>' method="post" id="frmBuscaProveedor" autocomplete="off">
                    <?php generaCampoTexto('nIdProveedor', $error ?? false, 'hidden', $proveedor ?? null, $modo); ?>
                    <?php if( $operacion !== 'entrada'): ?>
                    <div class="col">
                        <label for="dlProveedores" class="form-label"><?= $operacion === 'traspaso'?'Solicitar a': 'Proveedor'?></label>
                        <div class="input-group ">
                            <input class="form-control w-75" list="dlProveedores" placeholder="Escriba nombre de la sucursal" aria-label="codigo/nombre de <?= $campoproveedor?>" id="dlProveedores0" name="dlProveedores0" value="<?= $dlProveedor?>" />
                            <datalist id="dlProveedores" value = "<?= $dlProveedor?>" ></datalist>
                        </div>
                    </div>
                    <div class="col">
                        <label for="<?=$datefld?>" class="form-label">Fecha de <?= $operacion ?></label>
                        <div class="input-group ">
                            <?php generaCampoTexto("$datefld", $error ?? false, 'date', $registro ?? null, $modo); ?>
                        </div>
                    </div>
                    <?php endif?>
                    <?php if( $operacion !== 'compra'): ?>
                        <div class="col">
                            <label for="sObservacion" class="form-label">Observaciones</label>
                            <!-- input type="hidden" class="form-control" id='sObservacion' name="sObservacion" value = "< ?= $sObservacion? >"  -->
                            <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
                        </div>
                    <?php endif ?>
                </form>

                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1">
                    <label class="col-7">Artículos solicitados</label><span class="col-5 text-end fw-bold"><?= $nCont ?></span>
                </div>
                <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                <div class="text-center my-1">
                    <?php if ($nCont > 0 && $modo === 'A') : ?>
                        <button class="btn btn-outline-primary btn-sm me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="/entrada/limpiaOperacion/<?=$operacion?>/<?=$masterkey?>" data-mod-msj="Desea limpiar la venta? Toda la captura se eliminará!!">
                            Limpiar
                        </button>
                        <!-- button class="btn btn-sm btn-outline-success" < ?= $attrBtnCompletar ?> style="width:80px;" < ?= count($registros) ? '' : 'disabled' ?> -->
                        <button class="btn btn-sm btn-outline-success me-3" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?=$frmURL?>" data-mod-msj="Guardar la información!!">
                        <!-- button class="btn btn-sm btn-outline-success me-3" id="btnGuardar" -->
                            Guardar
                        </button>
                    <?php endif; ?>
                </div>
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



<form action="" method="post" id="frmEnvio">
    <input type="hidden" name="idDummyEnvio" value="" />
</form>

<?= generaModalGeneral('frmModal', 'modal-xl modal-dialog-scrollable') ?>
<?= generaModalGeneral('frmModalDis', 'modal-md') ?>

<script type="text/javascript">

    const selArticulo = {
        buscar: true,
        init: function(){
            $('#dlArticulos0')
            .on('focus', () => {
                selArticulo.buscar = true;
            })
            .on('input', selArticulo.onInput)
            .on('keydown', selArticulo.onKeydown);
        },
        
        muestraAlerta: function (m) {
            let aa = $('#wAlert .alert');
            aa.html(m);
            aa.show(700);
            setTimeout(() => {
                aa.hide(500)
            }, 1100);
        },

        onInput: function(e) {
            if (selArticulo.buscar === false) return;
            if (e.target.value && e.target.value.trim().length > 1) {
                let val = e.target.value.trim();
                if (/^\d+$/.test(val) === true) return;
                $.get(baseURL + '/articulo/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        let js = '';
                        for (x of data.registro) {
                            js += '<option value="' + x.sDescripcion.replace(/"/g,"''") +
                                '" data-id="' + x.nIdArticulo +
                                '" data-precio="' + x.nPrecio +
                                '">' ;
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
                selArticulo.buscar = false;
                $('#nIdArticulo').val(d.nIdArticulo);
                $('#dlArticulos0').val(d.sDescripcion);
                $('#nCant').focus();
            };

            function buscaCodArticulo(id) {
                $.post(baseURL + '/articulo/leeRegistro/' + id, {}, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    if (data.ok == '0') {
                        selArticulo.muestraAlerta('Artículo no encontrado');
                    } else {
                        asignaArticulo(data.registro);
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            };

            let valor = e.target.value.trim();
            if ((e.which == 13 || e.which == 9) && valor.length > 0) {
                e.preventDefault();
                if (/^\d+$/.test(valor) === true) {
                    // solo numero
                    buscaCodArticulo(valor);
                } else {
                    let b = false;
                    let nIdArt = -1;
                    $('#dlArticulos option').each((i, el) => {
                        if (el.value == e.target.value.replace(/"/g,"''")) {
                            b = true;
                            nIdArt = $(el).data('id');
                            return false;
                        }
                    });
                    if (b) {
                        $('#nIdArticulo').val(nIdArt);
                        $('#nCant').focus();
                    } else {
                        selArticulo.muestraAlerta('Articulo no Seleccionado');
                    }
                }
            }
        },
    };

    const valCantidad = {
        cantAnt: '',
        init: function(){
            $('#nCant')
                    .on('input', valCantidad.onInput)
                    .on('keydown', valCantidad.onKeydown);
        },
        onInput: function(e) {
            miGlobal.valNumero(e, valCantidad, { re: /^\d*(?:\.\d{0,3})?$/g })            
        },

        onKeydown: function(e) {
            if ((e.which == 13 || e.which == 9) && e.target.value > 0) {
                if($('#nIdArticulo').val() <= 0) 
                {
                    selArticulo.muestraAlerta('Articulo no Seleccionado');
                    return;
                }
                $('#frm00')[0].submit();
            }
        }
    };

    const movTabla = {
        cmd: '',
        init: function() {
            $('#mdConfirma').on('show.bs.modal', movTabla.confirmar);
            $('#btnGuardar00').on('click', movTabla.enviar);
        },

        confirmar: function(e) {
            let a = $(e.relatedTarget);
            movTabla.cmd = a.data('llamar');
            $('#mdConfirma div.modal-body > p').html(a.data(
                'mod-msj'));
        },

        enviar: function(e) {
            let f = $('#frmEnvio')[0];
            // se realiza un submit
            //window.alert(baseURL + '/' + movTabla.cmd);
            f.action = baseURL + '/' + movTabla.cmd;
            f.submit();
        }
    };

    const appSelProveedor = {
        buscar: true,
        observacion: '',
        fecha: '',
        init: function(){
            $('#dlProveedores0')
            .on('focus', () => {
                appSelProveedor.buscar = true;
            })
            .on('input', appSelProveedor.onInput)
            .on('keydown', appSelProveedor.onKeydown);
            $('#sObservacion')
            .on('focus', (e) => {
                appSelProveedor.observacion = e.target.value.trim();
            })
            .on('focusout', appSelProveedor.onObsFocusOut);
            $("#<?=$datefld?>")
            .on('focus', (e) => {
                appSelProveedor.fecha = e.target.value.trim();
            })
            .on('focusout', appSelProveedor.onDateFocusOut);
        },

        onInput: function(e) {
            if (appSelProveedor.buscar === false) return;
            if (e.target.value && e.target.value.trim().length > 1) {
                let val = e.target.value.trim();
                let js = '';
                if (/^\d+$/.test(val) === true) return;
                alert(baseURL + '/<?= $operacion === 'traspaso'?'sucursal':'proveedor'?>/buscaNombre/' + e.target.value.trim());
                $.get(baseURL + '/<?= $operacion === 'traspaso'?'sucursal':'proveedor'?>/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                    .done(function(data, textStatus, jqxhr) {
                        for (x of data.registro) {
                            <?php if($operacion === 'compra') :?>
                                js += '<option value="' + x.sNombre +
                                        '" data-id="' + x.nIdProveedor +
                                        '">';
                            <?php else:?>
                                js += '<option value="' + x.sDescripcion +
                                    '" data-id="' + x.nIdSucursal +
                                    '">';
                            <?php endif?>
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
                $('#nIdProveedor').val(d.nIdSucursal);
                $('#dlProveedores0').val(d.sDescripcion);
                <?php $deshabilitaCampos = '' ?>
                $('#dlArticulos0').removeAttr('disabled');
                $('#nCant').removeAttr('disabled');
                $('#dlArticulos0').focus();
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
                $.post(baseURL + '/<?= $operacion === 'compra'?'proveedor':'sucursal'?>/leeRegistro/' + id, {}, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    if (data.ok == '0') {
                        muestraAlerta('Proveedor no encontrado');
                    } else {
                        <?php if($operacion === 'compra') :?>
                            nId = data.registro.nIdProveedor;
                            sDes = data.registro.sNombre;
                        <?php else : ?>
                            nId = data.registro.nIdSucursal;
                            sDes = data.registro.sDescripcion;
                        <?php endif ?>
                        $('#nIdProveedor').val(nId);
                        $('#dlProveedores0').val(sDes);
                        $('#frmBuscaProveedor')[0].submit();
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            };

            if ((e.which == 13 || e.which == 9) && e.target.value) {
                e.preventDefault();
                let valor = e.target.value.trim();
                if (/^\d+$/.test(valor) === true) {
                    // solo numero
                    buscaCodProveedor(valor);
                } else {
                    $('#dlProveedores option').each((i, el) => {
                        if (el.value == e.target.value) {
                            asignaProveedor({
                                nIdSucursal: $(el).data('id'),
                                sDescripcion: $(el).val(),
                            });
                            return false;
                        }
                    });
                }
                $("#frmBuscaProveedor")[0].submit();
            }
        },

        onObsFocusOut: function(e) {
            let valor = e.target.value.trim();
            if (e.target.value && e.target.value.trim().length > 1) {
                if(valor == appSelProveedor.observacion) return;
                $("#frmBuscaProveedor")[0].submit();
            }
        },

        onDateFocusOut: function(e) {
            let valor = e.target.value.trim();
            if (e.target.value && e.target.value.trim().length > 1) {
                if(valor == appSelProveedor.fecha) return;
                $("#frmBuscaProveedor")[0].submit();
            }
        }
    };
    
    $(document).ready(function() {
        appSelProveedor.init();
        valCantidad.init();
        selArticulo.init();
        movTabla.init();
        /*
        appSelSucursal.init();
        */
       
    });

    $('#dlArticulos0').select();

</script>