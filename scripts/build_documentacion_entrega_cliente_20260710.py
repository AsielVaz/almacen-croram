from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    KeepTogether,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "output" / "pdf" / "documentacion_entrega_cliente_croram_2026-07-10.pdf"


def styles():
    base = getSampleStyleSheet()
    base.add(
        ParagraphStyle(
            name="DocTitle",
            parent=base["Title"],
            fontName="Helvetica-Bold",
            fontSize=20,
            leading=24,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#111827"),
            spaceAfter=6,
        )
    )
    base.add(
        ParagraphStyle(
            name="Subtitle",
            parent=base["Normal"],
            fontName="Helvetica",
            fontSize=9.5,
            leading=13,
            alignment=TA_CENTER,
            textColor=colors.HexColor("#4b5563"),
            spaceAfter=16,
        )
    )
    base.add(
        ParagraphStyle(
            name="Section",
            parent=base["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=13,
            leading=16,
            textColor=colors.HexColor("#111827"),
            spaceBefore=11,
            spaceAfter=7,
        )
    )
    base.add(
        ParagraphStyle(
            name="Body",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=9.2,
            leading=12.5,
            alignment=TA_LEFT,
            textColor=colors.HexColor("#111827"),
        )
    )
    base.add(
        ParagraphStyle(
            name="Small",
            parent=base["BodyText"],
            fontName="Helvetica",
            fontSize=8.2,
            leading=10.8,
            textColor=colors.HexColor("#374151"),
        )
    )
    return base


def p(text, style):
    return Paragraph(text, style)


def bullet(items, style):
    rows = []
    for item in items:
        rows.append([p("•", style), p(item, style)])
    table = Table(rows, colWidths=[0.18 * inch, 6.28 * inch])
    table.setStyle(
        TableStyle(
            [
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
                ("LEFTPADDING", (0, 0), (-1, -1), 0),
                ("RIGHTPADDING", (0, 0), (-1, -1), 4),
                ("TOPPADDING", (0, 0), (-1, -1), 1),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
            ]
        )
    )
    return table


def table(rows, col_widths, header=True):
    s = styles()
    data = [[p(str(cell), s["Small"]) for cell in row] for row in rows]
    t = Table(data, colWidths=col_widths, repeatRows=1 if header else 0)
    commands = [
        ("GRID", (0, 0), (-1, -1), 0.35, colors.HexColor("#d1d5db")),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 5),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]
    if header:
        commands.extend(
            [
                ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#e5e7eb")),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.HexColor("#111827")),
            ]
        )
    t.setStyle(TableStyle(commands))
    return t


def page_number(canvas, doc):
    canvas.saveState()
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(colors.HexColor("#6b7280"))
    canvas.drawString(0.55 * inch, 0.35 * inch, "Croram - Documentacion de entrega")
    canvas.drawRightString(7.95 * inch, 0.35 * inch, f"Pagina {doc.page}")
    canvas.restoreState()


def main():
    OUT.parent.mkdir(parents=True, exist_ok=True)
    s = styles()
    doc = SimpleDocTemplate(
        str(OUT),
        pagesize=letter,
        rightMargin=0.55 * inch,
        leftMargin=0.55 * inch,
        topMargin=0.55 * inch,
        bottomMargin=0.62 * inch,
        title="Documentacion de entrega a cliente - Croram",
        author="Codex",
    )

    story = [
        p("Documentacion de entrega a cliente", s["DocTitle"]),
        p("Cliente: Croram | Sistema: Almacen e inventario | Fecha de entrega: 10 de julio de 2026", s["Subtitle"]),
        p("Objetivo de la entrega", s["Section"]),
        p(
            "Este documento resume los ajustes entregados para el sistema de almacen, inventario, ordenes, reportes y etiquetas QR. "
            "La finalidad es dejar evidencia clara de los cambios funcionales, los puntos de validacion recomendados y las observaciones operativas para uso del cliente.",
            s["Body"],
        ),
        Spacer(1, 8),
        p("Resumen ejecutivo", s["Section"]),
        bullet(
            [
                "Se ajusto el flujo de ordenes de salida para iniciar en estado CAPTURADA y pasar a CONFIRMADA al aprobarse.",
                "Se respeto la actualizacion de inventario por trigger de base de datos, evitando duplicar movimientos desde codigo.",
                "Se agrego el reporte Log de Inventario con datos de inventario_movimientos relacionados solo con productos.",
                "Se ajustaron formatos QR para mostrar nombre o descripcion del articulo y QR de 4 x 4 cm.",
                "Se agrego evidencia visual de medida tipo flexometro en impresion termica, en blanco y negro.",
                "Se mantuvieron filtros y exportaciones para reportes operativos segun los modulos actuales.",
            ],
            s["Body"],
        ),
        p("Cambios entregados", s["Section"]),
        table(
            [
                ["Modulo", "Cambio", "Resultado esperado"],
                [
                    "Ordenes de salida",
                    "Alta de orden con estatus CAPTURADA; aprobacion cambia a CONFIRMADA.",
                    "La orden queda pendiente de aprobacion antes de afectar inventario mediante trigger.",
                ],
                [
                    "Inventario",
                    "Se omite insercion manual de inventario en alta de salida.",
                    "El movimiento de inventario lo controla la base de datos y se evita doble descuento.",
                ],
                [
                    "Reportes",
                    "Nuevo reporte Log de Inventario con filtros por fecha, tipo y origen.",
                    "El cliente puede consultar entradas y salidas registradas en inventario_movimientos.",
                ],
                [
                    "Exportaciones",
                    "Exportacion Excel para Log de Inventario.",
                    "La informacion en pantalla puede descargarse para revision externa.",
                ],
                [
                    "Etiquetas QR",
                    "QR termico y carta con tamano de 4 x 4 cm y texto de articulo.",
                    "La etiqueta permite identificar el producto fisicamente con una medida consistente.",
                ],
                [
                    "Impresion termica",
                    "Hoja en proporcion real, regla inferior tipo flexometro y margen final de corte.",
                    "Se deja evidencia visual del ancho de 10 cm y se reduce el riesgo de corte al final.",
                ],
                [
                    "Articulos",
                    "El costo de reposicion ahora se captura como costo por pieza.",
                    "Al editar un articulo ya no se divide el costo entre las unidades del inventario.",
                ],
                [
                    "Compras sugeridas",
                    "Se agrego la columna final Compra sugerida.",
                    "La compra sugerida se calcula como stock sugerido menos existencia actual.",
                ],
            ],
            [1.35 * inch, 2.85 * inch, 2.45 * inch],
        ),
        p("Detalle funcional por modulo", s["Section"]),
        p("<b>Ordenes de salida.</b> La orden se crea en estado CAPTURADA. En este punto queda pendiente de aprobacion. Al aprobarse, cambia a CONFIRMADA y la base de datos aplica el movimiento de inventario mediante trigger.", s["Body"]),
        Spacer(1, 5),
        p("<b>Log de Inventario.</b> El reporte muestra registros de la tabla inventario_movimientos unidos unicamente con productos. No se relacionan ordenes en este modulo porque el seguimiento por orden no forma parte del alcance actual.", s["Body"]),
        Spacer(1, 5),
        p("<b>Etiquetas QR.</b> La impresion termica usa etiqueta de 10 x 5 cm, QR de 4 x 4 cm, regla inferior de 0 a 10 cm en blanco y negro y margen tecnico inferior para evitar cortes.", s["Body"]),
        Spacer(1, 5),
        p("<b>Articulos.</b> En alta y edicion, el campo de costo se maneja como costo por pieza. Se retiro la conversion anterior que tomaba el valor capturado como costo total y lo dividia entre la existencia.", s["Body"]),
        Spacer(1, 5),
        p("<b>Compras sugeridas.</b> El reporte conserva el stock sugerido y agrega la compra sugerida al final, calculada como stock sugerido menos existencia actual.", s["Body"]),
        KeepTogether(
            [
                p("Pantallas y rutas principales", s["Section"]),
                table(
                    [
                        ["Pantalla", "Ruta", "Uso"],
                        ["Reportes", "reportes.php", "Modulo principal de consulta de reportes."],
                        ["Log de Inventario", "reportes.php?seccion=log_inventario", "Consulta de movimientos de inventario por producto."],
                        ["Exportar Log de Inventario", "reportes-exportar.php?tipo=log_inventario", "Descarga de Excel con los movimientos filtrados."],
                        ["Ordenes de salida", "ordenes-salida.php", "Listado y seguimiento de ordenes de salida."],
                        ["Detalle salida", "ordenes-salida-detalle.php", "Aprobacion de ordenes de salida."],
                        ["QR termico", "api/generaProductosQrPdf.php", "Generacion de PDF termico para etiquetas QR."],
                        ["QR carta", "api/generaProductosQrPdfCarta.php", "Generacion de PDF carta para etiquetas QR."],
                        ["Articulos", "articulos-form.php", "Alta y edicion de articulos con costo por pieza."],
                        ["Compras sugeridas", "reportes-compras-sugeridas.php", "Reporte de stock sugerido y compra sugerida."],
                    ],
                    [1.65 * inch, 2.45 * inch, 2.55 * inch],
                ),
            ]
        ),
        p("Validacion recomendada por el cliente", s["Section"]),
        bullet(
            [
                "Crear una orden de salida nueva y confirmar que aparezca con estatus CAPTURADA.",
                "Aprobar la orden y confirmar que cambie a CONFIRMADA.",
                "Verificar que el inventario se descuente una sola vez mediante el trigger de base de datos.",
                "Abrir Reportes > Log de Inventario y validar que aparezcan los movimientos por producto.",
                "Filtrar el Log de Inventario por fecha, tipo y origen; despues descargar el Excel.",
                "Generar una etiqueta QR termica y medir que el QR sea de 4 x 4 cm.",
                "Confirmar que la regla inferior se imprima en blanco y negro y no salga cortada.",
                "Editar un articulo y confirmar que el costo capturado se conserve como costo por pieza.",
                "Abrir Compras sugeridas y validar la columna final Compra sugerida.",
            ],
            s["Body"],
        ),
        p("Observaciones operativas", s["Section"]),
        bullet(
            [
                "Los movimientos de inventario dependen de triggers de base de datos; cualquier cambio futuro en estatus debe considerar esos triggers.",
                "El Log de Inventario no muestra trazabilidad completa por orden, por decision de alcance actual.",
                "La impresion termica puede variar por configuracion de impresora; se recomienda imprimir en escala 100%, sin ajuste automatico de pagina.",
                "Los cambios se aplicaron respetando la estructura actual del proyecto PHP y la base de datos existente.",
                "El costo capturado en articulos representa costo unitario por pieza; no debe capturarse como costo total del inventario.",
            ],
            s["Body"],
        ),
        p("Evidencia tecnica de validacion", s["Section"]),
        table(
            [
                ["Validacion", "Resultado"],
                ["Sintaxis PHP", "Archivos modificados validados con php -l sin errores."],
                ["Consulta Log de Inventario", "Consulta probada contra base de datos con movimientos reales."],
                ["PDF termico", "Generacion probada con hoja de 10 cm de ancho y margen final ampliado."],
                ["Base de datos", "Estatus CAPTURADA agregado a ordenes_salida y configurado como default."],
                ["Articulos", "Se retiro el calculo que dividia el costo capturado entre inventario actual."],
                ["Compras sugeridas", "Consulta validada con campo compra_sugerida calculado."],
            ],
            [2.25 * inch, 4.4 * inch],
        ),
        Spacer(1, 10),
        p(
            "Entrega preparada para revision del cliente. Se recomienda realizar una prueba completa en ambiente operativo con una entrada, una salida, consulta de Log de Inventario y una impresion real de etiqueta termica.",
            s["Body"],
        ),
    ]

    doc.build(story, onFirstPage=page_number, onLaterPages=page_number)
    print(OUT)


if __name__ == "__main__":
    main()
