<?php
$nCont = 0;
$nRecibidos = 0;
$porpagar = 0;

$dlProveedor =  $proveedor['sNombre'] ?? '';

$aKeys = [
    'compra'  => ['nIdCompra', 'dcompra', 'del proveedor'],
];
$masterkey = $aKeys[$operacion][0];
$datefld = $aKeys[$operacion][1];

?>

<style>
    #tbl tbody tr:hover {
        background-color: rgba(161, 191, 226, 0.501) !important;
    }
</style>

<div class="container-fluid h-100">
    <div class="row">
        <div class="col">
            <h5><?= $titulo ?></h5>
            <hr>
        </div>
    </div>
    <div class="row h-75">
        <div class="col bg-light px-4 pt-3 border rounded">
            <div class="position-relative">
                <div id="recepcionwAlert">
                    <div class="alert alert-danger alert-dismissible" style="display:none;" role="alert">
                    </div>
                </div>
            </div>

            <div class="mt-3 " style="z-index:1;">
                <table class="table table-striped table-hover table-sm" id="tbl">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descripción</th>
                            <th class="text-center" scope="col">Envio</th>
                            <?php if ($modo !== 'N') : ?>
                                <th class="text-end" scope="col">Solicitado</th>
                            <?php endif; ?>
                            <th class="text-end" scope="col">Precio</th>
                            <th class="text-end" scope="col">Recibido </th>
                            <th class="text-center">Por recibir</th>
                            <?php if ($operacion === 'compra' && $modo === 'N') : ?>
                                <th class="text-center" scope="col">Precio</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)) : ?>
                            <tr>
                                <td colspan="7" class="fs-5 text-center">No hay registros</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($registros as $r) : ?>
                                <?php
                                $nImp = 0;
                                $nRecibidos += $r['fPorRecibir'];
                                ?>
                                <tr>
                                    <td scope="row"> <?= ++$nCont ?> </td>
                                    <td><?= $r['sDescripcion'] ?></td>
                                    <td style="width:90px;" class="text-center"><?= $r['nIdEnvio'] ?></td>
                                    <?php if ($modo !== 'N') : ?>
                                        <td style="width:100px;" class="text-end"> <?= $r['fCantidad'] ?> </td>
                                    <?php endif; ?>
                                    <td style="width:100px;" class="text-end px-1"><?= $r['fImporte'] ?></td>
                                    <td style="width:90px;" class="text-end px-1"><?= $r['fRecibido'] ?></td>
                                    <td style="width:100px;">
                                        <input type="text" class="form-control input-sm py-0 px-1 text-end" id="art-<?= $nCont ?>" data-cantidad="<?= $r['fCantidad'] - $r['fRecibido'] ?>" data-idx=<?= $nCont ?> data-fuente="cantidad" name="<?= $operacion ?>[<?= $r['nIdArticulo'] ?>]" value="<?= $r['nIdEnvio'] > 0 ? 0 : $r['fPorRecibir'] ?>" <?= $r['fPorRecibir'] > 0 ? ($r['nIdEnvio'] > 0 ? 'disabled' : '') : 'disabled' ?>>
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
                <form action='<?= base_url($frmURL) ?>' method="post" id="frmRecepcion" autocomplete="off">
                    <?php generaCampoTexto($masterkey, $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                    <?php generaCampoTexto('nIdSucursal', $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                    <?php generaCampoTexto('nIdProveedor', $error ?? false, 'hidden', $proveedor ?? null, $modo); ?>
                    <?php if ($operacion !== 'entrada') : ?>
                        <?php generaCampoTexto("nIdOrigen", $error ?? false, 'hidden', $registro ?? null, $modo); ?>
                        <div class="col">
                            <label for="dlProveedores0" class="form-label"><?= $operacion === 'traspaso' ? 'Solicitar a' : 'Proveedor' ?></label>
                            <div class="input-group ">
                                <input class="form-control w-75" list="dlProveedores" placeholder="Escriba nombre del proveedor" aria-label="codigo/nombre del proveedor" id="dlProveedores0" name="dlProveedores0" value="<?= $dlProveedor ?>" readonly />
                                <datalist id="dlProveedores" value="<?= $dlProveedor ?>"></datalist>
                            </div>
                        </div>
                    <?php endif ?>
                    <div class="col">
                        <label for="<?= $datefld ?>" class="form-label">Fecha de <?= $operacion ?></label>
                        <div class="input-group ">
                            <?php generaCampoTexto("$datefld", $error ?? false, 'date', $registro ?? null, $modo); ?>
                        </div>
                    </div>
                    <div class="col">
                        <label for="sObservacion" class="form-label">Observaciones</label>
                        <?php generaCampoTexto('sObservacion', $error ?? false, 'textarea', $registro ?? null, $modo); ?>
                    </div>
                </form>

                <hr style="height:1px; background-color:gray; width:100%; margin: 3px 0;">
                <div class="row mb-1">
                    <div class="col-7">Artículos solicitados</div><span class="col-5 text-end fw-bold"><?= $nCont ?></span>
                </div>
                <hr style="height:1px; background-color:black; width:100%; margin: 2px 0 0 0;">
                <div class="text-center my-1">
                    <button class="btn btn-sm btn-outline-secondary me-4" id="btnBack00">
                        Regresar
                    </button>
                    <?php if ($registro['cEdoEntrega'] != '3' && $tipoaccion == 'r') : ?>
                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mdConfirma" data-llamar="<?= $frmURL ?>" data-mod-msj="Guardar la información!!">
                            Aceptar
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



<form action="" method="post" id="frmEnvio" style="display:none">
    <input type="hidden" name="idDummyEnvio" value="" />
</form>

<?= generaModalGeneral('frmModal', 'modal-xl modal-dialog-scrollable') ?>
<?= generaModalGeneral('frmModalDis', 'modal-md') ?>

<script type="text/javascript">
    $(document).ready(function() {
        const movTabla = {
            init: function() {
                $('#tbl').on('keydown', movTabla.tblKeyDown);
                $('#tbl input[id^="art-"]')
                    .on('focus', movTabla.tblCellOnFocus)
                    .on('input', movTabla.tblCellInput)
                    .on('focusout', movTabla.tblCellOutFocus);
                $("#art-1").select();
            },
            modo: "R",
            tblKeyDown: function(e) {
                function siguiente(e) {
                    let t = $(e.target);
                    let nextID = Number(t.data("idx")) + 1;
                    if ($("#art-" + nextID).length == 0) nextID = 1;
                    t.blur();
                    $("#art-" + nextID).select();
                };

                let t = e.target;
                if ((e.which == 13 || e.which == 9) && (t.value != movTabla.valAnt)) {
                    e.preventDefault();
                    // valida cantidad
                    if (parseFloat(t.value) > parseFloat($(t).data('cantidad'))) {
                        miGlobal.muestraAlerta('La cantidad a recibir no debe ser mayor a la solicitada', 'recepcion', 4000);
                        t.value = '0.00';
                    } else {
                        siguiente(e);
                    }
                } else {
                    if (e.which == 13) {
                        e.preventDefault();
                        siguiente(e);
                    }
                }
            },
            valant: '',
            tblCellOnFocus: function(e) {
                let t = e.target;
                movTabla.valant = t.value;
            },
            tblCellOutFocus: function(e) {
                let t = e.target;
                if (parseFloat(t.value) > parseFloat($(t).data('cantidad'))) {
                    miGlobal.muestraAlerta('La cantidad a recibir no debe ser mayor a la solicitada', 'recepcion', 4000);
                    t.value = '0.00';
                }
            },
            tblCellInput: function(e) {
                miGlobal.valNumero(e, movTabla, {
                    re: /^\d*(?:\.\d{0,3})?$/g
                });
            },
        };

        const appRecepcion = {
            cmd: '',
            frmDestino: '',
            init: function() {
                $('#mdConfirma').on('show.bs.modal', appRecepcion.confirmar);
                $('#btnGuardar00').on('click', appRecepcion.enviar);
                $('#btnBack00').on('click', function(e) {
                    history.back();
                });
            },
            confirmar: function(e) {
                //$(e).prop('disabled',true);
                //window.alert('shequehse');
                let a = $(e.relatedTarget);
                appRecepcion.cmd = a.data('llamar');
                appRecepcion.frmDestino = a.data('frmDestino');

                if (appRecepcion.frmDestino == null)
                    appRecepcion.frmDestino = '#frmEnvio';
                $('#mdConfirma div.modal-body > p').html(a.data(
                    'mod-msj'));
            },
            bndGuardar: false,
            conRecepcion: '<?= $nRecibidos > 0 ? '1' : '0' ?>',
            enviar: function(e) {
                if (appRecepcion.bndGuardar) return;
                $('#btnGuardar00')[0].disabled = true;
                appRecepcion.bndGuardar = true;
                let f = $(appRecepcion.frmDestino)[0];

                if (!appRecepcion.validaCaptura()) {
                    appRecepcion.bndGuardar = false;
                    $('#btnGuardar00')[0].disabled = false;
                    return;
                }

                miGlobal.agregaCamposHidden('#tbl [name]', appRecepcion.frmDestino);
                miGlobal.agregaCamposHidden('#frmRecepcion [name]', appRecepcion.frmDestino);

                // se realiza un submit
                f.action = baseURL + '/' + appRecepcion.cmd;
                $.ajax({
                    url: f.action, // '< ?= $frmURL ?>',
                    method: 'POST',
                    data: $(appRecepcion.frmDestino).serialize(),
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    history.back();
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                    appRecepcion.bndGuardar = false;
                });
                //f.submit();
                // 
            },
            validaCaptura: function() {
                let oRet = true;
                let nCant = 0;
                $('#tbl input[name^="compra["]').each((i, e) => {
                    let v = $(e).val();
                    if ((v === '') || (v === undefined) || (v === '.')) {
                        oRet = false;
                        $('#btnCancelar00').click();
                        miGlobal.muestraAlerta('Se debe de capturar la cantidad recibida.', 'recepcion', 4000);
                        return false;
                    }
                    nCant += parseFloat(v);
                });
                if (appRecepcion.conRecepcion == '1' && nCant == 0) {
                    $('#btnCancelar00').click();
                    miGlobal.muestraAlerta('No se capturó ninguna cantidad recibida para guardar.', 'recepcion', 4000);
                    return false;
                }
                return oRet;
            }
        };

        appRecepcion.init();
        movTabla.init();
    });
</script>