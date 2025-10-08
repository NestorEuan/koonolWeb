<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Catalogos\PrecioArticuloMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Catalogos\TipoListaMdl;
use App\Models\Seguridad\UsuarioMdl;
use App\Models\Ventas\CotizacionesDetMdl;
use App\Models\Ventas\CotizacionesMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Cotizaciones extends BaseController
{

    public function index()
    {
        $this->validaSesion();
        // se muestra el listado de cotizaciones

        $f = initFechasFiltro(null, null, '45');
        $tipo = $this->request->getVar('inputGroupSelectOpciones') ?? '0';
        $filtro = '';
        if ($tipo == 1) $filtro = $this->nIdUsuario;
        if ($tipo == 2) $filtro = $this->request->getVar('idCliente') ?? '';
        if ($tipo == 3) $filtro = $this->request->getVar('nFolioCotizacion') ?? '';

        $mdlCotizaciones = new CotizacionesMdl();

        $data['registros'] =  $mdlCotizaciones->getRegistros(
            false,
            $this->nIdSucursal,
            $f[0],
            $f[1],
            10,
            $tipo,
            $filtro
        );
        $data['pager'] = $mdlCotizaciones->pager;
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/cotizaciones', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function selecExporta($id)
    {
        $data = [
            'idcot' => $id
        ];
        echo view('ventas/cotizacionessel', $data);
    }

    public function exportaCotizacion($id = 0, $tipoFormato = '1')
    {
        $this->validaSesion();

        $data = $this->preparaDatos($id);
        return $this->genXlsCot($data, $tipoFormato);
    }

    private function preparaDatos($idCot)
    {
        $dia = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $mes = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $mdlSucursal = new SucursalMdl();
        $mdlCotizacion = new CotizacionesMdl();
        $mdlCotizacionDet = new CotizacionesDetMdl();
        $mdlPrecio = new PrecioArticuloMdl();
        $mdlUsuario = new UsuarioMdl();

        $datCot = [];
        $rCot = $mdlCotizacion->getRegistros($idCot);
        $rSuc = $mdlSucursal->getRegistroParaFacturar($rCot['nIdSucursal']);
        $rCotD = $mdlCotizacionDet->getRegistros($idCot, true);
        $rUsu = $mdlUsuario->getRegistros($this->nIdUsuario);
        $datCot['nomRZ'] = $rSuc[0]['sRazonSocial'];
        $datCot['nomSuc'] = $rSuc[0]['sDescripcion'];
        $datCot['cpSuc'] = $rSuc[0]['sCP'];
        $datCot['rfcSuc'] = $rSuc[0]['sRFC'];
        $datCot['telSuc'] = $rSuc[0]['sCelular'];
        $datCot['emailSuc'] = trim($rUsu['sMailCotizacion']) == '' ? $rSuc[0]['sEmail'] : $rUsu['sMailCotizacion'];
        $datCot['dirSuc'] = $rSuc[0]['sDireccion'];
        $datCot['folioCot'] = $rCot['nFolioCotizacion'];
        $datCot['Img2Print'] = $this->dataMenu['aInfoSis']['bannermain'];

        $fec = new  DateTime($rCot['dtAlta']);
        $sF = $fec->format('dmYw');
        $sH = $fec->format('h:i');
        $cfecha = $dia[intval(substr($sF, 8))] . ', ' .
            substr($sF, 0, 2) . ' de ' . $mes[intval(substr($sF, 2, 2)) - 1] .
            ' de ' . substr($sF, 4, 4) . '. (' . $sH . ')';
        $datCot['fecCot'] = $cfecha;

        $fec = new  DateTime($rCot['dVigencia']);
        $sF = $fec->format('dmYw');
        $sH = $fec->format('h:i');
        $cfecha = $dia[intval(substr($sF, 8))] . ', ' .
            substr($sF, 0, 2) . ' de ' . $mes[intval(substr($sF, 2, 2)) - 1] .
            ' de ' . substr($sF, 4, 4);
        $datCot['fecVig'] = $cfecha;

        $mdlTipoLista = new TipoListaMdl();
        $rpt = $mdlTipoLista->getRegistros(false, true);
        $tipoListaTapado = $rpt['nIdTipoLista'];

        $datCot['det'] = [];
        $nSumSub = 0.0;
        $nSumSubTap = 0.0;
        foreach ($rCotD as $k => $v) {
            $nCant = round(floatval($v['nCant']), 3);
            $pt = $mdlPrecio->buscaPrecio(
                $rCot['nIdSucursal'],
                $tipoListaTapado,
                $v['nIdArticulo'],
                $v['nCant']
            );
            $nPrecioT = round(floatval($pt == null ? round(floatval($v['nPrecio']) * 1.20, 2) : $pt['fPrecioFactura']), 2);
            $nImpT = round($nCant * $nPrecioT, 2);
            $nSumSubTap += $nImpT;

            $nPrecio = round(floatval($v['nPrecio']), 2);
            $nImp = round($nCant * $nPrecio, 2);
            $nSumSub += $nImp;

            $datCot['det'][] = [
                $v['nomArt'],
                $nPrecio,
                $nCant,
                0,
                $nPrecioT
            ];
        }
        $datCot['nTot'] = $nSumSub;
        $datCot['nTotTapado'] = $nSumSubTap;
        $datCot['nTotDiferencia'] = round($nSumSubTap - $nSumSub, 2);
        return $datCot;
    }

    private function genXlsCot(&$data, $tipoFormato)
    {
        $wb = new Spreadsheet();
        $ws = $wb->getActiveSheet();

        $fa = 1;

        $ws->getCell([2, $fa++])->setValue($data['nomRZ'])->getStyle()
            ->getFont()->setBold(true)->setSize(14);
        $texto = 'CP: ' . $data['cpSuc'] .
            '    RFC: ' . $data['rfcSuc'] .
            '    TEL: ' . $data['telSuc'];
        $ws->getCell([2, $fa++])->setValue($texto)->getStyle()
            ->getFont()->setBold(true)->setSize(14);

        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $richText->createText('CORREO ELECTRONICO: ');
        $a = $richText->createTextRun($data['emailSuc']);
        $a->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0000FF');
        $ws->getCell([2, $fa++])->setValue($richText)->getStyle()
            ->getFont()->setBold(true)->setSize(14);

        $ws->getCell([2, $fa++])->setValue($data['dirSuc'])->getStyle()
            ->getFont()->setBold(true)->setSize(14);
        $ws->getCell([2, $fa++])->setValue($data['fecCot'])->getStyle()
            ->getFont()->setBold(true)->setSize(14);
        $ws->getCell([1, $fa])->setValue('Vigencia hasta:  ' . $data['fecVig'])->getStyle()
            ->getFont()->setBold(true)->setSize(14);
        $ws->mergeCells([1, $fa, 4, $fa]);
        $ws->getCell([5, $fa])->setValue('Folio: ' . $data['folioCot'])->getStyle()
            ->getFont()->setBold(true)->setSize(14);
        $ws->getCell([1, ++$fa])->setValue('CONCEPTO');
        $ws->mergeCells([1, $fa, 2, $fa]);
        $ws->getCell([3, $fa])->setValue('CANTIDAD');
        $ws->getCell([4, $fa])->setValue('PRECIO');
        $ws->getCell([5, $fa++])->setValue('IMPORTE');

        foreach ($data['det'] as $v) {
            $ws->getCell([1, $fa])->setValue($v[0]);
            $ws->mergeCells([1, $fa, 2, $fa]);
            $ws->getCell([3, $fa])->setValue(intval($v[2]));
            if ($tipoFormato == '1') {
                $ws->getCell([4, $fa])->setValue($v[4]);
                $ws->getCell([5, $fa++])->setValue(round($v[2] * $v[4], 2));
            } else {
                $ws->getCell([4, $fa])->setValue($v[1]);
                $ws->getCell([5, $fa++])->setValue(round($v[2] * $v[1], 2));
            }
        }
        $ws->getCell([1, $fa])->setValue('Precio sujeto a cambio sin previo aviso, incluye el IVA');
        if ($tipoFormato == '1') {
            $ws->getCell([4, $fa])->setValue('TOTAL:');
            $ws->getCell([5, $fa++])->setValue($data['nTotTapado']);
            $ws->getCell([1, $fa])->setValue('Precio con descuento solo pago en efectivo sin factura');
        } else {
            $fa++;
        }
        $ws->getCell([4, $fa])->setValue('TOTAL:');
        $ws->getCell([5, $fa])->setValue($data['nTot']);
        $ft = 1;
        $ws->mergeCells([2, $ft, 5, $ft++]);
        $ws->mergeCells([2, $ft, 5, $ft++]);
        $ws->mergeCells([2, $ft, 5, $ft++]);
        $ws->mergeCells([2, $ft, 5, $ft++]);
        $ws->mergeCells([2, $ft, 5, $ft++]);

        $wsestilo = $ws->getStyle([2, 1, 5, 5]);
        $wsestilo->getAlignment()->setHorizontal('center');

        $wsestilo  = $ws->getStyle([1, 8, 5, $fa]);
        $wsestilo->getBorders()->getAllBorders()->setBorderStyle('thin');

        $wsestilo  = $ws->getStyle([1, 7, 5, 7]);
        $wsestilo->getAlignment()->setHorizontal('center')->setVertical('center');
        $wsestilo->getBorders()->getAllBorders()->setBorderStyle(true);
        $wsestilo->getFill()->setFillType('solid')->getStartColor()->setRGB('D9D9D9');

        $wsestilo  = $ws->getStyle([1, 7, 5, $fa]);
        $wsestilo->getFont()->setBold(true)->setSize(12);

        $wsestilo = $ws->getStyle([4, 8, 5, $fa - 2]);
        $wsestilo->getNumberFormat()->setFormatCode('$* #,##0.00');
        $ws->getStyle([5, 8, 5, $fa])->getNumberFormat()->setFormatCode('$* #,##0.00');

        $ws->getColumnDimensionByColumn(1)->setWidth(25);
        $ws->getColumnDimensionByColumn(2)->setWidth(25);
        $ws->getColumnDimensionByColumn(3)->setWidth(12);
        $ws->getColumnDimensionByColumn(4)->setWidth(15);
        $ws->getColumnDimensionByColumn(5)->setWidth(15);

        $ws->getRowDimension(7)->setRowHeight(25);

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath('assets/img/' . $data['Img2Print']);
        $drawing->setWidthAndHeight(180, 120);
        $drawing->setCoordinates('A1');

        $drawing->setWorksheet($ws);



        $xlsx = new Xlsx($wb);
        $nom = tempnam('assets', 'coti');
        $ainfo = pathinfo($nom);
        $arch = 'assets/' . $ainfo['filename'] . '.xlsx';

        $xlsx->save($arch);

        $wb->disconnectWorksheets();
        unset($wb);
        $text = file_get_contents($arch);
        unlink($arch);
        unlink($nom);

        return $this->response->download('cotizacion' . $data['folioCot'] . '.xlsx', $text);
    }
}
