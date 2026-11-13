<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();
requerir_permiso_json('ordenes_compra_ver');

require_once __DIR__ . '/fpdf/fpdf.php';
require_once __DIR__ . '/phpqrcode/qrlib.php';

function pdf_text($value)
{
    return mb_convert_encoding((string)$value, 'ISO-8859-1', 'UTF-8');
}

function pdf_fecha_es($value)
{
    $value = trim((string)$value);
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', substr($value, 0, 10));
    if (!$fecha) {
        return $value;
    }

    $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];

    return $fecha->format('d') . ' / ' . $meses[(int)$fecha->format('n')] . ' / ' . $fecha->format('Y');
}

class OrdenCompraPDF extends FPDF
{
    private $ordenData;

    function setOrdenData($data) {
        $this->ordenData = $data;
    }

    function Header()
    {
        // Fondo gris claro para header
        $this->SetFillColor(248, 249, 250);
        $this->Rect(0, 0, 210, 40, 'F');
        
        // Logo (esquina superior derecha)
        $logo = __DIR__ . '/../assets/images/logo_almacen.png';
        if (is_readable($logo)) {
            $this->Image($logo, 160, 10, 35);
        }

        // Título principal
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(52, 58, 64);
        $this->SetXY(15, 12);
        $this->Cell(0, 8, pdf_text('ORDEN DE ENTRADA'), 0, 1, 'L');
        
        // Línea decorativa bajo el título
        $this->SetDrawColor(73, 80, 87);
        $this->SetLineWidth(0.8);
        $this->Line(15, 22, 70, 22);
        
        // Folio con diseño destacado
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(108, 117, 125);
        $this->SetXY(15, 25);
        $this->Cell(30, 5, 'FOLIO:', 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 5, $this->ordenData['folio'] ?? 'N/A', 0, 1, 'L');
        
        // Línea inferior del header
        $this->SetDrawColor(233, 236, 239);
        $this->SetLineWidth(0.5);
        $this->Line(0, 40, 210, 40);
        
        $this->Ln(13);
    }

    function ProductHeader()
    {
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(73, 80, 87);
        $this->SetTextColor(255, 255, 255);
        foreach ([[12, '#'], [58, 'DESCRIPCIÓN'], [30, 'UBIC.'], [25, 'SOLICITADO'], [30, 'PRECIO UNIT.'], [35, 'SUBTOTAL']] as $i => $col) {
            $this->Cell($col[0], 7, pdf_text($col[1]), 1, $i === 5 ? 1 : 0, 'C', true);
        }
    }

    function ProductRow(array $values, bool $fill)
    {
        $widths = [12, 58, 30, 25, 30, 35];
        $aligns = ['C', 'L', 'L', 'C', 'R', 'R'];
        $this->SetFont('Arial', '', 8);
        $wrapped = [];
        $maxLines = 1;
        foreach ($values as $i => $value) {
            $lines = []; $line = '';
            foreach (explode(' ', pdf_text($value)) as $word) {
                if ($line !== '' && $this->GetStringWidth($line . ' ' . $word) > $widths[$i] - 2) {
                    $lines[] = $line; $line = '';
                }
                foreach (str_split(($line === '' ? '' : ' ') . $word) as $char) {
                    if ($this->GetStringWidth($line . $char) > $widths[$i] - 2) {
                        $lines[] = $line; $line = '';
                    }
                    $line .= $char;
                }
            }
            $lines[] = $line;
            $wrapped[] = implode("\n", $lines);
            $maxLines = max($maxLines, count($lines));
        }
        $height = max(6, $maxLines * 4 + 1);
        if ($this->GetY() + $height > 267) {
            $this->AddPage();
            $this->ProductHeader();
        }
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(33, 37, 41);
        $this->SetDrawColor(222, 226, 230);
        $this->SetFillColor($fill ? 248 : 255, $fill ? 249 : 255, $fill ? 250 : 255);
        $x = 10; $y = $this->GetY();
        foreach ($wrapped as $i => $value) {
            $this->Rect($x, $y, $widths[$i], $height, 'DF');
            $this->SetXY($x, $y + 0.5);
            $this->MultiCell($widths[$i], 4, $value, 0, $aligns[$i]);
            $x += $widths[$i];
        }
        $this->SetXY(10, $y + $height);
    }

    function Footer()
    {
        // Línea superior del footer
        $this->SetY(-20);
        $this->SetDrawColor(233, 236, 239);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(108, 117, 125);
        
        // Texto centrado
        $this->Cell(0, 5, pdf_text('Almacén Croram | Sistema de Gestión de Inventario'), 0, 1, 'C');
        $this->Cell(0, 5, pdf_text('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
    
    function InfoBox($label, $value, $width, $isLast = false)
    {
        // Etiqueta
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(73, 80, 87);
        $this->SetFillColor(248, 249, 250);
        $this->Cell($width / 2, 7, pdf_text($label), 1, 0, 'L', true);
        
        // Valor
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(33, 37, 41);
        $this->SetFillColor(255, 255, 255);
        $this->Cell($width / 2, 7, pdf_text($value), 1, $isLast ? 1 : 0, 'L', true);
    }
    
    function SectionTitle($title, $icon = '')
    {
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(73, 80, 87);
        $this->SetFillColor(248, 249, 250);
        
        $this->Cell(0, 6, pdf_text($icon . ' ' . $title), 0, 1, 'L', false);
        
        // Línea decorativa
        $this->SetDrawColor(73, 80, 87);
        $this->SetLineWidth(0.5);
        $y = $this->GetY();
        $this->Line(10, $y, 50, $y);
        
        $this->Ln(3);
    }

    private function countWrappedLines($width, $text)
    {
        $cw = $this->CurrentFont['cw'];
        $maxWidth = ($width - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $text = str_replace("\r", '', (string)$text);
        $length = strlen($text);

        if ($length > 0 && $text[$length - 1] === "\n") {
            $length--;
        }

        $separator = -1;
        $position = 0;
        $lineStart = 0;
        $lineWidth = 0;
        $lines = 1;

        while ($position < $length) {
            $character = $text[$position];
            if ($character === "\n") {
                $position++;
                $separator = -1;
                $lineStart = $position;
                $lineWidth = 0;
                $lines++;
                continue;
            }

            if ($character === ' ') {
                $separator = $position;
            }

            $lineWidth += $cw[$character] ?? 0;
            if ($lineWidth > $maxWidth) {
                if ($separator === -1 || $separator < $lineStart) {
                    if ($position === $lineStart) {
                        $position++;
                    }
                } else {
                    $position = $separator + 1;
                }

                $separator = -1;
                $lineStart = $position;
                $lineWidth = 0;
                $lines++;
                continue;
            }

            $position++;
        }

        return $lines;
    }

    function NotesBoxHeight($text, $width = 90)
    {
        $this->SetFont('Arial', '', 8);
        $bodyHeight = max(12, $this->countWrappedLines($width - 4, pdf_text($text)) * 4 + 3);
        return 6 + $bodyHeight;
    }

    function NotesBox($text, $x, $y, $width = 90)
    {
        $height = $this->NotesBoxHeight($text, $width);
        $bodyHeight = $height - 6;

        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(73, 80, 87);
        $this->SetFillColor(248, 249, 250);
        $this->SetDrawColor(222, 226, 230);
        $this->Cell($width, 6, pdf_text('NOTAS DE LA ORDEN:'), 1, 1, 'L', true);

        $this->Rect($x, $y + 6, $width, $bodyHeight);
        $this->SetXY($x + 2, $y + 7.5);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(33, 37, 41);
        $this->MultiCell($width - 4, 4, pdf_text($text), 0, 'L');
        $this->SetXY($x, $y + $height);

        return $y + $height;
    }

    function ValidationBlockHeight()
    {
        return 40;
    }

    function ValidationBlock($qrFile)
    {
        $startY = $this->GetY();
        $signatureWidth = 70;
        $signatureX1 = 45;
        $signatureX2 = 125;
        $boxY = $startY + 7;
        $boxHeight = 14;

        // El QR comparte la misma franja vertical que las firmas.
        $qrX = 12;
        $qrY = $startY + 8;
        $this->SetDrawColor(222, 226, 230);
        $this->SetLineWidth(0.3);
        $this->Rect($qrX - 2, $qrY - 2, 28, 28);
        $this->Image($qrFile, $qrX, $qrY, 24, 0, 'PNG');
        $this->SetFont('Arial', 'I', 6);
        $this->SetTextColor(108, 117, 125);
        $this->SetXY($qrX - 5, $qrY + 26);
        $this->Cell(34, 3, pdf_text('Escanea para ver online'), 0, 0, 'C');

        $this->SetXY($signatureX1, $startY);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(73, 80, 87);
        $this->Cell(150, 5, pdf_text('VALIDACIÓN Y AUTORIZACIÓN'), 0, 1, 'C');

        $this->SetFillColor(248, 249, 250);
        $this->SetXY($signatureX1, $boxY);
        $this->Cell($signatureWidth, $boxHeight, '', 1, 0, 'C', true);
        $this->SetXY($signatureX2, $boxY);
        $this->Cell($signatureWidth, $boxHeight, '', 1, 1, 'C', true);

        $labelY = $boxY + $boxHeight + 1;
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetTextColor(33, 37, 41);
        $this->SetXY($signatureX1, $labelY);
        $this->Cell($signatureWidth, 4, pdf_text('RESPONSABLE DE ALMACÉN'), 0, 0, 'C');
        $this->SetXY($signatureX2, $labelY);
        $this->Cell($signatureWidth, 4, pdf_text('AUTORIZA COMPRA'), 0, 1, 'C');

        $lineY = $labelY + 5;
        $this->SetDrawColor(222, 226, 230);
        $this->SetLineWidth(0.2);
        $this->Line($signatureX1 + 6, $lineY, $signatureX1 + $signatureWidth - 6, $lineY);
        $this->Line($signatureX2 + 6, $lineY, $signatureX2 + $signatureWidth - 6, $lineY);

        $this->SetFont('Arial', 'I', 6.5);
        $this->SetTextColor(108, 117, 125);
        $this->SetXY($signatureX1, $lineY + 0.5);
        $this->Cell($signatureWidth, 3, 'Nombre y Firma', 0, 0, 'C');
        $this->SetXY($signatureX2, $lineY + 0.5);
        $this->Cell($signatureWidth, 3, 'Nombre y Firma', 0, 1, 'C');

        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(73, 80, 87);
        $this->SetXY($signatureX1, $lineY + 4);
        $this->Cell($signatureWidth, 4, 'Fecha: ___________________', 0, 0, 'C');
        $this->SetXY($signatureX2, $lineY + 4);
        $this->Cell($signatureWidth, 4, 'Fecha: ___________________', 0, 1, 'C');

        $this->SetY($startY + $this->ValidationBlockHeight());
    }
}

/* =====================
   DATOS DE LA ORDEN
===================== */

include_once __DIR__ . '/adminOrdenes.php';
$adminOrdenes = new AdministradorOrdenes();
$ordenes = json_decode($adminOrdenes->obtenerOrdenCompra($_GET['id'] ?? 0));
$detallesOrden = json_decode($adminOrdenes->listarDetallesOrden($_GET['id'] ?? 0));

if (!$ordenes) {
    http_response_code(404);
    exit('La orden de entrada no existe.');
}

$orden = array(
    'folio'     => $ordenes[0]->folio ?? 'OC-00000',
    'fecha'     => pdf_fecha_es($ordenes[0]->fecha_orden ?? date('Y-m-d')),
    'proveedor' => $ordenes[0]->nombre_proveedor ?? 'SIN PROVEEDOR',
    'estatus'   => $ordenes[0]->estatus ?? 'NO VALIDADA',
    'nota'      => $ordenes[0]->nota ?? '',
);

$items = array();
foreach ($detallesOrden as $detalle) {
    $items[] = array(
        'producto' => $detalle->nombre_producto,
        'ubicacion' => $detalle->ubicacion ?? '',
        'cantidad' => (float)$detalle->cantidad,
        'solicitada' => (float)($detalle->cantidad_solicitada ?? $detalle->cantidad),
        'precio'   => (float)($detalle->precio_unitario ?? 0),
    );
}

// =====================
// GENERAR QR
// =====================

$qrData = 'https://grupocroram.com/almacen/dist/ordenes-entrada-detalle.php?id=' . ($_GET['id'] ?? 0);
$qrFile = tempnam(sys_get_temp_dir(), 'oc_qr_');
if ($qrFile === false) throw new RuntimeException('No se pudo crear el QR de la orden.');
register_shutdown_function(static function () use ($qrFile) { if (is_file($qrFile)) unlink($qrFile); });

QRcode::png(
    $qrData,
    $qrFile,
    QR_ECLEVEL_M,
    4,
    2
);

/* =====================
   GENERAR PDF
===================== */

$pdf = new OrdenCompraPDF();
$pdf->setOrdenData($orden);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

// =====================
// INFORMACIÓN GENERAL
// =====================

$pdf->SectionTitle('DATOS GENERALES');

// Usar el método InfoBox
$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

$pdf->InfoBox('FECHA DE ORDEN:', $orden['fecha'], 95, false);
$pdf->InfoBox('ESTATUS:', $orden['estatus'], 95, true);

$pdf->InfoBox('PROVEEDOR:', $orden['proveedor'], 190, true);

$pdf->Ln(5);

// =====================
// TABLA DE PRODUCTOS
// =====================

$pdf->SectionTitle('DETALLE DE PRODUCTOS');

$pdf->ProductHeader();
$total = 0;
foreach ($items as $index => $item) {
    $sub = $item['cantidad'] * $item['precio'];
    $total += $sub;
    $pdf->ProductRow([
        $index + 1, $item['producto'], $item['ubicacion'],
        $item['solicitada'],
        '$' . number_format($item['precio'], 2), '$' . number_format($sub, 2),
    ], $index % 2 !== 0);
}

// Línea final de la tabla
$pdf->Cell(190, 0, '', 'T', 1);

$pdf->Ln(5);

// =====================
// RESUMEN FINANCIERO
// =====================

// La nota comparte el espacio horizontal con los totales para aprovechar el
// área libre que queda debajo del detalle de productos.
$notaOrden = trim((string)$orden['nota']);
$alturaNotas = $notaOrden !== '' ? $pdf->NotesBoxHeight($notaOrden, 90) : 0;
$alturaResumen = 20;

if ($pdf->GetY() + max($alturaNotas, $alturaResumen) > 267) {
    $pdf->AddPage();
}

$inicioResumenY = $pdf->GetY();
$finNotasY = $inicioResumenY;

if ($notaOrden !== '') {
    $finNotasY = $pdf->NotesBox($notaOrden, 10, $inicioResumenY, 90);
}

$iva = $total * 0.16;
$grand = $total + $iva;

// Posicionar a la derecha
$pdf->SetXY(110, $inicioResumenY);

// Box contenedor del resumen
$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

// Subtotal
$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetX(110);
$pdf->Cell(50, 6, 'Subtotal:', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(40, 6, '$' . number_format($total, 2), 1, 1, 'R', true);

// IVA
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetX(110);
$pdf->Cell(50, 6, 'IVA (16%):', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(40, 6, '$' . number_format($iva, 2), 1, 1, 'R', true);

// Total - Destacado
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(73, 80, 87);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetX(110);
$pdf->Cell(50, 8, 'TOTAL:', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 8, '$' . number_format($grand, 2), 1, 1, 'R', true);

$finResumenY = $pdf->GetY();
$pdf->SetY(max($finNotasY, $finResumenY));
$pdf->Ln(5);

// =====================
// SECCIÓN DE FIRMAS
// =====================

$alturaValidacion = $pdf->ValidationBlockHeight();
if ($pdf->GetY() + $alturaValidacion > 267) {
    $pdf->AddPage();
}
$pdf->ValidationBlock($qrFile);

@unlink($qrFile);

/* =====================
   OUTPUT
===================== */

if (ob_get_length()) {
    ob_end_clean();
}
$pdf->Output('I', 'orden_compra_' . $orden['folio'] . '.pdf');
exit;
