<?php

namespace App\Models\Seguridad;

use CodeIgniter\Model;

class MenuMdl extends Model
{
    protected $table = 'sgmenu';

    protected $allowedFields = ['nIdPadre', 'sDescripcion', 'sAbrev', 'sLink', 'nOrden'];

    protected $primaryKey = 'nIdMenu';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    //protected $afterFind = ['checkPass'];

    public function getRegistros($id = 0)
    {
        if ($id === 0) {
            return $this->findAll();
        }
        return $this->where(['nIdMenu' =>  $id])->first();
    }

    public function getRegistroByFld($sFld, $val = false)
    {
        return $this->where([$sFld => $val])->first();
    }

    /**
     * REEMPLAZAR las funciones buildMenu() y recorreArbol() en MenuMdl.php
     * 
     * Estas funciones ahora generan un array estructurado para el sidebar
     * en lugar de HTML de Bootstrap
     */

    /**
     * Construye el menú en formato array para el sidebar SPA
     * 
     * @param int $idPadre ID del nodo padre (0 para raíz)
     * @param int $idUsuario ID del usuario para permisos
     * @return array [status, message, menuItems, opciones]
     */
    public function buildMenu($idPadre = 0, $idUsuario = 0): array
    {
        $sql = $this->db->table("sgusuario u")->where(["nIdUsuario" => $idUsuario])->getCompiledSelect();
        $user = $this->db->query($sql)->getResult();
        $nIdPerfil = $user[0]->nIdPerfil;

        $builder = $this->db->table("sgpermisoperfil p")->select("nIdMenu")
            ->join('sgusuario u', 'u.nIdPerfil = p.nIdPerfil', 'left')
            ->where(['u.nIdUsuario' => $idUsuario, 'p.nIdMenu >' => 0]);

        $sql = $builder->getCompiledSelect();
        $arrayRol = $this->db->query($sql);
        $aPermisos = [];

        if ($idUsuario == 1 || $nIdPerfil == -1) {
            $aPermisos[] = -1;
        } else {
            foreach ($arrayRol->getResult() as $permiso) {
                $aPermisos[] = intval($permiso->nIdMenu);
            }
        }

        if (empty($aPermisos)) {
            return [10, 'Acceso no autorizado', [], []];
        }

        return $this->recorreArbol($idPadre, $idUsuario, $aPermisos);
    }

    /**
     * Recorre el árbol del menú y construye array estructurado
     * 
     * @param int $idPadre ID del nodo padre
     * @param int $idUsuario ID del usuario
     * @param array $aPermisos Array de permisos del usuario
     * @return array [status, menuItems, opciones, iconMap]
     */
    public function recorreArbol($idPadre = 0, $idUsuario = 0, &$aPermisos = []): array
    {
        $nodos = $this->where(['nIdPadre' => $idPadre])
            ->orderBy('nOrden', 'ASC')
            ->orderBy('nIdMenu', 'ASC')
            ->findAll();

        if (empty($nodos)) {
            return [0, [], [], []];
        }

        $menuItems = [];
        $aOpciones = [];
        $iconMap = $this->getIconMap(); // Mapeo de nombres a iconos

        foreach ($nodos as $nodo) {
            // Saltar dividers
            if ($nodo['sDescripcion'] === '$divider$') {
                continue;
            }

            // Verificar permisos
            if ($aPermisos[0] !== -1) {
                if (array_search(intval($nodo['nIdMenu']), $aPermisos) === false) {
                    continue;
                }
            }

            // Procesar opciones especiales
            if ($nodo['sDescripcion'] === '$opcion$') {
                $aOpciones[$nodo['sLink']] = true;
                continue;
            }

            // Recursión para obtener hijos
            $children = $this->recorreArbol($nodo['nIdMenu'], $idUsuario, $aPermisos);

            // Combinar opciones
            $aOpciones = array_merge($aOpciones, $children[2]);

            // Determinar ícono basado en la descripción o link
            $icon = $this->determineIcon($nodo['sDescripcion'], $nodo['sLink'], $iconMap);

            // Construir item del menú
            $menuItem = [
                'text' => $nodo['sDescripcion'],
                'icon' => $icon,
                'url' => $nodo['sLink'],
                'id' => $nodo['nIdMenu']
            ];

            // Si tiene hijos, agregar submenú
            if ($children[0] > 0 && !empty($children[1])) {
                $menuItem['submenu'] = $children[1];
                $menuItem['url'] = null; // Menús con submenu no tienen URL directa
            }

            $menuItems[] = $menuItem;
        }

        $status = !empty($menuItems) ? 10 : 0;

        return [$status, $menuItems, $aOpciones, $iconMap];
    }

    /**
     * Determina el ícono apropiado basado en el nombre del menú
     * 
     * @param string $descripcion Descripción del menú
     * @param string $link Link del menú
     * @param array $iconMap Mapeo de palabras clave a iconos
     * @return string Clase de ícono de Bootstrap Icons
     */
    private function determineIcon($descripcion, $link, $iconMap): string
    {
        $desc = strtolower($descripcion);
        $url = strtolower($link ?? '');

        // Buscar coincidencias en el mapeo
        foreach ($iconMap as $keyword => $icon) {
            if (strpos($desc, $keyword) !== false || strpos($url, $keyword) !== false) {
                return $icon;
            }
        }

        // Ícono por defecto
        return 'bi-circle';
    }

    /**
     * Mapeo de palabras clave a iconos de Bootstrap Icons
     * Personaliza este array según tus necesidades
     * 
     * @return array Mapeo keyword => icono
     */
    private function getIconMap(): array
    {
        return [
            // Dashboard y reportes
            'dashboard' => 'bi-speedometer2',
            'inicio' => 'bi-house-door',
            'escritorio' => 'bi-house-door',
            'reporte' => 'bi-graph-up',
            'estadistica' => 'bi-bar-chart',

            // Ventas
            'venta' => 'bi-cart',
            'vender' => 'bi-cart-plus',
            'cotiza' => 'bi-clipboard-check',
            'factura' => 'bi-receipt',
            'remision' => 'bi-file-earmark-text',
            'ticket' => 'bi-receipt-cutoff',
            'cobr' => 'bi-cash-coin',
            'caja' => 'bi-cash-stack',
            'corte' => 'bi-calculator',

            // Compras
            'compra' => 'bi-cart-check',
            'proveedor' => 'bi-truck',
            'pedido' => 'bi-bag-check',
            'orden' => 'bi-file-earmark-plus',

            // Inventario
            'inventario' => 'bi-box-seam',
            'articulo' => 'bi-box',
            'producto' => 'bi-box',
            'almacen' => 'bi-building',
            'categoria' => 'bi-tags',
            'existencia' => 'bi-boxes',
            'stock' => 'bi-stack',
            'traspaso' => 'bi-arrow-left-right',

            // Clientes
            'cliente' => 'bi-people',
            'prospecto' => 'bi-person-plus',
            'contacto' => 'bi-person-lines-fill',

            // Configuración y administración
            'configuracion' => 'bi-gear',
            'configura' => 'bi-gear',
            'ajuste' => 'bi-sliders',
            'parametro' => 'bi-toggles',
            'empresa' => 'bi-building',
            'sucursal' => 'bi-shop',

            // Seguridad
            'seguridad' => 'bi-shield-lock',
            'usuario' => 'bi-person-badge',
            'perfil' => 'bi-person-gear',
            'permiso' => 'bi-key',
            'modulo' => 'bi-grid',
            'acceso' => 'bi-door-open',

            // Catálogos
            'catalogo' => 'bi-book',
            'lista' => 'bi-list-ul',
            'precio' => 'bi-currency-dollar',
            'descuento' => 'bi-percent',

            // Otros
            'bitacora' => 'bi-journal-text',
            'log' => 'bi-file-text',
            'auditoria' => 'bi-search',
            'ayuda' => 'bi-question-circle',
            'soporte' => 'bi-headset',
            'herramienta' => 'bi-tools',
            'utile' => 'bi-wrench',
            'impresora' => 'bi-printer',
            'correo' => 'bi-envelope',
            'notifica' => 'bi-bell',
        ];
    }

    /**
     * OPCIONAL: Método para generar HTML (compatibilidad con código anterior)
     * Solo usar si necesitas mantener el formato HTML en alguna parte
     */
    public function buildMenuHTML($idPadre = 0, $idUsuario = 0): string
    {
        $result = $this->buildMenu($idPadre, $idUsuario);

        if ($result[0] === 10) {
            return $this->convertArrayToHTML($result[1]);
        }

        return '<li class="nav-item"><span class="nav-link text-danger">' . $result[1] . '</span></li>';
    }

    /**
     * OPCIONAL: Convierte array de menú a HTML de Bootstrap (por compatibilidad)
     */
    private function convertArrayToHTML($menuItems, $isSubmenu = false): string
    {
        $html = '';

        foreach ($menuItems as $item) {
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                // Menú con submenu (dropdown)
                $html .= '<li class="nav-item dropdown">';
                $html .= '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">';
                $html .= htmlspecialchars($item['text']);
                $html .= '</a>';
                $html .= '<ul class="dropdown-menu">';
                $html .= $this->convertArrayToHTML($item['submenu'], true);
                $html .= '</ul>';
                $html .= '</li>';
            } else {
                // Item simple
                if ($isSubmenu) {
                    $html .= '<li><a class="dropdown-item" href="' . base_url($item['url']) . '">';
                    $html .= htmlspecialchars($item['text']);
                    $html .= '</a></li>';
                } else {
                    $html .= '<li class="nav-item">';
                    $html .= '<a class="nav-link" href="' . base_url($item['url']) . '">';
                    $html .= htmlspecialchars($item['text']);
                    $html .= '</a>';
                    $html .= '</li>';
                }
            }
        }

        return $html;
    }
}

/***********
 *

CREATE TABLE `sgmenu` (
  `nIdMenu` int(11) NOT NULL AUTO_INCREMENT,
  `nIdPadre` int(11) DEFAULT 0,
  `sDescripcion` varchar(85) DEFAULT NULL,
  `sAbrev` varchar(45) DEFAULT NULL,
  `sLink` varchar(45) DEFAULT NULL,
  `dtAlta` datetime DEFAULT NULL,
  `dtBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`nIdMenu`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

 *
 ***********/
