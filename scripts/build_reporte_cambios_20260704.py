from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "output" / "pdf" / "reporte_cambios_croram_2026-07-04.pdf"


def style_sheet():
    styles = getSampleStyleSheet()
    styles.add(
        ParagraphStyle(
            name="TitleCroram",
            parent=styles["Title"],
            fontName="Helvetica-Bold",
            fontSize=20,
            leading=24,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#1f2937"),
            spaceAfter=8,
        )
    )
    styles.add(
        ParagraphStyle(
            name="SubtitleCroram",
            parent=styles["Normal"],
            fontName="Helvetica",
            fontSize=10,
            leading=14,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#4b5563"),
            spaceAfter=18,
        )
    )
    styles.add(
        ParagraphStyle(
            name="Section",
            parent=styles["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=13,
            leading=16,
            textColor=colors.HexColor("#111827"),
            spaceBefore=12,
            spaceAfter=8,
        )
    )
    styles.add(
        ParagraphStyle(
            name="Body",
            parent=styles["BodyText"],
            fontName="Helvetica",
            fontSize=9.3,
            leading=12.5,
            alignment=TA_LEFT,
            textColor=colors.HexColor("#111827"),
        )
    )
    styles.add(
        ParagraphStyle(
            name="Small",
            parent=styles["BodyText"],
            fontName="Helvetica",
            fontSize=8.3,
            leading=11,
            textColor=colors.HexColor("#374151"),
        )
    )
    return styles


def p(text, style):
    return Paragraph(text.replace("\n", "<br/>"), style)


def build_table(rows, styles):
    data = [[p("Cambio aplicado", styles["Small"]), p("Detalle tecnico", styles["Small"])]]
    for left, right in rows:
        data.append([p(left, styles["Body"]), p(right, styles["Body"])])

    table = Table(data, colWidths=[2.3 * inch, 4.3 * inch], repeatRows=1)
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#e5e7eb")),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#111827")),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                ("GRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#d1d5db")),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 7),
                ("RIGHTPADDING", (0, 0), (-1, -1), 7),
                ("TOPPADDING", (0, 0), (-1, -1), 6),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
                ("BACKGROUND", (0, 1), (-1, -1), colors.white),
            ]
        )
    )
    return table


def main():
    OUT.parent.mkdir(parents=True, exist_ok=True)
    styles = style_sheet()

    doc = SimpleDocTemplate(
        str(OUT),
        pagesize=letter,
        rightMargin=0.55 * inch,
        leftMargin=0.55 * inch,
        topMargin=0.55 * inch,
        bottomMargin=0.55 * inch,
        title="Reporte de cambios Croram",
        author="Codex",
    )

    story = [
        p("Reporte de cambios aplicados", styles["TitleCroram"]),
        p("Cliente: Croram | Fecha: 04 de julio de 2026 | Proyecto: Sistema de almacen", styles["SubtitleCroram"]),
        p("Resumen", styles["Section"]),
        p(
            "Se aplicaron cambios en el flujo de inventario, costos PEPS, reportes, ordenes de entrada, generacion de etiquetas QR y edicion de articulos. "
            "Tambien se corrigieron registros existentes donde habia costos en cero con respaldo de ultima compra.",
            styles["Body"],
        ),
        Spacer(1, 10),
        p("Cambios aplicados", styles["Section"]),
    ]

    rows = [
        (
            "Entradas duplicadas de inventario",
            "Se confirmo que la base de datos ya cuenta con triggers para sumar inventario cuando una orden cambia a RECIBIDA. "
            "Se retiro la suma manual en el flujo web y en el API movil para que el inventario no se incremente dos veces.",
        ),
        (
            "Salidas con costo PEPS",
            "Se ajustaron las consultas de salidas para mostrar costo PEPS y subtotal. Cuando el costo esta en cero, el sistema toma costo unitario, costo de reposicion o ultima compra recibida como respaldo.",
        ),
        (
            "Reporte de movimientos de salida",
            "Los reportes de salidas ahora calculan unitario y total aunque el detalle historico tenga subtotal en cero, usando el costo disponible mas confiable.",
        ),
        (
            "Reporte de consumos por area",
            "El reporte de consumos por area calcula total y costo PEPS promedio con respaldo por ultima compra cuando los registros anteriores no tienen costo grabado.",
        ),
        (
            "Reporte de inventario",
            "Se corrigio el valor de inventario y costo promedio para considerar lotes PEPS, stock sin lote y ultima compra como respaldo de costo.",
        ),
        (
            "PEPS y lotes legacy",
            "El consumo PEPS ya no genera lotes legacy si el producto ya tiene lotes de compra registrados. Esto evita consumir primero lotes con costo cero.",
        ),
        (
            "Orden de entrada antes de autorizar",
            "Al autorizar una orden de entrada se muestra un resumen con proveedor, fecha y precio de ultima compra para validar la propuesta.",
        ),
        (
            "Etiquetas QR",
            "Los formatos de QR termico y carta imprimen la descripcion del articulo junto al QR de 4x4 cm. El texto se limita a 50 caracteres y ajusta el tamano de letra.",
        ),
        (
            "Edicion de articulos",
            "Al editar un articulo, el costo capturado se trata como costo total actual. Al guardar se calcula y almacena el costo por unidad dividiendo entre las unidades.",
        ),
        (
            "Correccion de datos existentes",
            "Se actualizaron costos en cero cuando existia costo de reposicion o ultima compra recibida. El caso Volante creta 2022 quedo con PEPS de $1,280.00 y subtotal de $2,560.00 en la salida OS-20260630103851.",
        ),
    ]
    story.append(build_table(rows, styles))

    story.extend(
        [
            Spacer(1, 12),
            p("Validaciones realizadas", styles["Section"]),
            p(
                "Se valido sintaxis PHP en los archivos modificados. Se probaron consultas del reporte de salidas, consumos por area e inventario. "
                "Tambien se probaron los PDFs de QR termico y carta con respuesta correcta.",
                styles["Body"],
            ),
            Spacer(1, 10),
            p("Pendiente observado", styles["Section"]),
            p(
                "Quedan algunos movimientos historicos sin costo porque no tienen costo de reposicion ni compra recibida de respaldo. "
                "No se asignaron valores manuales para evitar inventar precios.",
                styles["Body"],
            ),
        ]
    )

    doc.build(story)
    print(OUT)


if __name__ == "__main__":
    main()
