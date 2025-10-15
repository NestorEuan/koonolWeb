<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Catalogos\PrecioArticuloMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Catalogos\TipoListaMdl;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ArtPrecios extends BaseController
{

    public function index()
    {
        $this->validaSesion();

        $mdlTipoLst = new TipoListaMdl();
        $registrosTipoListas = $mdlTipoLst->getRegistros();
        $data['regTipos'] = $registrosTipoListas;
        $data['numListas'] = count($registrosTipoListas);

        $mdlListaArticulos = new ArticuloMdl();
        $regArticulos = $mdlListaArticulos->getRegistros(false, false, true);
        $data['regArt'] = $regArticulos;

        $model = new PrecioArticuloMdl();
        $s = $this->request->getVar('sDescri') ?? '';

        $data['registros'] = $model->getlstPrecios($this->nIdSucursal, 7, $s == '' ? false : $s, false, true);
        $data['pager'] = $model->pager;
        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/artprecios', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function generaArchListaPrecios()
    {
        $session = session();
        $idLista = $this->request->getVar('nIdTipoLista');
        // $chkPrecio = $this->request->getVar('chkGenPrecio') ?? '';
        $chkPrecio = 'on';
        $sFiltro = $this->request->getVar('sDescriLoad') ?? '';
        $sFiltro = $sFiltro == '' ? false : $sFiltro;
        $mdlTipoLst = new TipoListaMdl();
        $registrosTipoListas = [$mdlTipoLst->getRegistros($idLista)];

        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();
        $columna = 1;
        $filaTitulo = 3;

        $ws->getCell([1, 1])->setValue($idLista);

        $ws->getCell([$columna++, $filaTitulo])->setValue('Clasificación');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Id');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Articulo');
        $ws->getCell([$columna++, $filaTitulo])->setValue('Precios a Partir de N Pzas');
        foreach ($registrosTipoListas as $row) {
            $ws->mergeCells([$columna, $filaTitulo - 1, $columna + 1, $filaTitulo - 1])
                ->getCell([$columna, $filaTitulo - 1])->setValue($row['cNombreTipo']);
            $ws->getCell([$columna, $filaTitulo])->setValue('Precio Remision');
            $ws->getCell([$columna + 1, $filaTitulo])->setValue('Precio Factura');
            $ws->getCell([$columna + 2, $filaTitulo])->setValue('Precio Tapado');
            $columna += 3;
        }
        $columnaFinal = $columna - 1;

        $ws->getStyle([1, 2, $columnaFinal, 3])->applyFromArray([
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
        $wb->getActiveSheet()->getRowDimension(3)->setRowHeight(64);


        $mdlListaArticulos = new PrecioArticuloMdl();
        $regArticulos = $mdlListaArticulos->getlstPrecios($this->nIdSucursal, false, $sFiltro, $idLista);

        $fila = 4;
        $columna = 1;
        $cRem = 'L' . $idLista;
        $cFac = 'F' . $idLista;
        $cTap = 'T' . $idLista;
        $idAnt = '-1';
        $valAnt = '';
        foreach ($regArticulos as $row) {
            $ws->getCell([1, $fila])->setValue($row['sClasificacion']);
            $ws->getCell([2, $fila])->setValue($row['nIdArticulo']);
            $ws->getCell([3, $fila])->setValue($row['sDescripcion']);
            $ws->getCell([4, $fila])->setValue($row['faPartir']);
            if ($chkPrecio != '') {
                $ws->getCell([5, $fila])->setValue($row[$cRem]);
                $ws->getCell([6, $fila])->setValue($row[$cFac]);
                $ws->getCell([7, $fila])->setValue($row[$cTap]);
            }
            $fila++;
        }

        $wb->getActiveSheet()->getColumnDimensionByColumn(1)->setWidth(14);
        $wb->getActiveSheet()->getColumnDimensionByColumn(2)->setWidth(6);
        $wb->getActiveSheet()->getColumnDimensionByColumn(3)->setWidth(40);
        $wb->getActiveSheet()->getColumnDimensionByColumn(4)->setWidth(19);
        for ($i = 5; $i <= $columnaFinal; $i++) {
            $wb->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth(12);
        }

        $ws->getStyle([4, 4, 4, $fila])->applyFromArray([
            'numberFormat' => [
                'formatCode' => '0'
            ],
            'alignment' => [
                'horizontal' => 'right',
                'vertical' => 'center'
            ]
        ]);

        $ws->getStyle([5, 4, $columnaFinal, $fila + 50])->applyFromArray([
            'numberFormat' => [
                'formatCode' => '0.00'
            ],
            'alignment' => [
                'horizontal' => 'right',
                'vertical' => 'center'
            ]
        ]);

        $ws->getStyle([1, 4, 7, $fila + 50])->getProtection()->setLocked(false);
        $ws->getProtection()->setPassword('cig');
        $ws->getProtection()->setAutoFilter(false);
        $ws->getProtection()->setSheet(true);

        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'precio');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);

        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('listaDePrecios.xlsx', $text);
    }

    public function cargaPrecios()
    {
        $session = session();
        $data = ['msjError' => ''];
        if (strtoupper($this->request->getMethod()) === 'POST') {
            $a = $this->validaCamposCarga('carga');
            if ($a === false) {
                $arch = $this->request->getFile('sArchivo');
                if ($arch->hasMoved()) {
                    $data['error'] = ['sArchivo' => 'No se cargó correctamente el archivo, intente de nuevo'];
                } else {
                    if (!$arch->isValid()) {
                        $data['msjError'] = $arch->getErrorString() . '(' . $arch->getError() . ')';
                    } else {
                        $archExcel = WRITEPATH . 'uploads/' . $arch->store();
                        $r = $this->actualizaPrecios($archExcel);
                        if (isset($r['ok']) == true) {
                            echo 'oK';
                            return;
                        } else {
                            $data['msjError'] = $r['msj'];
                        }
                    }
                }
            } else {
                $data['msjError'] = $a['amsj'];
            }
        }
        echo view('catalogos/artprecioscarga', $data);
    }

    public function descargaPrecios()
    {
        $session = session();
        $data = [];
        if (strtoupper($this->request->getMethod()) === 'POST') {
            $a = $this->validaCamposCarga('descarga');
            if ($a === false) {
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        }
        $mdlLista = new TipoListaMdl();
        $data['regListas'] = $mdlLista->getRegistros();
        echo view('catalogos/artpreciosdescarga', $data);
    }

    public function validaCamposCarga($tipo)
    {
        if ($tipo == 'carga') {
            $reglas = [
                'sArchivo' => 'uploaded[sArchivo]|'
                    . 'ext_in[sArchivo,xlsx]'
            ];
            $reglasMsj = [
                'sArchivo' => [
                    'uploaded'   => 'No se cargó el archivo, intente de nuevo',
                    'ext_in' => 'Los archivos aceptados son de excel 2007 (.xlsx)'
                ],
            ];
        } else {
            $reglas = [
                'nIdTipoLista' => 'greater_than[0]'
            ];
            $reglasMsj = [
                'nIdTipoLista' => [
                    'greater_than'   => 'Seleccione la plantilla a descargar'
                ],
            ];
        }
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function actualizaPrecios($arch)
    {
        function validaLista($nIdLista)
        {
            $mdlLista = new TipoListaMdl();
            $rTipoLista = $mdlLista->getRegistros();
            $tope = count($rTipoLista);
            $encontrado = false;
            for ($i = 0; $i < $tope; $i++) {
                if ($rTipoLista[$i]['nIdTipoLista'] == $nIdLista) {
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                return ['msj' => 'El archivo cargado tiene un id de tipo de lista no válido'];
            }
            return false;
        }

        function preparaArreglo(&$aCeldas)
        {
            $aParaGuardar = [];
            $aIdsArticulosLeidos = [];
            $totalFilas = count($aCeldas);
            $idAnt = -1;
            $valAnt = '';
            $nContRango = 0;
            for ($fila = 3; $fila < $totalFilas; $fila++) {
                // ya no mas registros para leer
                if ($aCeldas[$fila][1] == null || $aCeldas[$fila][1] <= 0) break;
                if ($aCeldas[$fila][3] === 'N/A' || $aCeldas[$fila][3] === null) continue;
                if ($aCeldas[$fila][1] != $idAnt) {
                    // ya fué leído previamente?
                    if (isset($aIdsArticulosLeidos[strval($aCeldas[$fila][1])])) {
                        return [
                            'msj' => 'El articulo con id ' . $aCeldas[$fila][1] .
                                ' de la fila ' . strval($fila + 1) . ', ya fué ' .
                                'cargado previamente en la fila ' .
                                $aIdsArticulosLeidos[strval($aCeldas[$fila][1])]
                        ];
                    }
                    $aIdsArticulosLeidos[strval($aCeldas[$fila][1])] = strval($fila + 1);
                    $nContRango = 0;
                } else {
                    if ($aCeldas[$fila][3] <= $valAnt) {
                        return [
                            'msj' => 'El articulo con id ' . $aCeldas[$fila][1] .
                                ' de la fila ' . strval($fila + 1) . ', tiene un rango inválido '
                        ];
                    }
                    $aParaGuardar[count($aParaGuardar) - 1][1] = round(floatval($aCeldas[$fila][3]) - 0.0001, 4);
                    $nContRango++;
                }
                $idAnt = $aCeldas[$fila][1];
                $valAnt = $aCeldas[$fila][3];

                $set = [];
                if ($aCeldas[$fila][4] != null && is_numeric($aCeldas[$fila][4])) {
                    $set['fPrecio'] = $aCeldas[$fila][4];
                } else {
                    $set['fPrecio'] = 0;
                }
                if ($aCeldas[$fila][5] != null && is_numeric($aCeldas[$fila][5])) {
                    $set['fPrecioFactura'] = $aCeldas[$fila][5];
                } else {
                    $set['fPrecioFactura'] = 0;
                }
                if ($aCeldas[$fila][6] != null && is_numeric($aCeldas[$fila][6])) {
                    $set['fPrecioTapado'] = $aCeldas[$fila][6];
                } else {
                    $set['fPrecioTapado'] = 0;
                }

                $aParaGuardar[] = [
                    $idAnt,
                    9999999,
                    $set['fPrecio'],
                    $set['fPrecioFactura'],
                    $nContRango,
                    $aCeldas[$fila][3],
                    $set['fPrecioTapado']
                ];
            }
            return $aParaGuardar;
        }

        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        $ss = $reader->load($arch);
        $aCeldas = $ss->getActiveSheet()->toArray(null, true, true, false);
        $nIdLista = $aCeldas[0][0] ?? 0;
        $r = validaLista($nIdLista);
        if ($r) return $r;

        $mdlPrecioArticulo = new PrecioArticuloMdl();
        $aParaGuardar = preparaArreglo($aCeldas);
        if (isset($aParaGuardar['msj'])) return $aParaGuardar;
        $contRango = 0;
        $rPreArt = [];
        foreach ($aParaGuardar as $v) {
            if ($v[4] == 0) {
                if ($contRango < count($rPreArt)) {
                    // borro los siguientes registros
                    $tope = count($rPreArt);
                    for ($i = $contRango; $i < $tope; $i++) {
                        $mdlPrecioArticulo->set([
                            'nIdArticulo' => 0,
                            'fMaximo' => 0,
                            'fPrecio' => 0,
                            'fPrecioFactura' => 0,
                            'fPrecioTapado' => 0,
                            'faPartir' => 0
                        ])->where([
                            'nIdPrecioArticulo' => $rPreArt[$i]['nIdPrecioArticulo']
                        ])->update();
                    }
                }
                $rPreArt = $mdlPrecioArticulo->getIdRegistro($this->nIdSucursal, $nIdLista, $v[0]);
                $contRango = 0;
            }
            if (round($v[2], 2) == 0 && round($v[3], 2) == 0) {
                if (isset($rPreArt[$contRango])) {
                    $mdlPrecioArticulo->set([
                        'nIdArticulo' => 0,
                        'fMaximo' => 0,
                        'fPrecio' => 0,
                        'fPrecioFactura' => 0,
                        'fPrecioTapado' => 0,
                        'faPartir' => 0
                    ])
                        ->where('nIdPrecioArticulo', $rPreArt[$contRango]['nIdPrecioArticulo'])
                        ->update();
                    $contRango++;
                } else {
                    $mdlPrecioArticulo->set([
                        'nIdArticulo' => 0,
                        'fMaximo' => 0,
                        'fPrecio' => 0,
                        'fPrecioFactura' => 0,
                        'fPrecioTapado' => 0,
                        'faPartir' => 0
                    ])->where([
                        'nIdSucursal' => $this->nIdSucursal,
                        'nIdTipoLista' => $nIdLista,
                        'nIdArticulo' => $v[0]
                    ])->update();
                }
            } else {
                if (isset($rPreArt[$contRango])) {
                    $mdlPrecioArticulo->set([
                        'fMaximo' => $v[1],
                        'fPrecio' => $v[2],
                        'fPrecioFactura' => $v[3],
                        'fPrecioTapado' => $v[6],
                        'faPartir' => $v[5]
                    ])
                        ->where('nIdPrecioArticulo', $rPreArt[$contRango]['nIdPrecioArticulo'])
                        ->update();
                    $contRango++;
                } else {
                    $rPreArtNoAsignado = $mdlPrecioArticulo->getIdRegistro($this->nIdSucursal, $nIdLista);
                    if (count($rPreArtNoAsignado) == 0) {
                        $mdlPrecioArticulo->insert([
                            'nIdSucursal' => $this->nIdSucursal,
                            'nIdTipoLista' => $nIdLista,
                            'nIdArticulo' => $v[0],
                            'fMaximo' => $v[1],
                            'fPrecio' => $v[2],
                            'fPrecioFactura' => $v[3],
                            'fPrecioTapado' => $v[6],
                            'faPartir' => $v[5]
                        ]);
                    } else {
                        $mdlPrecioArticulo->set([
                            'nIdArticulo' => $v[0],
                            'fMaximo' => $v[1],
                            'fPrecio' => $v[2],
                            'fPrecioFactura' => $v[3],
                            'fPrecioTapado' => $v[6],
                            'faPartir' => $v[5]
                        ])
                            ->where('nIdPrecioArticulo', $rPreArtNoAsignado[0]['nIdPrecioArticulo'])
                            ->update();
                    }
                }
            }
        }
        return ['ok' => '1'];
    }

    public function updtprecio($idArticulo, $faPartir, $fMaximo, $fImporte, $aSucursal = false, $aLstPrecio = false) {}

    public function muestraPrecios($id)
    {
        $session = session();

        $mdlTipoLst = new TipoListaMdl();
        $registrosTipoListas = $mdlTipoLst->getRegistros();
        $data['regTipos'] = $registrosTipoListas;
        $data['numListas'] = count($registrosTipoListas);

        $model = new PrecioArticuloMdl();
        $data['registros'] = $model->getlstPrecios($this->nIdSucursal, false, false, false, true, $id);

        $data['idSucursal'] = $this->nIdSucursal;

        echo view('catalogos/artpreciosdisponibles', $data);
        return;
    }
}
