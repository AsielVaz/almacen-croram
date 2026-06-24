from datetime import date
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import BaseDocTemplate, Frame, PageTemplate, Paragraph, Spacer, Table, TableStyle


ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "entregables"
OUT_DIR.mkdir(exist_ok=True)
OUT_PDF = OUT_DIR / "reporte_cambios_croram_cliente.pdf"

BLUE = colors.HexColor("#2E74B5")
DARK = colors.HexColor("#202020")
GRAY = colors.HexColor("#666666")
LIGHT_BLUE = colors.HexColor("#E8EEF5")
BORDER = colors.HexColor("#DADDE3")


def header_footer(canvas, doc):
    canvas.saveState()
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(GRAY)
    canvas.drawRightString(7.5 * inch, 10.35 * inch, "Croram | Cambios realizados")
    canvas.drawCentredString(4.25 * inch, 0.45 * inch, f"Pagina {doc.page}")
    canvas.restoreState()


def styles():
    base = getSampleStyleSheet()
    base.add(ParagraphStyle("TitleMain", parent=base["Title"], fontName="Helvetica-Bold", fontSize=24, leading=30, textColor=DARK, alignment=TA_LEFT, spaceAfter=6))
    base.add(ParagraphStyle("Subtitle", parent=base["Normal"], fontName="Helvetica", fontSize=13, leading=17, textColor=GRAY, spaceAfter=18))
    base.add(ParagraphStyle("H1", parent=base["Heading1"], fontName="Helvetica-Bold", fontSize=15, leading=19, textColor=BLUE, spaceBefore=12, spaceAfter=8))
    base.add(ParagraphStyle("Body", parent=base["BodyText"], fontName="Helvetica", fontSize=10.5, leading=13.5, textColor=DARK, spaceAfter=7))
    base.add(ParagraphStyle("Cell", parent=base["BodyText"], fontName="Helvetica", fontSize=9, leading=11.5, textColor=DARK))
    base.add(ParagraphStyle("HeadCell", parent=base["BodyText"], fontName="Helvetica-Bold", fontSize=9, leading=11.5, textColor=BLUE, alignment=TA_CENTER))
    return base


def para(text, style):
    return Paragraph(text, style)


def meta_table(s):
    data = [
        [para("<b>Cliente</b>", s["Cell"]), para("Croram", s["Cell"])],
        [para("<b>Fecha</b>", s["Cell"]), para(date.today().strftime("%d/%m/%Y"), s["Cell"])],
        [para("<b>Documento</b>", s["Cell"]), para("Reporte de cambios realizados en el sistema de almacen", s["Cell"])],
    ]
    table = Table(data, colWidths=[1.5 * inch, 4.85 * inch], hAlign="LEFT")
    table.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.35, BORDER),
        ("BACKGROUND", (0, 0), (0, -1), colors.HexColor("#F2F4F7")),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("LEFTPADDING", (0, 0), (-1, -1), 7),
        ("RIGHTPADDING", (0, 0), (-1, -1), 7),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    return table


def changes_table(s):
    rows = [
        ("Menu", "Se retiro la opcion Subfamilias del submenu Familias."),
        ("Articulos", "Se ajustaron textos visibles para evitar caracteres incorrectos en pantallas intervenidas."),
        ("Orden de entrada", "En el catalogo de productos ahora aparece la ultima compra registrada con proveedor, fecha y precio."),
        ("Orden de entrada", "Se bloqueo el boton de envio mientras se registra la orden."),
        ("Ingreso de entrada", "Se bloqueo el boton Guardar Orden de Entrada mientras se procesa el ingreso."),
        ("Ingreso de entrada", "Una orden ya recibida queda protegida para no volver a cargar existencias."),
        ("Orden de salida", "El campo Area es obligatorio para registrar salidas."),
        ("Orden de salida", "El campo Observaciones es obligatorio para registrar salidas."),
        ("Orden de salida", "Se retiro el filtro de Subfamilia y se dejo el flujo enfocado por Familia, busqueda y Area."),
        ("Orden de salida", "Se bloqueo el boton de registro mientras se procesa la salida."),
        ("Salida rapida", "Se agrego el campo Observaciones."),
        ("Salida rapida", "Se bloqueo el boton Finalizar Salida mientras se procesa la salida."),
        ("Aprobacion de salida", "Una orden ya confirmada queda protegida para no volver a descontar existencias."),
        ("Aprobacion de salida", "El detalle muestra costo PEPS y total en pesos."),
        ("Reportes", "El reporte de inventario muestra Saldo inicial, Entradas, Salidas y Existencia."),
        ("Reportes", "Se retiro la columna Movimientos del reporte de inventario."),
        ("Reportes", "Se agrego el detalle de entradas con unidades, descripcion, precio unitario y total de compra."),
        ("Reportes", "Se agrego el detalle de salidas con observacion, area, costo PEPS y total."),
        ("Reportes", "Se agrego reporte de consumos por area y periodo."),
        ("Reportes", "Se agrego exportacion de consumos por area."),
        ("Compras sugeridas", "Se ajusto el calculo usando consumo mensual, dias de stock requeridos y tiempo de surtido."),
        ("Compras sugeridas", "Se conserva un campo para confirmar o ajustar el pedido sugerido."),
    ]
    data = [[para("Seccion", s["HeadCell"]), para("Cambio realizado", s["HeadCell"])]]
    data += [[para(area, s["Cell"]), para(change, s["Cell"])] for area, change in rows]
    table = Table(data, colWidths=[1.55 * inch, 4.8 * inch], repeatRows=1, hAlign="LEFT")
    table.setStyle(TableStyle([
        ("GRID", (0, 0), (-1, -1), 0.35, BORDER),
        ("BACKGROUND", (0, 0), (-1, 0), LIGHT_BLUE),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("LEFTPADDING", (0, 0), (-1, -1), 7),
        ("RIGHTPADDING", (0, 0), (-1, -1), 7),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    return table


def build():
    s = styles()
    doc = BaseDocTemplate(
        str(OUT_PDF),
        pagesize=letter,
        leftMargin=1 * inch,
        rightMargin=1 * inch,
        topMargin=1 * inch,
        bottomMargin=0.8 * inch,
        title="Cambios realizados - Croram",
        author="Programador del sistema",
    )
    frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="normal")
    doc.addPageTemplates([PageTemplate(id="main", frames=[frame], onPage=header_footer)])

    story = [
        Spacer(1, 0.2 * inch),
        para("Cambios realizados en el sistema", s["TitleMain"]),
        para("Sistema de Almacen Croram", s["Subtitle"]),
        meta_table(s),
        Spacer(1, 16),
        para("Listado de cambios", s["H1"]),
        para("A continuacion se presentan los cambios aplicados en el sistema.", s["Body"]),
        changes_table(s),
    ]
    doc.build(story)
    print(OUT_PDF)


if __name__ == "__main__":
    build()
