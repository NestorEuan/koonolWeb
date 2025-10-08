<?php
/**
 * NAVBAR PARA SIDEBAR CON ACORDEÓN
 * Versión para CodeIgniter con MenuMdl
 * 
 * El array $menuItems es generado por MenuMdl::buildMenu()
 */

// Verificar que existe el array de menú
if (!isset($menuItems) || !is_array($menuItems) || empty($menuItems)):
    // Si no hay menú, mostrar mensaje
    ?>
    <div class="menu-item">
        <div class="text-center p-3 text-light">
            <small>No hay menú disponible</small>
        </div>
    </div>
    <?php
    return;
endif;

// Renderizar menú
?>

<?php foreach ($menuItems as $item): ?>
    <div class="menu-item">
        <?php if (isset($item['submenu']) && is_array($item['submenu'])): ?>
            <!-- Menú con submenú (accordion) -->
            <a href="#" class="menu-header">
                <i class="menu-icon bi <?= $item['icon'] ?? 'bi-circle' ?>"></i>
                <span class="menu-text"><?= $item['text'] ?></span>
                <i class="menu-arrow bi bi-chevron-down ms-auto"></i>
            </a>
            
            <div class="submenu">
                <?php foreach ($item['submenu'] as $subitem): ?>
                    <a href="<?= base_url($subitem['url']) ?>" class="submenu-item">
                        <i class="bi bi-chevron-right me-2"></i>
                        <?= $subitem['text'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Menú sin submenú (enlace directo) -->
            <a href="<?= base_url($item['url']) ?>" class="menu-header" data-url="<?= $item['url'] ?>">
                <i class="menu-icon bi <?= $item['icon'] ?? 'bi-circle' ?>"></i>
                <span class="menu-text"><?= $item['text'] ?></span>
            </a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
