<?php
if (($bTipoConfirmar ?? '0') == '0') {
    $codeBtns = '<button type="button" class="btn btn-primary mt-3" data-bs-dismiss="modal">Cerrar</button>';
} else {
    $codeBtns = '<button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal" id="btnCancelar09">No</button>' .
        '<button type="button" class="btn btn-primary" id="btnGuardar09">Si</button>';
}
?>
<?= generaModalGeneral('frmModalMsjGral', 'modal-md modal-dialog-centered') ?>
<script>
    $(document).ready(function() {
        let app = {
            login: '<?= $login ?? '' ?>',
            nuevoCajero: '<?= $nuevoCorteCajero ?? '' ?>',
            urlDestino: '<?= $urlDestino ?? '0' ?>',
            modoConfirma: '<?= $bTipoConfirmar ?? '0' ?>',
            modalDisponibles: null,
            init: function() {
                app.modalDisponibles = bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('frmModalMsjGral')
                    );
                $('#frmModalMsjGral .modal-body').html(
                    '<div class="text-center">' +
                    '<h4><?= $titulo ?></h4>' +
                    '</div>' +
                    '<div class="border-bottom border-top text-center py-3">' +
                    '<?= $msjError ?>' +
                    '</div>' +
                    '<div class="text-center mt-4">' +
                    '<?= $codeBtns ?>' +
                    '</div>'
                );
                if (app.login == '1') {
                    $('#frmModalMsjGral button').on('click', () => {
                        location.assign('<?= base_url('/login') ?>');
                    });
                }
                if (app.nuevoCajero == '1') {
                    $('#frmModalMsjGral button').on('click', () => {
                        location.assign('<?= base_url('cortecaja') ?>');
                    });
                }
                if (app.urlDestino != '0') {
                    $('#frmModalMsjGral button').on('click', () => {
                        location.assign('<?= base_url($urlDestino ?? '') ?>');
                    });
                }
                if (app.modoConfirma == '1') {
                    $('#btnGuardar09').on('click', () => {
                        location.href = '<?= base_url($urlSI ?? '') ?>';
                    });
                    $('#btnCancelar09').on('click', () => {
                        location.href = '<?= base_url($urlNO ?? '') ?>';
                    });
                }
                app.modalDisponibles.show();
            }
        };

        app.init();
    });
</script>