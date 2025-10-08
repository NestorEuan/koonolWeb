<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr class="p-0 m-0">
<form action="<?= $frmURL ?>" method="post" id="addArticulofrm" class="m-0 p-0" enctype="multipart/form-data" accept-charset="utf-8">

    <nav>
        <div class="nav nav-tabs mb-3" id="navTabArt" role="tablist">
            <button class="nav-link active" id="navGeneralTab" data-bs-toggle="tab" data-bs-target="#navGeneral" type="button" role="tab" aria-controls="navGeneral" aria-selected="true">General</button>
            <button class="nav-link" id="navPrecioTab" data-bs-toggle="tab" data-bs-target="#navPrecio" type="button" role="tab" aria-controls="navPrecio" aria-selected="false">Precio</button>
            <button class="nav-link" id="navEntradaTab" data-bs-toggle="tab" data-bs-target="#navEntrada" type="button" role="tab" aria-controls="navEntrada" aria-selected="false">Entradas</button>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="navGeneral" role="tabpanel" aria-labelledby="navGeneralTab">
            <input type="hidden" id="nIdArticulo" name="nIdArticulo" value="<?= $id ?>">
            <div class="row input-group input-group-sm m-2">
                <?php generaCampoTexto('sDescripcion', $error ?? false, null, $registro ?? null, $modo, 'form-control-sm', 'placeholder="Descripción"'); ?>
            </div>
            <div class="row ">
                <div class="col-6">
                    <div class="col input-group input-group-sm">
                        <?php generaCampoTexto('sCodigo', $error ?? false, null, $registro ?? null, $modo, "form-control", 'placeholder="Código"'); ?>
                    </div>
                    <!-- 
            <div class="col">
                < ?php generaCampoTexto('cConDescripcionAdicional', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ? >
                <label for="cConDescripcionAdicional" class="form-check-label">Con Descripcion Adicional</label>
            </div>
            -->
                    <div class="col input-group input-group-sm">
                        <label for="nIdArtClasificacion" class="form-label m-1">Clasificación</label>
                        <?php generaCampoTexto('nIdArtClasificacion', $error ?? false, 'select', $registro ?? null, $modo ?? '', 'form-control m-1', 'placeholder="Clasificación', $regClasificacion, 'sClasificacion'); ?>
                    </div>
                    <div class="row">
                        <div class="col-2 input-group input-group-sm">
                            <?php generaCampoTexto('nCosto', $error ?? false, null, $registro ?? null, $modo, "form-control", 'placeholder="Costo"'); ?>
                        </div>
                        <!--
                <div class="col">
                    < ?php generaCampoTexto('cSinExistencia', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ? >
                    <label for="cSinExistencia" class="form-check-label">Articulo sin existencia</label>
                </div>
                <div class="col">
                    < ?php generaCampoTexto('cImporteManual', $error ?? false, 'checkbox', $registro ?? null, $modo, '', '', null, '1'); ? >
                    <label for="cImporteManual" class="form-check-label">Importe manual</label>
                </div>
                -->
                    </div>
                    <h6 class="fw-bold">Datos SAT-CFDI</h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group input-group-sm m-1">
                                <?php generaCampoTexto('sCveProdSer', $error ?? false, null, $registro ?? null, $modo, "form-control", 'placeholder="Clave SAT"'); ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="col-6 input-group input-group-sm m-1">
                                <?php generaCampoTexto('sCveUnidad', $error ?? false, null, $registro ?? null, $modo, "form-control", 'placeholder="Clave Unidad SAT"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col border rounded m-1 position-relative overflow-auto" style="max-height: 375px;">
                    <!-- img src="#" id="foto" class="img-fluid p-2 m-0 rounded text-center" alt="..."  style="object-fit: scale-down; max-height: 375px" -->
                    <img src="<?= ($img64 == '#') ? '#' : 'data:image/' . $tipoimg . ';base64, ' . $img64 ?>" id="foto" class="img-fluid p-2 m-0 rounded text-center" alt="...">

                    <div class="position-absolute bottom-0 end-0 p-2">
                        <?php generaCampoTexto('fFotoArchivo', $error ?? false, 'file', $registro ?? null, $modo, "", "hidden"); ?>
                        <i class="bi bi-camera text-primary" id="btnFotoArt" style="cursor:pointer;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="navPrecio" role="tabpanel" aria-labelledby="navPrecioTab">
            <div class="row m-2 py-0">
                <h7 class="bg-ligth px-2 fw-bold">Precio Venta</h7>
                <table class="table m-0">
                    <tr>
                        <?php
                        $nCols = 0;
                        foreach ($regListas as $rgLst) {
                            $nCols++;
                            if ($nCols == 5) {
                        ?>
                    </tr>
                    <tr>
                    <?php
                                //echo '</tr><tr>';
                                $nCols = 1;
                            }
                    ?>
                    <td>
                        <div class="col-8">

                            <div class="row input-group input-group-sm">
                                <?php echo "<B>{$rgLst['cNombreTipo']}</B>" ?>
                            </div>
                            <div class="row input-group input-group-sm">
                                <input type="text" class="form-control input-sm py-0" id="fPrecio-<?= $rgLst['nIdTipoLista'] ?>" name="fPrecio-<?= $rgLst['nIdTipoLista'] ?>" value="<?= $precios[0]['L' . $rgLst['nIdTipoLista']] ?? '' ?>" placeholder="Sugerido">
                            </div>
                            <div class="row input-group input-group-sm">
                                A partir de 12 (5%)
                            </div>
                            <div class="row input-group input-group-sm">
                                <input type="text" class="form-control input-sm py-0" id="fDoce-<?= $rgLst['nIdTipoLista'] ?>" name="fDoce-<?= $rgLst['nIdTipoLista'] ?>" value="<?= $precios[1]['L' . $rgLst['nIdTipoLista']] ?? '' ?>" placeholder="Sugerido">
                            </div>
                            <div class="row input-group input-group-sm">
                                A partir de 50 (10%)
                            </div>
                            <div class="row input-group input-group-sm">
                                <input type="text" class="form-control input-sm py-0" id="fCincuenta-<?= $rgLst['nIdTipoLista'] ?>" name="fCincuenta-<?= $rgLst['nIdTipoLista'] ?>" value="<?= $precios[2]['L' . $rgLst['nIdTipoLista']] ?? '' ?>">
                            </div>
                        </div>
                    </td>
                <?php
                            //echo "<td> {$rgLst['cNombreTipo']} </td>";
                        }
                ?>
                    </tr>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="navEntrada" role="tabpanel" aria-labelledby="navEntradaTab">
            <div class="row m-2 m-0">
                <h7 class="bg-ligth px-2 py-0 fw-bold">Entrada por alta de producto</h7>
                <table class="table m-0">
                    <tr>
                        <?php
                        $nCols = 0;
                        foreach ($regSucursales as $rgSuc) {
                            $nCols++;
                            if ($nCols == 5) {
                        ?>
                    </tr>
                    <tr>
                    <?php
                                //echo '</tr><tr>';
                                $nCols = 1;
                            }
                    ?>
                    <td>
                        <div class="col-6">

                            <div class="row input-group input-group-sm">
                                <?php echo "{$rgSuc['sClave']}" ?>
                            </div>
                            <div class="row input-group input-group-sm">
                                <?php generaCampoTexto('fEntrada-' . $rgSuc['nIdSucursal'], $error ?? false, null, $registro ?? null, $modo, "form-control", ''); ?>
                            </div>
                        </div>
                    </td>
                <?php
                            //echo "<td> {$rgLst['cNombreTipo']} </td>";
                        }
                ?>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</form>
<div class="d-flex justify-content-end">
    <?php if ($modo === 'A' || $modo === 'E') : ?>
        <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
    <?php else : ?>
        <span class="text-danger fw-bold me-4" id="txtBorrar">Desea borrar el registro?</span>
        <button type="button" class="btn btn-secondary px-2 me-2" id="btnGuardar">Si</button>
        <button type="button" class="btn btn-primary px-2" data-bs-dismiss="modal" id="btnCancelar">No</button>
    <?php endif; ?>

</div>

<script type="text/javascript">
    $(document).ready(function() {
        let addArticulo = {
            init: function() {
                $('#btnGuardar').on('click', addArticulo.enviar);
                $('#nCosto').on('input', addArticulo.onInput);
                $('#btnFotoArt').on('click', addArticulo.addFoto);
                $('#fFotoArchivo').on('change', addArticulo.actualizaFoto);
            },
            onInput: function(e) {
                miGlobal.valNumero(e, addArticulo);
            },
            enProceso: false,
            enviar: function(e) {
                if (addArticulo.enProceso) return;
                addArticulo.enProceso = true;
                $('#btnGuardar').prop('disabled', true);
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: (new FormData($('#addArticulofrm')[0])),
                    contentType: false,
                    processData: false,
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                    $('#btnGuardar').prop('disabled', false);
                    addArticulo.enProceso = false;
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    $('#btnGuardar').prop('disabled', false);
                    addArticulo.enProceso = false;
                });
            },
            addFoto: function(e) {
                var fotoArc = $('#fFotoArchivo').trigger('click');
            },
            actualizaFoto: function(e) {
                var fileName = e.target.files[0].name;
                //$("#fFotoArchivo").val(fileName);

                var reader = new FileReader();
                reader.onload = function(e) {
                    //console.log(e.target.name);
                    // get loaded data and render thumbnail.
                    $('#foto').attr('src', e.target.result);
                    //$('#foto').src = e.target.result;
                };
                //console.log(this.files[0].name);
                // read the image file as a data URL.
                reader.readAsDataURL(this.files[0]);

            },
        };
        let precioLista = {
            init: function() {
                $("input[id^='fPrecio-']")
                    .on('input', precioLista.sugerido);
            },
            sugerido: function(e) {
                miGlobal.valNumero(e, precioLista);
                //console.log(e.target.value);
                //$().val();
                let sufijo = e.target.id.substr(8);
                $("#fDoce-" + sufijo).val(parseFloat((e.target.value * 0.95).toFixed(2)));
                $("#fCincuenta-" + sufijo).val(parseFloat((e.target.value * 0.9).toFixed(2)));
            }
        };
        addArticulo.init();
        precioLista.init();
    });
</script>