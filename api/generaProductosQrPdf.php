<?php
require('fpdf/fpdf.php');
require_once 'phpqrcode/qrlib.php';

function pdf_text($value)
{
    return mb_convert_encoding((string)$value, 'ISO-8859-1', 'UTF-8');
}

function texto_etiqueta($producto)
{
    $texto = trim((string)($producto['descripcion'] ?? ''));
    if ($texto === '') {
        $texto = trim((string)($producto['nombre'] ?? ''));
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
| CONFIGURACIÓN IMPRESORA TÉRMICA
|--------------------------------------------------------------------------
| 58mm = ancho típico
| Alto dinámico (se calcula)
*/
$anchoMM = 100;
$altoPorQR = 46; // etiqueta horizontal: QR 4x4 cm + texto lateral
$altoDivider = 4; // mm entre etiquetas
$qrSize = 40;
$paddingBox = 4;
$anchoTexto = $anchoMM - ($paddingBox * 3) - $qrSize;

/*
|--------------------------------------------------------------------------
| EJEMPLOS DE QR
|--------------------------------------------------------------------------
*/
$qrs = array();

$productos = json_decode($_POST['productos'] ?? '[]', true);
if (isset($productos['sku'])) {
    $productos = [$productos];
}
if (!is_array($productos)) {
    $productos = [];
}

foreach ($productos as $producto) {
    if (!is_array($producto)) {
        continue;
    }
    $qrs[] = array(
        'sku'  => $producto['sku'],
        'nombre' => $producto['nombre'] ?? '',
        'descripcion' => texto_etiqueta($producto),
        'data' => $producto['sku'] // aquí puedes meter URL, hash, etc
    );
}

/*
|--------------------------------------------------------------------------
| CALCULAR ALTO TOTAL
|--------------------------------------------------------------------------
*/
$numQRs = count($qrs);
// Alto total: QRs + divisores (uno menos que QRs)
$altoTotal = ($numQRs * $altoPorQR) + (($numQRs - 1) * $altoDivider);

/*
|--------------------------------------------------------------------------
| PDF TÉRMICO
|--------------------------------------------------------------------------
*/
$pdf = new FPDF('P', 'mm', array($anchoMM, $altoTotal));
$pdf->SetMargins(4, 4, 4);
$pdf->SetAutoPageBreak(false);

$pdf->AddPage();

function drawDashedRect($pdf, $x, $y, $width, $height) {
    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(150, 150, 150);

    $dashLength = 1;
    $gapLength = 1.5;

    $currentX = $x;
    while ($currentX < $x + $width) {
        $endX = min($currentX + $dashLength, $x + $width);
        $pdf->Line($currentX, $y, $endX, $y);
        $currentX += $dashLength + $gapLength;
    }

    $currentX = $x;
    while ($currentX < $x + $width) {
        $endX = min($currentX + $dashLength, $x + $width);
        $pdf->Line($currentX, $y + $height, $endX, $y + $height);
        $currentX += $dashLength + $gapLength;
    }

    $currentY = $y;
    while ($currentY < $y + $height) {
        $endY = min($currentY + $dashLength, $y + $height);
        $pdf->Line($x, $currentY, $x, $endY);
        $currentY += $dashLength + $gapLength;
    }

    $currentY = $y;
    while ($currentY < $y + $height) {
        $endY = min($currentY + $dashLength, $y + $height);
        $pdf->Line($x + $width, $currentY, $x + $width, $endY);
        $currentY += $dashLength + $gapLength;
    }
}

/*
|--------------------------------------------------------------------------
| GENERAR QR UNO DEBAJO DEL OTRO CON LÍNEAS DIVISORIAS
|--------------------------------------------------------------------------
*/
$contador = 0;
foreach ($qrs as $qr) {
    $yEtiqueta = $pdf->GetY();
    drawDashedRect($pdf, $paddingBox, $yEtiqueta, $anchoMM - ($paddingBox * 2), $altoPorQR);

    // Archivo temporal QR
    $qrFile = sys_get_temp_dir() . '/qr_' . md5($qr['sku']) . '.png';

    QRcode::png(
        $qr['data'],
        $qrFile,
        QR_ECLEVEL_L,
        4,
        1
    );

    // QR 4x4 cm a la izquierda
    $xQR = $paddingBox * 2;
    $yQR = $yEtiqueta + (($altoPorQR - $qrSize) / 2);
    $pdf->Image($qrFile, $xQR, $yQR, $qrSize);

    // Descripcion truncada a 50 caracteres y SKU a la derecha.
    $nombreQr = $qr['descripcion'];
    $xTexto = $xQR + $qrSize + $paddingBox;
    $pdf->SetXY($xTexto, $yEtiqueta + 10);
    $pdf->SetFont('Arial', 'B', font_size_etiqueta($nombreQr));
    $pdf->MultiCell($anchoTexto, 5, pdf_text($nombreQr), 0, 'L');
    $pdf->SetXY($xTexto, $yEtiqueta + 31);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($anchoTexto, 5, pdf_text($qr['sku']), 0, 1, 'L');
    $pdf->SetY($yEtiqueta + $altoPorQR);

    // Limpiar QR temporal
    @unlink($qrFile);

    // Línea divisoria punteada (excepto después del último QR)
    $contador++;
    if ($contador < $numQRs) {
        $yLinea = $pdf->GetY() + 2;
        
        // Configurar estilo de línea punteada
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor(150, 150, 150); // Color gris
        
        // Dibujar línea punteada
        $margenLinea = 8; // márgen desde los bordes
        $xInicio = $margenLinea;
        $xFin = $anchoMM - $margenLinea;
        
        // Crear patrón punteado manualmente
        $longitudPunto = 1;
        $espacioPunto = 1.5;
        $x = $xInicio;
        
        while ($x < $xFin) {
            $xFinPunto = min($x + $longitudPunto, $xFin);
            $pdf->Line($x, $yLinea, $xFinPunto, $yLinea);
            $x += $longitudPunto + $espacioPunto;
        }
        
        // Espacio después de la línea divisoria
        $pdf->SetY($yLinea + $altoDivider - 2);
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
$pdf->Output('I', 'qr_termico.pdf');
exit;
