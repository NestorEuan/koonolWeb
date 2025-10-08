<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;
use App\Models\Almacen\InventarioMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Catalogos\ArticuloMdl;
use App\Models\Catalogos\TipoListaMdl;
use App\Models\Catalogos\ArtClasificacionMdl;
use App\Models\Catalogos\PrecioArticuloMdl;
use App\Models\Almacen\EntradaMdl;
use App\Models\Almacen\EntradaDetalleMdl;

//use Dompdf\Dompdf;
//use App\Libraries\myPdf;

class Articulo extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $model = new ArticuloMdl();

        $data['registros'] =  $model->getRegistros(
            false,
            20,
            true,
            $this->request->getVar('sDescri') ?? false
        );
        $data['pager'] = $model->pager;
        echo view('templates/header', $this->dataMenu);
        echo view('catalogos/articulos', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function imprimir()
    {
        $this->validaSesion();
        $model = new ArticuloMdl();

        $data['registros'] =  $model->getRegistros(
            false,
            true,
            true,
            $this->request->getVar('sDescri') ?? false
        );

        foreach ($data['registros'] as &$reg) {
            $reg['img64'] = '#';
            if ($reg['sRutaFoto'] !== '') {
                $rutArch = $reg['sRutaFoto'];
                $pos = strpos($rutArch, '.');
                $reg['tipoimg'] = substr($rutArch, $pos + 1);
                $cont = file_get_contents(WRITEPATH . 'uploads/' . $rutArch);
                $reg['img64'] = base64_encode($cont);
            }
        };

        $data['pager'] = $model->pager;
        echo view('templates/header', $this->dataMenu);
        /*
        $pdf = new myPdf('P', 'mm', 'Letter');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', '8');

        //////////// 4 imagenes por largo
        $imgWidth = $pdf->GetPageWidth() / 5;
        $imgPadW = $imgWidth / 5;
        $imgX = $imgPadW;
        $imgY = 5;

        ////////// 4 Imagenes por alto
        $imgHeight = $pdf->GetPageHeight() / 5;
        $imgPadH = $imgHeight / 5;
        $imgX = 2;
        $imgY = 5;


        foreach ($data['registros'] as $rr) {
            $pdf->Image(WRITEPATH . 'uploads/' . $rr['sRutaFoto'], $imgX, $imgY, 43.8); //,$pdf->GetPageWidth()/4);
            $imgX += $imgWidth + $imgPadW;
            if ($imgX + $imgWidth > $pdf->GetPageWidth()) {
                $imgY += 50;
                $imgX = $imgPadW;
            }
        }

        $pdf->Output('F', 'artCataList' . '.pdf');
        */
        echo view('catalogos/articuloscat', $data);
        echo view('templates/footer', $this->dataMenu);

        /* 
        Con DomPdf No sirven los css de bootstrap
        //$data['aInfoSis'] = $this->dataMenu['aInfoSis'];
        //$strHtml = view('templates/header', $this->dataMenu);
        $strHtml = view('catalogos/articuloscatx', $data);
        //$strHtml = $strHtml . view('templates/footer', $this->dataMenu);

        $domPdf = new Dompdf();
        $domPdf->loadHtml($strHtml);
        $domPdf->render();
        $domPdf->stream();
        */
    }

    public function inventario()
    {
        $this->validaSesion();
        $model = new InventarioMdl();
        $idsucursal = $this->request->getVar('nIdSucursal') ?? $this->nIdSucursal;
        $data['registros'] =   $model->getExistencias(
            $idsucursal,
            8,
            $this->request->getVar('sDescri') ?? false
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
        $mdlLista = new TipoListaMdl();
        $mdlSucursal = new SucursalMdl();
        $mdlPrecio = new PrecioArticuloMdl();

        $aTitulo = ['a' => 'Agrega', 'b' => 'Borra', 'e' => 'Edita'];
        $stitulo = $aTitulo[$tipoaccion] ?? 'Ver';

        $lstPrecio = $mdlLista->getRegistros();
        $lstSucursal = $mdlSucursal->getRegistros();

        $data = [
            'titulo' => $stitulo . ' Articulo',
            'frmURL' => base_url('articulo/' . $tipoaccion . ($id > 0 ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'id' => $id,
            'param1' => $tipoaccion,
            'param2' => $id,
            'img64' => '#',
            'regListas' => $lstPrecio,
            'regSucursales' => $lstSucursal,
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos($tipoaccion, $id, $model);
            if ($a === false) {
                $fFoto = $this->request->getFile('fFotoArchivo');
                if ($fFoto == null || !$fFoto->isValid()) {
                    $fFotoArchivo = '';
                    $sRutaFoto = '';
                } else {
                    $fFotoArchivo = $fFoto->getName();
                    $sRutaFoto = $fFoto->store();
                }

                $bSinExistencia = ($this->request->getVar('cSinExistencia') ?? false) == 'on' ? '1' : '0';
                $bImporteManual = ($this->request->getVar('cImporteManual') ?? false) == 'on' ? '1' : '0';
                $cConArticuloRelacionado = '0';
                $cConDescripcionAdicional = '0';
                $r = [
                    'sDescripcion' => $this->request->getVar('sDescripcion'),
                    'sCodigo' => trim($this->request->getVar('sCodigo') ?? ''),
                    'nIdArtClasificacion' => $this->request->getVar('nIdArtClasificacion'),
                    'sCveProdSer' => $this->request->getVar('sCveProdSer'),
                    'nCosto' => $this->request->getVar('nCosto'),
                    'sCveUnidad' => $this->request->getVar('sCveUnidad'),
                    'cUnidad' => $this->request->getVar('cUnidad'),
                    'fPeso' => 0,
                    'cSinExistencia' => $bSinExistencia,
                    'cImporteManual' => $bImporteManual,
                    'cConArticuloRelacionado' => $cConArticuloRelacionado,
                    'cConDescripcionAdicional' => $cConDescripcionAdicional,
                    'nIdArticuloAcumulador' => '0',
                    'nMedida' => 0,
                    'fFotoArchivo' => $fFotoArchivo,
                    'sRutaFoto' => $sRutaFoto
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdArticulo'] = $id;
                }
                $model->save($r);
                if ($tipoaccion === 'a')
                    $id = $model->insertID();
                $precioArt = new PrecioArticuloMdl();

                foreach ($lstPrecio as $itemLstPrecio) {
                    if ($this->request->getVar("fPrecio-{$itemLstPrecio['nIdTipoLista']}") === null) continue;
                    if ($this->request->getVar("fPrecio-{$itemLstPrecio['nIdTipoLista']}") === '') continue;
                    $nPrecio = floatval($this->request->getVar("fPrecio-{$itemLstPrecio['nIdTipoLista']}"));
                    if ($this->request->getVar("fDoce-{$itemLstPrecio['nIdTipoLista']}") === null || $this->request->getVar("fDoce-{$itemLstPrecio['nIdTipoLista']}") === '')
                        $nDoce = 0;
                    else
                        $nDoce = $this->request->getVar("fDoce-{$itemLstPrecio['nIdTipoLista']}");
                    if ($this->request->getVar("fCincuenta-{$itemLstPrecio['nIdTipoLista']}") === null || $this->request->getVar("fCincuenta-{$itemLstPrecio['nIdTipoLista']}") === '')
                        $nCincuenta = 0;
                    else
                        $nCincuenta = $this->request->getVar("fCincuenta-{$itemLstPrecio['nIdTipoLista']}");
                    foreach ($lstSucursal as $itemSuc) {
                        $precios = $precioArt->getIdRegistro($itemSuc['nIdSucursal'], $itemLstPrecio['nIdTipoLista'], $id);
                        if (count($precios) === 0) {
                            //crea
                            if ($nPrecio <= 0) continue;
                            $r = [
                                'nIdSucursal' => $itemSuc['nIdSucursal'],
                                'nIdArticulo' => $id,
                                'nIdTipoLista' => $itemLstPrecio['nIdTipoLista'],
                                'fMaximo' => 11.999999,
                                'faPartir' => 0,
                                'fPrecio' => $nPrecio,
                                'fPrecioFactura' => $nPrecio * 1.16,
                                'fPrecioTapado' => 0,
                            ];
                            $precioArt->save($r);

                            $r['fMaximo'] = 49.999999;
                            $r['faPartir'] = 12;
                            $r['fPrecio'] =  $nDoce;
                            $r['fPrecioFactura'] = $nDoce * 1.16;
                            $r['fPrecioTapado'] = 0;
                            $precioArt->save($r);

                            $r['fMaximo'] = 9999999;
                            $r['faPartir'] = 50;
                            $r['fPrecio'] =  $nCincuenta;
                            $r['fPrecioFactura'] = $nCincuenta * 1.16;
                            $r['fPrecioTapado'] = 0;
                            $precioArt->save($r);
                        } else {
                            //actualiza
                            $ii = 0;
                            foreach ($precios as $itemPrecio) {
                                $xPrice = $nPrecio;
                                if ($ii == 1)
                                    $xPrice = $nDoce;
                                if ($ii == 2)
                                    $xPrice = $nCincuenta;
                                $r = ['fPrecio' => $xPrice, 'fPrecioFactura' => $xPrice * 1.16];
                                $precioArt->set($r)->where('nIdPrecioArticulo', $itemPrecio['nIdPrecioArticulo'])->update();
                                $ii++;
                            }
                        }
                    }
                }
                foreach ($lstSucursal as $itemSuc) {
                    if ($this->request->getVar("fEntrada-{$itemSuc['nIdSucursal']}") == null) continue;
                    if ($this->request->getVar("fEntrada-{$itemSuc['nIdSucursal']}") == '') continue;
                    $nEntrada = floatval($this->request->getVar("fEntrada-{$itemSuc['nIdSucursal']}"));
                    if ($nEntrada <= 0) continue;
                    $entrada = new EntradaMdl();
                    $entradaDet = new EntradaDetalleMdl();
                    $timestamp = strtotime(date("d-m-Y"));
                    $dtS = date("Y-m-d", $timestamp);
                    $r = [
                        'dSolicitud' => $dtS,
                        'nIdSucursal' => $itemSuc['nIdSucursal'],
                        'nIdProveedor' => 0,
                        'fTotal' => 0,
                        'nProductos' => $nEntrada,
                        'sObservacion' => 'Entrada generada por alta de producto',
                    ];
                    $entrada->save($r);
                    $inserted = $entrada->insertID();
                    $det = [
                        'nIdEntrada' => $inserted,
                        'nIdArticulo' => $id,
                        'fCantidad' => $nEntrada,
                        'fRecibido' => 0,
                        'fPorRecibir' => $nEntrada,
                    ];
                    $entradaDet->save($det);
                }
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $model->getRegistros($id);
                $data['precios'] = $mdlPrecio->getlstPrecios(idArticulo: $id);
                // se lee la foto
                $rutArch = $data['registro']['sRutaFoto'];
                if ($rutArch == '') {
                    $data['tipoimg'] = '';
                    $data['img64'] = '#';
                } else {
                    $pos = strpos($rutArch, '.');
                    $data['tipoimg'] = substr($rutArch, $pos + 1);
                    $cont = file_get_contents(WRITEPATH . 'uploads/' . $rutArch);
                    $data['img64'] = base64_encode($cont);
                }
            } else
                $data['id'] = 0;
        }
        $mdlClasificacion = new ArtClasificacionMdl();
        $data['regClasificacion'] = $mdlClasificacion->getRegistros();
        echo view('catalogos/articulomtto', $data);
    }

    public function validaCampos($tipoaccion, $id, ArticuloMdl $model)
    {

        $cConArticuloRelacionado = ($this->request->getVar('cConArticuloRelacionado') ?? false) == 'on' ? '1' : '0';
        $nid = $this->request->getVar('nIdArticuloAcumulador') ?? '';
        $sDescripcion = $this->request->getVar('sDescripcion') ?? '';
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
        // withDeleted(true)->
        if ($sDescripcion != '') {
            $gg = $model->withDeleted(true)->like('sDescripcion', $sDescripcion, 'before')
                ->where('nIdArticulo <>', $id)->findAll();   //  builder()->getCompiledSelect();
            if (count($gg) > 0) {
                $arMsj['sDescripcion'] = 'Ya existe otro artículo con la misma descripción';
                if ($gg[0]['dtBaja'] != null) {
                    $arMsj['sDescripcion'] .= '. Con el id ' . $gg[0]['nIdArticulo'] .
                        ' y esta dado de baja.';
                }
            }
        }
        /*
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
        */

        $reglas = [
            'nIdArticulo' => 'greater_than_equal_to[0]',
            'sDescripcion' => 'required|min_length[3]',
            'sCodigo' => 'required|min_length[3]|is_unique[alarticulo.sCodigo, nIdArticulo, {nIdArticulo}]',
            'nIdArtClasificacion' => 'required|greater_than_equal_to[0]'
        ];
        $reglasMsj = [
            'nIdArticulo' => [
                'greater_than_equal_to' => 'Id Artículo erróneo'
            ],
            'sDescripcion' => [
                'required'   => 'Falta la descripción',
                'min_length' => 'Debe tener 3 caracteres como mínimo',
            ],
            'sCodigo' => [
                'required'   => 'Falta el código',
                'min_length' => 'Debe tener 3 caracteres como mínimo',
                'is_unique' => 'Ya existe otro artículo con el mismo código'
            ],
            'nIdArtClasificacion' => [
                'required' => 'Falta la clasificacion del producto',
                'greater_than_equal_to' => 'Falta la clasificacion del producto'
            ]
        ];

        if (!$this->validate($reglas, $reglasMsj) || count($arMsj) > 0) {
            $arr = $this->validator->getErrors();
            if (count($arMsj) > 0) {
                $arr = $arr + $arMsj;
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

    public function leeIdBarCode($id)
    {
        $model = new ArticuloMdl();
        $reg =  $model->getRegXIdXCod($id);
        if ($reg == null) {
            return json_encode(['ok' => '0']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sDescripcion, $conInventario = '0')
    {
        $model = new ArticuloMdl();
        if (($this->request->getVar('nombreventas') ?? false)) {
            $sDescripcion = $this->request->getVar('nombreventas');
        }
        if ($conInventario == '1') {
            $reg = $model->select(
                ' alarticulo.sCodigo, alarticulo.sDescripcion, alarticulo.nIdArticulo,' .
                    ' (' .
                    ' SELECT SUM(alinventario.fExistencia)' .
                    ' FROM alinventario' .
                    ' WHERE alinventario.nIdArticulo = alarticulo.nIdArticulo' .
                    ' GROUP BY alinventario.nIdArticulo' .
                    ' ) AS fExistencias ',
                false
            )->like(['sDescripcion' => $sDescripcion])
                ->orderBy('alarticulo.sDescripcion', 'asc')->findAll();
        } else {
            $reg =  $model->getRegistrosByName($sDescripcion, $conInventario = '0');
        }
        $t = count($reg);
        for ($i = 0; $i < $t; $i++) {
            $reg[$i]['sDescripcion'] = esc($reg[$i]['sDescripcion']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombred($sDescripcion)
    {
        $model = new ArticuloMdl();
        $reg = $model->where('sCodigo', $sDescripcion)->findAll();
        if (count($reg) == 0) {
            $reg =  $model->getRegistrosByName($sDescripcion);
        }
        $t = count($reg);
        for ($i = 0; $i < $t; $i++) {
            $reg[$i]['sDescripcion'] = esc($reg[$i]['sDescripcion']);
        }
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }
}
