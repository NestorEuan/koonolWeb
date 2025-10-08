<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Controllers\Catalogos\Cliente;
use App\Models\Almacen\EntregaDetalleMdl;
use App\Models\Almacen\EntregaMdl;
use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Catalogos\ClienteMdl;
use App\Models\Catalogos\PrecioArticuloMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Catalogos\TipoPagoMdl;
use App\Models\Catalogos\TipoRelacionMdl;
use App\Models\Catalogos\UsoCfdiMdl;
use App\Models\Envios\EnvioDetalleMdl;
use App\Models\Envios\EnvioMdl;
use App\Models\Seguridad\UsuarioMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\DirEntregaMdl;
use App\Models\Ventas\FacturaEspecialMdl;
use App\Models\Ventas\FacturasMdl;
use App\Models\Ventas\FacturasNomPubMdl;
use App\Models\Ventas\FacturasRelaMdl;
use App\Models\Ventas\MovPagAdelMdl;
use App\Models\Ventas\PagAdelMdl;
use App\Models\Ventas\SaldoPagAdelMdl;
use App\Models\Ventas\VentasCanceladasMdl;
use App\Models\Ventas\VentasDetAuxMdl;
use App\Models\Ventas\VentasDetMdl;
use App\Models\Ventas\VentasDetSafMdl;
use App\Models\Ventas\VentasMdl;
use App\Models\Ventas\VentasNomEntregaMdl;
use App\Models\Ventas\VentasPagoMdl;
use App\Models\Ventas\VentasSaldoMdl;
use App\Models\Viajes\EnvioDireccionMdl;
use CodeIgniter\Entity\Cast\DatetimeCast;
use DateTime;
use DateInterval;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Remisiones extends BaseController
{


    public function index()
    {
        // listado artLst usado :
        /*
        [0]     id, 
        [1]     nom, 
        [2]     cant comprada, 
        [3]     entregado , 
        [4]     existencia disponible en sucursal,  
        [5]     existencia disponible en otros almacenes, 
        [6]     cant a entregar, 
        [7]     cant a enviar,
        [8]     saldo restante, 
        [9]     Por Entregar Actual, 
        [10]    Modo ENV
        [11]    existencia fisica en la sucursal

        */
        $this->validaSesion();

        if (isset($_SESSION['vtEntrega'])) unset($_SESSION['vtEntrega']);
        $mdlUsuario = new UsuarioMdl();
        $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);

        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'));
        $mdlVenta = new VentasMdl();
        $tipo = $this->request->getVar('inputGroupSelectOpciones') ?? '0';
        $filtro = '';
        if ($tipo == 2 || $tipo == 5) $filtro = $this->nIdUsuario;
        if ($tipo == 3) $filtro = $this->request->getVar('idCliente') ?? '';
        if ($tipo == 4) $filtro = $this->request->getVar('nFolioRemision') ?? '';
        $data = [
            'registros' => $mdlVenta->getRegistros(
                false,
                $f[0],
                $f[1],
                'DESC',
                true,
                $this->nIdSucursal,
                $tipo,
                $filtro
                // ($_SESSION['perfilUsuario'] == '-1' ? false : $this->nIdUsuario),
            ),
            'pager' => $mdlVenta->pager,
            'bndCancela' => $_SESSION['perfilUsuario'] == '-1' ||  $regUsu['bCancelarRemision'] == '1',
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d'),
            'esMobil' => $this->request->getUserAgent()->isMobile(),
            'oEntregaModoENV' => $_SESSION['perfilUsuario'] == '-1' ||  $regUsu['bEntregaModoENV'] == '1',
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/remisiones', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    private function getFechas()
    {
        $fi = $this->request->getVar('dFecIni');
        if ($fi == null || $fi == '') {
            $fi = null;
        } else {
            $fi = new Datetime($fi);
        }
        $ff = $this->request->getVar('dFecFin');
        if ($ff == null || $ff == '') {
            $ff = null;
        } else {
            $ff = new Datetime($ff);
        }
        if ($fi === null) {
            $ff = new DateTime();
            $fi = clone $ff;
            $fi->sub(new DateInterval('P60D'));
        } else {
            if ($ff == null) {
                $ff = clone $fi;
                $ff->add(new DateInterval('P1M'));
            }
        }
        return [$fi, $ff];
    }

    public function entregaParcial($nId)
    {
        $this->validaSesion();
        $_SESSION['vtEntrega'] = [
            'idVenta' => $nId,
            'lstArtRelacionados' => []
        ];
        $idSucursal = $this->request->getVar('nIdSucursalEntrega') ?? $this->nIdSucursal;
        $mdlInventario = new InventarioMdl();
        $mdlArticulo = new ArticuloMdl();
        $mdlVentas = new VentasMdl();
        $mdlVentasDet = new VentasDetMdl();
        $mdlCli = new ClienteMdl();
        $_SESSION['vtEntrega']['lstArtRelacionados'] = [];

        $rv = $mdlVentas->getRegistros($nId);
        $rvd = $mdlVentasDet->getDetalleVenta($rv['nIdVentas']);
        $rc = $mdlCli->getRegistros($rv['nIdCliente']);

        $_SESSION['vtEntrega']['cli'] = [
            'id' => $rv['nIdCliente'],
            'nom' => $rc['sNombre'],
            'folRemision' => $rv['nFolioRemision'],
            'tipoCli' => $rc['cTipoCliente'],
            'idDirEnt' => $rv['nIdDirEntrega'],
            'envEnt' => '',
            'envDir' => '',
            'envCol' => '',
            'envTel' => '',
            'envRef' => ''
        ];
        /*
            00 - idArticulo
            01 - descripcion articulo
            02 - cantidad comprada original
            03 - Entregado al cliente
            04 - existencia disponible en sucursal
            05 - existencia disponible en otros almacenes
            06 - cant a entregar en sucursal
            07 - cant a enviar al cliente
            08 - Saldo por surtir
            09 - Cantidad por entregar al cliente de la remision (campo nPorEntregar) campo fijo
            10 - Producto se carga con el proveedor (modo ENV) (13)
            11 - Existencia fisica en sucursal del producto
            12 - Detalle de productos con acumulados (vigas de diferentes medidas por ejemplo)
                 esto pasa cuando se hace una venta anticipada, donde el cliente compra por ejemplo en ML
                 y se entrega por medidas (las vigas y vigetas).
                 Campos en ['vtEntrega']['lstArt'][12] tiene como key el idarticulo
                 
                 00 - idArticulo con relacion al padre
                 01 - Descripcion Medida a mostrar
                 02 - Medida a usar como factor
                 03 - Existencia
                 04 - Disponible
                 05 - Otros almacenes
                 06 - cant a entregar en sucursal
                 07 - cant a enviar al cliente
        */
        // listado es:
        /*
        [0]id, [1]nom, [2]cant comprada, [3]entregado , [4]exist en suc,  
        [5]exis en otro, [6]cant a entregar, [7]cant a enviar,
        [8]saldo restante, [9]Por Entregar Actual, [10]Modo ENV, 
        [11] exis fisica, [12] detalle si es 
        */
        $det = [];
        foreach ($rvd as $k => $v) {
            $ri = $mdlInventario->getRegistrosInv($v['nIdArticulo'], $idSucursal);
            $det[$v['nIdArticulo']] = [
                $v['nIdArticulo'],
                $v['nomArt'],
                round(floatval($v['nCant']), 3),
                round(floatval($v['nEntregado']), 3),
                round(floatval($ri['exiSuc'] ?? 0), 3),
                round(floatval($ri['exiOtros'] ?? 0), 3),
                0,
                0,
                round(floatval($v['nPorEntregar']), 3),
                round(floatval($v['nPorEntregar']), 3),
                '0',
                round(floatval($ri['exiFis'] ?? 0), 3),
                false
            ];

            $ra = $mdlArticulo->getRegistros($v['nIdArticulo']);
            if ($ra['cConArticuloRelacionado'] == '1') {
                $rasr = $mdlArticulo->getRegistrosRelacionados($v['nIdArticulo']);
                $arrMedidas = [];
                foreach ($rasr as $vv) {
                    $ri = $mdlInventario->getRegistrosInv($vv['nIdArticulo'], $idSucursal);
                    $arrMedidas[] = [
                        number_format(floatval($vv['nMedida']), 2, '.', ''),
                        $vv['nIdArticulo'],
                        round(floatval($ri['exiFis'] ?? 0), 3),
                        round(floatval($ri['exiSuc'] ?? 0), 3),
                        round(floatval($ri['exiOtros'] ?? 0), 3)
                    ];
                }
                $_SESSION['vtEntrega']['lstArtRelacionados'][$v['nIdArticulo']] = $arrMedidas;
                $det[$v['nIdArticulo']][4] = 0;
                $det[$v['nIdArticulo']][5] = 0;
                $det[$v['nIdArticulo']][11] = 0;
                $det[$v['nIdArticulo']][12] = [];
            }
        }
        $_SESSION['vtEntrega']['lstArt'] = $det;
        $this->assignaDatosEnvio('0', '0');
    }

    public function entregaTodo()
    {
        session();
        $nSumEntrega = 0;
        foreach ($_SESSION['vtEntrega']['lstArt'] as $k => $v) {
            if ($v[9] == 0) {
                $_SESSION['vtEntrega']['lstArt'][$k][6] = 0;
                $_SESSION['vtEntrega']['lstArt'][$k][7] = 0;
                $_SESSION['vtEntrega']['lstArt'][$k][8] = 0;
                continue;
            }
            if ($v[12] !== false) {
                $sumConcentrado = 0;
                foreach ($v[12] as $kk => $vv) {
                    // si tengo en envio, se lo sumo a la entrega
                    $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][6] +=
                        $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][7];
                    $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][7] = 0;
                    $sumConcentrado += round($vv[2] * $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][6], 3);
                }
                $_SESSION['vtEntrega']['lstArt'][$k][6] = $sumConcentrado;
                $_SESSION['vtEntrega']['lstArt'][$k][7] = 0;
                $nSumEntrega += $_SESSION['vtEntrega']['lstArt'][$k][6];
                continue;
            }
            $mdlArticulo = new ArticuloMdl();
            $bndValExistencia = ($mdlArticulo->getRegistros($k))['cSinExistencia'] == '0';
            if ($bndValExistencia === false) {
                $_SESSION['vtEntrega']['lstArt'][$k][6] = round($v[9], 3);
            } elseif (round($v[9], 3) <= round($v[11], 3)) {
                $_SESSION['vtEntrega']['lstArt'][$k][6] = round($v[9], 3);
            } else {
                $_SESSION['vtEntrega']['lstArt'][$k][6] = round($v[11], 3);
            }
            $_SESSION['vtEntrega']['lstArt'][$k][7] = 0;
            $_SESSION['vtEntrega']['lstArt'][$k][8] = $v[9] -
                $_SESSION['vtEntrega']['lstArt'][$k][6];
            $nSumEntrega += $_SESSION['vtEntrega']['lstArt'][$k][6];
        }
        $bEntrega = round($nSumEntrega, 3) > 0 ? '1' : '0';
        $this->assignaDatosEnvio('0', $bEntrega);
    }

    public function enviaTodo()
    {
        session();
        $nSumEnvio = 0;
        foreach ($_SESSION['vtEntrega']['lstArt'] as $k => $v) {
            if ($v[9] == 0) {
                $_SESSION['vtEntrega']['lstArt'][$k][6] = 0;
                $_SESSION['vtEntrega']['lstArt'][$k][7] = 0;
                $_SESSION['vtEntrega']['lstArt'][$k][8] = 0;
                continue;
            }
            if ($v[12] !== false) {
                $sumConcentrado = 0;
                foreach ($v[12] as $kk => $vv) {
                    // si tengo en entrega, se lo sumo al envio
                    $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][7] +=
                        $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][6];
                    $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][6] = 0;
                    $sumConcentrado += round($vv[2] * $_SESSION['vtEntrega']['lstArt'][$k][12][$kk][7], 3);
                }
                $_SESSION['vtEntrega']['lstArt'][$k][6] = 0;
                $_SESSION['vtEntrega']['lstArt'][$k][7] = $sumConcentrado;
                $nSumEnvio += $_SESSION['vtEntrega']['lstArt'][$k][7];
                continue;
            }
            $mdlArticulo = new ArticuloMdl();
            $bndValExistencia = ($mdlArticulo->getRegistros($k))['cSinExistencia'] == '0' && false; // por ser envio no se valida
            if ($bndValExistencia === false) {
                $_SESSION['vtEntrega']['lstArt'][$k][7] = round($v[9], 3);
            } elseif (round($v[9], 3) <= round($v[4], 3)) {
                $_SESSION['vtEntrega']['lstArt'][$k][7] = round($v[9], 3);
            } else {
                $_SESSION['vtEntrega']['lstArt'][$k][7] = round($v[4], 3);
            }
            $_SESSION['vtEntrega']['lstArt'][$k][6] = 0;
            $_SESSION['vtEntrega']['lstArt'][$k][8] = $v[9] -
                $_SESSION['vtEntrega']['lstArt'][$k][7];
            $nSumEnvio += $_SESSION['vtEntrega']['lstArt'][$k][7];
        }
        $bEnvio = round($nSumEnvio, 3) > 0 ? '1' : '0';
        $this->assignaDatosEnvio($bEnvio, '0');
    }

    private function assignaDatosEnvio($bEnvio, $bEntrega)
    {
        if ($this->request->getVar('sEnvEntrega') != null) {
            $_SESSION['vtEntrega']['cli']['envDir'] = $this->request->getVar('sEnvDireccion') ?? $_SESSION['vtEntrega']['cli']['envDir'];
            $_SESSION['vtEntrega']['cli']['envCol'] = $this->request->getVar('sEnvColonia') ?? $_SESSION['vtEntrega']['cli']['envCol'];
            $_SESSION['vtEntrega']['cli']['envTel'] = $this->request->getVar('sEnvTelefono') ?? $_SESSION['vtEntrega']['cli']['envTel'];
            $_SESSION['vtEntrega']['cli']['envEnt'] = $this->request->getVar('sEnvEntrega');
            $_SESSION['vtEntrega']['cli']['envRef'] = $this->request->getVar('sEnvReferencia');
        }
        $mdlSucursal = new SucursalMdl();
        $regSuc = $mdlSucursal->getRegistros(false, false, $this->nIdSucursal);
        array_splice($regSuc, 0, 0, [['nIdSucursal' => $this->nIdSucursal, 'sDescripcion' => 'Esta bodega']]);
        $data['regSucursales'] = $regSuc;
        $data['direcciones'] = [];
        if ($_SESSION['vtEntrega']['cli']['tipoCli'] == 'P') {
            $lstDir = [];
        } else {
            $mdlDirEntrega = new DirEntregaMdl();
            $lstDir = $mdlDirEntrega->listaDireccionesCliente($_SESSION['vtEntrega']['cli']['id']);
        }
        $data['direcciones'] = $lstDir;
        $_SESSION['vtEntrega']['cli']['idDirEnt'] = $this->request->getVar('nIdDirEntrega') ?? '0';

        $data['idventa'] = $_SESSION['vtEntrega']['idVenta'];
        $data['registros'] = $_SESSION['vtEntrega']['lstArt'];
        $data['cliente'] = $_SESSION['vtEntrega']['cli'];
        // $data['docu'] = $_SESSION['vtEntrega']['docu'];
        $data['hayEnvio'] = $bEnvio;
        $data['hayEntrega'] = $bEntrega;
        $data['hayEntregaPublico'] = $bEntrega == '1' && $_SESSION['vtEntrega']['cli']['tipoCli'] == 'P' ? '1' : '0';
        $data['nIdSucursalActual'] = $this->request->getVar('nIdSucursalEntrega') ?? $this->nIdSucursal;
        $data['bOtraSucursal'] = $this->nIdSucursal != $data['nIdSucursalActual'];
        $data['artRelacionados'] = $_SESSION['vtEntrega']['lstArtRelacionados'];
        echo view('ventas/entregaparcial', $data);
    }

    public function entregaArt($id, $tipoMov, $cant)
    {
        // listado es:
        /*
        [0]id, [1]nom, [2]cant comprada, [3]entregado , [4]exist en suc,  
        [5]exis en otro, [6]cant a entregar, [7]cant a enviar,
        [8]saldo restante, [9]Por Entregar Actual, [10]Modo ENV
        [11] existencia fisica
        */
        $this->validaSesion();

        $msjErr = '';
        $cant = round(floatval($cant), 3);
        $cantPorEntregar = $_SESSION['vtEntrega']['lstArt'][$id][9];
        $cantActual = $tipoMov == 'entrega' ? $_SESSION['vtEntrega']['lstArt'][$id][6] : $_SESSION['vtEntrega']['lstArt'][$id][7];
        $mdlArticulo = new ArticuloMdl();
        $bndValExistencia = ($mdlArticulo->getRegistros($id))['cSinExistencia'] == '0' && $tipoMov == 'entrega';
        $mdlUsuario = new UsuarioMdl();
        // $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        $bndValidaEntregaConDiponibles = false;
        if ($_SESSION['vtEntrega']['lstArt'][$id][10] == '1') {
            // en modo env no se valida existencia, solo debe validarse con la cantidad
            // y debe ser el movto de tipo envio no de entrega
            if ($tipoMov == 'entrega') {
                // $msjErr = 'No se puede realizar entregas en modo ENV, desmárquelo';
            } else {
                $_SESSION['vtEntrega']['lstArt'][$id][6] = 0;
                $nval = $cantPorEntregar - $cant;
                if (round($nval, 3) < 0) {
                    $cant = $cantPorEntregar;
                }
                $_SESSION['vtEntrega']['lstArt'][$id][7] = round($cant, 3);
                $_SESSION['vtEntrega']['lstArt'][$id][8] = round(
                    $cantPorEntregar -
                        $_SESSION['vtEntrega']['lstArt'][$id][6] -
                        $_SESSION['vtEntrega']['lstArt'][$id][7],
                    3
                );
            }
        } elseif (($bndValExistencia && $bndValidaEntregaConDiponibles && ($cant > $_SESSION['vtEntrega']['lstArt'][$id][4]))) {
            $msjErr = 'No hay suficientes productos disponibles en almacén';
        } elseif ($bndValExistencia && ($cant > $_SESSION['vtEntrega']['lstArt'][$id][11])) {
            $msjErr = 'No hay suficientes existencias fisicas del articulo en almacén';
        } elseif ($tipoMov == 'envio' && ($cant > $cantPorEntregar)) {
            $msjErr = 'La cantidad a enviar no puede ser mayor que la cantidad pendiente';
        } else {
            // segun lo capturado se hace el ajuste automatico sobre la cantidad capturada
            $nEntrega = $_SESSION['vtEntrega']['lstArt'][$id][6];
            $nEnvio = $_SESSION['vtEntrega']['lstArt'][$id][7];
            if ($tipoMov == 'entrega') {
                $nval = $cantPorEntregar - ($cant + $nEnvio);
                if (round($nval, 3) < 0) {
                    $cant = $cantPorEntregar - $nEnvio;
                }
                $_SESSION['vtEntrega']['lstArt'][$id][6] = round($cant, 3);
            } else {
                $nval = $cantPorEntregar - ($nEntrega + $cant);
                if (round($nval, 3) < 0) {
                    $cant = $cantPorEntregar - $nEntrega;
                }
                $_SESSION['vtEntrega']['lstArt'][$id][7] = round($cant, 3);
            }
            $_SESSION['vtEntrega']['lstArt'][$id][8] = round(
                $cantPorEntregar -
                    $_SESSION['vtEntrega']['lstArt'][$id][6] -
                    $_SESSION['vtEntrega']['lstArt'][$id][7],
                3
            );
        }
        if ($msjErr == '') {
            return $this->retornaEntregaArt($id, []);
        }
        return json_encode(['ok' => '0', 'msj' => $msjErr, 'cantActual' => $cantActual]);
    }

    public function entregaArtD($id)
    {
        $this->validaSesion();
        $nIdArticulo = intval($this->request->getVar('nIdArticulo'));
        $nEntrega = round(floatval($this->request->getVar('nEntrega') ?? 0), 2);
        $nEnvio = round(floatval($this->request->getVar('nEnvio') ?? 0), 2);
        $cantPorEntregar = $_SESSION['vtEntrega']['lstArt'][$id][9];
        // $nCantidadComprado = $_SESSION['vtEntrega']['lstArt'][$id][3];
        // valida los datos
        $msjErr = '';
        if ($nIdArticulo == 0) $msjErr = 'Falta seleccionar la medida.';
        if ($nEntrega == 0 && $nEnvio == 0) $msjErr .= ($msjErr == '' ? '' : '<br>') . 'Falta capturar la cantidad.';
        if ($msjErr != '') return json_encode(['ok' => '0', 'msj' => $msjErr]);

        $mdlArticulo = new ArticuloMdl();
        $regArt = $mdlArticulo->getRegistros($nIdArticulo);
        $mdlInventario = new InventarioMdl();
        $idSucursal = $this->request->getVar('nIdSucursalEntrega') ?? $this->nIdSucursal;
        $regInv = $mdlInventario->getRegistrosInv($nIdArticulo, $idSucursal);
        $mdlUsuario = new UsuarioMdl();
        // $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        $bndValidaEntregaConDiponibles = false;

        $nMedida = round(floatval($regArt['nMedida']), 2);
        $nCantidad = $nEntrega + $nEnvio;
        // sumar los registros para calcular el total
        $nSumMedidaEntrega = $nMedida * $nEntrega;
        $nSumMedidaEnvio = $nMedida * $nEnvio;
        foreach ($_SESSION['vtEntrega']['lstArt'][$id][12] as $k => $v) {
            if ($k == $nIdArticulo) continue;
            $nSumMedidaEntrega += $v[2] * $v[6];
            $nSumMedidaEnvio   += $v[2] * $v[7];
        }
        if ($cantPorEntregar < round($nSumMedidaEntrega + $nSumMedidaEnvio, 2)) {
            $msjErr = 'La cantidad a entregar no puede ser mayor que la cantidad comprada';
        } elseif ($bndValidaEntregaConDiponibles && (round(floatval($regInv['exiSuc']), 3) < $nEntrega)) {
            $msjErr = 'No hay suficientes productos disponibles en almacén';
        } elseif ($nEntrega > round(floatval($regInv['exiFis']), 3)) {
            $msjErr = 'No hay suficientes existencias en almacén';
        } else {
            $_SESSION['vtEntrega']['lstArt'][$id][12][$nIdArticulo] = [
                $nIdArticulo,
                number_format($nMedida, 2),
                $nMedida,
                round(floatval($regInv['exiFis']), 3),
                round(floatval($regInv['exiSuc']), 3),
                round(floatval($regInv['exiOtros']), 3),
                $nEntrega,
                $nEnvio
            ];
            $_SESSION['vtEntrega']['lstArt'][$id][6] = round($nSumMedidaEntrega, 3);
            $_SESSION['vtEntrega']['lstArt'][$id][7] = round($nSumMedidaEnvio, 3);

            $_SESSION['vtEntrega']['lstArt'][$id][8] = round(
                $cantPorEntregar -
                    $_SESSION['vtEntrega']['lstArt'][$id][6] -
                    $_SESSION['vtEntrega']['lstArt'][$id][7],
                3
            );
        }
        if ($msjErr == '') {
            return $this->retornaEntregaArt($id, [
                'idm' => $id,
                'nIdArticulo' => $nIdArticulo,
                'medida' => strval($nMedida),
                'exis' => round(floatval($regInv['exiFis']), 3),
                'dis' => round(floatval($regInv['exiSuc']), 3),
                'otr' => round(floatval($regInv['exiOtros']), 3),
                'nEnt' => $nEntrega,
                'nEnv' => $nEnvio
            ]);
        }
        return json_encode(['ok' => '0', 'msj' => $msjErr]);
    }

    public function borraArtD($idm, $idArticulo)
    {
        session();
        unset($_SESSION['vtEntrega']['lstArt'][$idm][12][$idArticulo]);
        $nSumMedidaEntrega = 0;
        $nSumMedidaEnvio = 0;
        foreach ($_SESSION['vtEntrega']['lstArt'][$idm][12] as $k => $v) {
            $nSumMedidaEntrega += $v[2] * $v[6];
            $nSumMedidaEnvio   += $v[2] * $v[7];
        }
        $_SESSION['vtEntrega']['lstArt'][$idm][6] = round($nSumMedidaEntrega, 3);
        $_SESSION['vtEntrega']['lstArt'][$idm][7] = round($nSumMedidaEnvio, 3);
        return $this->retornaEntregaArt($idm, []);
    }

    private function retornaEntregaArt($id, $aValores)
    {
        $sumEntrega = 0;
        $sumEnvio = 0;
        foreach ($_SESSION['vtEntrega']['lstArt'] as $v) {
            $sumEnvio += $v[7];
            $sumEntrega += $v[6];
        }
        $nPorSurtir = $_SESSION['vtEntrega']['lstArt'][$id][9] -
            $_SESSION['vtEntrega']['lstArt'][$id][6] -
            $_SESSION['vtEntrega']['lstArt'][$id][7];

        return json_encode([
            'ok' => '1',
            'id' => $id,
            'hayEntrega' => ($sumEntrega == 0 ? '0' : '1'),
            'hayEnvio' => ($sumEnvio == 0 ? '0' : '1'),
            'hayEntregaPublico' => ($_SESSION['vtEntrega']['cli']['tipoCli'] == 'P' && $sumEntrega > 0) ? '1' : '0',
            'nEntrega' => $_SESSION['vtEntrega']['lstArt'][$id][6],
            'nEnvio' => $_SESSION['vtEntrega']['lstArt'][$id][7],
            'porSurtir' => round($nPorSurtir, 2),
            'bndModoEnv' => $_SESSION['vtEntrega']['lstArt'][$id][10],
            'valores' => $aValores
        ]);
    }

    public function agregarDireccion()
    {
        session();
        $dir = str_replace(
            [chr(13) . chr(10), chr(9), chr(13)],
            [chr(10), ' ', chr(10)],
            $this->request->getVar('sEnvDireccion')
        );
        $ref = str_replace(
            [chr(13) . chr(10), chr(9), chr(13)],
            [chr(10), ' ', chr(10)],
            $this->request->getVar('sEnvReferencia')
        );

        $mdlDirEntrega = new DirEntregaMdl();
        $id = $mdlDirEntrega->insert([
            'nIdCliente' => $_SESSION['vtEntrega']['cli']['id'],
            'sEnvEntrega' => $this->request->getVar('sEnvEntrega'),
            'sEnvDireccion' => $dir,
            'sEnvColonia' => $this->request->getVar('sEnvColonia'),
            'sEnvTelefono' => $this->request->getVar('sEnvTelefono'),
            'sEnvReferencia' => $ref
        ]);
        $_SESSION['vtEntrega']['cli']['idDirEnt'] = $id;
        $reg = $mdlDirEntrega->listaDireccionesCliente(
            $_SESSION['vtEntrega']['cli']['id']
        );
        return json_encode(['ok' => '1', 'id' => $id, 'reg' => $reg]);
    }

    public function guardarEntrega()
    {
        $session = session();
        $mdlVentas = new VentasMdl();
        $mdlVentasdet = new VentasDetMdl();
        $mdlInventario = new InventarioMdl();
        $mdlEnvio = new EnvioMdl();
        $mdlEnvioDet = new EnvioDetalleMdl();
        $mdlDirEntrega = new DirEntregaMdl();
        $mdlVentasNomEntrega = new VentasNomEntregaMdl();
        $mdlArticulo = new ArticuloMdl();
        $mdlUsuario = new UsuarioMdl();
        $mdlEnvioDirec = new EnvioDireccionMdl();

        $cEntrega = $mdlVentasNomEntrega->getNomEntrega($_SESSION['vtEntrega']['idVenta']);
        $bndNuevoRegEntrega = $cEntrega['cNomEntrega'] ?? false;
        $cNomEntrega = $this->request->getVar('cNomEntrega') ?? '';
        $nIdDirEntrega = 0;
        $bEnvio = $this->request->getVar('hayEnvio');
        $nIdSucursalEntrega = $this->request->getVar('nIdSucursalEntrega');

        $mdlVentas->db->transStart();

        $_SESSION['bParaEnvio'] = '0';
        $_SESSION['bParaEntrega'] = '0';
        $_SESSION['bQuienRecogeEntrega'] = $this->request->getVar('quienRecoge');
        $_SESSION['nIdEntregaEnEnvio'] = 0;
        $nuevaDireccionEnvio = false;

        if ($bEnvio == '1') {
            $_SESSION['bParaEnvio'] = (intval($nIdSucursalEntrega) == intval($this->nIdSucursal) ? '1' : '2');
            $nIdDirEntrega = $this->request->getVar('nIdDirEntrega') ?? '0';
            $nIdDirEntrega = $nIdDirEntrega == '' ? '0' : $nIdDirEntrega;
            $ref = str_replace(
                [chr(13) . chr(10), chr(9), chr(13)],
                [chr(10), ' ', chr(10)],
                trim($this->request->getVar('sEnvReferencia'))
            );
            $dir = str_replace(
                [chr(13) . chr(10), chr(9), chr(13)],
                [chr(10), ' ', chr(10)],
                trim($this->request->getVar('sEnvDireccion'))
            );
            if ($_SESSION['vtEntrega']['cli']['tipoCli'] == 'P' || $nIdDirEntrega == '0') {
                // si es publico se agrega y lo actualizamos en ventas
                $nIdDirEntrega = $mdlDirEntrega->insert([
                    'nIdCliente' => $_SESSION['vtEntrega']['cli']['id'],
                    'sEnvEntrega' => $this->request->getVar('sEnvEntrega'),
                    'sEnvDireccion' => $dir,
                    'sEnvColonia' => $this->request->getVar('sEnvColonia'),
                    'sEnvTelefono' => $this->request->getVar('sEnvTelefono'),
                    'sEnvReferencia' => $ref
                ]);
                if ($nIdDirEntrega == '0') $nuevaDireccionEnvio = true;
            } else {
                $regDir = $mdlDirEntrega->where('nIdDirEntrega', $nIdDirEntrega)
                    ->first();
                if (
                    trim($regDir['sEnvEntrega']) != trim($this->request->getVar('sEnvEntrega')) ||
                    trim($regDir['sEnvDireccion']) != $dir ||
                    trim($regDir['sEnvColonia']) != trim($this->request->getVar('sEnvColonia')) ||
                    trim($regDir['sEnvTelefono']) != trim($this->request->getVar('sEnvTelefono')) ||
                    trim($regDir['sEnvReferencia']) != $ref
                ) {
                    $nuevaDireccionEnvio = true;
                }
                if ($nuevaDireccionEnvio) {
                    // si es la primera direccion de envio, guardo la anterior
                    $regEnvDir = $mdlEnvioDirec->where('nIdDirEntrega', $nIdDirEntrega)
                        ->limit(1)->first();
                    if ($regEnvDir == null) {
                        $sDirecEnvioInicial = trim($regDir['sEnvEntrega']) . '||' .
                            trim($regDir['sEnvDireccion']) . '||' .
                            trim($regDir['sEnvColonia']) . '||' .
                            trim($regDir['sEnvTelefono']) . '||' .
                            trim($regDir['sEnvReferencia']);
                        $mdlEnvioDirec->insert([
                            'nIdDirEntrega' => $nIdDirEntrega,
                            'nIdEnvio' => 0,
                            'sDirecEnvio' => $sDirecEnvioInicial
                        ]);
                    }
                }
                $mdlDirEntrega->set([
                    'sEnvEntrega' => $this->request->getVar('sEnvEntrega'),
                    'sEnvDireccion' => $dir,
                    'sEnvColonia' => $this->request->getVar('sEnvColonia'),
                    'sEnvTelefono' => $this->request->getVar('sEnvTelefono'),
                    'sEnvReferencia' => $ref
                ])->where('nIdDirEntrega', $nIdDirEntrega)
                    ->update();
            }
            if ($nuevaDireccionEnvio) {
                $sDirecEnvio = trim($this->request->getVar('sEnvEntrega')) . '||' .
                    $dir . '||' .
                    trim($this->request->getVar('sEnvColonia')) . '||' .
                    trim($this->request->getVar('sEnvTelefono')) . '||' .
                    $ref;
            }
            $mdlVentas->set([
                'bEnvio' => $bEnvio,
                'nIdDirEntrega' => $nIdDirEntrega
            ])
                ->where('nIdVentas', $_SESSION['vtEntrega']['idVenta'])
                ->update();
            $idEnvio = $mdlEnvio->insert([
                'nIdOrigen' => $_SESSION['vtEntrega']['idVenta'],
                'cOrigen' => 'ventas',
                'dSolicitud' => (new DateTime())->format('Y-m-d'),
                'nIdSucursal' => $nIdSucursalEntrega,
                'nIdCliente' => intval($_SESSION['vtEntrega']['cli']['id']),
                'nIdDirEntrega' => $nIdDirEntrega,
                'cEstatus' => '1'
            ], true);
            $_SESSION['nIdEntregaEnEnvio'] = $idEnvio;
            if ($nuevaDireccionEnvio) {
                $mdlEnvioDirec = new EnvioDireccionMdl();
                $mdlEnvioDirec->insert([
                    'nIdDirEntrega' => $nIdDirEntrega,
                    'nIdEnvio' => $idEnvio,
                    'sDirecEnvio' => $sDirecEnvio
                ]);
            }
            foreach ($_SESSION['vtEntrega']['lstArt'] as $v) {
                if ($v[7] <= 0) continue;
                if ($v[12] === false) {
                    $mdlEnvioDet->insert([
                        'nIdEnvio' => $idEnvio,
                        'nIdArticulo' => intval($v[0]),
                        'fCantidad' => $v[7],
                        'fPorRecibir' => $v[7],
                        'fRecibido' => '0',
                        'cModoEnv' => $v[10]
                    ]);
                    $retCompromete = $this->comprometeInv($v[0], '1', $nIdSucursalEntrega, $v[1], $v[4], $v[11], $v[7]);
                    if ($retCompromete['ok'] == '0') return $retCompromete;
                } else {
                    foreach ($v[12] as $vv) {
                        if ($vv[7] <= 0) continue;
                        $mdlEnvioDet->insert([
                            'nIdEnvio' => $idEnvio,
                            'nIdArticulo' => intval($vv[0]),
                            'fCantidad' => $vv[7],
                            'fPorRecibir' => $vv[7],
                            'fRecibido' => '0',
                            'cModoEnv' => $v[10]
                        ]);
                        $retCompromete = $this->comprometeInv($vv[0], '1', $nIdSucursalEntrega, $vv[1], $vv[4], $vv[3], $vv[7]);
                        if ($retCompromete['ok'] == '0') return $retCompromete;
                    }
                }
            }
        }
        if ($cNomEntrega != '') {
            if ($bndNuevoRegEntrega === false) {
                $mdlVentasNomEntrega->insert([
                    'nIdVentas' => $_SESSION['vtEntrega']['idVenta'],
                    'cNomEntrega' => $cNomEntrega
                ]);
            } else {
                $mdlVentasNomEntrega->set([
                    'cNomEntrega' => $cNomEntrega
                ])
                    ->where(
                        'nIdVentas',
                        intval($_SESSION['vtEntrega']['idVenta'])
                    )
                    ->update();
            }
        }
        $bndParaEntregar = false;
        foreach ($_SESSION['vtEntrega']['lstArt'] as $k => $v) {
            $mdlVentasdet->set([
                'nEnvio' => $v[7],
                'nEntrega' => $v[6],
                'nPorEntregar' => $v[8]
            ])->where([
                'nIdVentas' => $_SESSION['vtEntrega']['idVenta'],
                'nIdArticulo' => $v[0]
            ])->update();
            if (round($v[6], 3) > 0) $bndParaEntregar = true;
        }
        $_SESSION['nIdEntregaEnVenta'] = 0;
        if ($bndParaEntregar) {
            $_SESSION['bParaEntrega'] = (intval($nIdSucursalEntrega) == intval($this->nIdSucursal) ? '1' : '2');
            $mdlEntrega = new EntregaMdl();
            $mdlEntregaDet = new EntregaDetalleMdl();
            $idEntrega = $mdlEntrega->insert([
                'dEntrega' => (new DateTime())->format('Y-m-d'),
                'nIdSucursal' => $nIdSucursalEntrega,
                'nIdProveedor' => $_SESSION['vtEntrega']['cli']['id'],
                'nIdOrigen' => $_SESSION['vtEntrega']['idVenta'],
                'cEdoEntrega' => '1'
            ]);
            $_SESSION['nIdEntregaEnVenta'] = $idEntrega;
            foreach ($_SESSION['vtEntrega']['lstArt'] as $v) {
                if ($v[6] <= 0) continue;
                if ($v[12] === false) {
                    $mdlEntregaDet->insert([
                        'nIdEntrega' => $idEntrega,
                        'nIdArticulo' => intval($v[0]),
                        'fCantidad' => $v[6],
                        'fPorRecibir' => $v[6]
                    ]);
                    $retCompromete = $this->comprometeInv($v[0], '0', $nIdSucursalEntrega, $v[1], $v[4], $v[11], $v[6]);
                    if ($retCompromete['ok'] == '0') return $retCompromete;
                } else {
                    foreach ($v[12] as $vv) {
                        if ($vv[6] <= 0) continue;
                        $mdlEntregaDet->insert([
                            'nIdEntrega' => $idEntrega,
                            'nIdArticulo' => intval($vv[0]),
                            'fCantidad' => $vv[6],
                            'fPorRecibir' => $vv[6]
                        ]);
                        $retCompromete = $this->comprometeInv($vv[0], '0', $nIdSucursalEntrega, $vv[1], $vv[4], $vv[3], $vv[6]);
                        if ($retCompromete['ok'] == '0') return $retCompromete;
                    }
                }
            }
        }
        $mdlVentas->db->transComplete();
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'V');
        $_SESSION['lastSurtimiento'] = $_SESSION['vtEntrega']['idVenta'];

        if ($mdlVentas->db->transStatus() === false) {
            return json_encode([
                'ok' => '0',
                'msj' => 'Error: No se pudo guardar los datos, intente de nuevo guardar'
            ]);
        }
        $envioOtraSucursal = ($bEnvio == '1' && $nIdSucursalEntrega != $this->nIdSucursal);
        return json_encode([
            'ok' => '1',
            'id' => $_SESSION['vtEntrega']['idVenta'],
            'imprimir' => ($bndParaEntregar || ($bEnvio == '1')) ? '1' : '0',
            'nIdEntrega' => $_SESSION['nIdEntregaEnVenta'],
            'envioAotraSuc' => $envioOtraSucursal ? '1' : '0'
        ]);
    }

    // NOTA: el parametro modoENV me indica ahora que es envio y no se compromete el inventario.
    //       cuando es entrega se pasa '0' para indicar que si se compromete
    private function comprometeInv($idArt, $modoEnv, $idSuc, $cDes, $nDisponible, $nExistencia, $nCant)
    {
        $mdlInventario = new InventarioMdl();
        $mdlArticulo = new ArticuloMdl();
        $rArt = $mdlArticulo->getRegistros($idArt);
        if ($modoEnv == '0' && $rArt['cSinExistencia'] == '0') {  // si no tiene modo ENV  se valida inventario
            $rInv = $mdlInventario->getArticuloSucursal($idArt, $idSuc, true, true);
            if ($rInv == null) {
                return ['ok' => '0', 'msj' => 'Error: El producto ' . $cDes . ' no existe en el inventario, agregelo en el inventario.'];
            }
            $disponibleEnSuc =  round(floatval($rInv['fExistencia']), 3) - round(floatval($rInv['fComprometido']), 3);
            // if ($disponibleEnSuc < $nDisponible) {
            //     return ['ok' => '0', 'msj' => 'Error: Otro proceso a actualizado la existencia del inventario, debe modificar la entrega de producto'];
            // }
            // leemos el inventario
            $nSobreComprometido = round(floatval($rInv['fSobreComprometido']), 3);
            if ($disponibleEnSuc < $nCant) {
                $nComprometido = round(floatval($rInv['fExistencia']), 3);
                $nSobreComprometido += round($nCant - $disponibleEnSuc, 3);
            } else {
                $nComprometido = round(floatval($rInv['fComprometido']), 3) + $nCant;
            }
            // comprometemos el inventario
            $mdlInventario
                ->set('fComprometido', $nComprometido)
                ->set('fSobreComprometido', $nSobreComprometido)
                ->where([
                    'nIdSucursal' => $idSuc,
                    'nIdArticulo' => intval($idArt)
                ])->update();
        }
        return ['ok' => '1'];
    }

    public function repMayorMenor()
    {
        $this->validaSesion();

        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'));
        $nOrden = $this->request->getVar('nOrden') ?? '1';

        $mdlVenta = new VentasMdl();
        $data = [
            'registros' => $mdlVenta->repVentasMayorMenor($f[0], $f[1], $this->nIdSucursal, 8, $nOrden),
            'pager' => $mdlVenta->pager,
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d')
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/repoMayorMenor', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function generaProdMayorMenor()
    {
        $session = session();
        $f = $this->getFechas();
        $fIni = $f[0]->format('d-m-Y');
        $fFin = $f[1]->format('d-m-Y');
        $mdlVenta = new VentasMdl();
        $registros = $mdlVenta->repVentasMayorMenor($f[0], $f[1], $this->nIdSucursal, false, $this->request->getVar('nOrden') ?? '1');
        //$f[0]->format('Y-m-d')
        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $ws->getCell([1, 1])->setValue('Reporte de Productos de Mayor a Menor')
            ->getStyle([1, 1, 4, 1])
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 18
                ]
            ]);
        $ws->getRowDimension(1)->setRowHeight(26);
        $ws->getCell([1, 2])->setValue(
            'Período: Del ' . $fIni .
                ' al ' . $fFin
        );
        $ws->getCell([1, 3])->setValue(
            'Ordenado por cantidad ' .
                ($this->request->getVar('nOrden') == '1' ? 'Vendida' : 'Facturada')
        );
        $ws->mergeCells([1, 1, 4, 1])->mergeCells([1, 2, 4, 2])->mergeCells([1, 3, 4, 3]);
        $ws->getStyle([1, 1, 4, 3])->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => 'medium',
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true
            ]
        ]);

        $ws->mergeCells([1, 2, 4, 2]);

        $columna = 1;
        $filaTitulo = 4;
        $ws->getCell([$columna++, $filaTitulo])->setValue('Id');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Articulo');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Cant. Vendida');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Cant. Facturada');
        $ws->getStyle([1, 4, 4, 4])->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 12
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true
            ]
        ]);
        $ws->getColumnDimensionByColumn(1)->setWidth(6);
        $ws->getColumnDimensionByColumn(2)->setWidth(40);
        $ws->getColumnDimensionByColumn(3)->setWidth(19);
        $ws->getColumnDimensionByColumn(4)->setWidth(19);

        $fila = 5;
        foreach ($registros as $row) {
            $ws->getCell([1, $fila])->setValue($row['nIdArticulo']);
            $ws->getCell([2, $fila])->setValue($row['sDescripcion']);
            $ws->getCell([3, $fila])->setValue($row['porCant']);
            $ws->getCell([4, $fila])->setValue($row['cantFac']);
            $fila++;
        }

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repmm');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('reportemm.xlsx', $text);
    }

    public function repVentas()
    {
        $this->validaSesion();
        $perfil = $_SESSION['perfilUsuario'];

        $f = initFechasFiltro($this->request->getVar('dFecIni'), $this->request->getVar('dFecFin'));
        if ($perfil == -1) {
            $nSucursal = $this->request->getVar('nIdSucursal') ?? false;
        } else {
            $nSucursal = $this->nIdSucursal;
        }
        $nCliente = $this->request->getVar('nIdCliente') ?? false;

        $mdlVenta = new VentasMdl();
        $data = [
            'registros' => $mdlVenta->getRepoVentas($f[0], $f[1], $nSucursal, $nCliente),
            'pager' => $mdlVenta->pager,
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d'),
            'conSucursal' => ($perfil == -1)
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/repoVentas', $data);
        echo view('templates/footer', $this->dataMenu);
        return;
    }

    public function enviaCorreo()
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp-relay.sendinblue.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'cigfridor@gmail.com';                     //SMTP username
            $mail->Password   = 'bI6tEDfcA3g2JCMh';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('cigfridor@gmail.com', 'Mi empresa');
            $mail->addAddress('cigfridor@hotmail.com');     //Add a recipient
            $mail->addAddress('cigfridor@gmail.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            $mail->addAttachment('assets/archxls.xlsx', 'archivo');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Envio de Factura';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Mensaje enviado';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function cancelaRemision($id, $soloData = '0')
    {
        $this->validaSesion();
        $mdlVenta = new VentasMdl();
        $mdlFacturas = new FacturasMdl();
        $regFac = $mdlFacturas->getFacturasRemision($id, true);
        $_SESSION['cancelaventa'] = [
            'idVenta' => $id,
            'datos' => $mdlVenta->getRegistros($id),
            'detVenta' => $this->calculaImportesVenta($id),
            'impEnRemision' => 0,
            'facturaTimbrada' => (count($regFac) > 0 ? '1' : '0')
        ];
        $mdlCliente = new ClienteMdl();
        $rCli = $mdlCliente->getRegistros($_SESSION['cancelaventa']['datos']['nIdCliente']);
        // se puede cancelar la remision completa cuando:
        //   La remision no se ha aplicado para surtir, 
        // se puede cerrar la remision:
        //   El cliente devuelve producto, pero solo paga lo que recibió.
        //   El cliente devuelve producto porque quiere otro, 
        //    pero paga la remision completa.
        $data = [
            'datos' => $_SESSION['cancelaventa']['datos'],
            'registros' => $_SESSION['cancelaventa']['detVenta'][0],
            'impSumado' => $_SESSION['cancelaventa']['detVenta'][1],
            'facturaTimbrada' => $_SESSION['cancelaventa']['facturaTimbrada'],
            'frmURL' => base_url('remisiones/cancelaRemisionActual/' . $id),
            'tieneComprometido' => $_SESSION['cancelaventa']['detVenta'][2],
            'impPagadoAlChofer' => $_SESSION['cancelaventa']['detVenta'][3],
            'impSaldoAFavor' => $_SESSION['cancelaventa']['detVenta'][4],
            'esPublicoGral' => $rCli['cTipoCliente'] == 'P' ? '1' : '0',
            'cancelaFacturaEspecial' => isset($this->aPermiso['oMarcaFacturaEspecial'])
        ];
        if ($soloData == '1')
            return $data;
        else
            return view('ventas/cancelaremision', $data);
    }

    public function cancelaRemisionActual($idVentas)
    {
        $this->validaSesion();

        $valOpcion = $this->request->getVar('opcion');
        $mdlCliente = new ClienteMdl();
        $regCli = $mdlCliente->getRegistros($_SESSION['cancelaventa']['datos']['nIdCliente']);
        $mdlVentas = new VentasMdl();
        $mdlVentasDet = new VentasDetMdl();
        $mdlSaldoPagAde = new SaldoPagAdelMdl();
        $mdlMovPagoAnticipados = new MovPagAdelMdl();
        $mdlPagAdel = new PagAdelMdl();

        if ($valOpcion == '1' || $valOpcion == '4') {
            $mdlVentas->set(['cEdo' => '5'])
                ->where(['nIdVentas' => $idVentas])
                ->update();
            $mdlVentaCancel = new VentasCanceladasMdl();
            $mdlVentaCancel->insert([
                'nIdVentas' => $idVentas,
                'dtFecCancelacion' => (new DateTime())->format('Y-m-d'),
                'sMotivo' => $this->request->getVar('sMotivo')
            ]);
            $mdlSaldoVenta = new VentasSaldoMdl();
            $mdlSaldoVenta->where('nIdVentas', $idVentas)->delete();
            if ($regCli['cTipoCliente'] != 'P') {
                $regs = $mdlMovPagoAnticipados->getMovtosPorVentas($idVentas);
                foreach ($regs as $r) {
                    // saldo del cliente, saldo pago
                    $mdlCliente->actualizaSaldo($r['nIdCliente'], $r['nImporte']);
                    $rSal = $mdlSaldoPagAde->getIdSaldo($r['nIdPagosAdelantados']);
                    if ($rSal === null) {
                        $mdlSaldoPagAde->recuperaSaldoPorCancelacion($r['nImporte'], $r['nIdPagosAdelantados']);
                    } else {
                        $mdlSaldoPagAde->recuperaSaldoPorCancelacion($r['nImporte'], $r['nIdPagosAdelantados'], $rSal['nIdSaldoPagosAdelantados']);
                    }
                }
            }
            if ($valOpcion == '4') {
                // se guarda el registro en una tabla nueva : idVentas, fecha de marca de cancelacion.
                // y ese voy a usar para el reporte.
                $mdlFacEsp = new FacturaEspecialMdl();
                $mdlFacEsp->insert(['nIdVentas' => $idVentas]);
            }
        } elseif ($valOpcion == '2') {
            $arrRegVenta = $mdlVentas->select('nTotal')
                ->where(['nIdVentas' => $idVentas])->find();
            $importeOriginalRemision = round(floatval($arrRegVenta[0]['nTotal']), 2);
            $importeNuevoRemision = round(floatval($_SESSION['cancelaventa']['detVenta'][3]), 2);
            $mdlSaldoVenta = new VentasSaldoMdl();
            $regSaldoVenta = $mdlSaldoVenta->where('nIdVentas', $idVentas)->first();
            if ($regSaldoVenta !== null) {
                $saldoActualRemision = round(floatval($regSaldoVenta['nSaldo']), 2);
                $importePagado = $importeOriginalRemision - $saldoActualRemision;
                $saldoNuevo = round($importeNuevoRemision - $importePagado, 2);
                $aset = [];
                if ($saldoNuevo == 0) {
                    $aset['dFecSaldado'] = (new DateTime())->format('Y-m-d');
                }
                $aset['nSaldo'] = $saldoNuevo;
                $mdlSaldoVenta->set($aset)->where('nIdVentasSaldo', $regSaldoVenta['nIdVentasSaldo'])->update();

                // 'impPagadoAlChofer' => $_SESSION['cancelaventa']['detVenta'][3],
            }
            $mdlVentas->set(['cEdo' => '7', 'nTotal' => $_SESSION['cancelaventa']['detVenta'][3]])
                ->where(['nIdVentas' => $idVentas])
                ->update();
            $mdlVentasDet->set('nCant', 'nCant - nPorEntregar', false)
                ->where(['nIdVentas' => $idVentas])
                ->update();
            if ($regCli['cTipoCliente'] != 'P') {
                $nSaldoImporte = round($_SESSION['cancelaventa']['detVenta'][3], 2);
                $regs = $mdlMovPagoAnticipados->getMovtosPorVentas($idVentas);
                foreach ($regs as $r) {
                    $impMovto = round(floatval($r['nImporte']), 2);
                    if ($nSaldoImporte >= $impMovto) {
                        $impArecuperar = $impMovto;
                    } else {
                        $impArecuperar = $nSaldoImporte;
                    }
                    $impArecuperar = round($impArecuperar, 2);
                    // saldo del cliente, saldo pago
                    $mdlCliente->actualizaSaldo($r['nIdCliente'], $impArecuperar);
                    $rSal = $mdlSaldoPagAde->getIdSaldo($r['nIdPagosAdelantados']);
                    if ($rSal === null) {
                        $mdlSaldoPagAde->recuperaSaldoPorCancelacion($impArecuperar, $r['nIdPagosAdelantados']);
                    } else {
                        $mdlSaldoPagAde->recuperaSaldoPorCancelacion($impArecuperar, $r['nIdPagosAdelantados'], $rSal['nIdSaldoPagosAdelantados']);
                    }
                    $nSaldoImporte -= $impArecuperar;
                    if (round($nSaldoImporte, 2) == 0) break;
                }
            }
        } elseif ($valOpcion == '3') {
            $mdlPagAdel->db->transStart();
            $mdlVentas->set(['cEdo' => '7'])
                ->where(['nIdVentas' => $idVentas])
                ->update();
            if ($regCli['cTipoCliente'] != 'P') {
                // 'impSaldoAFavor' => $_SESSION['cancelaventa']['detVenta'][4]
                $nImporteAFavor = round($_SESSION['cancelaventa']['detVenta'][4], 2);

                $nIdPagAdel = $mdlPagAdel->insert([
                    'nIdCliente' => $regCli['nIdCliente'],
                    'nImporte' => $nImporteAFavor,
                    'nIdSucursal' => $_SESSION['cancelaventa']['datos']['nIdSucursal'],
                    'sObservaciones' => 'Saldo generado automaticamente al CERRAR ' .
                        'la remision con folio ' .
                        $_SESSION['cancelaventa']['datos']['nFolioRemision'],
                ]);
                $mdlCliente->actualizaSaldo(
                    $regCli['nIdCliente'],
                    $nImporteAFavor
                );
                $idSaldo = $mdlSaldoPagAde->getIdVacioSaldoDeposito();
                $mdlSaldoPagAde->guardaSaldoDeposito(
                    $nIdPagAdel,
                    $nImporteAFavor,
                    $idSaldo
                );
            }
            $mdlPagAdel->db->transComplete();
            if ($mdlPagAdel->db->transStatus() === false) {
                // generate an error... or use the log_message() function to log your error
                return json_encode(['ok' => '0', 'msj' => 'No se pudo concluir la transacción, intente de nuevo']);
            }
        }
        $mdlUsuario = new UsuarioMdl();
        $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'CR');
        return json_encode(['ok' => '1']);
    }

    /**
     * @return array [$arrVenta, $sumImportes, $tieneComprometido, $sumImportesEntregado, $sumImportesSaldoFavor]
     */
    private function calculaImportesVenta($nIdVentaParaDividirEnFacturas)
    {
        $arrVenta = [];
        $mdlVentaDet = new VentasDetMdl();
        $sumImportes = 0;
        $sumImportesEntregado = 0;
        $sumImportesSaldoFavor = 0;

        $aRegDet = $mdlVentaDet->getDetalleVenta($nIdVentaParaDividirEnFacturas);
        $nRegistros = count($aRegDet);
        $tieneComprometido = '0';
        for ($nInd = 0; $nInd < $nRegistros; $nInd++) {
            $nCant = round(floatval($aRegDet[$nInd]['nCant']), 2);
            // cantidad de lo entregado
            $nCant2 = round(floatval($aRegDet[$nInd]['nCant']) - floatval($aRegDet[$nInd]['nPorEntregar']), 2);
            // cantidad del saldo a favor (porque se pago la remision completa)
            $nCant3 = round(floatval($aRegDet[$nInd]['nPorEntregar']), 2);
            $paraSurtir = round(floatval($aRegDet[$nInd]['nPorEntregar']), 2);
            $nPrecio = round(floatval($aRegDet[$nInd]['nPrecio']), 2);

            $nImporte = round($nCant * $nPrecio, 2);

            $nDescuento = round(floatval($aRegDet[$nInd]['nDescuentoTotal']), 2);      // entra descuento por producto y por remision juntos
            $nImporteFinalProducto = $nImporte - $nDescuento;

            $nPrecioFinalXproducto = round($nImporteFinalProducto / $nCant, 6);

            $aRegDet[$nInd]['precioUniNew'] = $nPrecioFinalXproducto;
            $arrVenta[$aRegDet[$nInd]['nIdArticulo']] = [
                'id' => $aRegDet[$nInd]['nIdArticulo'],
                'des' => $aRegDet[$nInd]['nomArt'],
                'cant' => $nCant,
                'porsurtir' => $paraSurtir,
                'precio' => $nPrecioFinalXproducto,
                'idVentasDet' => $aRegDet[$nInd]['nIdVentasDet'],
                'imp' => round($nPrecioFinalXproducto * $nCant, 2),
                'capCant' => 0
            ];
            if (($nCant - $paraSurtir) > 0) $tieneComprometido = '1';
            $sumImportes += round($nPrecioFinalXproducto * $nCant, 2);
            $sumImportesEntregado += round($nPrecioFinalXproducto * $nCant2, 2);
            $sumImportesSaldoFavor += round($nPrecioFinalXproducto * $nCant3, 2);
        }
        return [$arrVenta, $sumImportes, $tieneComprometido, $sumImportesEntregado, $sumImportesSaldoFavor];
    }

    public function cambiausocfdi($idVentas, $idFacturas)
    {
        $this->validaSesion();
        $mdlUsocfdi = new UsoCfdiMdl();
        $mdlVentas = new VentasMdl();
        $mdlFacturas = new FacturasMdl();
        $mdlUsuario = new UsuarioMdl();
        $mdlFacturasRela = new FacturasRelaMdl();
        $mdlCliente = new ClienteMdl();
        $mdlFacturasNomPub = new FacturasNomPubMdl();
        $mdlTipoRelacion = new TipoRelacionMdl();
        $mdlVentasPago = new VentasPagoMdl();
        $mdlTipoPago = new TipoPagoMdl();

        $rv = $mdlVentas->getRegistros($idVentas);
        $rvp = $mdlVentasPago->getRegistros($idVentas);
        $noEfectivoAnticipado = false;
        foreach ($rvp as $p) {
            if ($p['nIdTipoPago'] != '6') {
                $noEfectivoAnticipado = true;
                break;
            }
        }
        $rc = $mdlCliente->where('nIdCliente', $rv['nIdCliente'])->first();
        $fac = $mdlFacturas->getRegistros($idVentas);
        $numFacturas = $mdlFacturas->selectCount('nIdVentas')->where('nIdVentas', $idVentas)->first();
        $bTieneVariasFacturas = intval($numFacturas['nIdVentas']) > 1 ? '1' : '0';
        $arel = $mdlFacturasRela->where('nIdFacturas', ($idFacturas ? $idFacturas : 0))->findAll();
        $sUUid = '';
        foreach ($arel as $k => $v) {
            $sUUid .= $v['sUUID'] . "\n";
        }
        $regUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        $rTmp = $mdlFacturasNomPub->where('nIdVentas', $idVentas)->first();
        $rNombrePublico = $rTmp == null ? '' : $rTmp['sNombre'];

        $data = [
            'folRemision' => $rv['nFolioRemision'],
            'lstUso' => $mdlUsocfdi->getRegistros(false, false, null, null, true),
            'lstFormaPago' => [
                ['cPPD' => 'PUE', 'sDescripcion' => 'PUE (Una Sola Exhibicion)'],
                ['cPPD' => 'PPD', 'sDescripcion' => 'PPD (En Parcialidades)']
            ],  //bOtroNombrePub
            'lstTipoRel' => $mdlTipoRelacion->getRegistros(false, true),
            'lstTipoPago' => $mdlTipoPago->getRegistros(false, false, '4,5,2,3'),
            'registro' => [
                'cIdUsoCfdi' => $this->request->getVar('cIdUsoCfdi') ?? $fac['cUsoCFDI'],
                'cIdTipoRelacion' => $this->request->getVar('cIdTipoRelacion') ?? $fac['cIdTipoRelacion'],
                'sClienteUso' => $this->request->getVar('sClienteUso') ?? $rv['sNombre'],
                'idClienteUso' => $this->request->getVar('idClienteUso') ?? $rv['nIdCliente'],
                'cTipoCliente' => $this->request->getVar('cTipoCliente') ?? $rc['cTipoCliente'],
                'dFechaFactura' => $this->request->getVar('dFechaFactura') ?? (new DateTime($fac['dFechaFactura']))->format('Y-m-d'),
                'cUUIDrelacionado' => $this->request->getVar('cUUIDrelacionado') ?? $sUUid,
                'bDividirEnVarias' => $this->request->getVar('bDividirEnVarias') ?? $rv['bVariasFacturas'],
                'cPPD' => $this->request->getVar('cPPD') ??  $fac['cMetodoPago'],
                'cNombrePublico' => $this->request->getVar('cNombrePublico') ?? $rNombrePublico,
                'nIdTipoPago' => $this->request->getVar('nIdTipoPago') ?? $fac['nIdTipoPago']
            ],
            'esPublicoGral' => $rc['cTipoCliente'] == 'P' ? '1' : '0',
            'url' => base_url('remisiones/cambiausocfdi/' . $idVentas . '/' . ($idFacturas ? $idFacturas : '0')),
            'bCambioFecha' => $regUsu['bCambioFechaFactura'],
            'bCambioCliente' => $regUsu['bCambioCliente'],
            'bTieneVariasFacturas' => $bTieneVariasFacturas,
            'modo' => 'E',
            'soloEfectivoAnticipado' => !$noEfectivoAnticipado
        ];
        array_splice($data['lstTipoRel'], 0, 0, [['cIdTipoRelacion' => '0', 'sDescripcion' => 'No Aplica']]);
        array_splice($data['lstTipoPago'], 0, 0, [['nIdTipoPago' => '0', 'sDescripcion' => 'Sin Cambio']]);

        if ($this->request->getMethod() == 'post') {
            $aErr = [];
            $a = [];
            $b = [];
            $c = [];
            $d = [];

            if ($regUsu['bCambioFechaFactura'] == '1') {
                $fecha = $this->request->getVar('dFechaFactura') ?? '';
                if ($fecha == '') {
                    $aErr['dFechaFactura'] = 'Debe asignar una fecha de factura';
                } else {
                    $a['dFechaFactura'] = $fecha;
                }
            }
            $uso = $this->request->getVar('cIdUsoCfdi') ?? '0';
            if ($uso == '-1') {
                $aErr['cIdUsoCfdi'] = 'Debe seleccionar un Uso de CFDI';
            } else {
                $a['cUsoCFDI'] = $uso;
            }
            $cPPD = $this->request->getVar('cPPD') ?? 'PUE';
            if ($cPPD == '-1') {
                $aErr['cPPD'] = 'Debe seleccionar el metodo de pago.';
            } else {
                $a['cMetodoPago'] = $cPPD;
            }
            $nTipoPago = $this->request->getVar('nIdTipoPago') ?? '0';
            if ($nTipoPago == '-1') {
                $aErr['nIdTipoPago'] = 'Debe seleccionar el tipo de pago.';
            } else {
                $a['nIdTipoPago'] = $nTipoPago;
            }

            if ($regUsu['bCambioCliente'] == '1') {
                $cliente = trim($this->request->getVar('idClienteUso') ?? '0');
                if ($cliente == '0' || $cliente == '') {
                    $aErr['sClienteUso'] = 'Debe seleccionar un cliente';
                } else {
                    $c['nIdCliente'] = $cliente;
                }

                $tipoCliente = ($this->request->getVar('cTipoCliente') ?? '');
                if ($tipoCliente == 'P') {
                    $d['sNombre'] = trim($this->request->getVar('cNombrePublico') ?? '');
                }
            }

            $tiporel = $this->request->getVar('cIdTipoRelacion') ?? '0';
            $tiporel = ($tiporel == '-1' ? '0' : $tiporel);
            $a['cIdTipoRelacion'] = $tiporel;
            $aRel = [];
            if ($tiporel != '0') {
                $relacionUUID = trim($this->request->getVar('cUUIDrelacionado') ?? '');
                $arr = explode(',', str_replace(["\r\n", "\n", "\r"], ',', $relacionUUID));
                $msjAr = '';
                foreach ($arr as $k => $v) {
                    if (strlen(trim($v)) == 0) continue;
                    if (strlen(trim($v)) < 35) {
                        $msjAr .= 'El UUID ' . $v . ' no es valido. ';
                    }
                    $aRel[] = $v;
                }
                if (count($aRel) == 0) $msjAr = 'No se ha agregado ningun UUID.';
                if ($msjAr != '') $aErr['cUUIDrelacionado'] = $msjAr;
            }

            if (count($aErr) > 0) {
                $data['error'] = $aErr;
            } else {
                $mdlFacturas->where('nIdVentas', $idVentas)
                    ->set($a)
                    ->update();
                $mdlFacturasRela->where('nIdFacturas', $idFacturas)->delete();
                if (count($aRel) > 0) {
                    // se agregan las facturas relacionadas.
                    foreach ($aRel as $k => $v) {
                        $mdlFacturasRela->insert([
                            'nIdFacturas' => $idFacturas,
                            'sUUID' => $v
                        ]);
                    }
                }
                if ($regUsu['bCambioCliente'] == '1') {
                    if (count($c) > 0) {
                        $mdlVentas->update($idVentas, [
                            'nIdCliente' => $c['nIdCliente']
                        ]);
                    }
                    if (count($d) > 0) {
                        $regTmp = $mdlFacturasNomPub->where('nIdVentas', $idVentas)->first();
                        if ($d['sNombre'] == '') {
                            if ($regTmp != null) $mdlFacturasNomPub->delete($idVentas);
                        } else {
                            if ($regTmp == null)
                                $mdlFacturasNomPub->insert([
                                    'nIdVentas' => $idVentas,
                                    'sNombre' => $d['sNombre']
                                ]);
                            else
                                $mdlFacturasNomPub->where('nIdVentas', $idVentas)->update([
                                    'sNombre' => $d['sNombre']
                                ]);
                        }
                    }
                }
                if ($bTieneVariasFacturas == '0') {
                    $bDividirFacturas = trim($this->request->getVar('bDividirEnVarias') ?? '') == '1' ? '1' : '0';
                    $mdlVentas->update($idVentas, [
                        'bVariasFacturas' => $bDividirFacturas
                    ]);
                }

                $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'CFF');
                $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'CCLI');
                echo 'oK';
                return;
            }
        }
        echo view('ventas/cambiausocfdi', $data);
        return;
    }

    public function productoAsaldoAfavor($idRemision = false, $guardar = '0')
    {
        $this->validaSesion();

        $data = [];

        if ($this->request->getMethod() == 'post') {
            if ($guardar == '0') {
                $mdlVenta = new VentasMdl();
                $mdlVentaD = new VentasDetMdl();
                $mdlCliente = new ClienteMdl();

                $rVenta = $mdlVenta->where(['nIdSucursal' => $this->nIdSucursal, 'nFolioRemision' => $idRemision])
                    ->first();
                if ($rVenta == null) return 'noOkNo se encuentra el folio en esta sucursal.';
                if ($rVenta['cEdo'] == '5') return 'noOkLa remision ya esta cancelada.';
                // se valida : 
                // si esta cancelada
                // si tiene producto pendiente de asignar
                $rDet = $mdlVentaD->select('nIdArticulo, nPorEntregar')->where([
                    'nIdVentas' => $rVenta['nIdVentas'],
                    'nPorEntregar >' => 0
                ])->findAll();
                if (count($rDet) == 0) return 'noOkLa remision seleccionada no tiene producto pendiente de asignar.';
                $rCliente = $mdlCliente->where('nIdCliente', $rVenta['nIdCliente'])->first();
                if ($rCliente['cTipoCliente'] == 'P') return 'noOkNo se puede asignar saldo a favor a publico general.';
                $data = $this->cancelaRemision($rVenta['nIdVentas'], '1');
                $data['frmURL'] = base_url('remisiones/productoAsaldoAfavor/' . $idRemision . '/1');
                echo view('ventas/productoasaldoafavormtto', $data);
                return;
            } else {
                $mdlCliente = new ClienteMdl();
                $regCli = $mdlCliente->getRegistros($_SESSION['cancelaventa']['datos']['nIdCliente']);
                $idVentas = $_SESSION['cancelaventa']['datos']['nIdVentas'];
                $mdlVentas = new VentasMdl();
                $mdlVentasDet = new VentasDetMdl();
                $mdlSaldoPagAde = new SaldoPagAdelMdl();
                $mdlMovPagoAnticipados = new MovPagAdelMdl();
                $mdlPagAdel = new PagAdelMdl();
                $mdlVentasDetsaf = new VentasDetSafMdl();

                $lista = $this->request->getVar('lista');
                foreach ($lista as $key => $value) {
                    if ($value['capCant'] <= 0) continue;
                    $mdlVentasDet->builder()->set('nPorEntregar', 'nPorEntregar - ' . $value['capCant'], false)
                        ->where('nIdVentasDet', intval($value['idVentasDet']))->update();
                    $mdlVentasDetsaf->insert([
                        'nIdVentasDet' => $value['idVentasDet'],
                        'dtAlta' => (new DateTime())->format('Y-m-d H:i:s'),
                        'nCantParaSaldo' => $value['capCant']
                    ]);
                }
                $nImporteAFavor = round(floatval($this->request->getVar('importesaldo')), 2);

                $mdlPagAdel->db->transStart();

                $nIdPagAdel = $mdlPagAdel->insert([
                    'nIdCliente' => $regCli['nIdCliente'],
                    'nImporte' => $nImporteAFavor,
                    'nIdSucursal' => $_SESSION['cancelaventa']['datos']['nIdSucursal'],
                    'sObservaciones' => 'Saldo generado automaticamente al MODIFICAR ' .
                        'la remision con folio ' .
                        $_SESSION['cancelaventa']['datos']['nFolioRemision'],
                ]);
                $mdlCliente->actualizaSaldo(
                    $regCli['nIdCliente'],
                    $nImporteAFavor
                );
                $idSaldo = $mdlSaldoPagAde->getIdVacioSaldoDeposito();
                $mdlSaldoPagAde->guardaSaldoDeposito(
                    $nIdPagAdel,
                    $nImporteAFavor,
                    $idSaldo
                );
                $mdlPagAdel->db->transComplete();
                if ($mdlPagAdel->db->transStatus() === false) {
                    // generate an error... or use the log_message() function to log your error
                    return json_encode(['ok' => '0', 'msj' => 'No se pudo concluir la transacción, intente de nuevo']);
                }
                return json_encode(['ok' => '1']);
            }
        }

        echo view('templates/header', $this->dataMenu);
        echo view('ventas/productoasaldoafavor', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function entregaModEnv($idVenta)
    {
        $this->validaSesion();
        $mdlVentas = new VentasMdl();
        $mdlVentasD = new VentasDetMdl();

        $data = [
            'frmURL' => base_url('remisiones/entregaModEnv/' . $idVenta),
        ];

        if ($this->request->getMethod() == 'post') {
            $mdlUsuario = new UsuarioMdl();
            // solo se cierra y se da por entregado
            $mdlVentasDet = new VentasDetMdl();
            $mdlVentas = new VentasMdl();
            $mdlVentasDet->where('nIdVentas', $idVenta)
                ->builder()
                ->set('nPorEntregar', 0)
                ->set('nEntregado', 'vtventasdet.nCant', false)
                ->update();
            $mdlVentas->where('nIdVentas', $idVenta)
                ->update(null, [
                    'cEdo' => '7',
                ]);
            $mdlUsuario->activaPermiso($this->nIdUsuario, 0, 'EME');
            echo 'ok';
            return;
        } else {
            // leemos los datos
            $data['registro'] = $mdlVentas->getRegistros($idVenta);

            $data['registros'] = $mdlVentasD->getDetalleVenta($idVenta);
            if ($data['registro']['cEdoEntrega'] > 0 || $data['registro']['cEdoEnvio'] > 0) $data['err'] = 'La remision no debe tener capturado ningun tipo de entrega o envio. Debe cancelar la entrega y/o envio.';
        }

        echo view('ventas/entregamodenv', $data);
        return;
    }

    private function corrigepreciotapado()
    {
        $this->validaSesion();
        $mdlVentas = new VentasMdl();
        $mdlVentasDet = new VentasDetMdl();
        $mdlPrecio = new PrecioArticuloMdl(); // precio de ventas
        $mdlVentasDetAux = new VentasDetAuxMdl();
        // selecccino que detalles estan mal para agregar nIdVentasDet
        $regs = $mdlVentas->db->query(
            'SELECT b.nIdFacturas, ax.nImporteTapadoFactura, c.nIdVentasDet, ' .
                ' c.nIdArticulo, c.nCant, a.nIdSucursal, cl.nIdTipoLista, c.nPrecio ' .
                ' FROM vtventas a' .
                ' INNER JOIN vtventasdet c ON a.nIdVentas = c.nIdVentas' .
                ' LEFT JOIN vtventasdetaux ax ON c.nIdVentasDet = ax.nIdVentasDet' .
                ' LEFT JOIN vtfacturas b ON a.nIdVentas = b.nIdVentas' .
                ' INNER JOIN alarticulo f ON c.nIdArticulo = f.nIdArticulo' .
                ' INNER JOIN vtventaspago p ON a.nIdVentas = p.nIdVentas' .
                ' INNER JOIN vttipopago tp ON p.nIdTipoPago = tp.nIdTipoPago' .
                ' INNER JOIN vtcliente cl ON a.nIdCliente = cl.nIdCliente' .
                ' WHERE a.dtAlta >= \'2024-05-21\' AND a.nIdCliente <> 1' .
                ' AND f.nIdArtClasificacion = 2' .
                ' HAVING b.nIdFacturas IS NOT NULL AND ax.nImporteTapadoFactura IS NULL' .
                ' ORDER BY a.nIdSucursal, a.nIdVentas'

        )->getResultArray();

        foreach ($regs as $v) {
            $p = $mdlPrecio->buscaPrecio(
                $v['nIdSucursal'],
                $v['nIdTipoLista'],
                $v['nIdArticulo'],
                $v['nCant'],
            );

            $mdlVentasDetAux->insert([
                'nIdVentasDet' => $v['nIdVentasDet'],
                'nImpComisionTotal' => 0,
                'nImpDescuentoProd' => 0,
                'nImpDescuentoGral' => 0,
                'nImporteTapadoFactura' => $p['fPrecioTapado'] * $v['nCant'],
            ]);
        }

        echo 'Registros insertados ' . count($regs);
        echo 'terminado';
    }
}
