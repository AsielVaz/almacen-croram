<?php
require('fpdf/fpdf.php');
require_once 'phpqrcode/qrlib.php';

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
        $this->Image(
            'https://grupocroram.com/almacen/dist/assets/images/logo_almacen.png',
            160,
            10,
            35
        );

        // Título principal
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(52, 58, 64);
        $this->SetXY(15, 12);
        $this->Cell(0, 8, utf8_decode('ORDEN DE COMPRA'), 0, 1, 'L');
        
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
        $this->Cell(0, 5, utf8_decode('Almacén Croram | Sistema de Gestión de Inventario'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
    
    function InfoBox($label, $value, $width, $isLast = false)
    {
        // Etiqueta
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(73, 80, 87);
        $this->SetFillColor(248, 249, 250);
        $this->Cell($width / 2, 7, utf8_decode($label), 1, 0, 'L', true);
        
        // Valor
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(33, 37, 41);
        $this->SetFillColor(255, 255, 255);
        $this->Cell($width / 2, 7, utf8_decode($value), 1, $isLast ? 1 : 0, 'L', true);
    }
    
    function SectionTitle($title, $icon = '')
    {
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(73, 80, 87);
        $this->SetFillColor(248, 249, 250);
        
        $this->Cell(0, 8, utf8_decode($icon . ' ' . $title), 0, 1, 'L', false);
        
        // Línea decorativa
        $this->SetDrawColor(73, 80, 87);
        $this->SetLineWidth(0.5);
        $y = $this->GetY();
        $this->Line(10, $y, 50, $y);
        
        $this->Ln(5);
    }
}

/* =====================
   DATOS DE LA ORDEN
===================== */

include_once 'adminOrdenes.php';
$adminOrdenes = new AdministradorOrdenes();
$ordenes = json_decode($adminOrdenes->obtenerOrdenCompra($_GET['id'] ?? 0));
$detallesOrden = json_decode($adminOrdenes->listarDetallesOrden($_GET['id'] ?? 0));

$orden = array(
    'folio'     => $ordenes[0]->folio ?? 'OC-00000',
    'fecha'     => $ordenes[0]->fecha_orden ?? date('Y-m-d'),
    'proveedor' => $ordenes[0]->nombre_proveedor ?? 'SIN PROVEEDOR',
    'estatus'   => $ordenes[0]->estatus ?? 'NO VALIDADA',
);

$items = array();
foreach ($detallesOrden as $detalle) {
    $items[] = array(
        'producto' => $detalle->nombre_producto,
            'cantidad' => (int)round((float)$detalle->cantidad),
        'precio'   => $detalle->precio_unitario,
    );
}

// =====================
// GENERAR QR
// =====================

$qrData = 'https://grupocroram.com/almacen/dist/ordenes-entrada-detalle.php?id=' . ($_GET['id'] ?? 0);
$qrFile = sys_get_temp_dir() . '/qr_' . md5($orden['folio']) . '.png';

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

$pdf->SectionTitle('DATOS GENERALES', chr(149));

// Usar el método InfoBox
$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

$pdf->InfoBox('FECHA DE ORDEN:', $orden['fecha'], 95, false);
$pdf->InfoBox('ESTATUS:', $orden['estatus'], 95, true);

$pdf->InfoBox('PROVEEDOR:', $orden['proveedor'], 190, true);

$pdf->Ln(8);

// =====================
// TABLA DE PRODUCTOS
// =====================

$pdf->SectionTitle('DETALLE DE PRODUCTOS', chr(149));

// Header de tabla con diseño moderno
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(73, 80, 87);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(73, 80, 87);
$pdf->SetLineWidth(0.3);

$pdf->Cell(12, 9, '#', 1, 0, 'C', true);
$pdf->Cell(88, 9, utf8_decode('DESCRIPCIÓN'), 1, 0, 'C', true);
$pdf->Cell(25, 9, 'CANTIDAD', 1, 0, 'C', true);
$pdf->Cell(30, 9, 'PRECIO UNIT.', 1, 0, 'C', true);
$pdf->Cell(35, 9, 'SUBTOTAL', 1, 1, 'C', true);

// Items de la tabla
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetDrawColor(222, 226, 230);

$total = 0;
$i = 1;
$fill = false;

foreach ($items as $item) {
    $sub = $item['cantidad'] * $item['precio'];
    $total += $sub;
    
    // Alternar color de fondo
    if ($fill) {
        $pdf->SetFillColor(248, 249, 250);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    
    $pdf->Cell(12, 8, $i, 'LR', 0, 'C', true);
    $pdf->Cell(88, 8, utf8_decode($item['producto']), 'LR', 0, 'L', true);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(25, 8, $item['cantidad'], 'LR', 0, 'C', true);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(30, 8, '$' . number_format($item['precio'], 2), 'LR', 0, 'R', true);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(35, 8, '$' . number_format($sub, 2), 'LR', 1, 'R', true);
    
    $pdf->SetFont('Arial', '', 9);
    $fill = !$fill;
    $i++;
}

// Línea final de la tabla
$pdf->Cell(190, 0, '', 'T', 1);

$pdf->Ln(8);

// =====================
// RESUMEN FINANCIERO
// =====================

$iva = $total * 0.16;
$grand = $total + $iva;

// Posicionar a la derecha
$pdf->SetX(110);

// Box contenedor del resumen
$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

// Subtotal
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetX(110);
$pdf->Cell(50, 7, 'Subtotal:', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(40, 7, '$' . number_format($total, 2), 1, 1, 'R', true);

// IVA
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetX(110);
$pdf->Cell(50, 7, 'IVA (16%):', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetFillColor(255, 255, 255);
$pdf->Cell(40, 7, '$' . number_format($iva, 2), 1, 1, 'R', true);

// Total - Destacado
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(73, 80, 87);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetX(110);
$pdf->Cell(50, 9, 'TOTAL:', 1, 0, 'L', true);

$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(40, 9, '$' . number_format($grand, 2), 1, 1, 'R', true);

$pdf->Ln(15);

// =====================
// SECCIÓN DE FIRMAS
// =====================

$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

// Configuración
$xInicio = 30;
$anchoFirma = 70;
$espacio = 20;

// Título de sección
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(73, 80, 87);
$pdf->Cell(0, 6, utf8_decode('VALIDACIÓN Y AUTORIZACIÓN'), 0, 1, 'C');
$pdf->Ln(5);

// Cajas de firma con borde
$pdf->SetFillColor(248, 249, 250);

// Firma 1 - Responsable de Almacén
$pdf->SetXY($xInicio, $pdf->GetY());
$pdf->Cell($anchoFirma, 25, '', 1, 0, 'C', true);

// Firma 2 - Autoriza Compra
$pdf->SetXY($xInicio + $anchoFirma + $espacio, $pdf->GetY());
$pdf->Cell($anchoFirma, 25, '', 1, 1, 'C', true);

$pdf->Ln(3);

// Títulos bajo las firmas
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(33, 37, 41);

$pdf->SetX($xInicio);
$pdf->Cell($anchoFirma, 5, utf8_decode('RESPONSABLE DE ALMACÉN'), 0, 0, 'C');

$pdf->SetX($xInicio + $anchoFirma + $espacio);
$pdf->Cell($anchoFirma, 5, utf8_decode('AUTORIZA COMPRA'), 0, 1, 'C');

$pdf->Ln(2);

// Líneas para nombre
$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.2);

$y = $pdf->GetY();
$pdf->Line($xInicio + 5, $y, $xInicio + $anchoFirma - 5, $y);
$pdf->Line($xInicio + $anchoFirma + $espacio + 5, $y, $xInicio + $anchoFirma + $espacio + $anchoFirma - 5, $y);

$pdf->Ln(1);

// Texto "Nombre y Firma"
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(108, 117, 125);

$pdf->SetX($xInicio);
$pdf->Cell($anchoFirma, 4, 'Nombre y Firma', 0, 0, 'C');

$pdf->SetX($xInicio + $anchoFirma + $espacio);
$pdf->Cell($anchoFirma, 4, 'Nombre y Firma', 0, 1, 'C');

$pdf->Ln(4);

// Campos de fecha
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(73, 80, 87);

$pdf->SetX($xInicio);
$pdf->Cell($anchoFirma, 4, 'Fecha: ___________________', 0, 0, 'C');

$pdf->SetX($xInicio + $anchoFirma + $espacio);
$pdf->Cell($anchoFirma, 4, 'Fecha: ___________________', 0, 1, 'C');

// =====================
// QR CODE
// =====================

// Posicionar QR en esquina inferior izquierda con marco
$qrY = 255;
$qrX = 12;

// Marco para el QR
$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);
$pdf->Rect($qrX - 2, $qrY - 2, 34, 34);

// QR
$pdf->Image($qrFile, $qrX, $qrY, 30);

// Texto bajo el QR
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(108, 117, 125);
$pdf->SetXY($qrX - 5, $qrY + 32);
$pdf->Cell(40, 3, utf8_decode('Escanea para ver online'), 0, 0, 'C');

@unlink($qrFile);

/* =====================
   OUTPUT
===================== */

$pdf->Output('I', 'orden_compra_' . $orden['folio'] . '.pdf');
exit;
