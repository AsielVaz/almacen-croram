<?php
require('fpdf/fpdf.php');
require_once 'phpqrcode/qrlib.php';

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN IMPRESORA TÉRMICA
|--------------------------------------------------------------------------
| 58mm = ancho típico
| Alto dinámico (se calcula)
*/
$anchoMM = 58;
$altoPorQR = 42; // mm aproximados por QR + texto
$altoDivider = 5; // mm para la línea divisoria punteada

/*
|--------------------------------------------------------------------------
| EJEMPLOS DE QR
|--------------------------------------------------------------------------
*/
$qrs = array();

$productos = json_decode($_POST['productos'] ?? '[]', true);

foreach ($productos as $producto) {
    $qrs[] = array(
        'sku'  => $producto['sku'],
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

/*
|--------------------------------------------------------------------------
| GENERAR QR UNO DEBAJO DEL OTRO CON LÍNEAS DIVISORIAS
|--------------------------------------------------------------------------
*/
$contador = 0;
foreach ($qrs as $qr) {

    // Archivo temporal QR
    $qrFile = sys_get_temp_dir() . '/qr_' . md5($qr['sku']) . '.png';

    QRcode::png(
        $qr['data'],
        $qrFile,
        QR_ECLEVEL_L,
        4,
        1
    );

    // Centrar QR
    $qrSize = 30;
    $xQR = ($anchoMM - $qrSize) / 2;

    $pdf->Image($qrFile, $xQR, $pdf->GetY(), $qrSize);

    $pdf->Ln($qrSize + 2);

    // SKU debajo
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 6, $qr['sku'], 0, 1, 'C');

    $pdf->Ln(4);

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
$pdf->Output('I', 'qr_termico.pdf');
exit;