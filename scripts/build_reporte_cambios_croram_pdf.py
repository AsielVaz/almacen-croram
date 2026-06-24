from datetime import date
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
    PageBreak,
)


ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "entregables"
OUT_DIR.mkdir(exist_ok=True)
OUT_PDF = OUT_DIR / "reporte_cambios_croram.pdf"

BLUE = colors.HexColor("#2E74B5")
DARK_BLUE = colors.HexColor("#1F4D78")
LIGHT_BLUE = colors.HexColor("#E8EEF5")
LIGHT_GRAY = colors.HexColor("#F2F4F7")
MID_GRAY = colors.HexColor("#606060")
INK = colors.HexColor("#202020")


def header_footer(canvas, doc):
    canvas.saveState()
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(MID_GRAY)
    canvas.drawRightString(7.5 * inch, 10.35 * inch, "Croram | Reporte tecnico de cambios")
    canvas.drawCentredString(4.25 * inch, 0.45 * inch, f"Sistema de Almacen Croram | Pagina {doc.page}")
    canvas.restoreState()


def make_styles():
    styles = getSampleStyleSheet()
    styles.add(
        ParagraphStyle(
            "ReportTitle",
            parent=styles["Title"],
            fontName="Helvetica-Bold",
            fontSize=24,
            leading=29,
            textColor=INK,
            spaceAfter=6,
            alignment=TA_LEFT,
        )
    )
    styles.add(
        ParagraphStyle(
            "ReportSubtitle",
            parent=styles["Normal"],
            fontName="Helvetica",
            fontSize=14,
            leading=18,
            textColor=MID_GRAY,
            spaceAfter=18,
        )
    )
    styles.add(
        ParagraphStyle(
            "H1Custom",
            parent=styles["Heading1"],
            fontName="Helvetica-Bold",
            fontSize=16,
            leading=20,
            textColor=BLUE,
            spaceBefore=14,
            spaceAfter=8,
        )
    )
    styles.add(
        ParagraphStyle(
            "H2Custom",
            parent=styles["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=12.5,
            leading=16,
            textColor=BLUE,
            spaceBefore=10,
            spaceAfter=6,
        )
    )
    styles.add(
        ParagraphStyle(
            "BodyCustom",
            parent=styles["BodyText"],
            fontName="Helvetica",
            fontSize=10.3,
            leading=13.2,
            textColor=INK,
            spaceAfter=7,
        )
    )
    styles.add(
        ParagraphStyle(
            "Small",
            parent=styles["BodyText"],
            fontName="Helvetica",
            fontSize=8.7,
            leading=11,
            textColor=INK,
        )
    )
    styles.add(
        ParagraphStyle(
            "TableHead",
            parent=styles["BodyText"],
            fontName="Helvetica-Bold",
            fontSize=8.8,
            leading=10.5,
            textColor=DARK_BLUE,
            alignment=TA_CENTER,
        )
    )
    return styles


def p(text, style):
    return Paragraph(text, style)


def bullet(text, styles):
    return Paragraph(f"&bull; {text}", styles["BodyCustom"])


def label_table(rows, styles):
    data = [[p(f"<b>{label}</b>", styles["Small"]), p(value, styles["Small"])] for label, value in rows]
    table = Table(data, colWidths=[1.7 * inch, 4.65 * inch], hAlign="LEFT")
    table.setStyle(
        TableStyle(
            [
                ("GRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#DADDE3")),
                ("BACKGROUND", (0, 0), (0, -1), LIGHT_GRAY),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("LEFTPADDING", (0, 0), (-1, -1), 7),
                ("RIGHTPADDING", (0, 0), (-1, -1), 7),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ]
        )
    )
    return table


def changes_table(styles):
    rows = [
        ("Menu", "Se retiro Subfamilias del submenu Familias.", "Reduce opciones duplicadas para el usuario final."),
        ("Salidas", "Se sustituyo el filtro de Subfamilia por Area y se hizo obligatoria.", "Permite registrar el gasto contra un centro de consumo."),
        ("Salidas", "Se hizo obligatoria la observacion de salida.", "Asegura trazabilidad minima del movimiento."),
        ("Ordenes", "Se agregaron tokens de idempotencia y bloqueo de botones.", "Evita duplicar ordenes por doble clic o reenvio accidental."),
        ("Backend", "Se bloquearon ordenes con FOR UPDATE antes de aplicar inventario.", "Una orden recibida o confirmada no puede volver a afectar existencias."),
        ("Entradas", "El catalogo de productos muestra proveedor, fecha y precio de ultima compra.", "Facilita capturar nuevas compras con referencia historica."),
        ("Salidas", "El detalle de salida muestra costo PEPS y total en pesos.", "Deja visible el costo real asociado a la salida."),
        ("Reportes", "Se agregaron detalle de entradas, detalle de salidas y consumos por area.", "Cubre unidades, precios, observaciones y costo PEPS."),
        ("Inventario", "Se reorganizo el reporte con saldo inicial, entradas, salidas y existencia.", "Elimina la columna Movimientos y mejora lectura contable."),
        ("Compras sugeridas", "Se ajusto el calculo con consumo mensual / 30.4 * dias requeridos + tiempo de surtido.", "El reporte vuelve a generar resultados con consumo configurado o historico."),
    ]
    data = [[p("Modulo", styles["TableHead"]), p("Cambio implementado", styles["TableHead"]), p("Impacto", styles["TableHead"])]]
    data += [[p(a, styles["Small"]), p(b, styles["Small"]), p(c, styles["Small"])] for a, b, c in rows]
    table = Table(data, colWidths=[1.15 * inch, 3.2 * inch, 2.0 * inch], repeatRows=1, hAlign="LEFT")
    table.setStyle(
        TableStyle(
            [
                ("GRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#DADDE3")),
                ("BACKGROUND", (0, 0), (-1, 0), LIGHT_BLUE),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("LEFTPADDING", (0, 0), (-1, -1), 6),
                ("RIGHTPADDING", (0, 0), (-1, -1), 6),
                ("TOPPADDING", (0, 0), (-1, -1), 5),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
            ]
        )
    )
    return table


def build_pdf():
    styles = make_styles()
    doc = BaseDocTemplate(
        str(OUT_PDF),
        pagesize=letter,
        leftMargin=1 * inch,
        rightMargin=1 * inch,
        topMargin=1 * inch,
        bottomMargin=0.8 * inch,
        title="Reporte tecnico de cambios - Croram",
        author="Programador del sistema",
    )
    frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="normal")
    doc.addPageTemplates([PageTemplate(id="main", frames=[frame], onPage=header_footer)])

    story = []
    story.append(Spacer(1, 0.25 * inch))
    story.append(p("Reporte tecnico de cambios", styles["ReportTitle"]))
    story.append(p("Sistema de Almacen Croram", styles["ReportSubtitle"]))
    story.append(
        label_table(
            [
                ("Cliente", "Croram"),
                ("Fecha", date.today().strftime("%d/%m/%Y")),
                ("Preparado por", "Programador del sistema"),
                ("Alcance", "Cambios funcionales, validaciones, reportes y controles anti-duplicidad solicitados por el cliente."),
            ],
            styles,
        )
    )
    story.append(Spacer(1, 14))

    story.append(p("Resumen ejecutivo", styles["H1Custom"]))
    story.append(
        p(
            "Se realizo una intervencion enfocada en mejorar la operacion diaria del inventario: se reforzo el registro de salidas por area, se redujo el riesgo de ordenes duplicadas, se agregaron reportes operativos y se ajusto el calculo de compras sugeridas. Los cambios se hicieron respetando la estructura actual del proyecto PHP procedural, sin introducir migraciones de base de datos ni dependencias externas nuevas.",
            styles["BodyCustom"],
        )
    )

    story.append(p("Cambios implementados", styles["H1Custom"]))
    story.append(changes_table(styles))
    story.append(PageBreak())

    story.append(p("Detalle tecnico", styles["H1Custom"]))
    story.append(p("Control de duplicidad y consistencia", styles["H2Custom"]))
    for text in [
        "Se agregaron tokens de solicitud en formularios de ordenes para identificar reenvios de la misma operacion.",
        "Se deshabilitan botones de accion durante el procesamiento para evitar doble clic.",
        "El backend valida el token y mantiene un historial corto en sesion para rechazar solicitudes repetidas.",
        "Las acciones criticas de recibir entrada y aprobar salida bloquean la orden con SELECT ... FOR UPDATE antes de modificar inventario.",
    ]:
        story.append(bullet(text, styles))

    story.append(p("Salidas e inventario PEPS", styles["H2Custom"]))
    for text in [
        "Las salidas requieren area y observaciones antes de registrarse.",
        "El costo PEPS calculado se conserva en el detalle de salida y se muestra en pantalla y reportes.",
        "El total en pesos de una salida se calcula desde cantidad por costo PEPS.",
    ]:
        story.append(bullet(text, styles))

    story.append(p("Reportes", styles["H2Custom"]))
    for text in [
        "El reporte de inventario ahora presenta saldo inicial, entradas, salidas y existencia final.",
        "Se agregaron reportes de detalle para entradas y salidas con unidades, precios, descripcion y totales.",
        "Se agrego reporte de consumos por area y periodo con costo PEPS promedio y total.",
        "La exportacion a Excel se actualizo para incluir las nuevas columnas.",
    ]:
        story.append(bullet(text, styles))

    story.append(p("Compras sugeridas", styles["H2Custom"]))
    for text in [
        "El calculo usa consumo mensual promedio dividido entre 30.4 y lo multiplica por dias de stock requeridos mas tiempo de surtido.",
        "Cuando no hay consumo historico de 12 meses, el calculo puede usar el consumo diario configurado en el articulo.",
        "La pantalla mantiene un campo editable para confirmar, aumentar o disminuir el pedido sugerido.",
    ]:
        story.append(bullet(text, styles))

    story.append(p("Cambios no aplicados o condicionados", styles["H1Custom"]))
    story.append(
        label_table(
            [
                ("Limpieza total de codificacion", "Se corrigieron textos visibles en pantallas intervenidas. No se aplico una conversion global agresiva porque el proyecto incluye muchas plantillas y librerias de terceros; hacerlo sin revision visual podria introducir errores nuevos."),
                ("Saldo inicial real", "No existe un campo historico dedicado para saldo inicial. Se calculo como existencia actual menos entradas mas salidas, usando los datos disponibles."),
                ("Kardex completo", "El sistema ya cuenta con historial por articulo. Convertirlo en Kardex contable completo requiere definir formato final, reglas de saldo acumulado y si se debe persistir costo por movimiento."),
                ("Migracion de seguridad", "No se movieron credenciales ni contrasenas a un esquema nuevo porque no estaba dentro del alcance operativo inmediato. Se recomienda atenderlo como fase tecnica independiente."),
            ],
            styles,
        )
    )

    story.append(p("Validacion realizada", styles["H1Custom"]))
    story.append(p("Se ejecuto validacion de sintaxis PHP con PHP 8.3 de Laragon sobre los archivos propios del sistema.", styles["BodyCustom"]))
    story.append(
        label_table(
            [
                ("Resultado", "57 archivos PHP revisados sin errores de sintaxis."),
                ("Comando equivalente", "php -l sobre archivos propios, excluyendo librerias vendor, FPDF y phpqrcode."),
                ("Riesgo residual", "No se ejecutaron pruebas integradas contra base de datos productiva desde este entorno; la validacion funcional final debe hacerse con usuarios y datos reales de Croram."),
            ],
            styles,
        )
    )

    story.append(p("Recomendaciones de siguiente fase", styles["H1Custom"]))
    for text in [
        "Crear una configuracion de base de datos fuera del codigo fuente.",
        "Migrar contrasenas de texto plano a password_hash/password_verify.",
        "Convertir consultas sensibles a prepared statements de forma progresiva.",
        "Definir formalmente el formato de Kardex para construir saldos acumulados y costos por movimiento.",
        "Probar el flujo completo con una orden de entrada, una salida y una exportacion de reportes usando datos reales.",
    ]:
        story.append(bullet(text, styles))

    doc.build(story)
    print(OUT_PDF)


if __name__ == "__main__":
    build_pdf()
