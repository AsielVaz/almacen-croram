from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "entregables" / "reporte_avances_programador_croram_2026-06-30.pdf"


def p(text, style):
    return Paragraph(text, style)


def bullet(text, style):
    return Paragraph(f"- {text}", style)


def build():
    OUT.parent.mkdir(parents=True, exist_ok=True)

    styles = getSampleStyleSheet()
    styles.add(ParagraphStyle(
        name="DocTitle",
        parent=styles["Title"],
        fontName="Helvetica-Bold",
        fontSize=18,
        leading=22,
        textColor=colors.HexColor("#1f2937"),
        spaceAfter=8,
    ))
    styles.add(ParagraphStyle(
        name="Meta",
        parent=styles["Normal"],
        fontSize=9,
        leading=12,
        textColor=colors.HexColor("#4b5563"),
        spaceAfter=14,
    ))
    styles.add(ParagraphStyle(
        name="Section",
        parent=styles["Heading2"],
        fontName="Helvetica-Bold",
        fontSize=12,
        leading=15,
        textColor=colors.HexColor("#111827"),
        spaceBefore=8,
        spaceAfter=6,
    ))
    styles.add(ParagraphStyle(
        name="Body",
        parent=styles["BodyText"],
        fontSize=9,
        leading=12,
        textColor=colors.HexColor("#111827"),
        spaceAfter=4,
    ))
    styles.add(ParagraphStyle(
        name="Small",
        parent=styles["BodyText"],
        fontSize=8,
        leading=10,
        textColor=colors.HexColor("#374151"),
    ))

    story = [
        p("Reporte de avances tecnicos - Croram", styles["DocTitle"]),
        p("Entrega preparada por programacion | Fecha: 30 de junio de 2026", styles["Meta"]),
        p("Resumen", styles["Section"]),
        p(
            "Se trabajaron ajustes sobre ordenes de entrada, ordenes de salida, reportes, "
            "catalogo de articulos, validacion por QR y calculos de inventario con costo PEPS.",
            styles["Body"],
        ),
        Spacer(1, 6),
        p("Avances realizados punto por punto", styles["Section"]),
    ]

    rows = [
        ["Punto", "Avance entregado", "Impacto tecnico"],
        [
            "1",
            "Catalogo de articulos con paginacion por API y busqueda en tiempo real.",
            "La pantalla deja de cargar todos los articulos en HTML y consulta a api/apiArticulos.php por pagina.",
        ],
        [
            "2",
            "PDF de orden de entrada corregido para mostrar precio unitario y subtotal.",
            "El generador usa precio_unitario real del detalle de compra y calcula subtotal por partida.",
        ],
        [
            "3",
            "Eliminacion del simbolo extrano al inicio de titulos de PDFs.",
            "Se removio el caracter que se enviaba antes de DATOS GENERALES y DETALLE DE PRODUCTOS.",
        ],
        [
            "4",
            "Proteccion contra duplicar entradas al ingresar partes.",
            "Antes de sumar inventario se valida si la orden ya genero lotes de inventario.",
        ],
        [
            "5",
            "Visualizacion de ultima compra en ordenes de entrada.",
            "En el detalle de entrada se muestra proveedor, fecha y precio de la ultima compra recibida del articulo.",
        ],
        [
            "6",
            "Calculo de inventario basado en costo PEPS disponible.",
            "Valor de inventario y costo por unidad se calculan con inventario_lotes y cantidades disponibles.",
        ],
        [
            "7",
            "Reportes de inventario ajustados por estatus real.",
            "Entradas solo consideran ordenes RECIBIDA y salidas solo consideran ordenes CONFIRMADA.",
        ],
        [
            "8",
            "Validacion por QR/SKU o ID en recepcion de entradas.",
            "Cada partida debe validar el articulo correcto antes de guardar la entrada.",
        ],
        [
            "9",
            "Validacion por QR/SKU o ID antes de aprobar salidas.",
            "La salida fisica se bloquea si el codigo escaneado no coincide con el articulo solicitado.",
        ],
        [
            "10",
            "Reporte de articulos obsoletos.",
            "Se agrego reporte con existencia, valor de inventario, costo por unidad y ultimo movimiento.",
        ],
        [
            "11",
            "Exportacion de articulos obsoletos.",
            "Se agrego salida Excel desde reportes-exportar.php para el nuevo reporte.",
        ],
        [
            "12",
            "Menu de reportes actualizado.",
            "Se agrego acceso a Obsoletos dentro del menu lateral de Reportes.",
        ],
    ]

    table = Table(rows, colWidths=[0.45 * inch, 2.85 * inch, 3.4 * inch], repeatRows=1)
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#e5e7eb")),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#111827")),
        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
        ("FONTNAME", (0, 1), (-1, -1), "Helvetica"),
        ("FONTSIZE", (0, 0), (-1, -1), 7.5),
        ("LEADING", (0, 0), (-1, -1), 9),
        ("GRID", (0, 0), (-1, -1), 0.25, colors.HexColor("#d1d5db")),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 5),
        ("RIGHTPADDING", (0, 0), (-1, -1), 5),
        ("TOPPADDING", (0, 0), (-1, -1), 4),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
        ("ALIGN", (0, 1), (0, -1), "CENTER"),
    ]))
    story.append(table)

    story.extend([
        Spacer(1, 10),
        p("Archivos principales modificados", styles["Section"]),
        bullet("api/adminArticulos.php - calculos PEPS, paginacion, reporte de obsoletos.", styles["Small"]),
        bullet("api/adminOrdenes.php - datos de ultima compra, validacion de lotes, SKU en detalles.", styles["Small"]),
        bullet("api/apiOrdenes.php - bloqueo para evitar entradas duplicadas.", styles["Small"]),
        bullet("articulos.php - nueva interfaz con busqueda y paginacion por API.", styles["Small"]),
        bullet("ordenes-entrada-detalle.php - validacion QR y ultima compra visible.", styles["Small"]),
        bullet("ordenes-salida-detalle.php - validacion QR antes de aprobar salida.", styles["Small"]),
        bullet("reportes.php y reportes-exportar.php - obsoletos e inventario ajustado.", styles["Small"]),
        bullet("api/generaOrdenSalidaPdf.php y api/generaOrdenSalidaInventarioPdf.php - PDFs corregidos.", styles["Small"]),
        Spacer(1, 8),
        p("Validacion realizada", styles["Section"]),
        bullet("Se ejecuto revision de sintaxis PHP en los archivos modificados.", styles["Small"]),
        bullet("Se probaron consultas contra base de datos para paginacion de articulos, inventario y obsoletos.", styles["Small"]),
        bullet("Se verifico que el PDF generado contenga texto extraible y la estructura esperada.", styles["Small"]),
    ])

    doc = SimpleDocTemplate(
        str(OUT),
        pagesize=letter,
        rightMargin=0.55 * inch,
        leftMargin=0.55 * inch,
        topMargin=0.55 * inch,
        bottomMargin=0.55 * inch,
        title="Reporte de avances tecnicos - Croram",
        author="Programacion",
    )
    doc.build(story)
    print(OUT)


if __name__ == "__main__":
    build()
