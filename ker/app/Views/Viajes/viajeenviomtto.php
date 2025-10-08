<div class="container-fluid position-relative">
    <div id="viajesenviowAlert">
        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;z-index:1900;" role="alert">
        </div>
    </div>
    <div class="row" id="cntTitulo2">
        <div class="col">
            <h4><?= $titulo ?></h4>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-9" id="cntTablaEnvio">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-center">Modo<br>ENV</th>
                        <th class="text-center">Descripción</th>
                        <th class="text-center">Solicitado</th>
                        <th class="text-center">Enviado</th>
                        <th class="text-center">Disponible</th>
                        <th class="text-center">Por Enviar</th>
                        <th class="text-center" style="width:90px;">Peso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $nCont = 0; ?>
                    <?php $nPesoTotal = 0; ?>
                    <?php foreach ($regsDet as $k => $r) : ?>
                        <?php
                        $nPesoProd = $r['fPeso'] * $r['capturada'];
                        $nPesoTotal += $nPesoProd;
                        if ($r['sindisponible'] && $r['cModoEnv'] == '0') :
                            $fondo = 'bg-danger text-light';
                        else :
                            $fondo = '';
                        endif;
                        $bDeshabilitado = $r['porSurtir'] == 0;
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?= ++$nCont ?></td>
                            <td class="text-center"><input type="checkbox" class="form-check-input" name="chkBx<?= $r['nIdArticulo'] ?>" id="chkBx<?= $r['nIdArticulo'] ?>" <?= $tipoAccion == 'e' && !$bDeshabilitado ? '' : 'disabled' ?> <?= $r['cModoEnv'] == '1' ? 'checked' : '' ?>></td>
                            <td><?= $r['nomArt'] ?></td>
                            <td class="text-center"><?= round(floatval($r['fCantidad']), 3) ?></td>
                            <td class="text-center"><?= round(floatval($r['fRecibido']), 3) ?></td>
                            <td class="text-center"><?= round(floatval($r['disponible']), 3) ?></td>
                            <td style="width:100px;"><input type="text" class="form-control form-control-sm text-end py-0 <?= $fondo ?>" name="txtEnviar<?= $r['nIdArticulo'] ?>" id="txtEnviar<?= $r['nIdArticulo'] ?>" <?= $tipoAccion == 'e' ? '' : 'readonly' ?> <?= $tipoAccion == 'e' && !$bDeshabilitado ? '' : 'disabled' ?> value="<?= $r['capturada'] ?>"></td>
                            <td class="text-center" id="txtPesoProducto<?= $r['nIdArticulo'] ?>"><?= round($nPesoProd / 1000, 3) > 0.49 ? round($nPesoProd / 1000, 3) . ' Ton' : round($nPesoProd, 3) . ' Kg' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-3 rounded bg-light p-3">
            <?php if ($regEnv['cOrigen'] == 'traspaso') : ?>
                <div class="mb-1 text-center">
                    <label class="d-block fw-bold fs-4  border border-2 rounded-3 border-secondary">Traspaso: <?= $regEnv['nIdOrigen'] ?></label>
                    <label class="d-block fw-bold">Para: <?= $sucursalDestino ?></label>
                </div>
            <?php else : ?>
                <div class="mb-1 text-center">
                    <label class="d-block fw-bold fs-4 border border-2 rounded-3 border-secondary">Remision: <?= $regEnv['nFolioRemision'] ?></label>
                </div>
            <?php endif; ?>
            <div class="mb-1">
                <label for="txtEnviarA" class="form-sm-label">Enviar a:</label>
                <label class="col-form-label text-center bg-secondary rounded-2 bg-opacity-25 d-block">
                    <?= $regEnv['sEnvEntrega'] . ' ' . $regEnv['sEnvDireccion'] . ' ' . $regEnv['sEnvColonia'] . ' ' . $regEnv['sEnvReferencia'] ?>
                </label>
            </div>
            <?php if ($regEnv['cOrigen'] == 'traspaso') : ?>
                <div class="mb-1">
                    <label class="form-sm-label">Fecha Solicitada Para Traspaso</label>
                    <label class="col-form-label text-center bg-secondary rounded-2 bg-opacity-25 d-block"><?= $fechaSol ?></label>
                </div>
            <?php else : ?>
                <div class="mb-1">
                    <label class="form-sm-label">Fecha Venta</label>
                    <label class="col-form-label text-center bg-secondary rounded-2 bg-opacity-25 d-block "><?= $fechaAlta ?></label>
                </div>
            <?php endif; ?>
            <div class="mb-1">
                <label for="sObservacionEnvio" class="form-label">Observaciones</label>
                <textarea name="sObservacionEnvio" id="sObservacionEnvio" cols="15" rows="2" class="form-control" <?= $tipoAccion == 'e' ? '' : 'readonly disabled' ?>><?= $sObservacionEnvio ?></textarea>
            </div>
            <div class="pb-1 text-center border-bottom border-dark  mt-2">
                <label class="form-label text-start pe-2">Peso</label>
                <label class="col-form-label py-1 px-2 text-center bg-secondary rounded-2 bg-opacity-25" style="width:100px;" id="nPesoViaje">0 Kg</label>
            </div>
            <?php if ($tipoAccion == 'e') : ?>
                <div class="d-flex justify-content-around align-items-center pt-3">
                    <button type="button" class="btn btn-outline-secondary w-50 mx-2" data-bs-dismiss="modal" id="btnSalirDeEnvio">Salir</button>
                    <button type="button" class="btn btn-outline-primary w-50 mx-2" id="btnGuardarEnvio">Aceptar</button>
                </div>
            <?php else : ?>
                <div class="d-flex justify-content-around align-items-center pt-3">
                    <button type="button" class="btn btn-outline-primary w-50 mx-2" data-bs-dismiss="modal">Aceptar</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function() {

        let appViajeEnvio = {
            cOrigen: '<?= $regEnv['cOrigen'] ?>',
            nIdOrigen: '<?= $regEnv['nIdOrigen'] ?>',
            idEnvio: '<?= $idEnvio ?>',
            arrEnvio: <?= json_encode($regsDet) ?>,
            arrJqTxts: null, // arreglo jquery que representa todos los inputs por enviar de la tabla.
            init: function() {
                $('#cntTablaEnvio')
                    .on('keydown', 'input[type="text"]', appViajeEnvio.onKeyDown)
                    .on('focusout', 'input[type="text"]', appViajeEnvio.onFocusOut)
                    .on('input', 'input[type="text"]', appViajeEnvio.onInput);
                $('#cntTablaEnvio').on('input', 'input[type="checkbox"]', appViajeEnvio.onInputChkBox);
                $('#mainCnt > div.row').addClass('h-100');

                $('#btnGuardarEnvio').on('click', appViajeEnvio.actualizaEnvio);
                let ele = $('#cntTablaEnvio')[0];
                ele.style.height = ($('#mainCnt').height() - $('#cntTitulo2').height()).toString() + 'px';
                ele.style.overflow = "auto";
                appViajeEnvio.calculaPesoTotal();
                appViajeEnvio.arrJqTxts = $('#cntTablaEnvio').find('input[type="text"]');
                $('#cntTablaEnvio input[type="text"]').filter(':enabled').first().select();
            },
            onInput: function(e) {
                miGlobal.valNumero(e, appViajeEnvio, {
                    re: /^\d*(?:\.\d{0,2})?$/g
                });
            },
            onInputChkBox: function(e) {
                let idArt = e.target.name.substring(5);
                let pos = appViajeEnvio.buscaIdArtArrEnvio(idArt);
                appViajeEnvio.arrEnvio[pos].cModoEnv = e.target.checked ? '1' : '0';

                appViajeEnvio.marcarNodisponible($('#txtEnviar' + idArt)[0], pos);
            },
            marcarNodisponible: function(ele, pos) {
                if (appViajeEnvio.arrEnvio[pos].cModoEnv == '0') {
                    let bValidaExis = appViajeEnvio.arrEnvio[pos].cSinExistencia == '0';
                    if (bValidaExis) {
                        let cond = appViajeEnvio.arrEnvio[pos].capturada > appViajeEnvio.arrEnvio[pos].disponible;
                        $(ele).toggleClass('bg-danger text-light', cond);
                    }
                } else {
                    $(ele).toggleClass('bg-danger text-light', false);
                }
            },
            onKeyDown: function(e) {
                if (e.which == 13) {
                    let pos = appViajeEnvio.arrJqTxts.index(e.target);
                    let posSig = (pos < (appViajeEnvio.arrJqTxts.length - 1) ? pos + 1 : 0);
                    appViajeEnvio.arrJqTxts.get(posSig).select();
                }
            },
            onFocusOut: function(e) {
                let valor = (e.target.value ?? '').trim();

                let idArt = e.target.name.substring(9);
                let pos = appViajeEnvio.buscaIdArtArrEnvio(idArt);
                let nValor = 0;
                if (valor != '') nValor = appViajeEnvio.redondea(valor, 3);

                let porSurtir = appViajeEnvio.redondea(appViajeEnvio.arrEnvio[pos].porSurtir, 3);
                if (nValor > porSurtir) {
                    nValor = porSurtir;
                    e.target.value = nValor;
                }
                if (nValor < 0) {
                    nValor = 0;
                    e.target.value = nValor;
                }
                appViajeEnvio.arrEnvio[pos].capturada = nValor;
                appViajeEnvio.marcarNodisponible(e.target, pos);
                appViajeEnvio.calculaPesoTotal(pos);
            },
            procesando: false,
            actualizaEnvio: function() {
                // se envian los datos
                //   [idArt, cantAsurtir, cantCapturada, pesoProducto, modoenv, comprometido]
                // se forma el arreglo de datos
                // si las cantidades son cero se descarta del viaje.
                let nCantidadTotalArt = 0;
                let arrDet = [];
                let tope = appViajeEnvio.arrEnvio.length;
                if (appViajeEnvio.procesando) return;
                appViajeEnvio.procesando = true;
                $('#btnGuardarEnvio')[0].disabled = true;

                for (i = 0; i < tope; i++) {
                    arrDet.push({
                        'idArt': appViajeEnvio.arrEnvio[i].nIdArticulo,
                        'capturada': appViajeEnvio.arrEnvio[i].capturada,
                        'modenv': appViajeEnvio.arrEnvio[i].cModoEnv,
                        'peso': appViajeEnvio.arrEnvio[i].fPeso,
                        'cSinExistencia': appViajeEnvio.arrEnvio[i].cSinExistencia
                    });
                }
                $.post('<?= $baseURL ?>', {
                        'cOrigen': appViajeEnvio.cOrigen,
                        'nIdOrigen': appViajeEnvio.nIdOrigen,
                        'idEnvio': appViajeEnvio.idEnvio,
                        'observacion': $('#sObservacionEnvio').val(),
                        'det': arrDet
                    }, null, 'html')
                    .done(function(data, textStatus, jqxhr) {
                        $('#cntTablaEnviosBody').html(data);
                        $('#btnSalirDeEnvio').click();
                    }).fail(function(jqxhr, textStatus, err) {
                        console.log('fail', jqxhr, textStatus, err);
                        appViajeEnvio.procesando = true;
                        $('#btnGuardarEnvio')[0].disabled = true;
                    });
            },

            buscaIdArtArrEnvio: function(id) {
                let tope = appViajeEnvio.arrEnvio.length;
                let i = 0;
                let pos = -1;
                for (i = 0; i < tope; i++) {
                    if (appViajeEnvio.arrEnvio[i].nIdArticulo == id) {
                        pos = i;
                        break;
                    }
                }
                return pos;
            },

            calculaPesoTotal: function(pos) {
                let tope = appViajeEnvio.arrEnvio.length;
                let i = 0;
                let suma = 0;
                if (pos !== undefined) {
                    $('#txtPesoProducto' + appViajeEnvio.arrEnvio[pos].nIdArticulo).text(
                        appViajeEnvio.formateaPeso(
                            parseFloat(appViajeEnvio.arrEnvio[pos].capturada) * parseFloat(appViajeEnvio.arrEnvio[pos].fPeso)
                        )
                    );
                }
                for (i = 0; i < tope; i++) {
                    suma = suma + (parseFloat(appViajeEnvio.arrEnvio[i].capturada) * parseFloat(appViajeEnvio.arrEnvio[i].fPeso));
                }
                $('#nPesoViaje').text(appViajeEnvio.formateaPeso(suma));
            },

            formateaPeso: function(peso) {
                n = appViajeEnvio.redondea((peso / 1000), 3); // redondeoa tres decimales

                if (n > 0.49)
                    s = appViajeEnvio.redondea(n, 2).toString() + ' Ton';
                else
                    s = appViajeEnvio.redondea(peso, 2).toString() + ' Kg';
                return s
            },

            redondea: function(num, decimales) {
                decimales = Math.pow(10, decimales);
                return Math.round(parseFloat(num) * decimales) / decimales;
            }
        };
        appViajeEnvio.init();
    });
</script>