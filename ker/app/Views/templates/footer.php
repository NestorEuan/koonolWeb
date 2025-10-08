    </div>
    </div>

    <footer class="d-print-none container-fluid py-3 px-5 text-light bg-light d-flex justify-content-between position-absolute start-0 end-0 bottom-0 border-top border-secondary" style="height:63px;">
        <div style="display: inline-block;">
            <!--
            <img src="< ?= base_url() ? >/assets/img/canaco.jpeg" class="" alt="..." height="30">
            <img src="< ?= base_url() ? >/assets/img/coparmex.jpeg" class=" ms-2" alt="..." height="30">
            <img src="< ?= base_url() ? >/assets/img/hechoenyuc.jpeg" class=" ms-2" alt="..." height="30">
            -->
        </div>
        <div class="d-none d-sm-inline-block fw-bold" id="nomSucursalFooter" style="color:rgb(232, 87, 55);"></div>
        <div style="display: inline-block;" class="digital-clock">00:00:00</div>
        <div style="display: inline-block;">
            <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['logobottom'] ?>" class="me-2 border rounded" alt="..." height="30">
            <span class="text-dark">&COPY;2023</span>
        </div>
    </footer>

    <script type="text/javascript">
        $(document).ready(function() {
            miGlobal.dameMenu();
            $('#nomSucursalFooter').html(miGlobal.nomSucursal);
            clockUpdate();
            setInterval(clockUpdate, 1000);
            //miGlobal.toggleBlockPantalla();
        });

        function clockUpdate() {
            var date = new Date();
            $('.digital-clock').css({
                'color': '#fff',
                'text-shadow': '0 0 6px #ff0'
            });

            function addZero(x) {
                if (x < 10) {
                    return x = '0' + x;
                } else {
                    return x;
                }
            }

            function twelveHour(x) {
                if (x > 12) {
                    return x = x - 12;
                } else if (x == 0) {
                    return x = 12;
                } else {
                    return x;
                }
            }

            var h = addZero(twelveHour(date.getHours()));
            var m = addZero(date.getMinutes());
            var s = addZero(date.getSeconds());

            $('.digital-clock').text(h + ':' + m + ':' + s)
        }
    </script>

    </body>

    </html>