<?php

namespace App\Controllers\Almacen;

use App\Controllers\BaseController;
use App\Models\Almacen\MovimientoMdl;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Cardex extends BaseController
{
    public function index()
    {
        $this->validaSesion();
        $fi = $this->request->getVar('dFecIni');
        $ff = $this->request->getVar('dFecFin');
        $f = initFechasFiltro($fi, $ff, '30');
        $data = [
            'fecIni' => $f[0]->format('Y-m-d'),
            'fecFin' => $f[1]->format('Y-m-d')
        ];

        // se validan todos los datos
        $id = $this->request->getVar('idProducto') ?? '';
        if($id != '') {
            $mdlMovto = new MovimientoMdl();
            $data['registros'] = $mdlMovto->getMovtosArticulo($id, $this->nIdSucursal, $f[0], $f[1]);
            $_SESSION['cardex']['datos'] = $data['registros'];
        } else {
            $data['registros'] = [];
        }

        echo view('templates/header', $this->dataMenu);
        echo view('almacen/cardex', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function exporta()
    {
        $this->validaSesion();
        $crdxArr = $_SESSION['cardex']['datos'];
        $crdx2Exp[] = ["Fecha","Tipo Docto","Id Docto","Cantidad","Saldo"];
        $saldo = $crdxArr[0]['fSaldoInicial'];
        $crdx2Exp[] = [$crdxArr[0]['dtAlta'],'Saldo Inicial', '', $crdxArr[0]['fSaldoInicial']];
        foreach($_SESSION['cardex']['datos'] as $r)
        {
            $saldo += $r['fCantidad'];
            $crdx2Exp[] = [ $r['dtAlta'], $r['cOrigen'], $r['nIdOrigen'], $r['fCantidad'], $saldo ];
        }

        $text = $this->ExportaCsv('test.csv',$crdx2Exp);
        return $this->response->download('cardex.csv',$text);
    }
}