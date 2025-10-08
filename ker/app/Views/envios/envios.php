<?php
$bPrint = isset($printerMode) ? 'true' : 'false';
$aKeys = [
    'envio' => ['nIdEnvio', 'dSolicitud',],
];
$sKey = $aKeys[$operacion][0];
$sMov = $aKeys[$operacion][1];
$containerClass = "container bg-light mt-4";
$tblClass = "table table-striped table-hover table-sm";
$borderClass = "col bg-light px-4 pt-3 border rounded";
if (isset($printerMode)) {
    $containerClass = "overflow-auto container-fluid imprimir";
    $tblClass = "table table-sm";
    $borderClass = "col px-4 pt-3";
}
generaCssImpresion();
// $cEdo = '0';
if (isset($aWhere)) {
    $cEdo = $aWhere['Edo'];
    $dIni = $aWhere['dIni'] ?? '';
    $dFin = $aWhere['dFin'] ?? '';
}

?>

<div class="<?= $containerClass ?>">
    <h5><?= $titulo ?></h5>
    <?php if (!isset($printerMode)) : ?>
        <form class="row border rounded mb-3 py-1" action="<?= base_url('envio/') ?>" method="get">
            <div class="col-12 mb-1 col-md-4 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text">Estado</span>
                    <select class="form-select text-center" name="cEstado" id="cEstado">
                        <option value="0" <?= $cEdo == '0' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="1" <?= $cEdo == '1' ? 'selected' : '' ?>>Surtidos</option>
                        <option value="2" <?= $cEdo == '2' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </div>
            </div>
            <div class="col-12 mb-1 col-md-4 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text w-25">Del</span>
                    <input type="date" name="dIni" id="dIni" class="form-control text-center" value="<?= $dIni ?>">
                </div>
            </div>
            <div class="col-12 mb-1 col-md-4 col-lg-2">
                <div class="input-group">
                    <span class="input-group-text w-25">Al</span>
                    <input type="date" name="dFin" id="dFin" class="form-control text-center" value="<?= $dFin ?>">
                </div>
            </div>
            <div class="col-12 mt-2 text-sm-end d-flex flex-column flex-md-row col-lg mt-lg-0">
                <button type="submit" class="btn btn-secondary bg-gradient mb-1 me-md-auto">Filtrar</button>
                <div class="col-2 d-flex align-items-center justify-content-end">
                    <button type="button" class="btn btn-primary bg-gradient mb-1">Exportar</button>
                </div>
                <div class="col-2 d-flex align-items-center justify-content-end">
                    <a class="btn bi-printer-fill btn-primary" href="<?= base_url() ?>/envio/p" role="button"></a>
                </div>
                <!-- div class="col-6 d-flex align-items-center justify-content-end" -->

                <!-- /!-->

            </div>
        </form>
    <?php endif; ?>
    <?php if (!isset($printerMode)) : ?>
        <nav>
            <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-envio-tab" data-bs-toggle="tab" data-bs-target="#nav-envio" type="button" role="tab" aria-controls="nav-envio" aria-selected="true">Ventas</button>
                <button class="nav-link" id="nav-cliente-tab" data-bs-toggle="tab" data-bs-target="#nav-cliente" type="button" role="tab" aria-controls="nav-cliente" aria-selected="true">Clientes</button>
                <button class="nav-link" id="nav-prod-tab" data-bs-toggle="tab" data-bs-target="#nav-prod" type="button" role="tab" aria-controls="nav-prod" aria-selected="false">Productos</button>
            </div>
        </nav>
    <?php endif; ?>

    <?php if (!isset($printerMode)) : ?>
        <div class="tab-content" id="nav-tabContent">
        <?php endif; ?>

        <?php if (!isset($printerMode)) : ?>
            <div class="tab-pane fade" id="nav-cliente" role="tabpanel" aria-labelledby="nav-cliente-tab">
            <?php endif; ?>

            <table class="<?= $tblClass ?>">
                <thead>
                    <tr>
                        <th class="text-center">Envio/Origen/Folio<br>F. Solicitud</th>
                        <th>Telefono</th>
                        <th>Recibe (cliente)</th>
                        <th>Dirección</th>
                        <th>Referencias</th>
                        <th>Enviado</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan='6' class='fs-5 text-center'>No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <?php
                            $nKey = $r[$sKey];
                            $dMov = $r[$sMov];
                            $cOrigen = substr($r['cOrigen'], 0, 3);
                            $nFolio = $cOrigen == 'ven' ? $r['nFolioRemision'] : $r['nFolioTraspaso'];
                            $dFecDocu = $cOrigen == 'ven' ? $r['dAltaVenta'] : $r['dAltaTraspaso'];
                            $dFecDocu = (new DateTime($dFecDocu))->format('d-m-Y');
                            ?>
                            <tr>
                                <td class="text-center"> <?= $nKey . '/' . $cOrigen . '/' . $nFolio . '<br>' . $dFecDocu ?> </td>
                                <td> <?= $r['sEnvTelefono'] ?> </td>
                                <td> <?= /*sprintf( "%d %s (%s)", $r['nIdCliente'], $r['sEnvEntrega'], $r['sNombre'])*/
                                        "{$r['sEnvEntrega']} <BR> ({$r['sNombre']})" ?></td>
                                <td> <?= $r['sEnvDireccion'] . "<BR>" . $r['sEnvColonia'] ?></td>
                                <td> <?= $r['sEnvReferencia'] ?></td>
                                <td> <?= $r['cEstatus'] ?? 'Pendiente' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!isset($printerMode)) : ?>
            </div>
        <?php endif; ?>

        <?php if (isset($printerMode)) : ?>
            <p class="page-break"></p>
        <?php endif; ?>

        <?php if (!isset($printerMode)) : ?>
            <div class="tab-pane fade show" id="nav-prod" role="tabpanel" aria-labelledby="nav-prod-tab">
            <?php endif; ?>

            <table class="<?= $tblClass ?>">
                <thead>
                    <th>#</th>
                    <th>Artículo</th>
                    <th>Por enviar</th>
                </thead>
                <tbody id="tblArticulos">
                    <?php if (empty($articulos)) : ?>
                        <tr>
                            <td colspan='5' class='fs-5 text-center'>No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($articulos as $arti) : ?>
                            <?php if($arti['fPorRecibir'] == '0') continue; ?>
                            <tr>
                                <td>
                                    <?= $arti['nIdArticulo'] ?>
                                </td>
                                <td>
                                    <?= $arti['sArticulo'] ?>
                                </td>
                                <td>
                                    <?= $arti['fPorRecibir']  ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif ?>
                </tbody>
            </table>

            <?php if (!isset($printerMode)) : ?>
            </div>
        <?php endif; ?>

        <?php if (isset($printerMode)) : ?>
            <p class="page-break"></p>
        <?php endif; ?>

        <?php if (!isset($printerMode)) : ?>
            <div class="tab-pane fade show active" id="nav-envio" role="tabpanel" aria-labelledby="nav-envio-tab">
            <?php endif; ?>

            <table class="<?= $tblClass ?>">
                <thead>
                    <tr>
                        <th class="text-center">Envio/Origen/Folio<br>F. Solicitud</th>
                        <th>Telefono</th>
                        <th>Enviar a</th>
                        <th>Dirección</th>
                        <th>Referencias</th>
                        <th>Enviado</th>
                        <?php if (!isset($printerMode)) : ?>
                            <th>Acción</th>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php
                    if (empty($detallenvio)) {
                        echo "<tr>";
                        echo "    <td colspan='5' class='fs-5 text-center'>No hay registros</td>";
                        echo "</tr>";
                    } else {
                        $nEnv = 0;
                        foreach ($detallenvio as $r) {
                            $nKey = $r[$sKey];
                            $dMov = $r[$sMov];
                            if ($nEnv <> $nKey) {

                                if ($nEnv <> 0) {
                                    echo "  </body>";
                                    echo "</table>";
                                    echo "</td></tr>";
                                }
                                $cOrigen = substr($r['cOrigen'], 0, 3);
                                $nFolio = $cOrigen == 'ven' ? $r['nFolioRemision'] : $r['nFolioTraspaso'];
                                $dFecDocu = $cOrigen == 'ven' ? $r['dAltaVenta'] : $r['dAltaTraspaso'];
                                $dFecDocu = (new DateTime($dFecDocu))->format('d-m-Y');
                                echo "<tr>";
                                echo '    <td class="text-center"> ' . $nKey . '/' . $cOrigen . '/' . $nFolio . '<br>' . $dFecDocu . " </td>";
                                echo "    <td> {$r['sEnvTelefono']} </td>";
                                echo "    <td> {$r['nIdCliente']} {$r['sEnvEntrega']} <BR>({$r['sNombre']})</td>";
                                echo "    <td> {$r['sEnvDireccion']}<BR>{$r['sEnvColonia']}</td>";
                                echo "    <td> " . $r['sEnvReferencia'] . "</td>";
                                echo "    <td> " . $r['cEstatus'] ?? 'Pendiente' . "</td>";
                                if (!isset($printerMode)) {
                                    if ($r['cOrigen'] == 'traspaso') {
                                        echo "<td><i class=\"bi bi-trash text-secondary\" style=\"font-size: 1.2rem;\"></i></td>";
                                    } else {
                                        echo "    <td> " .
                                            "<a href=\"\" style=\"text-decoration: none;\" " .
                                            " data-bs-toggle=\"modal\" data-bs-target=\"#mdEnvioBorraConfirma\" " .
                                            " data-llamar=\"" . base_url() . "/envio/b/" . $nKey . "\" data-mod-msj=\"Eliminar envio?\" " .
                                            " > " .
                                            "<i class=\"bi bi-trash\" style=\"cursor:pointer; font-size: 1.2rem;\"></i>" .
                                            "</a>" .
                                            "</td>";
                                    }

                                    echo "    <td> " .
                                        "<button class='btn  bi-caret-down text-danger btn-link me-3 ' data-bs-toggle='collapse' " .
                                        "data-bs-target='#tbldet$nKey' style='cursor:pointer;' ></button> " .
                                        "</td>";
                                }
                                echo "</tr>";

                                if (true) {
                                    echo "<tr><td></td><td colspan='4'>";
                                    echo "<table class=' " . (!isset($printerMode) ? ' collapse ' : '') . $tblClass .  "' id='tbldet$nKey'>";
                                    echo "  <thead>";
                                    echo "    <tr>";
                                    echo "        <th>#</th>";
                                    echo "        <th>Artículo</th>";
                                    echo "        <th>Entregar/Enviar</th>";
                                    echo "        <th>Entregado/Enviado</th>";
                                    echo "        <th>Por Entregar/<br>Por Enviar</th>";
                                    echo "        <th>ENV</th>";
                                    echo "    </tr>";
                                    echo "  </thead>";
                                    echo "  <tbody id='detailTabla'>";
                                }

                                $nEnv = $nKey;
                            }
                            echo "<tr>";
                            echo "    <td>" . $r['nIdArticulo'] . "</td>";
                            echo "    <td>" . $r['sArticulo'] . "</td>";
                            echo "    <td>" . $r['fCantidad'] . "</td>";
                            echo "    <td>" . $r['fRecibido'] . "</td>";
                            echo "    <td>" . $r['fPorRecibir'] . "</td>";
                            echo "    <td>" . ($r['cModoEnv'] == '1' ? '**' : '') . "</td>";
                            echo "</tr>";
                        }
                        echo "  </body>";
                        echo "</table>";
                        echo "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <?php if (!isset($printerMode)) : ?>
            </div>
        <?php endif; ?>
        </div>
</div>
</div>

<div class="modal fade" id="mdEnvioBorraConfirma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3>Confirmar acción</h3>
                <hr>
                <p>Confirma borrar el registro?</p>
                <hr>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnEnvioMdlCancelar">No</button>
                    <button type="button" class="btn btn-primary" id="btnEnvioBorrar">Si</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let imprime = <?= $bPrint  ?>;
        if (imprime == true) {
            $('#mainCnt').addClass('container-fluid').removeClass('container')[0].style.height = 'auto';
            $('#mainCnt > div').removeClass('row');
            $('footer').remove()
            window.print();
            history.back();
        }
        const appEnvios = {
            cmd: '',
            frmViajeDestino: '',
            init: function() {
                $("#dIni").on('change', appEnvios.valFin);
                $("#dFin").on('change', appEnvios.valIni);
                $("#mdEnvioBorraConfirma").on("show.bs.modal", appEnvios.confirmar);
                $("#btnEnvioBorrar").on("click", appEnvios.enviar);
                // $('#frmFiltro').on('submit', appMovimientos.filtrar);
            },
            valFin: function(e) {
                $("#dFin").attr('min', e.target.value);
                $("#dFin").attr('value', e.target.value);
                //window.alert(e.target.value);
            },
            valIni: function(e) {
                $("#dIni").attr('max', e.target.value);
                if ($("#dIni").value == null || $("#dIni").value == '')
                    $("#dIni").attr('value', e.target.value);
            },
            filtrar: function(e) {
                e.preventDefault();
                miGlobal.toggleBlockPantalla('Consultando información...');
                this.submit();
            },
            confirmar: function(e) {
                let a = $(e.relatedTarget);
                appEnvios.cmd = a.data('llamar');
            },
            btnGuardar: false,
            enviar: function(e) {
                if (appEnvios.btnGuardar) return;
                appEnvios.btnGuardar = true;
                miGlobal.toggleBlockPantalla('Espere un momento...');
                $.ajax({
                    url: appEnvios.cmd,
                    method: 'POST',
                    //data: $('#addProveedorfrm').serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        //$('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            }
        };

        appEnvios.init();
    });
</script>