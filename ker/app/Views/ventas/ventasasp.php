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
            <i class="bi bi-calculator-fill text-primary me-2 fs-4" style="cursor: pointer;" title="Abrir Calculadora" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/calculadora"></i>
        </div>
        <hr>
    </div>
    <div class="row" id="areaRowCapturaPrincipal">
        <div class="col bg-light px-4 pt-3 border rounded border-primary" id="areaCapturaPdincipal">
            <div class="position-relative" style="z-index:1;">
                <form action="<?= base_url('ventas/modoDocu') ?>" method="post" id="frm02" autocomplete="off" class="row">
                    <div class="col-10">
                        <div class="input-group border rounded mb-2" id="areaModoDocu"></div>
                    </div>
                </form>
                <form action="" method="post" id="frm00" autocomplete="off"></form>
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
                    <tbody id="tblBodyArticulos">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col" style="max-width: 350px;">
            <div class="container-fluid border rounded pt-3  bg-light">
                <form action="<?= base_url('ventas/agregaCliente') ?>" method="post" id="frmBuscaCliente" autocomplete="off">
                    <input type="hidden" name="nIdCliente" id="nIdCliente" value="">
                    <div id="areaCapCliente"></div>
                </form>
                <div class="row mb-1 lh-1">
                    <span class="col-12 fw-bold text-center" id="ventaNomCliente"></span>
                </div>
                <div id="ventaDatosCliente"></div>
                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1 lh-1">
                    <label class="col-7">Articulos vendidos</label><span class="col-5 text-end fw-bold" id="ventaNumArticulos"></span>
                </div>
                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1 lh-1">
                    <label class="col-7">Subtotal</label><span class="col-5 text-end fw-bold" id="ventaSubTotal"></span>
                </div>
                <div class="row mb-1 lh-1">
                    <label class="col-7">Descuento</label><span class="col-5 text-end fw-bold" id="ventaDescuento"></span>
                </div>
                <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                <div class="row fw-bold fs-4">
                    <label class="col-4">TOTAL</label><span class="col-8 text-end" id="ventaTotal"></span>
                </div>
                <div id="ventaAreaErrorPago"></div>
                <div id="ventaAreaMuestraPago"></div>
                <div class="text-center my-1" id="ventaConRegistros">
                    <button class="btn btn-sm btn-outline-primary m-1 btnSoloVenta" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/descuentoGral">
                        Descuento
                    </button>
                    <button class="btn btn-outline-primary btn-sm m-1 btnSoloVenta" type="button" id="btnEnEspera">
                        En espera
                    </button>
                    <button class="btn btn-outline-primary btn-sm m-1" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="ventas/nVenta" data-mod-msj="Desea limpiar la venta? Toda la captura se eliminará!!">
                        Limpiar
                    </button>
                    <button class="btn btn-sm btn-outline-success m-1 btncls-completar" style="width:80px;">
                        Completar
                    </button>
                </div>
                <div class="text-center mt-2 mb-1" id="ventaLastdoc">
                    <button class="btn btn-sm btn-outline-primary me-3" data-llamar="ventas/reimprimirLastDoc" id="bntReimprimirLast">
                        Reimprimir Ultimo Docto (Remision)
                    </button>
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
<?= generaModalGeneral('frmModalNorm', 'modal-md modal-dialog-scrollable') ?>
<?= generaModalGeneral('frmModalDis', 'modal-md') ?>
<?= generaModalGeneral('frmModalBuscArt', 'modal-xl modal-dialog-scrollable') ?>

<script>
    $(document).ready(function() {
        let enfo = '<?= $enfoque ?>';

        let ventas = {
            data: {
                registros: <?= json_encode($registros) ?>,
                enfoque: '<?= $enfoque ?>',
                cliente: <?= json_encode($cliente) ?>,
                docu: <?= json_encode($docu) ?>,
                pago: <?= json_encode($pago) ?>,
                permisoDescuentos: <?= $permisoDescuentos ?>,
                permisoCapturaImporte: <?= $permisoCapturaImporte ?>,
                permisofacturaSinMovtoExistencia: <?= $permisofacturaSinMovtoExistencia ? 'true' : 'false' ?>,
                nomAgente: <?= ($nomAgente !== '') ? "'" . $nomAgente . "'" : 'null' ?>,
                lstTipoListas: <?= isset($lstTipoListas) ? json_encode($lstTipoListas) : 'null' ?>,
                idTipoLista: <?= isset($lstTipoListas) ? '\'' . $idTipoLista . '\'' : 'null' ?>,
                lastDoc: <?= $lastDoc ? 'true' : 'false' ?>,
                rEnEspera: <?= json_encode($rEnEspera) ?>,
                tabventaEnEspera: '<?= $tabventaEnEspera ?>',
            },

            init: function() {
                ventas.refrescaVenta({
                    refreshEnEspera: true,
                    refreshModoDoc: true,
                    refreshRegistros: true,
                    refreshPagos: true,
                    refreshBotones: true,
                });
            },

            asignaData: function(data) {
                if (data.registros) ventas.data.registros = data.registros;
                if (data.enfoque) {
                    ventas.data.enfoque = data.enfoque;
                    enfo = data.enfoque;
                }
                if (data.cliente) ventas.data.cliente = data.cliente;
                if (data.docu) ventas.data.docu = data.docu;
                if (data.pago) ventas.data.pago = data.pago;
                if (data.permisoDescuentos) ventas.data.permisoDescuentos = data.permisoDescuentos;
                if (data.permisoCapturaImporte) ventas.data.permisoCapturaImporte = data.permisoCapturaImporte;
                if (data.permisofacturaSinMovtoExistencia !== undefined) ventas.data.permisofacturaSinMovtoExistencia = data.permisofacturaSinMovtoExistencia;
                if (data.nomAgente) ventas.data.nomAgente = data.nomAgente !== '' ? data.nomAgente : null;
                if (data.lstTipoListas) ventas.data.lstTipoListas = data.lstTipoListas;
                if (data.idTipoLista) ventas.data.idTipoLista = data.idTipoLista;
                if (data.lastDoc !== undefined) ventas.data.lastDoc = data.lastDoc;
                if (data.rEnEspera) ventas.data.rEnEspera = data.rEnEspera;
                if (data.tabventaEnEspera) ventas.data.tabventaEnEspera = data.tabventaEnEspera;
            },
            refrescaVentaEnEspera: function() {
                // formar las ventas ne espera
                if (ventas.data.rEnEspera.length > 0) {
                    let cad = '<ul class="nav nav-tabs" id="tabEnEspera" role="tablist">';
                    ventas.data.rEnEspera.forEach(function(o, i) {
                        let tabItem = 'tab' + o.sdtAlta;
                        let itemActivo = tabItem == ventas.data.tabventaEnEspera ? ' active' : '';
                        cad = cad +
                            '<li class="nav-item" role="presentation">' +
                            '<button class="nav-link' + itemActivo + '" id="' + tabItem + '" type="button" role="tab" aria-controls="home" aria-selected="false">' +
                            'Venta ' + (i + 1).toString() +
                            '</button>' +
                            ' </li>';

                    });
                    cad = cad + '</ul>';
                    $('#tabEnEspera').remove();
                    $('#areaCapturaPdincipal').prepend(cad);
                } else {
                    $('#tabEnEspera').remove();
                }
            },
            refrescaVentaModoDocu: function() {
                // formar el modo de documento
                let cadModoDocu = '' +
                    '<div class="form-check form-check-inline">' +
                    '<input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc1" value="R" ' + (ventas.data.docu.modoDocu === 'R' ? 'checked' : '') + ' >' +
                    '<label class="form-check-label" for="cTipoDoc1">Remisión</label>' +
                    '</div>' +
                    '<div class="form-check form-check-inline">' +
                    '<input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc2" value="F" ' + (ventas.data.docu.modoDocu === 'F' ? 'checked' : '') + ' >' +
                    '<label class="form-check-label" for="cTipoDoc2">Factura</label>' +
                    '</div>' +
                    '<div class="form-check form-check-inline">' +
                    '<input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc3" value="C" ' + (ventas.data.docu.modoDocu === 'C' ? 'checked' : '') + ' >' +
                    '<label class="form-check-label" for="cTipoDoc3">Cotización</label>' +
                    '</div>' +
                    '<div class="form-check form-check-inline me-5">' +
                    '<input class="form-check-input" type="radio" name="sModoDoc" id="cTipoDoc5" value="CB" ' + (ventas.data.docu.modoDocu === 'CB' ? 'checked' : '') + ' >' +
                    '<label class="form-check-label" for="cTipoDoc5">Busca Cotización</label>' +
                    '</div>';
                $('#areaModoDocu').html(cadModoDocu);
                if (['R', 'F', 'C'].indexOf(ventas.data.docu.modoDocu) > -1) {
                    cadModoDocu = '' +
                        '<input type="hidden" name="nIdArticulo" id="nIdArticulo" value="">' +
                        '<div class="input-group ">' +
                        '<input type="text" class="form-control text-end me-1" placeholder="Cantidad" aria-label="Cantidad del producto" id="nCant" name="nCant" tabindex="2" data-llamar="ventas/" value="1" />' +
                        '<input type="text" class="form-control w-75" id="dlArticulos0" name="dlArticulos0" list="dlArticulos" placeholder="Escriba codigo.  + o - para aumentar / disminuir cantidad." aria-label="id/codigo/nombre del producto" tabindex="3" />' +
                        '<datalist id="dlArticulos"></datalist>' +
                        '<i class="bi bi-search text-primary ms-2 fs-4" style="cursor: pointer;" title="Buscar artículo" data-bs-toggle="modal" data-bs-target="#frmModalBuscArt" data-llamar="ventas/buscarticulodlg"></i>' +
                        '</div>';

                } else {
                    cadModoDocu = '' +
                        '<input type="hidden" name="nIdArticulo1" id="nIdArticulo1" value="">' +
                        '<div class="input-group ">' +
                        '<input class="form-control w-25" id="dlBuscaRC0" name="dlBuscaRC0" placeholder="Escriba Folio ' + (ventas.data.docu.modoDocu == 'RB' ? 'Remisión' : 'Cotización') + '" aria-label="Busca Folio" tabindex="1" />' +
                        '<i class="bi bi-search text-primary me-2 fs-4" style="cursor: pointer;" title="Buscar" data-bs-toggle="modal" data-bs-target="#frmModalBuscArt" data-llamar="ventas/buscarticulodlg"></i>' +
                        '</div>';
                }
                $('#frm00').html(cadModoDocu);
                // deshabilitar campos si no hay nombre selecccionado (dependiendo del modo de documento)
                let deshabilitaCampos = null;
                if (ventas.data.docu.modoDocu == 'C' && ventas.data.cliente.nomManual == '') {
                    deshabilitaCampos = 'disabled';
                } else if ((ventas.data.docu.modoDocu == 'R' || ventas.data.docu.modoDocu == 'F') && ventas.data.cliente.nom == '') {
                    deshabilitaCampos = 'disabled';
                }
                $('#dlArticulos0, #nCant').attr('disabled', deshabilitaCampos);

            },
            refrescaVentaRegistros: function() {
                // formar los registors de ventas
                $('#tblBodyArticulos').empty();
                let numArticulos = 0;
                let rows = '';
                for (let k in ventas.data.registros) {
                    let oval = ventas.data.registros[k];
                    let nImp = parseFloat(oval[3]) * parseFloat(oval[2]);
                    numArticulos += parseFloat(oval[3]);

                    rows = rows + '<tr>';
                    if (oval[16].substr(0, 1) == '1') {
                        rows = rows + '<td>' + oval[1].replace(/\r\n|\r|\n/g, '<br />') + '</td>';
                    } else {
                        rows = rows + '<td>' + oval[1] + '</td>';
                    }
                    rows = rows + '<td class="text-end pe-5">' + oval[2].toFixed(2) + '</td>';
                    rows = rows + '<td class="text-center">' + oval[3] + '</td>';
                    rows = rows + '<td class="text-end pe-3">' + nImp.toFixed(2) + '</td>';
                    rows = rows + '<td class="text-end pe-3">' + oval[18].toFixed(2) + '</td>';
                    rows = rows + '<td class="text-end pe-3">' + oval[10].toFixed(2) + '</td>';
                    rows = rows + '<td>';
                    rows = rows + '<i class="bi bi-trash-fill text-primary me-2 " data-bs-toggle="modal" data-bs-target="#mdConfirma" data-titulo="Confirma Borrado" data-llamar="ventas/borraArticulo/' + oval[0] + '" data-mod-msj="Confirma borrar el registro?" style="cursor:pointer;" title="Borrar registro"></i>';
                    if (ventas.data.permisoDescuentos == '1') {
                        rows = rows + '<i class="bi bi-discount-fill text-primary me-2" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/descuentoProd/' + oval[0] + '" style="cursor:pointer;" title="Descuento"></i>';
                    }
                    if (ventas.data.permisoCapturaImporte == '1') {
                        rows = rows + '<i class="bi bi-coin text-primary me-2" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/cambiaPrecio/' + oval[0] + '" style="cursor:pointer;" title="Capturar importe"></i>';
                    }
                    if (oval[16].substr(0, 1) == '1') {
                        rows = rows + '<i class="bi bi-pencil-square text-primary me-2" data-bs-toggle="modal" data-bs-target="#frmModalDis" data-llamar="ventas/ampliaDescripcion/' + oval[0] + '" style="cursor:pointer;" title="Ampliar Descripción"></i>';
                    } else {
                        rows = rows + '<i class="bi bi-pencil-square text-secondary me-2" title="Ampliar Descripción"></i>';
                    }
                    rows = rows + '</td>';
                    rows = rows + '</tr>';
                }
                if (rows == '') rows = '<tr><td colspan="7" class="text-center">No hay registros</td></tr>';
                $('#tblBodyArticulos').append(rows);
                $('#ventaNumArticulos').html(numArticulos.toFixed(0));
            },
            refrescaVentaPagos: function() {
                // validar el id del usuario
                let muestraPagos = true;
                let validaListadoCliente = true;
                let muestraDatosCli = true;
                let nomCliente = ventas.data.cliente.nom;
                // let attrBtnCompletar = ' id="btnPagar" data-llamar="ventas/entrega" ';
                if (ventas.data.docu.modoDocu == 'C') {
                    muestraPagos = false;
                    validaListadoCliente = false;
                    muestraDatosCli = false;
                    nomCliente = ventas.data.cliente.nomManual;
                    // attrBtnCompletar = ' id="btnPagarCotizacion" data-llamar="" ';
                }

                $('#areaCapCliente').empty();
                if (validaListadoCliente) {
                    cad = '' +
                        '<div class="input-group ">' +
                        '<input type="text" class="form-control form-control-sm" id="dlClientes0" name="dlClientes0" list="dlClientes" placeholder="Nombre o código del cliente" aria-label="codigo/nombre del cliente" tabindex="1" />' +
                        '<datalist id="dlClientes"></datalist>' +
                        '</div>';
                } else {
                    cad = '' +
                    '<input type="text" class="form-control form-control-sm mb-2" id="dlClientes0" name="dlClientes0" placeholder="Nombre del cliente" aria-label="nombre del cliente" tabindex="1" />' +
                    '<select name="nIdTipoLista" id="nIdTipoLista" class="form-select form-select-sm" aria-label="selecciona">' +
                    ' <option value="0">Lista...</option>';
                    for (let k in ventas.data.lstTipoListas) {
                        let oval = ventas.data.lstTipoListas[k];
                        cad = cad + '<option value="' + oval.nIdTipoLista + '" ' + (oval.nIdTipoLista == ventas.data.idTipoLista ? 'selected' : '') + '>' + oval.cNombreTipo + '</option>';
                    }
                    cad = cad + '</select>';
                }
                $('#areaCapCliente').html(cad);
                $('#ventaNomCliente').text(nomCliente);
                $('#ventaDatosCliente').empty();
                if (muestraDatosCli) {
                    cad = '' +
                        '<div class="row">' +
                        '<div class="col text-center lh-1" style="font-size:.9rem !important;">Lista:&nbsp;&nbsp;<strong>' + (typeof(ventas.data.cliente.nomTipoLis) == 'undefined' ? '' : ventas.data.cliente.nomTipoLis) + '</strong></div>' +
                        '</div>' +
                        '<div class="row mb-1 lh-1">' +
                        '<label class="col-5">Email</label><span class="col-7 text-break">' + ventas.data.cliente.email + '</span>' +
                        '</div>' +
                        '<div class="row mb-1 lh-1">' +
                        '<label class="col-5">Direccion</label><span class="col-7 text-break">' + ventas.data.cliente.dire + '</span>' +
                        '</div>';
                    $('#ventaDatosCliente').html(cad);
                }
                // totales de venta
                $('#ventaSubTotal').html(ventas.data.pago.sub.toFixed(2));
                $('#ventaDescuento').html(ventas.data.pago.des.toFixed(2));
                $('#ventaTotal').html(ventas.data.pago.tot.toFixed(2));
                // error de pago
                $('#ventaAreaErrorPago').empty();
                if (ventas.data.pago.msjErr !== '') {
                    let cad = '<div class="collapse" id="errCollPago"><div class="alert alert-danger text-center mb-1 py-2 " role="alert">';
                    cad = cad + ventas.data.pago.msjErr + '</div></div>';
                    $('#ventaAreaErrorPago').html(cad);
                }
                // muestra la captura del pago
                $('#ventaAreaMuestraPago').empty();
                if (muestraPagos) {
                    let campoImporte = ventas.data.pago.tot - ventas.data.pago.acum;
                    if (campoImporte < 0) {
                        campoImporte = 0;
                    }
                    let cad = '<div class="row border rounded bg-light pt-2">' +
                        '<div class="col">' +
                        '<p class="lh-1 fw-bold text-center mb-1" style="font-size:0.8em;">Seleccione el pago, la cantidad y pulse ENTER/ENTRAR</p>' +
                        '<form action="" method="post" id="frm03">' +
                        '<input type="hidden" name="frm03Hidd" id="frm03Hidd" value="">' +
                        '<div class="input-group">' +
                        '<select class="form-select form-select-sm" aria-label="" name="frm03Tipo" id="frm03Tipo" tabindex="4">';
                    for (let k in ventas.data.pago.tipos) {
                        let oval = ventas.data.pago.tipos[k];
                        cad = cad + '<option value="' + k.toString() + '" ' + (k.toString() == ventas.data.pago.tipoSeleccionado ? 'selected' : '') + ' data-comision="' + oval[1].toFixed(2) + '">' + oval[2] + '</option>';
                    }
                    cad = cad + '</select>' +
                        '<input type="text" id="frm03Pago" name="frm03Pago" aria-label="Pago" class="form-control form-control-sm text-end" placeholder="Pago" tabindex="5" value="' + campoImporte.toFixed(2) + '">' +
                        '</div>' +
                        '</form>' +
                        '<table class="table table-sm">' +
                        '<thead>' +
                        '<tr>' +
                        '<th></th>' +
                        '<th></th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody id="frm03Tbody">';
                    for (let k in ventas.data.pago.lst) {
                        let oval = ventas.data.pago.lst[k];
                        cad = cad + '<tr>' +
                            '<td>' +
                            '<i class="bi bi-trash-fill text-secondary me-1 fs-6" data-llamar="ventas/borraPago/' + oval['id'] + '" style="cursor:pointer;">' +
                            '</i>' +
                            '<span class="fs-6">' + oval['des'] + '</span>' +
                            '</td>' +
                            '<td class="text-end">' + oval['imp'].toFixed(2) + '</td>' +
                            '</tr>';
                    }
                    cad = cad +
                        '</tbody>' +
                        '</table>' +
                        '</div>' + // <div class="col">
                        '</div>' // <div class="row border rounded ...
                    ;
                    if (ventas.data.pago.cambio > 0) {
                        cad = cad + '<div class="row fw-bold fs-4">' +
                            '<label class="col-5">CAMBIO</label><span class="col-7 text-end">' + ventas.data.pago.cambio.toFixed(2) + '</span>' +
                            '</div>';
                    }
                    $('#ventaAreaMuestraPago').append(cad);
                }

            },
            refrescaVentaBotones: function() {
                // se muestran los botones si hay registros
                if (Object.keys(ventas.data.registros).length > 0) {
                    if (ventas.data.docu.modoDocu == 'C') {
                        $('#ventaConRegistros').find('button.btnSoloVenta').addClass('d-none');
                        $('#ventaConRegistros').find('.btncls-completar').attr('data-llamar', '');
                        $('#ventaConRegistros').find('.btncls-completar').attr('id', 'btnPagarCotizacion');
                    } else {
                        $('#ventaConRegistros').find('button.btnSoloVenta').removeClass('d-none');
                        $('#ventaConRegistros').find('button').removeAttr('disabled');
                        $('#ventaConRegistros').find('.btncls-completar').attr('data-llamar', 'ventas/entrega');
                        $('#ventaConRegistros').find('.btncls-completar').attr('id', 'btnPagar');
                    }
                    if (ventas.data.pago.completar) {
                        $('#ventaConRegistros').find('.btncls-completar').removeAttr('disabled');
                    } else {
                        $('#ventaConRegistros').find('.btncls-completar').attr('disabled', 'disabled');
                    }
                    $('#ventaConRegistros').removeClass('d-none');
                } else {
                    $('#ventaConRegistros').find('button').attr('disabled', 'disabled');
                    $('#ventaConRegistros').addClass('d-none');
                }
                if (ventas.data.lastDoc) {
                    $('#ventaLastdoc').removeClass('d-none');
                    $('#ventaLastdoc').find('button').removeAttr('disabled');
                } else {
                    $('#ventaLastdoc').addClass('d-none');
                    $('#ventaLastdoc').find('button').attr('disabled', 'disabled');
                }

            },
            refrescaVenta: function({
                refreshEnEspera = false,
                refreshModoDoc = false,
                refreshRegistros = false,
                refreshPagos = false,
                refreshBotones = false
            } = {}) {
                let opciones = {
                    refreshEnEspera,
                    refreshModoDoc,
                    refreshRegistros,
                    refreshPagos,
                    refreshBotones,
                };
                $('#areaRowCapturaPrincipal').addClass('d-none');
                if (opciones.refreshEnEspera) ventas.refrescaVentaEnEspera();
                if (opciones.refreshModoDoc) ventas.refrescaVentaModoDocu();
                if (opciones.refreshRegistros) ventas.refrescaVentaRegistros();
                if (opciones.refreshPagos) ventas.refrescaVentaPagos();
                if (opciones.refreshBotones) ventas.refrescaVentaBotones();



                if (opciones.refreshModoDoc) autoArt2.init();
                autoCli.init();
                // movTabla.init();
                if (opciones.refreshModoDoc) docu.init();
                pago.init();
                // ** appVentas.init(); contiene parte de ventas.init()
                if(opciones.refreshBotones) appVentas.init2();
                $('#areaRowCapturaPrincipal').removeClass('d-none');
                appVentas.muestraErrorPago();
                $('#tabEnEspera').on('click', appVentas.enEsperaSelect);
                ventas.refrescaEnfoque();
            },

            refrescaEnfoque: function() {
                let enfo = ventas.data.enfoque;
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
            },
        };

        let movTabla = {
            cmd: '',
            enProceso: false,

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
                if (movTabla.enProceso) return;
                movTabla.enProceso = true;
                let elem = document.getElementById('mdConfirma');
                bootstrap.Modal.getInstance(elem).hide();
                miGlobal.toggleBlockPantalla('...');
                $.post(baseURL + '/' + movTabla.cmd, {
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    miGlobal.toggleBlockPantalla('');
                    if (data.ok == '1') {
                        ventas.asignaData(data.data);
                        ventas.refrescaVenta({
                            refreshEnEspera: true,
                            refreshModoDoc: true,
                            refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });

                    }
                    movTabla.enProceso = false;
                }).
                fail(function(jqxhr, textStatus, err) {
                    miGlobal.toggleBlockPantalla('');
                    movTabla.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                });

            }
        };

        let docu = {
            init: function() {
                $('#frm02').on('change', docu.onChange);
            },
            enProceso: false,
            onChange: function(e) {
                if (docu.enProceso) return;
                docu.enProceso = true;
                miGlobal.toggleBlockPantalla('Cambiando modo de documento...');
                $.post(baseURL + '/ventas/modoDocu', {
                    sModoDoc: e.target.value,
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    miGlobal.toggleBlockPantalla('');
                    if (data.ok == '1') {
                        ventas.asignaData(data.data);
                        ventas.refrescaVenta({
                            refreshEnEspera: true,
                            refreshModoDoc: true,
                            refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });

                    }
                    docu.enProceso = false;
                }).
                fail(function(jqxhr, textStatus, err) {
                    miGlobal.toggleBlockPantalla('');
                    docu.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };

        let appVentas = {
            enProceso: false,
            init: function() {
                $('#bntReimprimirLast').on('click', appVentas.reimprimeDocto);
                $('#btnPagarCotizacion').on('click', appVentas.guardaCotizacion);
                $('#btnPagar').on('click', appVentas.nomodal);

                $('#frmModalDis').on('show.bs.modal', appVentas.modalDescGral);
                $('#frmModalDis').on('hidden.bs.modal', () => {
                    $('body').off('keydown.calculadora');
                });

                $('#frmModalBuscArt').on('show.bs.modal', appVentas.modalBuscArt);
                $('#btnEnEspera').on('click', appVentas.enEsperaAdd);
                // $('#tabEnEspera').on('click', appVentas.enEsperaSelect);

                // appVentas.muestraErrorPago();
            },
            init2: function() {
                $('#btnPagarCotizacion').on('click', appVentas.guardaCotizacion);
                $('#btnPagar').on('click', appVentas.nomodal);
            },
            muestraErrorPago: function() {
                let e = document.getElementById('errCollPago');
                let m;
                if (e !== null) {
                    m = bootstrap.Collapse.getOrCreateInstance(
                        e);
                    m.show();
                }
            },
            enEsperaAdd: function() {
                if (appVentas.enProceso) return;
                appVentas.enProceso = true;
                miGlobal.toggleBlockPantalla('Iniciando Otra Venta...');

                $.post(baseURL + '/ventas/enEspera/a', {
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    miGlobal.toggleBlockPantalla('');
                    appVentas.enProceso = false;
                    if (data.ok == '1') {
                        ventas.asignaData(data.data);
                        ventas.refrescaVenta({
                            refreshEnEspera: true,
                            // refreshModoDoc: true,
                            refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });

                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    miGlobal.toggleBlockPantalla('');
                    appVentas.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
            enEsperaSelect: function(e) {
                if (!($(e.target).hasClass('nav-link'))) return;
                miGlobal.toggleBlockPantalla('cargando venta...');

                $.post(baseURL + '/ventas/enEspera/s', {
                    tabEsperaSeleccionado: e.target.id,
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    miGlobal.toggleBlockPantalla('');
                    if (data.ok == '1') {
                        ventas.asignaData(data.data);
                        ventas.refrescaVenta({
                            refreshEnEspera: true,
                            // refreshModoDoc: true,
                            refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });

                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    miGlobal.toggleBlockPantalla('');
                    console.log('fail', jqxhr, textStatus, err);
                });

            },
            reimprimeDocto: function(e) {
                if(appVentas.enProceso) return;
                appVentas.enProceso = true;
                // ventas/reimprimirLastDoc
                $.post(baseURL + '/ventas/reimprimirLastDoc', {
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    appVentas.enProceso = false;
                    if(data.id == '0') return;
                    if(data.tipo == 'V') {
                        window.open(baseURL + '/ventas/imprimeRemision/' + data.id + '/1/cierra', '_blank');
                    } 
                }).
                fail(function(jqxhr, textStatus, err) {
                    appVentas.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
            guardaCotizacion: function() {
                let f = $('#frmEnvio')[0];
                // f.action = baseURL + '/ventas/guardaVenta';
                // f.submit();
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
                if (appVentas.enProceso) {
                    e.preventDefault();
                    return;
                }
                appVentas.enProceso = true;
                if (d == 'ventas/entrega') {
                    $('#btnPagar')[0].disabled = true;
                    let msj = appVentas.validaDatos();
                    if (msj != '') {
                        e.preventDefault();
                        miGlobal.muestraAlerta(msj, 'ventas', 3000);
                        $('#btnPagar')[0].disabled = false;
                        appVentas.enProceso = false;
                        return;
                    }
                }
                $.post(baseURL + '/ventas/entrega/1', {
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    appVentas.enProceso = false;
                    if (data.ok == '1') {
                        window.open(baseURL + '/ventas/imprimeRemision/' + data.idventa + '/1/cierra', '_blank');
                        ventas.asignaData(data.data);
                        ventas.refrescaVenta({
                            refreshEnEspera: true,
                            refreshModoDoc: true,
                            refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });
                    } else {
                        miGlobal.muestraAlerta(data.msj, 'ventas', 3000);
                        $('#btnPagar')[0].disabled = false;
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    appVentas.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                    $('#btnPagar')[0].disabled = false;
                });
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
                if (appVentas.enProceso) return;
                appVentas.enProceso = true;
                miGlobal.toggleBlockPantalla('Cambiando precio de lista...');
                $.post(baseURL + '/ventas/cambiaLista/' + e.target.value, {
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    appVentas.enProceso = false;
                    miGlobal.toggleBlockPantalla('');
                    if (data.ok == '1') {
                        ventas.asignaData(data.data);
                        ventas.refrescaVenta({
                            // refreshEnEspera: true,
                            // refreshModoDoc: true,
                            refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    appVentas.enProceso = false;
                    miGlobal.toggleBlockPantalla('');
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
            validaDatos: function() {
                if ($('#frm03Tbody tr').length == 0) return 'Falta agregar el pago de la remision.';
                return '';
            }
        };

        let autoArt2 = {
            arrArticulos: <?= json_encode($regArticulos) ?>,
            init: function() {
                $('#dlArticulos0').on('keydown', autoArt2.onKeydownArt) // input text articulo
                    .on('input', autoArt2.onInput);
            },

            onInput: function(e) {
                function buscaNombre(val) {
                    let js = '';
                    $('#dlArticulos').html('');
                    for (x of autoArt2.arrArticulos) {
                        if (x.des.toLowerCase().indexOf(val.toLowerCase()) > -1) {
                            js += '<option value="' + x.des.replace(/"/g, "''") +
                                '" data-id="' + x.id +
                                '">';
                        }
                    }
                    $('#dlArticulos').html(js);
                };

                if (e.target.value && e.target.value.trim().length > 1) {
                    let val = e.target.value.trim();
                    if (/^\d+$/.test(val) === true) return;
                    buscaNombre(val);
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
                        retJSON: true,
                    }, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            if (data.tipomsj == 'MSJ') {
                                miGlobal.muestraAlerta(data.msj, 'ventas', 1700);
                                $(e.target)[0].select();
                            } else {
                                $('#frmModalDis .modal-body').html(data.html);
                                autoArt2.muestraDisponibles();
                            }
                        } else {
                            ventas.asignaData(data.data);
                            ventas.refrescaVenta({
                                // refreshEnEspera: true,
                                refreshModoDoc: true,
                                refreshRegistros: true,
                                refreshPagos: true,
                                refreshBotones: true,
                            });

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
            buscar: true,

            init: function() {
                $('#dlClientes0')
                    .on('focus', () => {
                        autoCli.buscar = !(ventas.data.docu.modoDocu == 'C');
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
                function buscaCliente(id) {
                    $.post(baseURL + '/ventas/agregaCliente', {
                        id: id,
                        retJSON: true,
                    }, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta(data.msj, 'ventas', 1500);
                            $(e.target)[0].select();
                        } else {
                            ventas.asignaData(data.data);
                            ventas.refrescaVenta({
                                // refreshEnEspera: true,
                                refreshModoDoc: true,
                                refreshRegistros: true,
                                refreshPagos: true,
                                refreshBotones: true,
                            });
                        }
                    })
                }

                function cambiaCliente(id) {
                    $.post(baseURL + '/ventas/agregaCliente', {
                        id: id,
                        retJSON: true,
                    }, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta(data.msj, 'ventas', 1500);
                            $(e.target)[0].select();
                        } else {
                            ventas.asignaData(data.data);
                            ventas.refrescaVenta({
                                // refreshEnEspera: true,
                                refreshModoDoc: true,
                                refreshRegistros: true,
                                refreshPagos: true,
                                refreshBotones: true,
                            });
                        }
                    })

                }

                if ((e.which == 13 || e.which == 9) && e.target.value) {
                    e.preventDefault();
                    if (ventas.data.docu.modoDocu == 'C') {
                        cambiaCliente($('#dlClientes0').val());
                    } else {
                        buscaCliente(e.target.value);
                    }
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                    }
                }
            }
        };

        let pago = {
            cantAnt: '',
            buscar: true,
            enProceso: false,
            init: function() {
                $('#frm03Pago')
                    .on('input', pago.onInput)
                    .on('keydown', pago.onKeydown);
                $('#frm03Tbody').on('click', pago.onClick);
                $('#frm03Tipo').on('change', pago.verificaComision);
                $('#nIdTipoLista').on('change', appVentas.onChangeTipoLista);
            },

            onClick: function(e) {
                if (pago.enProceso) return;
                pago.enProceso = true;
                let d = $(e.target).data('llamar');
                if (d == undefined) {
                    pago.enProceso = false;
                    return;
                }
                miGlobal.toggleBlockPantalla('Borrando pago...');

                // frm03Tipo
                let ob = $('#frm03Tipo')[0];
                let opcion = ob.options[ob.selectedIndex];
                let idTipoPago = $(opcion).val();
                $.post(baseURL + '/' + d + '/' + idTipoPago, {
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    pago.enProceso = false;
                    miGlobal.toggleBlockPantalla('');
                    ventas.asignaData(data.data);
                    if (data.ok == '0') {
                        miGlobal.muestraAlerta(data.msj, 'ventas', 2500);
                        ventas.refrescaEnfoque();
                    } else {
                        ventas.refrescaVenta({
                            // refreshEnEspera: true,
                            // refreshModoDoc: true,
                            // refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    pago.enProceso = false;
                    miGlobal.toggleBlockPantalla('');
                    console.log('fail', jqxhr, textStatus, err);
                });
            },

            onInput: function(e) {
                miGlobal.valNumero(e, pago);

            },

            onKeydown: function(e) {
                function agregaPago(idPago, valor) {
                    if (pago.enProceso) return;
                    pago.enProceso = true;
                    miGlobal.toggleBlockPantalla('Agregando pago...');
                    $.post(baseURL + '/ventas/agregaPago', {
                        frm03Tipo: idPago,
                        frm03Pago: valor,
                        retJSON: true,
                    }, null, 'json').
                    done(function(data, textStatus, jqxhr) {
                        pago.enProceso = false;
                        miGlobal.toggleBlockPantalla('');
                        if (data.ok == '0') {
                            miGlobal.muestraAlerta(data.msj, 'ventas', 1500);
                            $(e.target)[0].select();
                        } else {
                            ventas.asignaData(data.data);
                            ventas.refrescaVenta({
                                // refreshEnEspera: true,
                                // refreshModoDoc: true,
                                // refreshRegistros: true,
                                refreshPagos: true,
                                refreshBotones: true,
                            });
                        }
                    }).
                    fail(function(jqxhr, textStatus, err) {
                        pago.enProceso = false;
                        miGlobal.toggleBlockPantalla('');
                        console.log('fail', jqxhr, textStatus, err);
                    });
                };

                if (e.which == 13 && e.target.value > 0) {
                    e.preventDefault();
                    let valor = e.target.value.trim();
                    // miGlobal.toggleBlockPantalla('Agregando pago...');
                    // console.log(valor);
                    // console.log($('#frm03Tipo'), $('#frm03Tipo').val());
                    agregaPago($('#frm03Tipo').val(), valor);
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                    }
                }
            },

            verificaComision: function(e) {
                if (e.target.selectedIndex == -1) return;
                if(pago.enProceso) return;
                pago.enProceso = true;
                miGlobal.toggleBlockPantalla('Actualizando tipo de pago...');
                // frm03Tipo
                let opcion = e.target.options[e.target.selectedIndex];
                let idTipoPago = $(opcion).val();
                $.post(baseURL + '/ventas/aplicaComision/' + idTipoPago, {
                    id: idTipoPago,
                    retJSON: true,
                }, null, 'json').
                done(function(data, textStatus, jqxhr) {
                    pago.enProceso = false;
                    miGlobal.toggleBlockPantalla('');
                    ventas.asignaData(data.data);
                    if (data.ok == '1') {
                        ventas.refrescaVenta({
                            // refreshEnEspera: true,
                            // refreshModoDoc: true,
                            refreshRegistros: true,
                            refreshPagos: true,
                            refreshBotones: true,
                        });
                    } else {
                        miGlobal.muestraAlerta(data.msj, 'ventas', 2000);
                    }
                }).
                fail(function(jqxhr, textStatus, err) {
                    miGlobal.toggleBlockPantalla('');
                    pago.enProceso = false;
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        }
        ventas.init();
        movTabla.init();
        appVentas.init();

    });
</script>