<?php 
/**
 * VISTA DESKTOP - Single Page Application
 * Esta es la vista inicial que se carga al entrar al sistema
 */

// Si hay redirección automática a caja (ker)
if(isset($paraCaja)): 
?>
    <script>
        miGlobal.toggleBlockPantalla('Accesando a ventas');
        location.href = '<?= base_url('ventasasp') ?>';
    </script>
<?php 
    return;
endif; 
?>

<!-- VISTA DESKTOP INICIAL -->
<div class="desktop-welcome">
    <style>
        .desktop-welcome {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 500px;
            padding: 40px 20px;
            text-align: center;
        }
        
        .desktop-logo-container {
            position: relative;
            margin-bottom: 40px;
            animation: fadeInScale 0.8s ease-out;
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .desktop-logo {
            max-width: 500px;
            width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .desktop-logo:hover {
            transform: scale(1.02);
        }
        
        .desktop-content {
            max-width: 600px;
            animation: fadeInUp 1s ease-out 0.3s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .desktop-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .desktop-subtitle {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .desktop-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .quick-action-btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .desktop-footer-logo {
            position: fixed;
            bottom: 90px;
            right: 30px;
            opacity: 0.3;
            transition: opacity 0.3s;
        }
        
        .desktop-footer-logo:hover {
            opacity: 0.6;
        }
        
        .desktop-footer-logo img {
            height: 60px;
            width: auto;
            filter: grayscale(100%);
        }
        
        @media (max-width: 768px) {
            .desktop-logo {
                max-width: 350px;
            }
            
            .desktop-title {
                font-size: 1.5rem;
            }
            
            .desktop-subtitle {
                font-size: 1rem;
            }
            
            .desktop-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .desktop-footer-logo {
                bottom: 80px;
                right: 15px;
            }
            
            .desktop-footer-logo img {
                height: 40px;
            }
        }
    </style>

    <div class="desktop-logo-container">
        <img src="<?= base_url('assets/img/' . ($aInfoSis['bannermain'] ?? 'logo.png')) ?>" 
             alt="<?= $aInfoSis['nomempresa'] ?>" 
             class="desktop-logo">
    </div>

    <div class="desktop-content">
        <h1 class="desktop-title">
            Bienvenido a <?= $aInfoSis['nomempresa'] ?>
        </h1>
        <p class="desktop-subtitle">
            Sistema de Punto de Venta e Inventario
        </p>

        <!-- Acciones Rápidas -->
        <div class="quick-actions">
            <a href="<?= base_url('ventas/nueva') ?>" class="quick-action-btn">
                <i class="bi bi-cart-plus"></i>
                Nueva Venta
            </a>
            <a href="<?= base_url('inventario/articulos') ?>" class="quick-action-btn">
                <i class="bi bi-box"></i>
                Inventario
            </a>
            <a href="<?= base_url('reportes') ?>" class="quick-action-btn">
                <i class="bi bi-graph-up"></i>
                Reportes
            </a>
        </div>

        <!-- Estadísticas Rápidas (Opcional - puedes cargar datos reales) -->
        <div class="desktop-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Ventas Hoy</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Productos</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Clientes</div>
            </div>
        </div>
    </div>

    <!-- Logo inferior decorativo -->
    <div class="desktop-footer-logo">
        <img src="<?= base_url('assets/img/' . ($aInfoSis['logobottom'] ?? 'logo.png')) ?>" 
             alt="Logo">
    </div>
</div>

<script>
$(document).ready(function() {
    // Aquí puedes cargar estadísticas reales vía AJAX
    // loadDashboardStats();
    
    // Interceptar clicks en acciones rápidas para usar SPA
    $('.quick-action-btn').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href').replace(baseURL + '/', '');
        const title = $(this).text().trim();
        AppSPA.loadPage(url, title);
    });
});

// Función ejemplo para cargar estadísticas
function loadDashboardStats() {
    $.ajax({
        url: baseURL + '/api/dashboard/stats',
        method: 'GET',
        dataType: 'json'
    }).done(function(data) {
        if (data.ventas) $('.stat-card:eq(0) .stat-value').text(data.ventas);
        if (data.productos) $('.stat-card:eq(1) .stat-value').text(data.productos);
        if (data.clientes) $('.stat-card:eq(2) .stat-value').text(data.clientes);
    }).fail(function() {
        console.log('No se pudieron cargar las estadísticas');
    });
}
</script>