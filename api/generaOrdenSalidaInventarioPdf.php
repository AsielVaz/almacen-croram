<?php
require_once __DIR__ . '/../auth.php';
requerir_autenticacion_json();
requerir_permiso_json('ordenes_salida_ver');

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

class OrdenSalidaPDF extends FPDF
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
        
        // Logo local para que el PDF no dependa de una URL externa.
        $logo = __DIR__ . '/../assets/images/logo_almacen.png';
        if (is_readable($logo)) {
            $this->Image($logo, 160, 10, 35);
        }

        // Título principal
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(220, 53, 69); // Rojo para salida
        $this->SetXY(15, 12);
        $this->Cell(0, 8, pdf_text('ORDEN DE SALIDA'), 0, 1, 'L');
        
        // Línea decorativa bajo el título (roja)
        $this->SetDrawColor(220, 53, 69);
        $this->SetLineWidth(0.8);
        $this->Line(15, 22, 68, 22);
        
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
        
        $this->Ln(8);
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
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(73, 80, 87);
        $this->SetFillColor(248, 249, 250);
        $this->Cell($width / 2, 6, pdf_text($label), 1, 0, 'L', true);
        
        // Valor
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(33, 37, 41);
        $this->SetFillColor(255, 255, 255);
        $this->Cell($width / 2, 6, pdf_text($value), 1, $isLast ? 1 : 0, 'L', true);
    }
    
    function SectionTitle($title, $icon = '')
    {
        $this->SetFont('Arial', 'B', 10);
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

    function ProductTableHeader()
    {
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(73, 80, 87);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(73, 80, 87);
        $this->SetLineWidth(0.3);

        $this->Cell(12, 7, '#', 1, 0, 'C', true);
        $this->Cell(68, 7, pdf_text('DESCRIPCIÓN'), 1, 0, 'C', true);
        $this->Cell(22, 7, pdf_text('UBIC.'), 1, 0, 'C', true);
        $this->Cell(25, 7, 'CANTIDAD', 1, 0, 'C', true);
        $this->Cell(35, 7, pdf_text('COSTO PEPS'), 1, 0, 'C', true);
        $this->Cell(28, 7, 'TOTAL', 1, 1, 'C', true);
    }

    function ProductRow($number, $item, $mostrarCostos, $fill)
    {
        $description = pdf_text($item['producto']);
        $location = pdf_text($item['ubicacion']);
        $this->SetFont('Arial', '', 8);
        $descriptionLines = $this->WrappedLineCount(66, $description);
        $locationLines = $this->WrappedLineCount(20, $location);
        $rowHeight = max(6, max($descriptionLines, $locationLines) * 3.5 + 1.5);

        if ($this->GetY() + $rowHeight > 267) {
            $this->AddPage();
            $this->ProductTableHeader();
        }

        $widths = [12, 68, 22, 25, 35, 28];
        $values = [
            $number,
            $description,
            $location,
            $item['cantidad'],
            $mostrarCostos ? '$' . number_format((float)$item['costo_promedio'], 2) : 'PENDIENTE',
            $mostrarCostos ? '$' . number_format((float)$item['subtotal'], 2) : 'PENDIENTE',
        ];
        $alignments = ['C', 'L', 'L', 'C', 'R', 'R'];
        $startX = $this->GetX();
        $startY = $this->GetY();

        $this->SetFillColor($fill ? 248 : 255, $fill ? 249 : 255, $fill ? 250 : 255);
        $this->SetDrawColor(222, 226, 230);
        foreach ($widths as $index => $width) {
            $x = $this->GetX();
            $this->Rect($x, $startY, $width, $rowHeight, 'DF');
            $this->SetXY($x, $startY + max(0.5, ($rowHeight - 3.5 * (($index === 1) ? $descriptionLines : (($index === 2) ? $locationLines : 1))) / 2));
            $this->SetFont('Arial', in_array($index, [3, 4, 5], true) ? 'B' : '', 8);
            if (in_array($index, [1, 2], true)) {
                $this->MultiCell($width, 3.5, $values[$index], 0, $alignments[$index]);
            } else {
                $this->Cell($width, 3.5, $values[$index], 0, 0, $alignments[$index]);
            }
            $this->SetXY($x + $width, $startY);
        }

        $this->SetXY($startX, $startY + $rowHeight);
    }

    function ValidationBlockHeight()
    {
        return 39;
    }

    function ValidationBlock($qrFile)
    {
        $startY = $this->GetY();
        $qrX = 13;
        $qrY = $startY + 6;

        $this->SetDrawColor(220, 53, 69);
        $this->SetLineWidth(0.4);
        $this->Rect($qrX - 2, $qrY - 2, 28, 28);
        $this->Image($qrFile, $qrX, $qrY, 24, 0, 'PNG');
        $this->SetFont('Arial', 'I', 6);
        $this->SetTextColor(108, 117, 125);
        $this->SetXY($qrX - 5, $qrY + 27);
        $this->Cell(34, 3, pdf_text('Escanea para ver online'), 0, 0, 'C');

        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(73, 80, 87);
        $this->SetXY(45, $startY);
        $this->Cell(150, 5, pdf_text('VALIDACIÓN Y AUTORIZACIÓN'), 0, 1, 'C');

        $boxY = $startY + 7;
        $this->SetFillColor(248, 249, 250);
        $this->SetDrawColor(222, 226, 230);
        $this->Rect(47, $boxY, 65, 14, 'DF');
        $this->Rect(127, $boxY, 65, 14, 'DF');

        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(33, 37, 41);
        $this->SetXY(47, $boxY + 15);
        $this->Cell(65, 4, pdf_text('AUTORIZA SALIDA'), 0, 0, 'C');
        $this->SetXY(127, $boxY + 15);
        $this->Cell(65, 4, pdf_text('RECIBE PRODUCTOS'), 0, 0, 'C');

        $lineY = $boxY + 23;
        $this->SetDrawColor(222, 226, 230);
        $this->SetLineWidth(0.2);
        $this->Line(52, $lineY, 107, $lineY);
        $this->Line(132, $lineY, 187, $lineY);

        $this->SetFont('Arial', 'I', 6);
        $this->SetTextColor(108, 117, 125);
        $this->SetXY(47, $lineY);
        $this->Cell(65, 3, 'Nombre y Firma', 0, 0, 'C');
        $this->SetXY(127, $lineY);
        $this->Cell(65, 3, 'Nombre y Firma', 0, 0, 'C');

        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(73, 80, 87);
        $this->SetXY(47, $lineY + 5);
        $this->Cell(65, 3, 'Fecha: ___________________', 0, 0, 'C');
        $this->SetXY(127, $lineY + 5);
        $this->Cell(65, 3, 'Fecha: ___________________', 0, 0, 'C');

        $this->SetY($startY + $this->ValidationBlockHeight());
    }

    private function WrappedLineCount($width, $text)
    {
        $text = str_replace("\r", '', (string)$text);
        if ($text === '') {
            return 1;
        }

        $words = preg_split('/\s+/', $text);
        $lines = 1;
        $line = '';
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if ($line !== '' && $this->GetStringWidth($candidate) > $width) {
                $lines++;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        return $lines;
    }
}

/* =====================
   DATOS DE LA ORDEN
===================== */

include_once __DIR__ . '/adminOrdenes.php';
$adminOrdenes = new AdministradorOrdenes();
$ordenes = json_decode($adminOrdenes->obtenerOrdenSalida($_GET['id'] ?? 0));
$detallesOrden = json_decode($adminOrdenes->listarDetallesOrdenSalida($_GET['id'] ?? 0));

$orden = array(
    'folio'     => $ordenes[0]->folio ?? 'OS-00000',
    'fecha'     => pdf_fecha_es($ordenes[0]->fecha_salida ?? date('Y-m-d')),
    'estatus'   => $ordenes[0]->estatus ?? 'NO VALIDADA',
    'area'      => $ordenes[0]->nombre_area ?? '',
    'nota'      => $ordenes[0]->nota ?? '',
);
$mostrarCostos = $orden['estatus'] === 'CONFIRMADA';

$items = array();
foreach ($detallesOrden as $detalle) {
    $items[] = array(
        'producto' => $detalle->nombre_producto,
        'ubicacion' => $detalle->ubicacion ?? '',
        'cantidad' => (int)round((float)$detalle->cantidad),
        'costo_promedio' => $mostrarCostos ? (float)($detalle->costo_promedio ?? 0) : null,
        'subtotal' => $mostrarCostos
            ? ((int)round((float)$detalle->cantidad)) * (float)($detalle->costo_promedio ?? 0)
            : null,
    );
}

// =====================
// GENERAR QR
// =====================

$qrData = 'https://grupocroram.com/almacen/dist/ordenes-salida-detalle.php?id=' . ($_GET['id'] ?? 0);
$qrFile = tempnam(sys_get_temp_dir(), 'os_qr_');
if ($qrFile === false) {
    throw new RuntimeException('No se pudo crear el QR de la orden de salida.');
}
register_shutdown_function(static function () use ($qrFile) {
    if (is_file($qrFile)) {
        unlink($qrFile);
    }
});

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

$pdf = new OrdenSalidaPDF();
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

$pdf->InfoBox('FECHA DE SALIDA:', $orden['fecha'], 95, false);
$pdf->InfoBox('ESTATUS:', $orden['estatus'], 95, true);
$pdf->InfoBox('AREA:', $orden['area'] !== '' ? $orden['area'] : 'N/A', 95, true);

if (trim((string)$orden['nota']) !== '') {
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(73, 80, 87);
    $pdf->Cell(0, 5, 'OBSERVACIONES:', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->MultiCell(0, 4, pdf_text($orden['nota']), 1, 'L');
}

$pdf->Ln(5);

// =====================
// TABLA DE PRODUCTOS
// =====================

$pdf->SectionTitle('DETALLE DE PRODUCTOS');

$pdf->ProductTableHeader();

// Items de la tabla
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetDrawColor(222, 226, 230);

$totalUnidades = 0;
$totalCosto = 0;
$i = 1;
$fill = false;

foreach ($items as $item) {
    $totalUnidades += $item['cantidad'];
    $totalCosto += (float)($item['subtotal'] ?? 0);
    
    $pdf->ProductRow($i, $item, $mostrarCostos, $fill);
    $fill = !$fill;
    $i++;
}

// Línea final de la tabla
$pdf->Cell(190, 0, '', 'T', 1);

$pdf->Ln(5);

// =====================
// RESUMEN DE CANTIDADES
// =====================

// Posicionar a la derecha
$pdf->SetX(100);

// Box contenedor del resumen
$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

// Total de artículos
$pdf->SetFont('Arial', '', 8);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetX(100);
$pdf->Cell(50, 6, 'Total de productos:', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(40, 6, count($items), 1, 1, 'C', true);

// Total de unidades - Destacado
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(220, 53, 69);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetX(100);
$pdf->Cell(50, 7, 'TOTAL UNIDADES:', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, $totalUnidades, 1, 1, 'C', true);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(220, 53, 69);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetX(100);
$pdf->Cell(50, 7, 'TOTAL COSTO:', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, $mostrarCostos ? '$' . number_format($totalCosto, 2) : 'PENDIENTE', 1, 1, 'C', true);

$pdf->Ln(7);

// =====================
// NOTA IMPORTANTE
// =====================

$pdf->SetDrawColor(220, 53, 69);
$pdf->SetLineWidth(0.5);
$pdf->SetFillColor(255, 243, 245);

// Caja de nota
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(220, 53, 69);
$pdf->Cell(0, 5, pdf_text('  NOTA IMPORTANTE'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetDrawColor(222, 226, 230);
$pdf->MultiCell(0, 4, pdf_text('Los productos listados en esta orden serán descontados del inventario. Verifique las cantidades antes de aprobar la salida.'), 1, 'L', true);

$pdf->Ln(5);

// Mantener QR y firmas juntos; si no caben, pasan completos a la siguiente hoja.
if ($pdf->GetY() + $pdf->ValidationBlockHeight() > 267) {
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
    $pdf->Output('I', 'orden_salida_' . $orden['folio'] . '.pdf');
exit;
