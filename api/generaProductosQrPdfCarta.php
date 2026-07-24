<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();
requerir_permiso_json('articulos_ver');

require('fpdf/fpdf.php');
require_once 'phpqrcode/qrlib.php';

function pdf_text($value)
{
    return mb_convert_encoding((string)$value, 'ISO-8859-1', 'UTF-8');
}

function texto_etiqueta($producto)
{
    $texto = trim((string)($producto['nombre'] ?? ''));
    if ($texto === '') {
        $texto = trim((string)($producto['descripcion'] ?? ''));
    }

    return mb_substr($texto, 0, 50, 'UTF-8');
}

function font_size_etiqueta($texto)
{
    $largo = mb_strlen((string)$texto, 'UTF-8');
    if ($largo <= 18) {
        return 12;
    }
    if ($largo <= 32) {
        return 10;
    }
    return 8;
}

/*
|--------------------------------------------------------------------------
| RECIBIR PRODUCTOS
|--------------------------------------------------------------------------
*/
$productos = json_decode($_POST['productos'] ?? '[]', true);
if (isset($productos['sku'])) {
    $productos = [$productos];
}
if (!is_array($productos)) {
    $productos = [];
}

/*
|--------------------------------------------------------------------------
| CONSTRUIR ARRAY DE QR (según cantidad)
|--------------------------------------------------------------------------
*/
$qrs = array();

foreach ($productos as $producto) {
    if (!is_array($producto)) {
        continue;
    }
    $qrs[] = array(
        'sku'  => $producto['sku'],
        'nombre' => $producto['nombre'] ?? '',
        'descripcion' => texto_etiqueta($producto),
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
$qrSize       = 40; // 4x4 cm
$anchoTexto   = 48;
$paddingY     = 4;
$paddingBox   = 3; // padding interno del recuadro
$altoBloque   = $qrSize + ($paddingBox * 2);
$anchoBloque  = $qrSize + $anchoTexto + ($paddingBox * 3);

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

    $nombreQr = $qr['descripcion'];
    $xTexto = $xQR + $qrSize + $paddingBox;
    $pdf->SetXY($xTexto, $yQR + 4);
    $pdf->SetFont('Arial', 'B', font_size_etiqueta($nombreQr));
    $pdf->MultiCell($anchoTexto, 5, pdf_text($nombreQr), 0, 'L');
    $pdf->SetXY($xTexto, $yQR + 29);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($anchoTexto, 5, pdf_text($qr['sku']), 0, 0, 'L');

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
if (ob_get_length()) {
    ob_end_clean();
}
$pdf->Output('I', 'qr_carta.pdf');
exit;
