<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\ArtClasificacionMdl;
use App\Models\Catalogos\SucursalMdl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Articulo extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new ArticuloMdl();

        $data['registros'] =  $model->getRegistros(
            false,
            true,
            true,
            $this->request->getVar('sDescri') ?? false
        );
        $data['pager'] = $model->pager;
        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/articulos', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function inventario()
    {
        $this->validaSesion();
        $model = new InventarioMdl();
        $idsucursal = $this->request->getVar('nIdSucursal') ?? $this->nIdSucursal;
        $data['registros'] =   $model->getExistencias(
            $idsucursal,
            8,
            $this->request->getVar('sDescri') ?? false,
            $this->request->getVar('flchkcomprometido') ?? false
        );
        $data['pager'] = $model->pager;
        $mdlSucursal = new SucursalMdl();
        $data['regSucursales'] = $mdlSucursal->getRegistros();
        $data['nIdSucursalActual'] = $idsucursal;
        echo view('templates/header', $this->dataMenu);
        echo view('almacen/inventario', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function accion($tipoaccion, $id = 0)
    {

        $model = new ArticuloMdl();

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo[$tipoaccion] ?? 'Ver';

        $data = [
            'titulo' => $stitulo . ' Articulo',
            'frmURL' => base_url('articulo/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'param1' => $tipoaccion,
            'param2' => $id,
        ];

        if ($this->request->getMethod() == 'post') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos($tipoaccion, $id);
            if ($a === false) {
                $bSinExistencia = ($this->request->getVar('cSinExistencia') ?? false) == 'on' ? '1' : '0';
                $bImporteManual = ($this->request->getVar('cImporteManual') ?? false) == 'on' ? '1' : '0';
                $cUsaPrecioTapado = ($this->request->getVar('cPrecioTapadoFactura') ?? false) == 'on' ? '1' : '0';
                $cConArticuloRelacionado = ($this->request->getVar('cConArticuloRelacionado') ?? false) == 'on' ? '1' : '0';
                $cConDescripcionAdicional = ($this->request->getVar('cConDescripcionAdicional') ?? false) == 'on' ? '1' : '0';
                $r = [
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'sCodigo' => trim($this->request->getVar('sCodigo') ?? ''),
                    'nIdArtClasificacion' => $this->request->getVar('nIdArtClasificacion'),
                    'sCveProdSer' => $this->request->getVar('sCveProdSer'),
                    'sCveUnidad' => $this->request->getVar('sCveUnidad'),
                    'cUnidad' => $this->request->getVar('cUnidad'),
                    'fPeso' => $this->request->getVar('fPeso'),
                    'nCosto' => $this->request->getVar('nCosto'),
                    'cSinExistencia' => $bSinExistencia,
                    'cImporteManual' => $bImporteManual,
                    'cConArticuloRelacionado' => $cConArticuloRelacionado,
                    'cConDescripcionAdicional' => $cConDescripcionAdicional,
                    'nIdArticuloAcumulador' => ($cConArticuloRelacionado == '1' ? '' : $this->request->getVar('nIdArticuloAcumulador')),
                    'nMedida' => $this->request->getVar('nMedida'),
                    'cPrecioTapadoFactura' => $cUsaPrecioTapado,
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdArticulo'] = $id;
                }
                $model->save($r);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $model->getRegistros($id);
            }
        }
        $mdlClasificacion = new ArtClasificacionMdl();
        $data['regClasificacion'] = $mdlClasificacion->getRegistros();
        echo view('catalogos/articulomtto', $data);
    }

    public function validaCampos($tipoaccion, $id)
    {

        $cConArticuloRelacionado = ($this->request->getVar('cConArticuloRelacionado') ?? false) == 'on' ? '1' : '0';
        $nid = $this->request->getVar('nIdArticuloAcumulador') ?? '';
        $nMedida = $this->request->getVar('nMedida') ?? '';
        $arMsj = [];
        if ($cConArticuloRelacionado == '1') {
            if ($nid != '' && $nid != '0') {
                $arMsj['nIdArticuloAcumulador'] = 'Este campo debe ir vacio si está marcado "Tiene articulos seleccionados"';
            }
            if ($nMedida != '' && $nMedida != '0') {
                $arMsj['nMedida'] = 'Este campo debe ir vacio si está marcado "Tiene articulos seleccionados"';
            }
        }
        // validamos el campo codigo
        $cod = trim($this->request->getVar('sCodigo') ?? '');
        if ($cod != '') {
            $mdlArticulo = new ArticuloMdl();
            if ($tipoaccion == 'a')
                $reg = $mdlArticulo->getRegistrosPorCod($cod);
            else
                $reg = $mdlArticulo->getRegistrosPorCod($cod, $id);
            if ($reg !== null) {
                $arMsj['sCodigo'] = 'Ya existe otro articulo con el mismo codigo';
            }
        }
        // validamos el campo descripcion
        $sDes = trim($this->request->getVar('sDescripcion') ?? '');
        if ($sDes != '') {
            $mdlArticulo = new ArticuloMdl();
            if ($tipoaccion == 'a')
                $reg = $mdlArticulo->getRegistrosPorDescri($sDes);
            else
                $reg = $mdlArticulo->getRegistrosPorDescri($sDes, $id);
            if ($reg !== null) {
                $arMsj['sDescripcion'] = 'Ya existe otro articulo con la misma descripcion';
            }
        }

        $reglas = [
            'sDescripcion' => 'required|min_length[3]',
            'nIdArtClasificacion' => 'required|greater_than_equal_to[0]'
        ];
        $reglasMsj = [
            'sDescripcion' => [
                'required'   => 'Falta la descripcion',
                'min_length' => 'Debe tener 3 caracteres como minimo'
            ],
            'nIdArtClasificacion' => [
                'required' => 'Falta la clasificacion del producto',
                'greater_than_equal_to' => 'Falta la clasificacion del producto'
            ]
        ];

        if (!$this->validate($reglas, $reglasMsj) || count($arMsj) > 0) {
            $arr = $this->validator->getErrors();
            foreach ($arMsj as $k => $v) {
                if (isset($arr[$k])) {
                    $arr[$k] = $arr[$k] . '. ' . $v;
                } else {
                    $arr[$k] = $v;
                }
            }
            return ['amsj' => $arr];
        }
        return false;
    }

    public function leeRegistro($id, $esc = false, $porCodigo = false)
    {
        $model = new ArticuloMdl();
        if ($porCodigo)
            $reg =  $model->getRegistrosPorCod($id);
        else
            $reg =  $model->getRegistros($id);
        if ($reg == null) {
            return json_encode(['ok' => '0']);
        }
        if ($esc) $reg['sDescripcion'] = esc($reg['sDescripcion']);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion)
    {
        $model = new ArticuloMdl();
        $reg =  $model->getRegistrosByName($sDescripcion);
        $t = count($reg);
        for ($i = 0; $i < $t; $i++) {
            $reg[$i]['sDescripcion'] = esc($reg[$i]['sDescripcion']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function exportaXLSinventario()
    {
        $this->validaSesion();

        $model = new InventarioMdl();
        $idsucursal = $this->request->getVar('nIdSucursal') ?? $this->nIdSucursal;
        $registros =   $model->getExistencias(
            $idsucursal,
            false,
            $this->request->getVar('sDescri') ?? false,
            $this->request->getVar('flchkcomprometido') ?? false
        );
        $mdlSucursal = new SucursalMdl();
        $rSucursal = $mdlSucursal->getRegistros($idsucursal);
        $bComprometido = $this->request->getVar('flchkcomprometido') ?? false;

        $wb = new Spreadsheet();
        if ($bComprometido !== 'on')
            $this->xlsxExistencias($wb, $registros, $rSucursal);
        else
            $this->xlsxComprometidos($wb, $registros, $rSucursal);

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'repca');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('repoInventario.xlsx', $text);
    }

    private function xlsxExistencias(Spreadsheet $wb, $registros, $rSucursal)
    {
        $ws = $wb->getActiveSheet();
        $ws->getCell([1, 1])->setValue('Reporte de Existencias')
            ->getStyle([1, 1, 6, 1])
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 18
                ]
            ]);

        $ws->getRowDimension(1)->setRowHeight(26);
        $ws->getCell([1, 2])->setValue($rSucursal['sDescripcion']);
        $ws->getCell([1, 3])->setValue('Contiene en descripcion: ' . (empty($this->request->getVar('sDescri') ?? '') ? 'Todos los articulos' : $this->request->getVar('sDescri')));

        $ws->mergeCells([1, 1, 6, 1])->mergeCells([1, 2, 6, 2])->mergeCells([1, 3, 6, 3]);
        $ws->getStyle([1, 1, 6, 3])->applyFromArray([
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

        $columna = 1;
        $filaTitulo = 4;
        $ws->getCell([$columna++, $filaTitulo])->setValue('Id');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Descripcion');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Código');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Existencia');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Comprometido');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Sobrecomprometido');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Disponible');
        $ws->getStyle([1, 4, $columna - 1, 4])->applyFromArray([
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

        $fila = 5;
        foreach ($registros as $row) {
            $ws->getCell([1, $fila])->setValue($row['nIdArticulo']);
            $ws->getCell([2, $fila])->setValue($row['nomArt']);
            $ws->getCell([3, $fila])->setValue($row['codArt']);
            $ws->getCell([4, $fila])->setValue(round(floatval($row['fExistencia']), 2));
            $ws->getCell([5, $fila])->setValue(round(floatval($row['fComprometido']), 2));
            $ws->getCell([6, $fila])->setValue(round(floatval($row['fSobreComprometido']), 2));
            $ws->getCell([7, $fila])->setValue(round(floatval($row['fExistencia']) - floatval($row['fComprometido']), 2));
            $fila++;
        }
        $ws->getStyle([3, 5, 3, $fila])->getNumberFormat()->setFormatCode('@');
        $ws->getStyle([3, 5, 6, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([3, 5, 6, $fila])->getAlignment()->setHorizontal('right');

        $ws->getColumnDimensionByColumn(1)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(2)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(3)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(4)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(5)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(6)->setAutoSize(true);
    }

    private function xlsxComprometidos(Spreadsheet $wb, $registros, $rSucursal)
    {
        $objFecha = new \PhpOffice\PhpSpreadsheet\Shared\Date();
        $ws = $wb->getActiveSheet();

        $ws->getCell([1, 1])->setValue('Reporte de Comprometidos')
            ->getStyle([1, 1, 6, 1])
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 18
                ]
            ]);

        $ws->getRowDimension(1)->setRowHeight(26);
        $ws->getCell([1, 2])->setValue($rSucursal['sDescripcion']);
        $ws->getCell([1, 3])->setValue('Contiene en descripcion: ' . (empty($this->request->getVar('sDescri') ?? '') ? 'Todos los articulos' : $this->request->getVar('sDescri')));

        $ws->mergeCells([1, 1, 6, 1])->mergeCells([1, 2, 6, 2])->mergeCells([1, 3, 6, 3]);
        $ws->getStyle([1, 1, 6, 3])->applyFromArray([
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

        $columna = 1;
        $filaTitulo = 4;
        $ws->getCell([$columna++, $filaTitulo])->setValue('Id');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Descripcion');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Existencia');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Comprometido');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Sobrecomprometido');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Disponible');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Comprometido Inventario');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Sobre Comprometido Inventario');

        $ws->getStyle([1, 4, $columna - 1, 4])->applyFromArray([
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

        $fila = 5;
        foreach ($registros[1] as $row) {
            $nExistencia = round(floatval($row['fExistencia']), 3);
            $nComprometidoInventario = round(floatval($row['fComprometido']), 3);
            $nSobreComprometidoInventario = round(floatval($row['fSobreComprometido']), 3);
            $nTotal = round(floatval($row['cant']), 3);
            $nComprometido = $nTotal <= $nExistencia ? $nTotal : $nExistencia;
            $nSobreComprometido = $nTotal > $nExistencia ? ($nTotal - $nExistencia) : 0;
            $ws->getCell([1, $fila])->setValue($row['nIdArticulo']);
            $ws->getCell([2, $fila])->setValue($row['nomArt']);
            $ws->getCell([3, $fila])->setValue(round($nExistencia, 2));
            $ws->getCell([4, $fila])->setValue(round($nComprometido, 2));
            $ws->getCell([5, $fila])->setValue(round($nSobreComprometido, 2));
            $ws->getCell([6, $fila])->setValue(round($nExistencia - $nComprometido, 2));
            $ws->getCell([7, $fila])->setValue(round($nComprometidoInventario, 2));
            $ws->getCell([8, $fila])->setValue(round($nSobreComprometidoInventario, 2));
            if(($nComprometidoInventario != $nComprometido) || ($nSobreComprometidoInventario != $nSobreComprometido)) {
                $ws->getStyle([1, $fila, 8, $fila])->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFFF00');
            }
            if($nComprometidoInventario != $nComprometido) {
                $ws->getStyle([4, $fila, 4, $fila])->getFill()->setFillType('solid')->getStartColor()->setARGB('FF00FFFF');
                $ws->getStyle([7, $fila, 7, $fila])->getFill()->setFillType('solid')->getStartColor()->setARGB('FF00FFFF');
            }
            if($nSobreComprometidoInventario != $nSobreComprometido) {
                $ws->getStyle([5, $fila, 5, $fila])->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFF55FF');
                $ws->getStyle([8, $fila, 8, $fila])->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFF55FF');
            }
            $fila++;
        }
        $ws->getStyle([3, 5, 6, $fila])->getNumberFormat()->setFormatCode('#,##0.00');
        $ws->getStyle([3, 5, 6, $fila])->getAlignment()->setHorizontal('right');

        $ws->getColumnDimensionByColumn(1)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(2)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(3)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(4)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(5)->setAutoSize(true);
        $ws->getColumnDimensionByColumn(6)->setAutoSize(true);









        $ws1 = $wb->createSheet();
        $ws1->setTitle('Detalle de comprometidos');
        $columna = 1;
        $filaTitulo = 4;
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Fecha' . chr(10) . 'Entrega/Viaje');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Entrega');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Viaje');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Envio');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Remision');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Cliente');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('ID Articulo');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Articulo');
        $ws1->getCell([$columna++, $filaTitulo])->setValue('Cantidad');
        $ws1->getStyle([1, 4, $columna - 1, 4])->applyFromArray([
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

        $fila = 5;
        $filaIni = 5;
        $filaActual = '';
        foreach ($registros[0] as $row) {
            if($filaActual == '') $filaActual = $row['entrega'] . $row['viaje'];
            if($filaActual != ('' . $row['entrega'] . $row['viaje'])) { 
                $ws1->getStyle([1, $filaIni, 9, $fila - 1])->getBorders()->getOutline()->setBorderStyle('thin');
                $filaActual = '' . $row['entrega'] . $row['viaje'];
                $filaIni = $fila;
            }
            $fecha = $objFecha->PHPToExcel(substr($row['fecha'], 0, 10));
            $ws1->getCell([1, $fila])->setValue($fecha);
            if ($row['tipo'] == 'E') $ws1->getCell([2, $fila])->setValue($row['entrega']);
            if ($row['tipo'] == 'V') $ws1->getCell([3, $fila])->setValue($row['viaje']);
            if ($row['tipo'] == 'V') $ws1->getCell([4, $fila])->setValue($row['envio']);
            $ws1->getCell([5, $fila])->setValue($row['nFolioRemision']);
            $ws1->getCell([6, $fila])->setValue($row['sNombre']);
            $ws1->getCell([7, $fila])->setValue($row['nIdArticulo']);
            $ws1->getCell([8, $fila])->setValue($row['nomArt']);
            $ws1->getCell([9, $fila])->setValue(round(floatval($row['cant']), 2));
            $fila++;
        }
        $ws1->getStyle([1, 5, 1, $fila])->getNumberFormat()->setFormatCode('dd/mm/yyyy;@');
        $ws1->getStyle([1, $filaIni, 9, $fila - 1])->getBorders()->getOutline()->setBorderStyle('thin');


        $ws1->getColumnDimensionByColumn(1)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(2)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(3)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(4)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(5)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(6)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(7)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(8)->setAutoSize(true);
        $ws1->getColumnDimensionByColumn(9)->setAutoSize(true);

        $ws->setTitle('Resumen');

    }
    
        public function exportArticulos()
    {
        $this->validaSesion();
        $model = new ArticuloMdl();

        $data['registros'] =  $model->getRegistros(
            false,
            false,
            true,
            $this->request->getVar('sDescri') ?? false
        );

        $crdx2Exp[] = [
            'nIdArtClasificacion',
            'sDescripcion',
            'sCveProdSer',
            'sCveUnidad',
            'cUnidad',
            'cSinExistencia',
            'fPeso',
            'cImporteManual',
            'nCosto',
            'cConArticuloRelacionado',
            'nIdArticuloAcumulador',
            'nMedida',
            'sCodigo',
            'cConDescripcionAdicional',
            'cPrecioTapadoFactura',
            'sClasificacion'
        ];
        foreach ($data['registros'] as $r) {
            $crdx2Exp[] = [
                $r['nIdArtClasificacion'],
                $r['sDescripcion'],
                $r['sCveProdSer'],
                $r['sCveUnidad'],
                $r['cUnidad'],
                $r['cSinExistencia'],
                $r['fPeso'],
                $r['cImporteManual'],
                $r['nCosto'],
                $r['cConArticuloRelacionado'],
                $r['nIdArticuloAcumulador'],
                $r['nMedida'],
                $r['sCodigo'],
                $r['cConDescripcionAdicional'],
                $r['cPrecioTapadoFactura'],
                $r['sClasificacion']
            ];
        }

        $text = $this->ExportaCsv('test.csv', $crdx2Exp);
        return $this->response->download('articulos.csv', $text);
    }

}
