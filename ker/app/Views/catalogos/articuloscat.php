<?php

use Fpdf\Fpdf;

if (!class_exists('myPdf')) {
    class myPdf extends fpdf
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
                iconv('UTF-8', 'ISO-8859-1', 'Catálogo de productos'),
                0,
                0,
                'C'
            );
            $this->Cell(66.66, 10, 'Página ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
            $this->Cell(0, 10, 'Kerlis', 0, 0, 'R');
        }
    }
}

$pdf = new myPdf('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', '8');

/*
$pdf->SetFillColor(200, 200, 200);
// $pdf->Text(108, 5, 'CFDI');
// $pdf->Cell(108);
$x = 109;
$pdf->Cell(16, 5, 'CFDI', 0, 0, 'L');
$pdf->SetFont('Arial', '', '8');
$pdf->Cell(85, 5, 'Comprobante Fiscal Digital Ver. 4.0', 0, 0, 'R');
$pdf->Ln(10);
*/

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

/*
$i = 0;
$imgCnt = 0;
$imgY = -245;
$imgX = 0;
foreach( $registros as $rr){
    if( $i > 9 ) {
        $i = 0;
        $imgCnt = 0;
        $imgY = 5;
        $imgX = 0;
    };
    if(!($imgX < $pdf->GetPageWidth() - $imgPadW))
        $imgY += 50;
    if( $imgY > 250 ) $imgY = 5;

};
*/

foreach ($registros as $rr) {
    $pdf->Image(WRITEPATH . 'uploads/' . $rr['sRutaFoto'], $imgX, $imgY, 43.8); //,$pdf->GetPageWidth()/4);
    $imgX += $imgWidth + $imgPadW;
    if ($imgX + $imgWidth > $pdf->GetPageWidth()) {
        $imgY += 50;
        $imgX = $imgPadW;
    }
}

/*
while ($i < 10) {
    for ($imgY = 5; $imgY < 250; $imgY += 50) {
        for ($imgX = $imgPadW; $imgX < $pdf->GetPageWidth() - $imgPadW;) {
            $pdf->RoundedRect($imgX, $imgY, $imgWidth, $imgHeight - $imgPadH, 4, 'D');
            if(  $registros[$imgCnt++]['sRutaFoto'] !== '')
                $pdf->Image(WRITEPATH . 'uploads/' . $registros[$imgCnt++]['sRutaFoto'], $imgX, $imgY, 0, $imgHeight - $imgPadH); //,$pdf->GetPageWidth()/4);
            if ($imgCnt > 4) $imgCnt = 0;
            $imgX += $imgWidth + $imgPadW;
        }
        $imgX = $imgPadW;
    }
    $pdf->AddPage();
    $i++;
}
*/

$pdf->Output('F', 'artCataList' . '.pdf');
