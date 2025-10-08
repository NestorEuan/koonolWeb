<?php

$arbol = [];
foreach ($registros as $r) {
    if ($r['sDescripcion'] == '$divider$') continue;
    if ($r['sDescripcion'] == '$opcion$') {
        $texto = '$OPC$ : ' . $r['sAbrev'] . ' : ' . $r['sLink'];
    } else {
        $texto = $r['sDescripcion'];
    }
    $a = [
        'id' => $r['nIdMenu'],
        'parent' => ($r['nIdPadre'] == '0' ? '#' : $r['nIdPadre']),
        'text' => $texto 
    ];
    if($r['nIdPadre'] != '0') $a['state'] = [ 'selected' => ($r['conPermiso'] == '0' ? 0 : 1)];
    $arbol[] = $a;
}
?>
<h5 class="bg-ligth px-2 fw-bold"><?= $titulo ?></h5>
<hr>
<div class="row">
    <div class="col">
        <div class="mb-3">
            <label for="sPerfil" class="form-label">Nombre perfil</label>
            <?php generaCampoTexto('sPerfil', $error ?? false, null, $registro ?? null, $modo); ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div id="jstree_div"></div>
    </div>
</div>
<hr>
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
        let addPerfil = {
            init: function() {
                $('#btnGuardar').on('click', addPerfil.enviar);
                $('#jstree_div').jstree({
                    'plugins' : [ 'checkbox' ],
                    'core': {
                        'data': <?= json_encode($arbol)?>
                    },
                    'checkbox' : {
                        'three_state' : true
                    }
                });
            },
            enviar: function(e) {
                let a1 = $('#jstree_div').jstree().get_selected();
                let a2 = $('#jstree_div').jstree().get_undetermined();
                $.ajax({
                    url: '<?= $frmURL ?>',
                    method: 'POST',
                    data: { 'sPerfil' : $('#sPerfil').val(), 'permisos' : a1.concat(a2) },
                    dataType: 'html'
                }).done(function(data, textStatus, jqxhr) {
                    if (data.substr(0, 2) === 'oK') {
                        location.reload();
                    } else {
                        $('#frmModal .modal-body').html(data);
                    }
                }).fail(function(jqxhr, textStatus, err) {
                    console.log('fail', jqxhr, textStatus, err);
                });
            },
        };
        addPerfil.init();
    });
</script>