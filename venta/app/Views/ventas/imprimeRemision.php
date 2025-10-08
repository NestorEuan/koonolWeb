<?php

use Fpdf\Fpdf;

class PDF1 extends Fpdf
{
    public $fechaImp = null;
    public $nomEmpresa;
    public $nomSucursal;
    public $datCli;
    public $datEnvio = '';

    public function Header()
    {
        $this->SetLineWidth(0.1);

        $this->SetFont('Arial', '', '8');
        $this->Cell(65.30, 6, $this->fechaImp->format('d/m/Y H:i:s'), 'B', 0, 'L');
        $this->SetFont('Arial', 'B', '10');
        $this->Cell(65.30, 6, 'COPIA BODEGA', 'B', 0, 'C');
        $this->SetFont('Arial', '', '8');
        $this->Cell(65.30, 6, 'Pagina ' . $this->PageNo() . '/{nb}', 'B', 1, 'R');


        $yPos = $this->GetY();
        $this->MultiCell(65.30, 4, utf8_decode($this->nomEmpresa), 0, 'L');
        $yPosFin1 = $this->GetY();

        $this->SetY($yPos);
        $this->Cell(65.30 * 2);
        $this->MultiCell(65.30, 4, utf8_decode($this->nomSucursal), 0, 'L');
        $yPosFin2 = $this->GetY();

        $this->SetY($yPosFin1 > $yPosFin2 ? $yPosFin1 : $yPosFin2);
        $this->Cell(0, 0, '', 'T', 1);

        $yPos = $this->GetY();
        $this->MultiCell(86.196, 4, utf8_decode($this->datCli), 0, 'L');
        $yPosFin1 = $this->GetY();

        $this->SetY($yPos);
        $this->Cell(86.196);

        $this->SetFont('Arial', 'B', '10');
        $this->Cell(23.508, 12, 'Enviar A >>', 0, 0, 'C');
        $this->SetFont('Arial', '', '8');

        if ($this->datEnvio == 'ENTREGAR EN BODEGA.') {
            $this->Cell(86.196, 10, $this->datEnvio, 0, 1, 'C');
            $yPosFin2 = 0;
        } else {
            $this->MultiCell(86.196, 4, utf8_decode($this->datEnvio), 1, 'L');
            $yPosFin2 = $this->GetY();
        }
        $this->SetY($yPosFin1 > $yPosFin2 ? $yPosFin1 : $yPosFin2);
        $this->Cell(0, 0, '', 'T', 1);
    }
}



// ancho de linea 195.9
$pdf = new PDF1('P', 'mm', 'Letter');
$pdf->nomEmpresa = 'FERROMAT MOTUL S.A. DE C.V.' . chr(10) .
    'CALLE 26 TAB. CAT. 4229 MOTUL DE CARRILLO PUERTO' . chr(10) .
    'MOTUL YUCATAN C.P. 97430' . chr(10) .
    'FMO131114PT6';
$pdf->nomSucursal = 'SUCURSAL NORTE' . chr(10) .
    'CALLE 11 Nº 316 x SOBRE PERIFERICO SANTA GERTRUDIS COPO' . chr(10) .
    'MERIDA YUCATAN C.P. 97305';
$pdf->fechaImp = new DateTime();
$pdf->datCli = 'Código: ' . sprintf('%06d', intval($cli['nIdCliente'])) .
    '  RFC: ' . strtoupper(trim($cli['sRFC'])) .
    '  C.P.: ' . $cli['cCP'] . chr(10) .
    $cli['sNombre'] . chr(10) . $cli['sDireccion'];
$pdf->datEnvio = $enviarA;
$pdf->SetFont('Arial', '', '8');
$pdf->SetFillColor(255, 255, 255);
$pdf->AliasNbPages();
$pdf->AddPage();
//  195.9 4 columnas y margen (30mm)
// 4 columnas cantidad, descripcion, precio unitario, importe
$margenIzq = 15;
$tabla = 195.9 - 30;
$entreEspacio = 2;
$col1 = 0.10 * $tabla;
$col2 = 0.50 * $tabla;
$col3 = 0.15 * $tabla;
$col4 = $tabla - $col1 - $col2 - $col3 - ($entreEspacio * 3);
$pdf->SetFont('Arial', 'B', '8');
$pdf->Cell(15);
$pdf->Cell($col1, 4, 'Cantidad', 1, 0, 'R');
$pdf->Cell($entreEspacio);
$pdf->Cell($col2, 4, 'Descripcion', 1, 0, 'L');
$pdf->Cell($entreEspacio);
$pdf->Cell($col3, 4, 'Precio Unitario', 1, 0, 'R');
$pdf->Cell($entreEspacio);
$pdf->Cell($col4, 4, 'Importe', 1, 1, 'R');

$pdf->SetFont('Arial', '', '8');
foreach ($lst as $k => $v) {
    $pdf->Cell(15);
    $pdf->Cell($col1, 4, number_format($v[3], 3), 0, 0, 'R');
    $pdf->Cell($entreEspacio);
    $pdf->Cell($col2, 4, $v[1], 0, 0, 'L');
    $pdf->Cell($entreEspacio);
    $pdf->Cell($col3, 4, number_format($v[2], 2), 0, 0, 'R');
    $pdf->Cell($entreEspacio);
    $pdf->Cell($col4, 4, number_format(round($v[3] * $v[2], 2), 2), 0, 1, 'R');
}


$pdf->Output();
