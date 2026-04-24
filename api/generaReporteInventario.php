<?php
require('fpdf/fpdf.php');

class ReporteInventarioPDF extends FPDF
{
    private $fechaReporte;
    private $totalArticulos;

    function setReporteData($fecha, $total) {
        $this->fechaReporte = $fecha;
        $this->totalArticulos = $total;
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
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(52, 58, 64);
        $this->SetXY(15, 12);
        $this->Cell(0, 8, utf8_decode('REPORTE DE CONCILIACIÓN'), 0, 1, 'L');
        
        $this->SetFont('Arial', '', 11);
        $this->SetXY(15, 21);
        $this->Cell(0, 5, utf8_decode('Inventario Físico'), 0, 1, 'L');
        
        // Línea decorativa bajo el título
        $this->SetDrawColor(73, 80, 87);
        $this->SetLineWidth(0.8);
        $this->Line(15, 28, 85, 28);
        
        // Fecha del reporte
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(108, 117, 125);
        $this->SetXY(15, 31);
        $this->Cell(25, 4, 'FECHA:', 0, 0, 'L');
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 4, $this->fechaReporte, 0, 1, 'L');
        
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
   RECIBIR DATOS
===================== */

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

if (!$datos || !isset($datos['conciliacion'])) {
    die('Datos inválidos');
}

$fechaReporte = $datos['fecha'] ?? date('Y-m-d');
$conciliacion = $datos['conciliacion'];

/* =====================
   CALCULAR RESUMEN
===================== */

$totalArticulos = count($conciliacion);
$conDiferencia = 0;
$sobrante = 0;
$faltante = 0;
$valorSobrante = 0;
$valorFaltante = 0;

foreach ($conciliacion as $item) {
    if ($item['diferencia'] != 0) {
        $conDiferencia++;
        
        if ($item['diferencia'] > 0) {
            $sobrante += $item['diferencia'];
        } else {
            $faltante += abs($item['diferencia']);
        }
    }
}

/* =====================
   GENERAR PDF
===================== */

$pdf = new ReporteInventarioPDF();
$pdf->setReporteData($fechaReporte, $totalArticulos);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

// =====================
// RESUMEN GENERAL
// =====================

$pdf->SectionTitle('RESUMEN DE CONCILIACIÓN', chr(149));

$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

$pdf->InfoBox('TOTAL DE ARTÍCULOS REVISADOS:', $totalArticulos, 95, false);
$pdf->InfoBox('ARTÍCULOS CON DIFERENCIA:', $conDiferencia, 95, true);

$pdf->InfoBox('UNIDADES SOBRANTES:', $sobrante, 95, false);
$pdf->InfoBox('UNIDADES FALTANTES:', $faltante, 95, true);

$pdf->Ln(8);

// =====================
// TABLA DE CONCILIACIÓN
// =====================

$pdf->SectionTitle('DETALLE DE CONCILIACIÓN', chr(149));

// Header de tabla
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(73, 80, 87);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(73, 80, 87);
$pdf->SetLineWidth(0.3);

$pdf->Cell(10, 9, 'ID', 1, 0, 'C', true);
$pdf->Cell(70, 9, utf8_decode('ARTÍCULO'), 1, 0, 'C', true);
$pdf->Cell(25, 9, 'SKU', 1, 0, 'C', true);
$pdf->Cell(20, 9, 'SISTEMA', 1, 0, 'C', true);
$pdf->Cell(20, 9, 'REAL', 1, 0, 'C', true);
$pdf->Cell(25, 9, 'DIFERENCIA', 1, 1, 'C', true);

// Items de la tabla
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetDrawColor(222, 226, 230);

$fill = false;

foreach ($conciliacion as $item) {
    // Alternar color de fondo
    if ($fill) {
        $pdf->SetFillColor(248, 249, 250);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    
    $pdf->Cell(10, 8, $item['id_articulo'], 'LR', 0, 'C', true);
    
    // Nombre del artículo (puede ser largo)
    $nombreCorto = substr($item['nombre_articulo'], 0, 50);
    if (strlen($item['nombre_articulo']) > 50) {
        $nombreCorto .= '...';
    }
    $pdf->Cell(70, 8, utf8_decode($nombreCorto), 'LR', 0, 'L', true);
    
    $pdf->Cell(25, 8, $item['sku'], 'LR', 0, 'C', true);
    
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 8, $item['sistema'], 'LR', 0, 'C', true);
    
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(20, 8, $item['real'], 'LR', 0, 'C', true);
    
    // Diferencia con color
    $pdf->SetFont('Arial', 'B', 8);
    $diferencia = $item['diferencia'];
    
    if ($diferencia > 0) {
        $pdf->SetTextColor(40, 167, 69); // Verde
        $texto = '+' . $diferencia;
    } elseif ($diferencia < 0) {
        $pdf->SetTextColor(220, 53, 69); // Rojo
        $texto = $diferencia;
    } else {
        $pdf->SetTextColor(108, 117, 125); // Gris
        $texto = '0';
    }
    
    $pdf->Cell(25, 8, $texto, 'LR', 1, 'C', true);
    
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetFont('Arial', '', 8);
    $fill = !$fill;
}

// Línea final de la tabla
$pdf->Cell(170, 0, '', 'T', 1);

$pdf->Ln(10);

// =====================
// RESUMEN FINAL
// =====================

// Posicionar a la derecha
$pdf->SetX(110);

$pdf->SetDrawColor(222, 226, 230);
$pdf->SetLineWidth(0.3);

// Box de resumen
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetX(110);
$pdf->Cell(50, 7, 'Total revisados:', 1, 0, 'L', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(30, 7, $totalArticulos, 1, 1, 'C', true);

// Con diferencias
$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetX(110);
$pdf->Cell(50, 7, 'Con diferencias:', 1, 0, 'L', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(220, 53, 69);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 7, $conDiferencia, 1, 1, 'C', true);

$pdf->Ln(15);

// =====================
// NOTA IMPORTANTE
// =====================

$pdf->SetDrawColor(255, 193, 7);
$pdf->SetLineWidth(0.5);
$pdf->SetFillColor(255, 243, 205);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(133, 100, 4);
$pdf->Cell(0, 7, utf8_decode('  OBSERVACIONES'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(73, 80, 87);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetDrawColor(222, 226, 230);
$pdf->MultiCell(0, 5, utf8_decode('Las diferencias encontradas deben ser verificadas y ajustadas en el sistema. Se recomienda investigar las causas de las discrepancias antes de realizar correcciones masivas.'), 1, 'L', true);

$pdf->Ln(10);

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
$pdf->Cell(0, 6, utf8_decode('VALIDACIÓN'), 0, 1, 'C');
$pdf->Ln(5);

// Cajas de firma con borde
$pdf->SetFillColor(248, 249, 250);

// Firma 1 - Realizó Conciliación
$pdf->SetXY($xInicio, $pdf->GetY());
$pdf->Cell($anchoFirma, 25, '', 1, 0, 'C', true);

// Firma 2 - Revisó y Autorizó
$pdf->SetXY($xInicio + $anchoFirma + $espacio, $pdf->GetY());
$pdf->Cell($anchoFirma, 25, '', 1, 1, 'C', true);

$pdf->Ln(3);

// Títulos bajo las firmas
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(33, 37, 41);

$pdf->SetX($xInicio);
$pdf->Cell($anchoFirma, 5, utf8_decode('REALIZÓ CONCILIACIÓN'), 0, 0, 'C');

$pdf->SetX($xInicio + $anchoFirma + $espacio);
$pdf->Cell($anchoFirma, 5, utf8_decode('REVISÓ Y AUTORIZÓ'), 0, 1, 'C');

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

/* =====================
   OUTPUT
===================== */

$pdf->Output('I', 'reporte_conciliacion_' . $fechaReporte . '.pdf');
exit;