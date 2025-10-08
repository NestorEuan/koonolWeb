<div class="container bg-light mt-4 border">
    <h4>Activar Permisos para Usuarios</h4>
    <div class="row border rounded mb-3 py-2">
        <div class="col-sm-7">
            <div class="row">
                <label for="slogin" class="col-3 col-md-2 col-form-label">Login</label>
                <div class="col-6 col-md-7"><input type="text" name="sLogin" class="form-control" id="sLogin"></div>
                <div class="col-3 col-md-3"><button class="btn btn-secondary bg-gradient">Filtrar</button></div>
            </div>
        </div>
        <div class="col-sm-5 mt-3 mt-sm-0 ">
            <button class="btn btn-primary bg-gradient me-3" data-bs-toggle="modal" data-bs-target="#frmModal" id="btnAgregar" data-llamar="usuario/a">Agregar</button>
            <button class="btn btn-primary bg-gradient">Exportar</button>
        </div>
    </div>
    <div class="row border rounded">

        <div class="table-responsive-lg">
            <table class="table table-striped" id="tbl">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Login</th>
                        <th>Nombre</th>
                        <?php if ($permisoDescuentos) : ?>
                            <th class="text-center">Permitir<br>descuentos</th>
                        <?php endif; ?>
                        <?php if ($permisoCambiarPrecio) : ?>
                            <th class="text-center">Permitir<br>Cambiar Precio</th>
                        <?php endif; ?>
                        <?php if ($permisoActivarPrecioSinComisionDescuento) : ?>
                            <th class="text-center">No Aplicar Comision/Descuento<br>en Cambiar Precio</th>
                        <?php endif; ?>
                        <?php if ($permisoVentaSinDisponibles) : ?>
                            <th class="text-center">Permitir<br>Venta Sin Disponibles</th>
                        <?php endif; ?>
                        <?php if ($permisoGuardarSinValidarDivFac) : ?>
                            <th class="text-center">Permitir en<br>Dividir facturas<br>guardar sin validar</th>
                        <?php endif; ?>
                        <?php if ($permisoCancelarRemision) : ?>
                            <th class="text-center">Permitir<br>Cancelar</th>
                        <?php endif; ?>
                        <?php if ($permisoMantenerPrecioCoti) : ?>
                            <th class="text-center">Mantener Precio<br>Cotizacion</th>
                        <?php endif; ?>
                        <?php if ($permisoCambiaFechaFactura) : ?>
                            <th class="text-center">Cambiar Fecha<br>Factura</th>
                        <?php endif; ?>
                        <?php if ($permisoPermiteCredito) : ?>
                            <th class="text-center">Autoriza<br>Credito</th>
                        <?php endif; ?>
                        <th>Sucursal</th>
                    </tr>
                </thead>
                <tbody id="bodyTabla">
                    <?php if (empty($registros)) : ?>
                        <tr>
                            <td colspan="6" class="fs-5 text-center">No hay registros</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($registros as $r) : ?>
                            <tr>
                                <td><?= $r['nIdUsuario'] ?></td>
                                <td><?= $r['sLogin'] ?></td>
                                <td><?= $r['sNombre'] ?></td>
                                <?php if ($permisoDescuentos) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activadescuento/' . $r['nIdUsuario']) ?>" <?= $r['bDaDescuento'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoCambiarPrecio) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activacambioprecio/' . $r['nIdUsuario']) ?>" <?= $r['bDaPrecio'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoActivarPrecioSinComisionDescuento) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activacambiopreciosincomisiondescuento/' . $r['nIdUsuario']) ?>" <?= $r['bPrecioSinRecal'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoVentaSinDisponibles) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activaventasindisponible/' . $r['nIdUsuario']) ?>" <?= $r['bDaVtaSinDisponibles'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoGuardarSinValidarDivFac) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activadivfacnovalidar/' . $r['nIdUsuario']) ?>" <?= $r['bDivFacNoValidar'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoCancelarRemision) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activacancelaremision/' . $r['nIdUsuario']) ?>" <?= $r['bCancelarRemision'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoMantenerPrecioCoti) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activamantenerpreciocoti/' . $r['nIdUsuario']) ?>" <?= $r['bMantenerPrecioCotiza'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoCambiaFechaFactura) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activacambiafecfac/' . $r['nIdUsuario']) ?>" <?= $r['bCambioFechaFactura'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <?php if ($permisoPermiteCredito) : ?>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" data-llamar="<?= base_url('usuario/activapermitecredito/' . $r['nIdUsuario']) ?>" <?= $r['bPermitirCredito'] ? 'checked' : '' ?>>
                                    </td>
                                <?php endif; ?>
                                <td><?= $r['nomSucursal'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= $pager->links() ?>
    </div>

</div>

<?= generaModalGeneral('frmModal', 'modal-dialog-scrollable') ?>

<script type="text/javascript">
    const appPermisoUsuario = {
        init: function() {
            //$('#tbl').on('keydown', appPermisoUsuario.tblKeyDown);
            $('#tbl input[type="checkbox"]').on('click', appPermisoUsuario.tblCellClick);
        },
        tblCellClick: function(e) {
            let t = e.target;
            $.post($(t).data('llamar') + '/' + t.checked);
        }

    };
    $(document).ready(function() {
        appPermisoUsuario.init();
    });
</script>