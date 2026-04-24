<?php
require('fpdf/fpdf.php');
require_once 'phpqrcode/qrlib.php';

/*
|--------------------------------------------------------------------------
| RECIBIR PRODUCTOS
|--------------------------------------------------------------------------
*/
$productos = json_decode($_POST['productos'] ?? '[]', true);

/*
|--------------------------------------------------------------------------
| CONSTRUIR ARRAY DE QR (según cantidad)
|--------------------------------------------------------------------------
*/
$qrs = array();

foreach ($productos as $producto) {
    $qrs[] = array(
        'sku'  => $producto['sku'],
        'data' => $producto['sku']
    );
}

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN HOJA CARTA
|--------------------------------------------------------------------------
*/
$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN DE BLOQUES
|--------------------------------------------------------------------------
*/
$qrSize       = 30; // tamaño QR
$altoTexto    = 6;
$paddingY     = 4;
$paddingBox   = 3; // padding interno del recuadro
$altoBloque   = $qrSize + $altoTexto + $paddingY + ($paddingBox * 2);
$anchoBloque  = $qrSize + ($paddingBox * 2);

$anchoPagina  = $pdf->GetPageWidth() - 20;
$bloquesFila  = floor($anchoPagina / $anchoBloque);
$espacioX     = ($anchoPagina - ($bloquesFila * $anchoBloque)) / max(1, $bloquesFila - 1);

$xInicial = 10;
$yInicial = 10;

$x = $xInicial;
$y = $yInicial;

/*
|--------------------------------------------------------------------------
| FUNCIÓN PARA DIBUJAR RECTÁNGULO PUNTEADO
|--------------------------------------------------------------------------
*/
function drawDashedRect($pdf, $x, $y, $width, $height) {
    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(150, 150, 150);
    
    $dashLength = 1;
    $gapLength = 1.5;
    
    // Línea superior
    $currentX = $x;
    while ($currentX < $x + $width) {
        $endX = min($currentX + $dashLength, $x + $width);
        $pdf->Line($currentX, $y, $endX, $y);
        $currentX += $dashLength + $gapLength;
    }
    
    // Línea inferior
    $currentX = $x;
    while ($currentX < $x + $width) {
        $endX = min($currentX + $dashLength, $x + $width);
        $pdf->Line($currentX, $y + $height, $endX, $y + $height);
        $currentX += $dashLength + $gapLength;
    }
    
    // Línea izquierda
    $currentY = $y;
    while ($currentY < $y + $height) {
        $endY = min($currentY + $dashLength, $y + $height);
        $pdf->Line($x, $currentY, $x, $endY);
        $currentY += $dashLength + $gapLength;
    }
    
    // Línea derecha
    $currentY = $y;
    while ($currentY < $y + $height) {
        $endY = min($currentY + $dashLength, $y + $height);
        $pdf->Line($x + $width, $currentY, $x + $width, $endY);
        $currentY += $dashLength + $gapLength;
    }
}

/*
|--------------------------------------------------------------------------
| IMPRIMIR QR EN GRID
|--------------------------------------------------------------------------
*/
foreach ($qrs as $index => $qr) {

    // Salto de página si no cabe otra fila
    if ($y + $altoBloque > $pdf->GetPageHeight() - 10) {
        $pdf->AddPage();
        $x = $xInicial;
        $y = $yInicial;
    }

    // Dibujar recuadro punteado
    drawDashedRect($pdf, $x, $y, $anchoBloque, $altoBloque);

    // Generar QR temporal
    $qrFile = sys_get_temp_dir() . '/qr_' . md5($qr['sku'] . $index) . '.png';

    QRcode::png(
        $qr['data'],
        $qrFile,
        QR_ECLEVEL_L,
        4,
        1
    );

    // Posición del QR (centrado en el recuadro)
    $xQR = $x + $paddingBox;
    $yQR = $y + $paddingBox;

    // QR
    $pdf->Image($qrFile, $xQR, $yQR, $qrSize);

    // SKU debajo (centrado)
    $pdf->SetXY($x, $yQR + $qrSize + 1);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($anchoBloque, $altoTexto, $qr['sku'], 0, 0, 'C');

    @unlink($qrFile);

    // Mover a la siguiente columna
    $x += $anchoBloque + $espacioX;

    // Si ya no cabe horizontalmente, nueva fila
    if ($x + $anchoBloque > $pdf->GetPageWidth() - 10) {
        $x = $xInicial;
        $y += $altoBloque + 4; // espacio adicional entre filas
    }
}

/*
|--------------------------------------------------------------------------
| SALIDA
|--------------------------------------------------------------------------
*/
$pdf->Output('I', 'qr_carta.pdf');
exit;