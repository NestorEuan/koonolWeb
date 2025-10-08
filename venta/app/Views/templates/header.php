<!DOCTYPE html>
<html lang="es" style="height:100%;">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $aInfoSis['nomempresa'] ?></title>
    
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="<?= base_url() ?>/assets/img/<?= $aInfoSis['icono'] ?>" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/js/themes/default/style.min.css">
    
    <!-- CSS Principal del Sistema -->
    <link rel="stylesheet" href="<?= base_url();?>/assets/css/main.css">

    <!-- jQuery y Bootstrap JS -->
    <script src="<?= base_url() ?>/assets/js/jquery.js"></script>
    <script src="<?= base_url() ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>/assets/js/jstree.js"></script>

    <script>
        const baseURL = '<?= base_url() ?>';
        let miGlobal = {
            nomSucursal: '<?= $sucursal ?? '' ?>',
            entregaCampo: null,
            
            dameMenu: function(e) {
                $("#mainMenu ul.navbar-nav > li.nav-item > ul.dropdown-menu li > a").on("click", function() {
                    miGlobal.toggleBlockPantalla('Cargando ' + $(this).text() + ' ...');
                });
            },
            
            valNumero: function(e, obj, op) {
                op = op || {};
                if (op.propValorAnterior == undefined) op.propValorAnterior = 'cantAnt';
                if (op.validacionEnBlur == undefined) op.validacionEnBlur = false;
                if (op.numeroMax == undefined) op.numeroMax = false;
                if (obj[op.propValorAnterior] == undefined) obj[op.propValorAnterior] = '';
                if (e.target.value && e.target.value.trim().length > 0) {
                    let r = (op.re != undefined) ? op.re : /^\d*(?:\.\d{0,2})?$/g;
                    if (r.test(e.target.value.trim())) {
                        if(op.numeroMax !== false) e.target.value = (Number(e.target.value.trim()) > op.numeroMax ? op.numeroMax.toString() : e.target.value);
                        if (op.min != undefined) {
                            let n = parseInt(e.target.value.trim());
                            if (op.decimal != undefined) {
                                op.decimal = Math.pow(10, op.decimal);
                                n = Math.round(parseFloat(e.target.value.trim()) * op.decimal) / op.decimal;
                            }
                            if (n >= op.min && n <= op.max) {
                                if (!op.validacionEnBlur) obj[op.propValorAnterior] = e.target.value.trim();
                            } else {
                                e.target.value = obj[op.propValorAnterior];
                            }
                        } else {
                            if (!op.validacionEnBlur) obj[op.propValorAnterior] = e.target.value.trim();
                        }
                    } else {
                        e.target.value = obj[op.propValorAnterior];
                    }
                } else {
                    if (!op.validacionEnBlur) obj[op.propValorAnterior] = '';
                }
            },

            muestraAlerta: function(mensaje, idParent, tiempoMostrado, tiempoDespliegue, callBack) {
                let bndQuitar = false;
                if (idParent === undefined) {
                    idParent = mensaje;
                    bndQuitar = true;
                } else {
                    idParent = idParent ?? '';
                }
                idParent = '#' + idParent + 'wAlert .alert';
                tiempoDespliegue = tiempoDespliegue ?? 500;
                let jObj = $(idParent);
                if (jObj.length > 0 && bndQuitar) {
                    jObj.slideUp(300);
                    return;
                }
                jObj.html(mensaje);
                jObj.slideDown(tiempoDespliegue);
                if (tiempoMostrado) {
                    tiempoMostrado = tiempoMostrado < 1500 ? 1500 : tiempoMostrado;
                    setTimeout(() => {
                        if(callBack) callBack();
                        jObj.slideUp(300)
                    }, tiempoMostrado);
                }
            },

            toggleBlockPantalla: function(msj) {
                // Usar Skeleton Loading (Opción C)
                SPALoader.showSkeleton(msj);
            },
            
            agregaCamposHidden: function(sSelector, sFormDestino) {
                let oF = $(sFormDestino);
                $(sSelector).each((i, el) => {
                    oF.append(
                        '<input type="hidden" name="' + el.name +
                        '" value="' + el.value + '">'
                    );
                });
            }
        };
    </script>

</head>

<body>

<div class="app-container">
    
    <!-- HEADER SUPERIOR -->
    <header class="app-header">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list" style="font-size: 1.5rem;"></i>
            </button>
            
            <div class="header-brand">
                <img src="<?= base_url()?>/assets/img/<?= $aInfoSis['bannernavbrand'] ?>" alt="Logo">
                <span class="header-brand-text"><?= $aInfoSis['nomempresa'] ?></span>
            </div>
        </div>

        <div class="header-right">
            <div class="header-sucursal">
                <i class="bi bi-building me-2"></i>
                <span><?= $sucursal ?? 'Sucursal' ?></span>
            </div>

            <div class="header-clock" id="headerClock">00:00:00</div>

            <div class="header-user" id="headerUser">
                <div class="user-avatar">
                    <?= strtoupper(substr($slogin ?? 'U', 0, 1)) ?>
                </div>
                <span class="user-name"><?= $slogin ?? 'Usuario' ?></span>
                <i class="bi bi-chevron-down ms-2"></i>
            </div>

            <div class="user-dropdown" id="userDropdown">
                <a href="<?= base_url('usuario/changepasswd/logeado/' . ($nIdUsuario ?? '')) ?>">
                    <i class="bi bi-key me-2"></i>
                    Cambiar Contraseña
                </a>
                <a href="<?= base_url('login') ?>">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <!-- ÁREA PRINCIPAL CON SIDEBAR Y CONTENIDO -->
    <div class="app-main">
        
        <!-- SIDEBAR NAVEGACIÓN -->
        <aside class="app-sidebar" id="appSidebar">
            <nav class="sidebar-menu" id="sidebarMenu">
                <?php 
                    // CAMBIO CRÍTICO: Incluir navbar.php que renderiza el menú
                    echo view('templates/navbar');
                ?>
            </nav>
        </aside>

        <!-- Overlay para mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- ÁREA DE CONTENIDO -->
        <main class="app-content" id="appContent">
            <!-- Skeleton Loader (Opción C - ACTIVA) -->
            <div class="skeleton-loader" id="skeletonLoader">
                <div class="skeleton-card">
                    <div class="skeleton-header"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                </div>
                <div class="skeleton-card">
                    <div class="skeleton-header"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                </div>
            </div>

            <!-- Contenido dinámico -->
            <div class="content-wrapper" id="contentWrapper">