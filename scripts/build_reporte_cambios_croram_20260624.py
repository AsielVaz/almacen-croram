from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "entregables" / "reporte_cambios_croram_2026-06-24.pdf"


def bullet(text, style):
    return Paragraph(f"- {text}", style)


def build():
    OUT.parent.mkdir(parents=True, exist_ok=True)
    styles = getSampleStyleSheet()
    styles.add(ParagraphStyle(
        name="TitleCroram",
        parent=styles["Title"],
        fontName="Helvetica-Bold",
        fontSize=18,
        leading=22,
        textColor=colors.HexColor("#1f2937"),
        spaceAfter=12,
    ))
    styles.add(ParagraphStyle(
        name="Subtitle",
        parent=styles["Normal"],
        fontSize=10,
        leading=14,
        textColor=colors.HexColor("#4b5563"),
        spaceAfter=18,
    ))
    styles.add(ParagraphStyle(
        name="Section",
        parent=styles["Heading2"],
        fontName="Helvetica-Bold",
        fontSize=12,
        leading=15,
        textColor=colors.HexColor("#111827"),
        spaceBefore=10,
        spaceAfter=8,
    ))
    styles.add(ParagraphStyle(
        name="BodyC",
        parent=styles["BodyText"],
        fontSize=9,
        leading=12,
        textColor=colors.HexColor("#111827"),
        spaceAfter=5,
    ))

    story = [
        Paragraph("Reporte de cambios aplicados - Croram", styles["TitleCroram"]),
        Paragraph("Fecha: 24 de junio de 2026", styles["Subtitle"]),
        Paragraph("Cambios aplicados", styles["Section"]),
    ]

    applied = [
        "Se agrego generacion automatica de SKU consecutivo para articulos nuevos cuando el campo SKU queda vacio.",
        "Se corrigio el indicador de siguiente ID y el preview de SKU en alta de articulos.",
        "Se agrego carga de inventario actual al editar articulos y guardado de inventario exacto en edicion.",
        "Se cambio la recepcion de ordenes de entrada para usar las cantidades registradas en la orden y evitar duplicados provenientes del formulario.",
        "Se agrego autorizacion de orden de compra antes de ingresar partes, dejando el flujo en tres pasos: captura, autorizacion e ingreso.",
        "Se agrego boton de autorizacion en el detalle de orden de entrada y se bloqueo el ingreso hasta que la orden este autorizada.",
        "Se agrego fecha de inicio de analisis al reporte de compras sugeridas.",
        "Se ajusto el calculo de compras sugeridas para usar el promedio desde la fecha de inicio hasta hoy, con maximo de 12 meses.",
        "Se agrego campo de pedido confirmado en compras sugeridas para modificar el pedido sugerido por pieza.",
        "Se agregaron columnas de valor de inventario y costo por unidad en el reporte de inventario y en su exportacion.",
        "Se dividio el menu de reportes en Inventario, Proveedor, Entradas y salidas, Compras sugeridas y Reporte de articulos.",
        "Se dividio la pantalla de reportes por secciones para que no muestre toda la informacion junta.",
        "Se mantuvieron filtros por rango de fechas en reportes de entradas, salidas, compras por proveedor y consumos por area.",
        "Se agrego area, observaciones, precio, total por partida y total de costo en el PDF de orden de salida.",
        "Se dejaron las unidades sin decimales en vistas y reportes relevantes.",
        "Se ordenaron las listas de ordenes de entrada y salida de mas recientes a mas antiguas.",
        "Se agrego debounce a la busqueda de articulos para reducir retrasos al escribir.",
        "Se ajusto el formato de QR en hoja carta a QR de 4x4 cm con nombre a la derecha y texto truncado a 50 caracteres.",
    ]

    for item in applied:
        story.append(bullet(item, styles["BodyC"]))

    story.append(Spacer(1, 8))
    story.append(Paragraph("Cambios no aplicados completamente", styles["Section"]))

    pending_rows = [
        ["Solicitud", "Motivo"],
        ["Campo Ubicacion en articulos y formatos", "No se encontro una columna de base de datos existente para guardar Ubicacion sin migracion."],
        ["Validacion fisica por QR en recepcion y entrega", "Requiere definir el flujo operativo de escaneo por partida y el dato exacto que debe validar el lector."],
        ["Reporte de articulos obsoletos", "Requiere confirmar criterio final: sin salidas, sin entradas, o sin cualquier movimiento durante 12 meses."],
    ]
    table = Table(pending_rows, colWidths=[2.2 * inch, 4.6 * inch])
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#e5e7eb")),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#111827")),
        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
        ("FONTNAME", (0, 1), (-1, -1), "Helvetica"),
        ("FONTSIZE", (0, 0), (-1, -1), 8),
        ("LEADING", (0, 0), (-1, -1), 10),
        ("GRID", (0, 0), (-1, -1), 0.25, colors.HexColor("#d1d5db")),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    story.append(table)

    doc = SimpleDocTemplate(
        str(OUT),
        pagesize=letter,
        rightMargin=0.55 * inch,
        leftMargin=0.55 * inch,
        topMargin=0.55 * inch,
        bottomMargin=0.55 * inch,
        title="Reporte de cambios aplicados - Croram",
        author="Codex",
    )
    doc.build(story)


if __name__ == "__main__":
    build()
    print(OUT)
