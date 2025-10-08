</div>
        </main>
    </div>

    <!-- FOOTER -->
    <footer class="app-footer">
        <div class="footer-copyright">
            &copy; <?= date('Y') ?> - Todos los derechos reservados
        </div>
        
        <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['logobottom'] ?>" alt="Logo" class="footer-logo">
    </footer>

</div>

<!-- Loading Overlay (Opción A - DESACTIVADA) -->
<!-- Para activar: cambiar display:none por display:flex en main.css -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <div class="loading-text" id="loadingText">Cargando...</div>
    </div>
</div>

<!-- Progress Bar (Opción B - DESACTIVADA) -->
<!-- Para activar: cambiar display:none por display:block en main.css -->
<div class="progress-loader" id="progressLoader">
    <div class="progress-bar-animated"></div>
</div>

<!-- Tooltip para menú colapsado -->
<div class="menu-tooltip" id="menuTooltip"></div>

<script type="text/javascript">
$(document).ready(function() {
    
    // =====================================================
    // INICIALIZACIÓN DEL SISTEMA
    // =====================================================
    
    AppSPA.init();
    
    // =====================================================
    // RELOJ DEL HEADER
    // =====================================================
    
    function updateClock() {
        const date = new Date();
        
        function addZero(x) {
            return x < 10 ? '0' + x : x;
        }
        
        function twelveHour(x) {
            if (x > 12) return x - 12;
            if (x === 0) return 12;
            return x;
        }
        
        const h = addZero(twelveHour(date.getHours()));
        const m = addZero(date.getMinutes());
        const s = addZero(date.getSeconds());
        
        $('#headerClock').text(h + ':' + m + ':' + s);
    }
    
    updateClock();
    setInterval(updateClock, 1000);
    
});

// =====================================================
// SINGLE PAGE APPLICATION - GESTOR DE CONTENIDO
// =====================================================

const AppSPA = {
    currentPage: null,
    
    init: function() {
        this.initSidebar();
        this.initUserDropdown();
        this.initMenuNavigation();
        this.loadDesktop();
    },
    
    // =====================================================
    // SIDEBAR - Toggle y Responsive
    // =====================================================
    
    initSidebar: function() {
        const $sidebar = $('#appSidebar');
        const $toggle = $('#sidebarToggle');
        const $overlay = $('#sidebarOverlay');
        
        // Toggle sidebar
        $toggle.on('click', function() {
            if (window.innerWidth <= 768) {
                // Mobile: overlay
                $sidebar.toggleClass('mobile-open');
                $overlay.toggleClass('show');
            } else {
                // Desktop: collapse
                $sidebar.toggleClass('collapsed');
            }
        });
        
        // Cerrar sidebar en mobile al hacer click en overlay
        $overlay.on('click', function() {
            $sidebar.removeClass('mobile-open');
            $overlay.removeClass('show');
        });
        
        // Tooltips para sidebar colapsado
        if (window.innerWidth > 768) {
            this.initTooltips();
        }
    },
    
    initTooltips: function() {
        const $tooltip = $('#menuTooltip');
        
        $('.menu-header').on('mouseenter', function(e) {
            if ($('#appSidebar').hasClass('collapsed')) {
                const text = $(this).find('.menu-text').text();
                $tooltip.text(text).addClass('show');
                
                const rect = this.getBoundingClientRect();
                $tooltip.css({
                    top: rect.top + (rect.height / 2) - ($tooltip.height() / 2),
                    left: rect.right + 10
                });
            }
        }).on('mouseleave', function() {
            $tooltip.removeClass('show');
        });
    },
    
    // =====================================================
    // USER DROPDOWN
    // =====================================================
    
    initUserDropdown: function() {
        const $userBtn = $('#headerUser');
        const $dropdown = $('#userDropdown');
        
        $userBtn.on('click', function(e) {
            e.stopPropagation();
            $dropdown.toggleClass('show');
        });
        
        $(document).on('click', function() {
            $dropdown.removeClass('show');
        });
        
        $dropdown.on('click', function(e) {
            e.stopPropagation();
        });
    },
    
    // =====================================================
    // NAVEGACIÓN DEL MENÚ
    // =====================================================
    
    initMenuNavigation: function() {
        // Accordion behavior
        $('.menu-header').on('click', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const $submenu = $this.next('.submenu');
            
            // Si tiene submenú, toggle accordion
            if ($submenu.length > 0) {
                $this.toggleClass('expanded');
                $submenu.toggleClass('show');
                
                // Cerrar otros submenús
                $('.menu-header').not($this).removeClass('expanded');
                $('.submenu').not($submenu).removeClass('show');
            } else {
                // Si no tiene submenú, cargar página
                const url = $this.data('url');
                if (url) {
                    AppSPA.loadPage(url, $this.find('.menu-text').text());
                }
            }
        });
        
        // Click en items del submenú
        $('.submenu-item').on('click', function(e) {
            e.preventDefault();
            
            const url = $(this).attr('href');
            const title = $(this).text().trim();
            
            // Marcar como activo
            $('.submenu-item').removeClass('active');
            $(this).addClass('active');
            
            // Cerrar sidebar en mobile
            if (window.innerWidth <= 768) {
                $('#appSidebar').removeClass('mobile-open');
                $('#sidebarOverlay').removeClass('show');
            }
            
            AppSPA.loadPage(url, title);
        });
    },
    
    // =====================================================
    // CARGAR PÁGINA VÍA AJAX
    // =====================================================
    
    loadPage: function(url, title) {
        if (this.currentPage === url) return;
        
        this.currentPage = url;
        const $content = $('#contentWrapper');
        
        // Mostrar loader
        SPALoader.showSkeleton(title);
        
        $.ajax({
            url: baseURL + '/' + url,
            method: 'GET',
            dataType: 'html'
        })
        .done(function(data) {
            // Ocultar loader
            SPALoader.hide();
            
            // Cargar contenido con animación
            $content.html(data).addClass('content-slide-enter');
            
            // Remover clase de animación después de completarse
            setTimeout(() => {
                $content.removeClass('content-slide-enter');
            }, 400);
            
            // Re-inicializar eventos del menú si es necesario
            miGlobal.dameMenu();
        })
        .fail(function(jqxhr, textStatus, err) {
            SPALoader.hide();
            $content.html(
                '<div class="alert alert-danger m-4">' +
                '<h4><i class="bi bi-exclamation-triangle me-2"></i>Error al cargar el contenido</h4>' +
                '<p>No se pudo cargar la página solicitada. Por favor, intenta nuevamente.</p>' +
                '<p class="mb-0"><small>Error: ' + textStatus + '</small></p>' +
                '</div>'
            );
            console.error('Error loading page:', jqxhr, textStatus, err);
        });
    },
    
    // =====================================================
    // CARGAR DESKTOP INICIAL
    // =====================================================
    
    loadDesktop: function() {
        const $content = $('#contentWrapper');
        
        // Cargar vista desktop inicial
        $.ajax({
            url: baseURL + '/desktop',
            method: 'GET',
            dataType: 'html'
        })
        .done(function(data) {
            $content.html(data).addClass('fade-in');
            setTimeout(() => {
                $content.removeClass('fade-in');
            }, 300);
        })
        .fail(function() {
            $content.html(
                '<div class="text-center p-5">' +
                '<i class="bi bi-house-door" style="font-size: 4rem; color: #ccc;"></i>' +
                '<h3 class="mt-3">Bienvenido al Sistema</h3>' +
                '<p class="text-muted">Selecciona una opción del menú para comenzar</p>' +
                '</div>'
            );
        });
    }
};

// =====================================================
// GESTOR DE LOADERS (3 OPCIONES)
// =====================================================

const SPALoader = {
    
    // Opción C: Skeleton Loading (ACTIVA por defecto)
    showSkeleton: function(message) {
        $('#skeletonLoader').addClass('active');
        $('#contentWrapper').css('opacity', '0.3');
    },
    
    // Opción A: Overlay con Spinner
    // Para usar: llamar SPALoader.showOverlay(message) en lugar de showSkeleton
    showOverlay: function(message) {
        $('#loadingText').text(message || 'Cargando...');
        $('#loadingOverlay').addClass('active');
    },
    
    // Opción B: Progress Bar
    // Para usar: llamar SPALoader.showProgress() en lugar de showSkeleton
    showProgress: function() {
        $('#progressLoader').addClass('active');
    },
    
    // Ocultar todos los loaders
    hide: function() {
        $('#skeletonLoader').removeClass('active');
        $('#contentWrapper').css('opacity', '1');
        $('#loadingOverlay').removeClass('active');
        $('#progressLoader').removeClass('active');
    }
};

// =====================================================
// COMPATIBILIDAD CON FUNCIÓN ANTERIOR
// =====================================================

miGlobal.toggleBlockPantalla = function(msj) {
    if (msj) {
        SPALoader.showSkeleton(msj);
    } else {
        SPALoader.hide();
    }
};

</script>

</body>
</html>