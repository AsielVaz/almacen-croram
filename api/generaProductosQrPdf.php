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
| CONFIGURACIÓN IMPRESORA TÉRMICA
|--------------------------------------------------------------------------
| Etiqueta térmica horizontal: 10 x 5 cm
| QR: 4 x 4 cm
| Regla inferior tipo flexómetro: evidencia visual de 0 a 10 cm
| FPDF trabaja en milímetros, por eso se convierten los centímetros.
*/
$cm = 10;
$anchoEtiquetaCm = 10;
$altoEtiquetaCm = 5;
$altoReglaCm = 0.8;
$qrSizeCm = 4;
$paddingBoxCm = 0.4;
$altoDividerCm = 0.4;
$margenCorteCm = 0.8;

$anchoMM = $anchoEtiquetaCm * $cm;
$altoPorQR = $altoEtiquetaCm * $cm;
$altoRegla = $altoReglaCm * $cm;
$altoBloqueEtiqueta = $altoPorQR + $altoRegla;
$altoDivider = $altoDividerCm * $cm;
$margenCorte = $margenCorteCm * $cm;
$qrSize = $qrSizeCm * $cm;
$paddingBox = $paddingBoxCm * $cm;
$anchoTexto = $anchoMM - ($paddingBox * 4) - $qrSize;

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
// Alto total: etiquetas con regla + divisores (uno menos que etiquetas).
$altoTotal = max($altoBloqueEtiqueta + $margenCorte, ($numQRs * $altoBloqueEtiqueta) + (max(0, $numQRs - 1) * $altoDivider) + $margenCorte);

/*
|--------------------------------------------------------------------------
| PDF TÉRMICO
|--------------------------------------------------------------------------
*/
$orientacion = $anchoMM > $altoTotal ? 'L' : 'P';
$pdf = new FPDF($orientacion, 'mm', array($anchoMM, $altoTotal));
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

function drawFlexometro($pdf, $x, $y, $width, $height, $centimetros) {
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetLineWidth(0.2);
    $pdf->Rect($x, $y, $width, $height, 'DF');

    $mmPorCm = $width / max(1, $centimetros);
    for ($cmActual = 0; $cmActual <= $centimetros; $cmActual++) {
        $xCm = $x + ($cmActual * $mmPorCm);
        $pdf->Line($xCm, $y, $xCm, $y + $height);
        $pdf->SetFont('Arial', '', 5);
        $pdf->SetXY($xCm + 0.5, $y + $height - 3.2);
        $pdf->Cell(5, 2.5, $cmActual . 'cm', 0, 0, 'L');

        if ($cmActual === $centimetros) {
            continue;
        }

        for ($mmActual = 1; $mmActual < 10; $mmActual++) {
            $xMm = $xCm + ($mmActual * ($mmPorCm / 10));
            $altoMarca = ($mmActual === 5) ? ($height * 0.62) : ($height * 0.38);
            $pdf->Line($xMm, $y, $xMm, $y + $altoMarca);
        }
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
    drawFlexometro($pdf, 0, $yEtiqueta + $altoPorQR, $anchoMM, $altoRegla, $anchoEtiquetaCm);
    $pdf->SetY($yEtiqueta + $altoBloqueEtiqueta);

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
