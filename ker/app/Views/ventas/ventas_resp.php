<?php
$nCont = 0;
// <i class="bi bi-info-circle-fill text-dark"></i>
if ($docu['modoDocu'] == 'C') {
    $deshabilitaCampos = ($cliente['nomManual'] == '') ? 'disabled' : '';
} elseif ($docu['modoDocu'] == 'R' || $docu['modoDocu'] == 'F') {
    $deshabilitaCampos = ($cliente['nom'] == '') ? 'disabled' : '';
}
if ($docu['modoDocu'] == 'F') {
    $permisofacturaSinMovtoExistencia = $permisofacturaSinMovtoExistencia ?? false;
}

$CampoImporte = round(floatval($pago['tot']) - floatval($pago['acum']), 2);
if ($CampoImporte <= 0) {
    $CampoImporte = 0;
}

$muestraPagos = true;
$validaListadoCliente = true;
$muestraDatosCli = true;
$nomCliente = $cliente['nom'];
// $attrBtnCompletar = 'data-bs-toggle="modal" data-bs-target="#frmModal" id="btnPagar" data-llamar="ventas/entrega" ';
$attrBtnCompletar = ' id="btnPagar" data-llamar="ventas/entrega" ';
$fPesoTotal = 0;

if ($docu['modoDocu'] == 'C') {
    $muestraPagos = false;
    $validaListadoCliente = false;
    $muestraDatosCli = false;
    $nomCliente = $cliente['nomManual'];
    $attrBtnCompletar = 'id="btnPagarCotizacion" data-llamar=""';
}
?>
<style>
    #tbl tbody tr:hover {
        background-color: rgba(161, 191, 226, 0.501) !important;
    }
</style>
<div class="container-fluid h-100 position-relative">
    <div id="ventaswAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <div class="row">
        <div class="col-5">
            <h4>Ventas</h4>
        </div>
        <div class="col-7 text-end">
            <?php if ($nomAgente !== '') : ?>
                <span class="text-white bg-dark fw-bold px-2 me-2 rounded">Agente de Ventas:</span>
                <span class="text-primary fw-bold pe-5"><?= $nomAgente ?></span>
            <?php endif; ?>
            <i class="bi bi-calculator-fill text-primary me-2 fs-4" style="cursor: pointer;" title="Abrir Calculadora" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/calculadora"></i>
        </div>
        <hr>
    </div>
    <div class="row">
        <div class="col bg-light px-4 pt-3 border rounded border-primary">
            <?php if (count($rEnEspera) > 0) : ?>
                <ul class="nav nav-tabs" id="tabEnEspera" role="tablist">
                    <?php $nCont2 = 1; ?>
                    <?php foreach ($rEnEspera as $rEs) : ?>
                        <?php $valTabEnEspera = 'tab' . (new DateTime($rEs['dtAlta']))->format('YmdHis'); ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $tabventaEnEspera == $valTabEnEspera ? 'active' : '' ?>" id="<?= $valTabEnEspera ?>" type="button" role="tab" aria-controls="home" aria-selected="false">
                                <?= 'Venta ' . strval($nCont2++) ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                    
                </ul>
            <?php endif; ?>
            <div class="position-relative" style="z-index:1;">
                <form action="<?= base_url('ventas/modoDocu') ?>" method="post" id="frm02" autocomplete="off" class="row">
                    <div class="col-10">
                        <div class="input-group border rounded mb-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc1" value="R" <?= $docu['modoDocu'] === 'R' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cTipoDoc1">Remisión</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc2" value="F" <?= $docu['modoDocu'] === 'F' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cTipoDoc2">Factura</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc3" value="C" <?= $docu['modoDocu'] === 'C' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cTipoDoc3">Cotización</label>
                            </div>
                            <div class="form-check form-check-inline me-5">
                                <input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc5" value="CB" <?= $docu['modoDocu'] === 'CB' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cTipoDoc5">Busca Cotización</label>
                            </div>
                            <!-- 
                                < ?php if ($docu['modoDocu'] === 'F') : ? >
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="chkVariasFacturas" id="chkVariasFacturas" <?= $docu['variasFacturas'] === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="chkVariasFacturas">Dividir en varias facturas</label>
                                    </div>
                                    < ?php if ($permisofacturaSinMovtoExistencia) : ? >
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="chkFacturaSinMovtoInv" id="chkFacturaSinMovtoInv" <?= $docu['sinMovtoInv'] === '1' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="chkFacturaSinMovtoInv">No mover inventario</label>
                                        </div>
                                    < ?php endif; ? >
                                < ?php endif; ? >
                            -->
                        </div>
                    </div>
                </form>
                <?php if (in_array($docu['modoDocu'], ['R', 'F', 'C'])) : ?>
                    <form action="<?= base_url('ventas/agregaArticulo') ?>" method="post" id="frm00" autocomplete="off">
                        <input type="hidden" name="nIdArticulo" id="nIdArticulo" value="">
                        <div class="input-group ">
                            <input type="text" class="form-control text-end me-1" placeholder="Cantidad" aria-label="Cantidad del producto" id="nCant" name="nCant" tabindex="2" data-llamar="ventas/" value="1" <?= $deshabilitaCampos ?> />
                            <input type="text" class="form-control w-75" id="dlArticulos0" name="dlArticulos0" list="dlArticulos" placeholder="Escriba codigo.  + o - para aumentar / disminuir cantidad." aria-label="id/codigo/nombre del producto" tabindex="3" <?= $deshabilitaCampos ?> />
                            <datalist id="dlArticulos"></datalist>
                            <i class="bi bi-search text-primary ms-2 fs-4" style="cursor: pointer;" title="Buscar artículo" data-bs-toggle="modal" data-bs-target="#frmModalBuscArt" data-llamar="ventas/buscarticulodlg"></i>
                        </div>
                    </form>
                <?php else : ?>
                    <form action="<?= base_url('ventas/buscaDoc') ?>" method="post" id="frm000" autocomplete="off">
                        <input type="hidden" name="nIdArticulo1" id="nIdArticulo1" value="">
                        <div class="input-group ">
                            <input class="form-control w-25" id="dlBuscaRC0" name="dlBuscaRC0" placeholder="Escriba Folio <?= $docu['modoDocu'] == 'RB' ? 'Remisión' : 'Cotización' ?>" aria-label="Busca Folio" tabindex="1" />
                            <datalist id="dlBuscaRC"></datalist>
                            <i class="bi bi-search text-primary me-2 fs-4" style="cursor: pointer;" title="Abrir Calculadora" data-bs-toggle="modal" data-bs-target="#frmModalBuscArt" data-llamar="ventas/buscarticulodlg"></i>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="mt-3 position-relative" style="z-index:1;">
                <table class="table table-striped table-hover" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">Descripción</th>
                            <th class="text-end pe-5" scope="col">Precio Uni.</th>
                            <th class="text-center" scope="col">Cantidad</th>
                            <th class="text-end" scope="col">Importe</th>
                            <th class="text-end" scope="col">Comision</th>
                            <th class="text-end" scope="col">Descuento</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="8" class="fs-5 text-center">No hay
                                    registros</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php
                                $nImp = round(floatval($r[3]) * floatval($r[2]), 2);
                                $fPesoTotal += round(floatval($r[3]) * floatval($r[17]), 2);
                                ?>
                                <tr>
                                    <?php $nCont++; ?>
                                    <td>
                                        <?php if (substr($r[16], 0, 1) == '1') : ?>
                                            <?= str_replace(array("\r\n", "\r", "\n"), "<br />", $r[1]) ?>
                                        <?php else : ?>
                                            <?= $r[1] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-5"><?= number_format($r[2], 2) ?>
                                    </td>
                                    <td class="text-center"><?= $r[3] ?>
                                    </td>
                                    <td class="text-end pe-3"><?= number_format($nImp, 2) ?>
                                    </td>
                                    <td class="text-end pe-3"><?= number_format($r[18], 2) ?></td>
                                    <td class="text-end pe-3"><?= number_format($r[10], 2) ?></td>
                                    <td>
                                        <i class="bi bi-trash-fill text-primary me-2 " data-bs-toggle="modal" data-bs-target="#mdConfirma" data-titulo="Confirma Borrado" data-llamar="ventas/borraArticulo/<?= $r[0] ?>" data-mod-msj="Confirma borrar el registro?" style="cursor:pointer;" title="Borrar registro"></i>
                                        <?php if ($permisoDescuentos == '1') : ?>
                                            <i class="bi bi-discount-fill text-primary me-2" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/descuentoProd/<?= $r[0] ?>" style="cursor:pointer;" title="Descuento"></i>
                                        <?php endif; ?>
                                        <?php if ($permisoCapturaImporte == '1') : ?>
                                            <i class="bi bi-coin text-primary me-2" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/cambiaPrecio/<?= $r[0] ?>" style="cursor:pointer;" title="Capturar importe"></i>
                                        <?php endif; ?>
                                        <?php if (substr($r[16], 0, 1) == '1') : ?>
                                            <i class="bi bi-pencil-square text-primary me-2" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/ampliaDescripcion/<?= $r[0] ?>" style="cursor:pointer;" title="Ampliar Descripción"></i>
                                        <?php else : ?>
                                            <i class="bi bi-pencil-square text-secondary me-2" title="Ampliar Descripción"></i>
                                        <?php endif; ?>
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
                <form action="<?= base_url('ventas/agregaCliente') ?>" method="post" id="frmBuscaCliente" autocomplete="off">
                    <input type="hidden" name="nIdCliente" id="nIdCliente" value="">
                    <?php if ($validaListadoCliente) : ?>
                        <div class="input-group ">
                            <input type="text" class="form-control form-control-sm" id="dlClientes0" name="dlClientes0" list="dlClientes" placeholder="Nombre o código del cliente" aria-label="codigo/nombre del cliente" tabindex="1" />
                            <datalist id="dlClientes"></datalist>
                            <!-- <button class="btn btn-outline-dark bi bi-plus-square" type="button" id="btnAddNewCliente" data-llamar="cliente/a/1" data-bs-toggle="modal" data-bs-target="#frmModal" style="z-index: auto;"></button> -->
                        </div>
                    <?php else : ?>
                        <input type="text" class="form-control form-control-sm mb-2" id="dlClientes0" name="dlClientes0" placeholder="Nombre del cliente" aria-label="nombre del cliente" tabindex="1" />
                        <select name="nIdTipoLista" id="nIdTipoLista" class="form-select form-select-sm" aria-label="selecciona">
                            <option value="0">Lista...</option>
                            <?php foreach ($lstTipoListas as $v) : ?>
                                <option value="<?= $v['nIdTipoLista'] ?>" <?= ($v['nIdTipoLista'] == $idTipoLista) ? 'selected' : '' ?>>
                                    <?= esc($v['cNombreTipo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </form>
                <div class="row mb-1 lh-1">
                    <span class="col-12 fw-bold text-center"><?= $nomCliente ?></span>
                </div>
                <?php if ($muestraDatosCli) : ?>
                    <div class="row">
                        <div class="col text-center lh-1" style="font-size:.9rem !important;">Lista:&nbsp;&nbsp;<strong><?= $cliente['nomTipoLis'] ?? '' ?></strong></div>
                    </div>
                    <div class="row mb-1 lh-1">
                        <label class="col-5">Email</label><span class="col-7 text-break"><?= $cliente['email'] ?></span>
                    </div>
                    <div class="row mb-1 lh-1">
                        <label class="col-5">Direccion</label><span class="col-7 text-break"><?= $cliente['dire'] ?></span>
                    </div>
                <?php endif; ?>
                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1 lh-1">
                    <label class="col-7">Articulos vendidos</label><span class="col-5 text-end fw-bold"><?= array_sum(array_column($registros, 3)) ?></span>
                </div>
                <div class="row mb-1 pb-1 lh-1 border-bottom">
                    <?php
                    $sUni = 'kg';
                    if ($fPesoTotal > 1000) {
                        $fPesoTotal = round($fPesoTotal / 1000, 2);
                        $sUni = 'Tn';
                    }
                    ?>
                    <label class="col-7">Peso</label><span class="col-5 text-end fw-bold"><?= number_format($fPesoTotal, 2) . ' ' . $sUni ?></span>
                </div>
                <div class="row mb-1 lh-1">
                    <label class="col-7">Subtotal</label><span class="col-5 text-end fw-bold"><?= number_format($pago['sub'], 2) ?></span>
                </div>
                <div class="row mb-1 lh-1">
                    <label class="col-7">Descuento</label><span class="col-5 text-end fw-bold"><?= number_format($pago['des'], 2) ?></span>
                </div>
                <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                <div class="row fw-bold fs-4">
                    <label class="col-4">TOTAL</label><span class="col-8 text-end"><?= number_format($pago['tot'], 2) ?></span>
                </div>
                <?php if ($pago['msjErr'] !== '') : ?>
                    <div class="collapse" id="errCollPago">
                        <div class="alert alert-danger text-center mb-1 py-2 " role="alert">
                            <?= $pago['msjErr'] ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($muestraPagos) : ?>
                    <div class="row border rounded bg-light pt-2">
                        <div class="col">
                            <p class="lh-1 fw-bold text-center mb-1" style="font-size:0.8em;">Seleccione el pago, la cantidad y pulse ENTER/ENTRAR</p>
                            <form action="<?= base_url('ventas/agregaPago') ?>" method="post" id="frm03">
                                <input type="hidden" name="frm03Hidd" id="frm03Hidd" value="">
                                <div class="input-group">
                                    <select class="form-select form-select-sm" aria-label="" name="frm03Tipo" id="frm03Tipo" tabindex="4">
                                        <?php foreach ($pago['tipos'] as $k => $r) : ?>
                                            <?php if ($pago['tipoSeleccionado'] == '') $pago['tipoSeleccionado'] = $k; ?>
                                            <option value="<?= $k ?>" <?= $pago['tipoSeleccionado'] == $k ? 'selected' : '' ?> data-comision="<?= number_format($r[1], 4) ?>">
                                                <?= $r[2] ?>
                                            </option>
                                            <?php $bBndPago = false; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" id="frm03Pago" name="frm03Pago" aria-label="Pago" class="form-control form-control-sm text-end" placeholder="Pago" tabindex="5" value="<?= $CampoImporte ?>">
                                </div>
                            </form>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="frm03Tbody">
                                    <?php foreach ($pago['lst'] as $r) : ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-trash-fill text-secondary me-1 fs-6" data-llamar="ventas/borraPago/<?= $r['id'] ?>" style="cursor:pointer;">
                                                </i>
                                                <span class="fs-6"><?= $r['des'] ?></span>
                                            </td>
                                            <td class="text-end"><?= number_format($r['imp'], 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($pago['cambio'] > 0) : ?>
                        <div class="row fw-bold fs-4">
                            <label class="col-5">CAMBIO</label><span class="col-7 text-end"><?= number_format($pago['cambio'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="text-center my-1">
                    <?php if ($nCont > 0) : ?>
                        <?php if ($docu['modoDocu'] != 'C') : ?>
                            <button class="btn btn-sm btn-outline-primary m-1" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/descuentoGral">
                                Descuento
                            </button>
                            <button class="btn btn-outline-primary btn-sm m-1" type="button" id="btnEnEspera">
                                En espera
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-primary btn-sm m-1" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="ventas/nVenta" data-mod-msj="Desea limpiar la venta? Toda la captura se eliminará!!">
                            Limpiar
                        </button>
                        <button class="btn btn-sm btn-outline-success m-1" <?= $attrBtnCompletar ?> style="width:80px;" <?= $pago['completar'] ? '' : 'disabled' ?>>
                            Completar
                        </button>
                    <?php endif; ?>
                </div>
                <?php if ($lastDoc) : ?>
                    <div class="text-center mt-2 mb-1">
                        <button class="btn btn-sm btn-outline-primary me-3" data-llamar="ventas/reimprimirLastDoc" id="bntReimprimirLast">
                            Reimprimir Ultimo Docto (Remision)
                        </button>
                    </div>
                <?php endif; ?>
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
<?= generaModalGeneral('frmModalNorm', 'modal-md modal-dialog-scrollable') ?>
<?= generaModalGeneral('frmModalDis', 'modal-md') ?>
<?= generaModalGeneral('frmModalBuscArt', 'modal-xl modal-dialog-scrollable') ?>


<script>
    $(document).ready(function() {
        let enfo = '<?= $enfoque ?>';
        let appVentas = {
            init: function() {
                $('#bntReimprimirLast').on('click', appVentas.reimprimeDocto);
                $('#btnPagarCotizacion').on('click', appVentas.guardaCotizacion);
                $('#btnPagar').on('click', appVentas.nomodal);
                $('#nIdTipoLista').on('change', appVentas.onChangeTipoLista);

                $('#frmModalDis').on('show.bs.modal', appVentas.modalDescGral);
                $('#frmModalDis').on('hidden.bs.modal', () => {
                    $('body').off('keydown.calculadora');
                });

                $('#frmModalBuscArt').on('show.bs.modal', appVentas.modalBuscArt);
                $('#btnEnEspera').on('click', appVentas.enEsperaAdd);
                $('#tabEnEspera').on('click', appVentas.enEsperaSelect);


                let e = document.getElementById('errCollPago');
                let m;
                if (e !== null) {
                    m = bootstrap.Collapse.getOrCreateInstance(
                        e);
                    m.show();
                }
            },
            enEsperaAdd: function() {
                let f = $('#frmEnvio')[0];
                f.action = baseURL + '/ventas/enEspera/a';
                f.submit();
            },
            enEsperaSelect: function(e) {
                if(!($(e.target).hasClass('nav-link'))) return;
                miGlobal.toggleBlockPantalla('cargando venta...');
                let f = $('#frmEnvio')[0];
                $('#frmEnvio').append(
                        '<input type="hidden" name="tabEsperaSeleccionado" value="' + e.target.id + '">'
                    );
                f.action = baseURL + '/ventas/enEspera/s';
                f.submit();
            },
            reimprimeDocto: function(e) {
                let cmd = $(e.target).data('llamar');
                let a = $('#frmEnvio');
                a[0].action = baseURL + '/' + cmd;
                a[0].submit();
            },
            guardaCotizacion: function() {
                let f = $('#frmEnvio')[0];
                f.action = baseURL + '/ventas/guardaVenta';
                f.submit();
            },
            proceso: false,
            modal: function(e) {
                let d = $(e.relatedTarget).data('llamar');
                if (appVentas.proceso) {
                    e.preventDefault();
                    return;
                }
                appVentas.proceso = true;
                if (d == 'ventas/entrega') {
                    $('#btnPagar')[0].disabled = true;
                    let msj = appVentas.validaDatos();
                    if (msj != '') {
                        e.preventDefault();
                        miGlobal.muestraAlerta(msj, 'ventas', 3000);
                        return;
                    }
                }
                $.ajax({
                    url: baseURL + '/' + $(e
                        .relatedTarget).data(
                        'llamar'),
                    method: 'GET',
                    data: {},
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModal .modal-body').html(data);
                    appVentas.proceso = false;
                    $('#btnPagar')[0].disabled = false;
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr,
                        textStatus, err);
                    $('#btnPagar')[0].disabled = false;
                    appVentas.proceso = false;
                });
            },
            nomodal: function(e) {
                let d = $(e.target).data('llamar');
                if (appVentas.proceso) {
                    e.preventDefault();
                    return;
                }
                appVentas.proceso = true;
                if (d == 'ventas/entrega') {
                    $('#btnPagar')[0].disabled = true;
                    let msj = appVentas.validaDatos();
                    if (msj != '') {
                        e.preventDefault();
                        miGlobal.muestraAlerta(msj, 'ventas', 3000);
                        $('#btnPagar')[0].disabled = false;
                        return;
                    }
                }
                let f = $('#frmEnvio')[0];
                f.action = baseURL + '/ventas/entrega/1';
                f.submit();
            },
            modalDescGral: function(e) {
                let d = $(e.relatedTarget).data('llamar');
                if (d == undefined) return; // indica que no tiene 'llamar'
                $.ajax({
                    url: baseURL + '/' + $(e
                        .relatedTarget).data(
                        'llamar'),
                    method: 'GET',
                    data: {},
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModalDis .modal-body').html(
                        data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr,
                        textStatus, err);
                });
            },
            modalBuscArt: function(e) {
                let d = $(e.relatedTarget).data('llamar');
                $('#frmModalBuscArt .modal-body').html('');
                $.ajax({
                    url: baseURL + '/' + d,
                    method: 'GET',
                    data: {},
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    $('#frmModalBuscArt .modal-body').html(data);
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },


            onChangeTipoLista: function(e) {
                let f = $('#frmEnvio')[0];
                f.action = baseURL + '/ventas/cambiaLista/' + e.target.value;
                f.submit();
            },
            validaDatos: function() {
                if ($('#frm03Tbody tr').length == 0) return 'Falta agregar el pago de la remision.';
                return '';
            }
        };

        let autoArt2 = {
            //hndFrmModalNorm: false,
            buscar: true,
            init: function() {
                $('#dlArticulos0').on('keydown', autoArt2.onKeydownArt) // input text articulo
                    .on('input', autoArt2.onInput)
                    .on('focus', () => {
                        autoArt2.buscar = true;
                    });
                //autoArt2.hndFrmModalNorm = new bootstrap.Modal(document.getElementById('frmModalNorm'), {});

            },

            onInput: function(e) {
                if (autoArt2.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/articulo/buscaNombre/nnnnn', {
                        nombreventas: val,
                    }, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let js = '';
                            for (x of data.registro) {
                                js += '<option value="' + x.sDescripcion.replace(/"/g, "''") +
                                    '" data-id="' + x.sCodigo +
                                    '" data-precio="' + x.nPrecio +
                                    '" data-existencia="' + x.fExistencia +
                                    '">';
                            }
                            $('#dlArticulos').html(js);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdArticuloBuscar').val('');
                    $('#nPrecio').val('');
                }
            },

            onKeydownArt: function(e) {

                function buscaArticulo(id) {
                    // al recibir un registro y con inventario, se agrega directamente
                    // se puede devolver la existencia en otros almacenes si no tiene
                    // si tiene se envia
                    let c = $('#nCant').val();
                    $.post(baseURL + '/ventas/buscaCodigo/eeee/' + c, {
                        nombreventas: id,
                    }, null, 'html').
                    done(function(data, textStatus, jqxhr) {
                        if (data.substr(0, 4) == 'nOoK') {
                            if (data.substr(4, 3) == 'MSJ') {
                                miGlobal.muestraAlerta(data.substr(7), 'ventas', 1700);
                                $(e.target)[0].select();
                            } else {
                                $('#frmModalDis .modal-body').html(data.substr(4));
                                autoArt2.muestraDisponibles();
                            }
                        } else {
                            $('#mainCnt > div.row').first().html(data);
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                function mueveCantidad(k) {
                    // 107 ++
                    if (k == 107) {
                        e.preventDefault();
                        let a = $('#nCant').val().toString();
                        if (a == '')
                            aVal = 1;
                        else
                            aVal = parseInt(a);
                        aVal++;
                        $('#nCant').val(aVal);
                    }
                    // 109 --
                    if (k == 109) {
                        e.preventDefault();
                        let a = $('#nCant').val().toString();
                        if (a == '')
                            aVal = 1;
                        else
                            aVal = parseInt(a);
                        if (aVal > 1) aVal--;
                        $('#nCant').val(aVal);
                    }
                };

                let valor = e.target.value.trim();
                if ((e.which == 13 || e.which == 9) && valor.length > 0) {
                    e.preventDefault();
                    // busco articulo (cod o desc)
                    buscaArticulo(valor.trim());
                } else {
                    mueveCantidad(e.which);
                }
            },

            onInputCant: function(e) {
                miGlobal.valNumero(e, autoArt2, {
                    re: /^\d*$/g
                })
            },

            modalDisponibles: null,
            muestraDisponibles: function() {
                $('#frmModalDis').off('.bs.modal.disponibles')
                    .on('hidden.bs.modal.disponibles', () => {
                        $('#dlArticulos0')[0].select();
                    });
                autoArt2.modalDisponibles = bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('frmModalDis')
                    );
                autoArt2.modalDisponibles.show();
            },
        };

        let autoCli = {
            cantAnt: '',
            buscar: true,
            init: function() {
                $('#dlClientes0')
                    .on('focus', () => {
                        autoCli.buscar = <?= $validaListadoCliente ? 'true' : 'false' ?>;
                    })
                    .on('input', autoCli.onInput)
                    .on('keydown', autoCli.onKeydown);
            },

            onInput: function(e) {
                if (autoCli.buscar === false) return;
                if (e.target.value && e.target.value.trim().length > 3) {
                    let val = e.target.value.trim();
                    if (/^\d+$/.test(val) === true) return;
                    $.get(baseURL + '/cliente/buscaNombre/' + e.target.value.trim(), {}, null, 'json')
                        .done(function(data, textStatus, jqxhr) {
                            let a = '';
                            for (const x of data.registro) {
                                a += '<option value="' + x.sNombre +
                                    '" data-id="' + x.nIdCliente + '">';
                            }
                            $('#dlClientes').html(a);
                        })
                        .fail(function(jqxhr, textStatus, err) {
                            console.log('fail', jqxhr, textStatus, err);
                        });
                } else {
                    $('#nIdCliente').val('');
                }
            },

            onKeydown: function(e) {
                function buscaCodCliente(id) {
                    $.post(baseURL + '/cliente/leeRegistro/' + id, {}, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta('Cliente no encontrado', 'ventas', 1500);
                            $(e.target)[0].select();
                        } else {
                            $('#nIdCliente').val(data.registro.nIdCliente);
                            $('#frmBuscaCliente')[0].submit();
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                function validaCliente(e) {
                    let valor = e.target.value.trim();
                    if (/^\d+$/.test(valor) === true) {
                        buscaCodCliente(valor); // solo numeros
                    } else {
                        let b = false,
                            br = false;
                        $('#dlClientes option').each((i, el) => {
                            if (!br) br = el;
                            if (el.value == e.target.value) {
                                autoCli.buscar = false;
                                $('#nIdCliente').val($(el).data('id'));
                                b = true;
                                return false;
                            }
                        });
                        if (b) {
                            $('#frmBuscaCliente')[0].submit();
                        } else {
                            if (br) {
                                autoCli.buscar = false;
                                $('#nIdCliente').val($(br).data('id'));
                                $('#frmBuscaCliente')[0].submit();
                            } else {
                                miGlobal.muestraAlerta('Cliente no Seleccionado', 'ventas', 1500);
                            }
                        }
                    }
                };

                if ((e.which == 13 || e.which == 9) && e.target.value) {
                    e.preventDefault();
                    <?php if ($validaListadoCliente) : ?>
                        validaCliente(e);
                    <?php else : ?>
                        $('#nIdCliente').val($('#dlClientes0').val());
                        $('#frmBuscaCliente')[0].submit();
                    <?php endif; ?>
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                    }
                }
            }
        };

        let movTabla = {
            cmd: '',
            init: function() {
                $('#mdConfirma').on('show.bs.modal', movTabla.borrar);
                $('#btnGuardar00').on('click', movTabla.enviar);
            },

            borrar: function(e) {
                let a = $(e.relatedTarget);
                movTabla.cmd = a.data('llamar');
                $('#mdConfirma div.modal-body > p').html(a.data(
                    'mod-msj'));
                let tit = a.data('titulo');

                if (tit) $('#mdConfirma div.modal-body > h3').html(tit);
            },

            enviar: function(e) {
                let f = $('#frmEnvio')[0];
                // se realiza un submit
                f.action = baseURL + '/' + movTabla.cmd;
                f.submit();
            }
        };

        let docu = {
            init: function() {
                $('#frm02').on('change', docu.onChange);
            },

            onChange: function(e) {
                $('#frm02')[0].submit();
            }

        };

        let pago = {
            cantAnt: '',
            buscar: true,
            init: function() {
                $('#frm03Pago')
                    .on('input', pago.onInput)
                    .on('keydown', pago.onKeydown);
                $('#frm03Tbody').on('click', pago.onClick);
                $('#frm03Tipo').on('change', pago.verificaComision);
            },

            onClick: function(e) {
                let f = $('#frmEnvio')[0];
                let d = $(e.target).data('llamar');
                if (d == undefined) return;
                miGlobal.toggleBlockPantalla('Borrando pago...');

                // frm03Tipo
                let ob = $('#frm03Tipo')[0];
                let opcion = ob.options[ob.selectedIndex];
                let idTipoPago = $(opcion).val();
                // se realiza un submit
                f.action = baseURL + '/' + $(e.target).data('llamar') + '/' + idTipoPago;
                f.submit();
            },

            onInput: function(e) {
                miGlobal.valNumero(e, pago);

            },

            onKeydown: function(e) {
                if (e.which == 13 && e.target.value > 0) {
                    e.preventDefault();
                    let valor = e.target.value.trim();
                    miGlobal.toggleBlockPantalla('Agregando pago...');
                    $('#frm03')[0].submit();
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                    }
                }
            },

            verificaComision: function(e) {
                if (e.target.selectedIndex == -1) return;
                let f = $('#frm03')[0];
                miGlobal.toggleBlockPantalla('Actualizando tipo de pago...');
                // frm03Tipo
                let opcion = e.target.options[e.target.selectedIndex];
                let idTipoPago = $(opcion).val();
                f.action = baseURL + '/ventas/aplicaComision/' + idTipoPago;
                f.submit();
            }
        }

        autoArt2.init();
        autoCli.init();
        movTabla.init();
        docu.init();
        pago.init();
        appVentas.init();
        if (enfo == 'art') {
            $('#dlArticulos0').select();
        } else if (enfo == 'tiplis') {
            $('#nIdTipoLista').focus();
        } else if (enfo == 'art1') {
            $('#dlBuscaRC0').select();
        } else if (enfo == 'selPag') {
            $('#frm03Pago').select();
        } else if (enfo == 'pag') {
            $('#frm03Pago').select();
        } else if (enfo == 'cli') {
            $('#dlClientes0').select();
        }
    });
</script>