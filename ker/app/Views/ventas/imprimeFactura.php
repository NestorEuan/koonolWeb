<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeEnlarge;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeNone;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeShrink;
use Fpdf\Fpdf;
use Luecano\NumeroALetras\NumeroALetras;

if (!class_exists('PDF1')) {
    class PDF1 extends fpdf
    {
        public $fechaImp = null;
        public $nomEmpresa;
        public $nomSucursal;
        public $datCli;
        public $datEnvio = '';


        function RoundedRect($x, $y, $w, $h, $r, $style = '')
        {
            $k = $this->k;
            $hp = $this->h;
            if ($style == 'F')
                $op = 'f';
            elseif ($style == 'FD' || $style == 'DF')
                $op = 'B';
            else
                $op = 'S';
            $MyArc = 4 / 3 * (sqrt(2) - 1);
            $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
            $xc = $x + $w - $r;
            $yc = $y + $r;
            $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

            $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
            $xc = $x + $w - $r;
            $yc = $y + $h - $r;
            $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
            $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
            $xc = $x + $r;
            $yc = $y + $h - $r;
            $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
            $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
            $xc = $x + $r;
            $yc = $y + $r;
            $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
            $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
            $this->_out($op);
        }

        function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
        {
            $h = $this->h;
            $this->_out(sprintf(
                '%.2F %.2F %.2F %.2F %.2F %.2F c ',
                $x1 * $this->k,
                ($h - $y1) * $this->k,
                $x2 * $this->k,
                ($h - $y2) * $this->k,
                $x3 * $this->k,
                ($h - $y3) * $this->k
            ));
        }

        // public function Header()
        // {
        //     $this->SetLineWidth(0.1);

        //     $this->SetFont('Arial', '', '8');
        //     $this->Cell(65.30, 6, $this->fechaImp->format('d/m/Y H:i:s'), 'B', 0, 'L');
        //     $this->SetFont('Arial', 'B', '10');
        //     $this->Cell(65.30, 6, 'COPIA BODEGA', 'B', 0, 'C');
        //     $this->SetFont('Arial', '', '8');
        //     $this->Cell(65.30, 6, 'Pagina ' . $this->PageNo() . '/{nb}', 'B', 1, 'R');


        //     $yPos = $this->GetY();
        //     $this->MultiCell(65.30, 4, utf8_decode($this->nomEmpresa), 0, 'L');
        //     $yPosFin1 = $this->GetY();

        //     $this->SetY($yPos);
        //     $this->Cell(65.30 * 2);
        //     $this->MultiCell(65.30, 4, utf8_decode($this->nomSucursal), 0, 'L');
        //     $yPosFin2 = $this->GetY();

        //     $this->SetY($yPosFin1 > $yPosFin2 ? $yPosFin1 : $yPosFin2);
        //     $this->Cell(0, 0, '', 'T', 1);

        //     $yPos = $this->GetY();
        //     $this->MultiCell(86.196, 4, utf8_decode($this->datCli), 0, 'L');
        //     $yPosFin1 = $this->GetY();

        //     $this->SetY($yPos);
        //     $this->Cell(86.196);

        //     $this->SetFont('Arial', 'B', '10');
        //     $this->Cell(23.508, 12, 'Enviar A >>', 0, 0, 'C');
        //     $this->SetFont('Arial', '', '8');

        //     if ($this->datEnvio == 'ENTREGAR EN BODEGA.') {
        //         $this->Cell(86.196, 10, $this->datEnvio, 0, 1, 'C');
        //         $yPosFin2 = 0;
        //     } else {
        //         $this->MultiCell(86.196, 4, utf8_decode($this->datEnvio), 1, 'L');
        //         $yPosFin2 = $this->GetY();
        //     }
        //     $this->SetY($yPosFin1 > $yPosFin2 ? $yPosFin1 : $yPosFin2);
        //     $this->Cell(0, 0, '', 'T', 1);
        // }

        public function Footer()
        {
            // Go to 1.5 cm from bottom
            $this->SetY(-15);
            $y = $this->GetY();
            $this->SetFillColor(200, 200, 200);
            $this->RoundedRect(5, $y, 205, 10, 2, 'F');

            // Select Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Print current and total page numbers
            $this->Cell(
                66.66,
                10,
                iconv('UTF-8', 'ISO-8859-1', 'Este documento es una representación impresa de un CFDI'),
                0,
                0,
                'C'
            );
            $this->Cell(66.66, 10, 'Pagina ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
            $this->Cell(0, 10, 'FERROMAT 2022', 0, 0, 'R');
        }
    }
}
// set some text to print   
//'LETTER' => array(  612.000,   792.000), // = (  216 x 279  ) mm  = (  8.50 x 11.00 ) in

// $pp = new Fpdf();
// ancho de linea 195.9
$pdf = new PDF1('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();


$pdf->SetFillColor(200, 200, 200);
$pdf->SetFont('Arial', 'B', '16');
// $pdf->Text(108, 5, 'CFDI');
$pdf->SetXY(108, 10);
// $pdf->Cell(108);
$x = 109;
$pdf->Cell(16, 5, 'CFDI', 0, 0, 'L');
$pdf->SetFont('Arial', '', '8');
$pdf->Cell(85, 5, 'Comprobante Fiscal Digital Ver. 4.0', 0, 0, 'R');

$pdf->RoundedRect(108, 15, 102, 25, 3, 'F');
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY($x, 16);
$pdf->Cell(50, 4, 'Serie/Folio', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(50, 4, 'Folio Fiscal', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(50, 4, 'Certificado SAT', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(50, 4, 'Certificado del Emisor', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(50, 4, 'Fecha y Hora de Certificacion', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(50, 4, 'Fecha y Hora de Emision', 0, 2, 'L');
$x = 159;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($x, 16);

$pdf->SetFont('Arial', 'B', '8');
$pdf->Cell(50, 4, $Dat['Serie'] . ' / ' . $Dat['Folio'], 0, 2, 'R');

$pdf->SetFont('Arial', '', '8');
$pdf->SetX($x);
$pdf->Cell(50, 4, $Dat['UUID'], 0, 2, 'R');
$pdf->SetX($x);
$pdf->Cell(50, 4, $Dat['NoCertificadoSAT'], 0, 2, 'R');
$pdf->SetX($x);
$pdf->Cell(50, 4, $Dat['NoCertificado'], 0, 2, 'R');
$pdf->SetX($x);
$pdf->Cell(50, 4, $Dat['FechaTimbrado'], 0, 2, 'R');
$pdf->SetX($x);
$pdf->Cell(50, 4, $Dat['Fecha'], 0, 2, 'R');

$pdf->RoundedRect(29, 5, 54, 36, 2, 'D');
$pdf->Image(('assets/img/ferromatmedio.png'), 30, 5, null, 35);
$pdf->SetXY(5, 42);
$pdf->SetFont('Arial', 'B', '11');
$pdf->Cell(103, 4.5, iconv('UTF-8', 'ISO-8859-1', 'Teléfono: ' . $DatSuc['sCelular']), 0, 1, 'C');
$pdf->SetX(5);
$pdf->Cell(103, 4.5, iconv('UTF-8', 'ISO-8859-1', 'Email: ' . $DatSuc['sEmail']), 0, 1, 'C');

$pdf->RoundedRect(5, 53, 101, 29, 3, 'D');
$pdf->SetXY(8, 54);
$pdf->SetFont('Arial', 'B', '8');
$pdf->Cell(50, 4, 'Emisor', 0, 2, 'L');
$pdf->SetX(6);
$pdf->SetFont('Arial', '', '8');
$pdf->Cell(50, 4, 'FMO131114PT6 FERROMAT MOTUL SA DE CV', 0, 2, 'L');
$pdf->SetX(6);
$pdf->MultiCell(99, 4, iconv('UTF-8', 'ISO-8859-1', '26 S/N' . chr(10) .
    'Motul de Carrillo Puerto, C.P. 97430' . chr(10) .
    'Merida, Yucatán, México'), 0, 'L');

$x = 109;   // $this->aXmlDatFactura['cadFormaPago']
$pdf->SetXY($x, 44);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(30, 4, 'Tipo Comprobante', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(30, 4, 'Moneda', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(30, 4, 'Expedido en', 0, 2, 'L');
$pdf->SetX($x);
$pdf->Cell(30, 4, 'Forma de Pago:', 0, 2, 'L');
$pdf->Ln(4);
$pdf->SetX($x);
$pdf->Cell(30, 4, 'Metodo de Pago:', 0, 2, 'L');
$pdf->Ln(4);
$pdf->SetX($x);
$pdf->Cell(30, 4, 'Regimen Fiscal:', 0, 2, 'L');
$pdf->SetX($x);

$result = Builder::create()
    ->data($Dat['QR'])
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
    ->size(300)
    ->margin(10)
    ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
    ->validateResult(false)
    ->build();

$imgCode = $result->saveToFile('assets/qr' . $idUsuario . '.png');
$pdf->Image('assets/qr' . $idUsuario . '.png', 171, 43, 40);

$pdf->RoundedRect(108, 43, 64, 39, 3, 'D');
$x = 139;
$pdf->SetXY($x, 44);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', '7');

$pdf->Cell(32, 4, 'Ingreso', 0, 2, 'R');
$pdf->SetX($x);
$pdf->Cell(32, 4, 'MXN', 0, 2, 'R');
$pdf->SetX($x);
$pdf->Cell(32, 4, $Dat['LugarExpedicion'], 0, 2, 'R');
$x = 109;
$pdf->Ln(4);
$pdf->SetX($x);
$pdf->Cell(62, 4, $Dat['FormaPago'], 0, 2, 'L');
$pdf->Ln(4);
$pdf->SetX($x);
$pdf->Cell(62, 4, $Dat['MetodoPago'], 0, 2, 'L');
$pdf->Ln(4);
$pdf->SetX($x);
$pdf->Cell(62, 4, substr($Dat['emiRegimenFiscal'] . ' - ' . $Dat['emiCadRegimenFiscal'],0 , 52), 0, 2, 'L');

$pdf->RoundedRect(5, 84, 205, 14, 3, 'D');
$pdf->SetXY(6, 85);
$pdf->SetFont('Arial', 'B', '8');
$pdf->Cell(80, 4, 'Receptor', 0, 0, 'L');
$pdf->Cell(30, 4, 'Regimen Fiscal', 0, 0, 'C');
$pdf->Cell(20, 4, 'Codigo Postal', 0, 0, 'C');
$pdf->Cell(20, 4, 'Uso CFDI', 0, 2, 'C');
// $pdf->Cell(30, 4, 'Uso CFDI', 0, 0, 'C');
// $pdf->Cell(40, 4, 'Regimen Fiscal', 0, 0, 'C');
// $pdf->Cell(0, 4, 'Domicilo Fiscal', 0, 2, 'C');

$pdf->SetX(6);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', '7');
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(80, 4, iconv('UTF-8', 'ISO-8859-1', $Dat['recRfc'] . ' ' . $Dat['recNombre']), 0, 'L');
$pdf->SetXY($x + 80, $y);
$pdf->Cell(30, 4, $Dat['recRegimenFiscalReceptor'] ?? '', 0, 0, 'C');
$pdf->Cell(20, 4, $Dat['recDomicilioFiscalReceptor'] ?? '', 0, 0, 'C');
$pdf->Cell(20, 4, $Dat['recUsoCFDI'], 0, 2, 'C');
// $pdf->Cell(30, 4, $Dat['recUsoCFDI'], 0, 0, 'C');
// $pdf->SetXY($x + 80, $y + 4);
// $pdf->Cell(30, 4, substr($Dat['recCadUsoCFDI'], 0, 23), 0, 0, 'C');
// $pdf->Cell(40, 4, $Dat['recRegimenFiscalReceptor'] ?? '', 0, 0, 'C');
// $pdf->Cell(0, 4, $Dat['recDomicilioFiscalReceptor'] ?? '', 0, 2, 'C');
$pdf->Ln(6);
$pdf->SetX(6);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', '6');
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(20, 2, 'Clave del Producto o Servicio', 0, 'C');
$pdf->SetXY($x + 20, $y + 1);
$pdf->MultiCell(20, 2, iconv('UTF-8', 'ISO-8859-1', 'Número Identificación'), 0, 'C');
$pdf->SetXY($x + 40, $y + 1);
$pdf->Cell(10, 4, 'Cantidad', 0, 0, 'C');
// $pdf->SetXY($x + 2, $y + 1);
$pdf->Cell(20, 4, 'Tipo Unidad', 0, 0, 'C');
$pdf->Cell(50, 4, iconv('UTF-8', 'ISO-8859-1', 'Descripción'), 0, 0, 'C');
$pdf->Cell(20, 4, 'Precio Unitario', 0, 0, 'C');
$pdf->Cell(20, 4, 'Descuentos', 0, 0, 'C');
$pdf->Cell(20, 4, 'Impuestos', 0, 0, 'C');
$pdf->Cell(24, 4, 'Importe', 0, 2, 'C');
$y = $pdf->GetY() + 1.5;
$pdf->Line(5, $y, 210, $y);

$pdf->SetFont('Arial', '', '6');
$pdf->Ln(2);
$pdf->SetX(6);
$x = $pdf->GetX();
$y = $pdf->GetY();
$yLineaAnterior = $y;
foreach ($Dat['conceptos'] as $v) {
    $y = $pdf->GetY();
    $pdf->Cell(20, 4, $v[0], 0, 0, 'C');
    $pdf->Cell(20, 4, $v[4], 0, 0, 'C');
    $pdf->Cell(10, 4, intval($v[1]), 0, 0, 'C');
    $pdf->Cell(20, 4, $v[2] . ' ' . $v[3], 0, 0, 'C');
    $x = $pdf->GetX();
    $pdf->MultiCell(50, 4, iconv('UTF-8', 'ISO-8859-1', $v[5]), 0, 'J');
    $yLineaAnterior = $pdf->GetY();
    $pdf->SetXY($x + 50, $y);
    $pdf->Cell(20, 4, $v[6], 0, 0, 'R');
    $pdf->Cell(20, 4, $v[7], 0, 0, 'R');
    $pdf->Cell(20, 4, $v[9], 0, 0, 'R');
    $pdf->Cell(24, 4, $v[8], 0, 0, 'R');
    $pdf->RoundedRect(108, $yLineaAnterior, 102, 4, 2.2, 'D');
    $pdf->SetXY(110, $yLineaAnterior);
    $pdf->SetFont('Arial', 'B', '6');
    $pdf->Cell(10, 4, 'Impuesto', 0, 0, 'C');
    $pdf->Cell(15, 4, 'Tipo', 0, 0, 'C');
    $pdf->Cell(20, 4, 'Base', 0, 0, 'C');
    $pdf->Cell(14, 4, 'Tipo Factor', 0, 0, 'C');
    $pdf->Cell(19, 4, 'Tasa o Cuota', 0, 0, 'C');
    $pdf->Cell(20, 4, 'Importe', 0, 0, 'C');
    $pdf->SetFont('Arial', '', '6');
    $pdf->SetXY(110, $yLineaAnterior + 4);
    $pdf->Cell(10, 4, 'IVA', 0, 0, 'C');
    $pdf->Cell(15, 4, 'Traslado', 0, 0, 'C');
    $pdf->Cell(20, 4, $v[10][0], 0, 0, 'C');
    $pdf->Cell(14, 4, $v[10][2], 0, 0, 'C');
    $pdf->Cell(19, 4, $v[10][3], 0, 0, 'C');
    $pdf->Cell(20, 4, $v[10][4], 0, 2, 'R');
    $y = $pdf->GetY() + 1.5;
    $pdf->Line(5, $y, 210, $y);


    $pdf->SetXY(6, $yLineaAnterior + 10);
}



$formato = new NumeroALetras();
$nImp = round(floatval($Dat['Total']), 2);
$formato->conector = 'PESOS CON';
$sImp = '***' . $formato->toInvoice($nImp, 2, 'M.N.') . '***';

$pdf->SetY($pdf->GetY() + 2);
$y = $pdf->GetY();
$pdf->SetFont('Arial', 'B', '7');
$pdf->Cell(20, 4, 'Importe con Letra', 0, 2, 'L');
$pdf->SetX(6);
$pdf->SetFont('Arial', '', '7');
$pdf->Cell(100, 4, $sImp, 0, 2, 'L');

$pdf->SetXY(140, $y);
$pdf->SetFont('Arial', 'B', '8');
$pdf->Cell(40, 4, 'SubTotal', 0, 0, 'R');
$pdf->Cell(28, 4, '$' . number_format(floatval($Dat['SubTotal']), 2), 0, 2, 'R');

$pdf->SetX(140);
$pdf->Cell(40, 4, 'IVA 16%', 0, 0, 'R');
$pdf->Cell(28, 4, '$' . $Dat['impuestosIVA'], 0, 2, 'R');

$pdf->SetX(140);
$pdf->Cell(40, 4, 'Impuestos Retenidos', 0, 0, 'R');
$pdf->Cell(28, 4, '$' . number_format(floatval(0), 2), 0, 2, 'R');

$pdf->SetX(140);
$pdf->Cell(40, 4, 'TOTAL', 0, 0, 'R');
$pdf->Cell(28, 4, '$' . number_format(floatval($Dat['Total']), 2), 0, 2, 'R');
$pdf->Ln(2);

$pdf->SetX(6);
$pdf->SetFont('Arial', 'B', '6');
$pdf->Cell(20, 4, 'Sello digital del CFDI:', 0, 2, 'L');
$pdf->SetX(6);
$pdf->SetFont('Arial', '', '7');
$pdf->MultiCell(0, 2, $Dat['SelloCFD'], 0, 'J');

$pdf->SetXY(6, $pdf->GetY() + 2);
$pdf->SetFont('Arial', 'B', '6');
$pdf->Cell(20, 4, 'Sello digital del SAT:', 0, 2, 'L');
$pdf->SetX(6);
$pdf->SetFont('Arial', '', '7');
$pdf->MultiCell(0, 2, $Dat['SelloSAT'], 0, 'J');

$pdf->SetXY(6, $pdf->GetY() + 2);
$pdf->SetFont('Arial', 'B', '6');
$pdf->Cell(20, 4, iconv('UTF-8', 'ISO-8859-1', 'Cadena Original del Complemento de Certificación Digital del SAT:'), 0, 2, 'L');
$pdf->SetX(6);
$pdf->SetFont('Arial', '', '7');
$pdf->MultiCell(0, 2, $Dat['cadOriTimbre'], 0, 'J');
if ($archDestino === false)
    $pdf->Output();
else
    $pdf->Output('F', $archDestino . '.pdf');
