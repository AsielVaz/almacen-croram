from pathlib import Path
from html import escape


ROOT = Path(__file__).resolve().parents[1]
WIKI = ROOT / "wiki"

NAV = [
    ("Inicio", [
        ("index.html", "Resumen general"),
        ("inicio-sesion.html", "Acceso al sistema"),
    ]),
    ("Operacion", [
        ("articulos.html", "Articulos e inventario"),
        ("ordenes.html", "Ordenes"),
        ("reportes.html", "Reportes"),
        ("catalogos.html", "Catalogos"),
        ("usuarios.html", "Usuarios"),
    ]),
    ("Tecnico", [
        ("api-movil.html", "API movil"),
        ("cambios.html", "Cambios aplicados"),
    ]),
    ("Apoyo", [
        ("buenas-practicas.html", "Buenas practicas"),
    ]),
]


def nav(active):
    chunks = [
        '<aside class="sidebar">',
        '<div class="brand">Wiki Croram</div>',
        '<div class="brand-sub">Sistema de almacen e inventario</div>',
    ]
    for title, links in NAV:
        chunks.append('<div class="nav-group">')
        chunks.append(f'<div class="nav-title">{title}</div>')
        for href, label in links:
            cls = "nav-link active" if href == active else "nav-link"
            chunks.append(f'<a class="{cls}" href="{href}">{label}</a>')
        chunks.append("</div>")
    chunks.append("</aside>")
    return "\n".join(chunks)


def page(filename, title, eyebrow, h1, intro, body):
    html = f"""<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{escape(title)} - Wiki Croram</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="layout">
        {nav(filename)}
        <main class="content">
            <section class="hero">
                <div class="eyebrow">{escape(eyebrow)}</div>
                <h1>{escape(h1)}</h1>
                <p class="muted">{escape(intro)}</p>
            </section>
            {body}
            <p class="footer-note">Ultima actualizacion: 04 de julio de 2026. Documentacion interna para Croram.</p>
        </main>
    </div>
</body>
</html>
"""
    (WIKI / filename).write_text(html, encoding="utf-8")


def card(title, content):
    return f'<section class="card"><h2>{title}</h2>{content}</section>'


def grid(*cards):
    return '<section class="grid">' + "".join(cards) + "</section>"


def ul(items):
    return "<ul class=\"steps\">" + "".join(f"<li>{item}</li>" for item in items) + "</ul>"


def ol(items):
    return "<ol class=\"steps\">" + "".join(f"<li>{item}</li>" for item in items) + "</ol>"


def table(headers, rows):
    head = "".join(f"<th>{h}</th>" for h in headers)
    body = "".join("<tr>" + "".join(f"<td>{cell}</td>" for cell in row) + "</tr>" for row in rows)
    return f'<div class="table-wrap"><table><thead><tr>{head}</tr></thead><tbody>{body}</tbody></table></div>'


def badge(text, cls=""):
    return f'<span class="chip {cls}">{text}</span>'


def write_styles():
    (WIKI / "styles.css").write_text(
        """:root {
    --bg: #f4f6f8;
    --panel: #ffffff;
    --text: #1f2933;
    --muted: #6b7280;
    --border: #d7dee7;
    --primary: #495057;
    --accent: #dc3545;
    --ok: #198754;
    --warn: #b7791f;
    --blue: #2563eb;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: "Segoe UI", Tahoma, sans-serif;
    background: linear-gradient(180deg, #f7f8fa 0%, #eef2f5 100%);
    color: var(--text);
}

a { color: inherit; }

.layout {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    min-height: 100vh;
}

.sidebar {
    background: #20262d;
    color: #f8fafc;
    padding: 24px 18px;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.brand { font-size: 1.22rem; font-weight: 800; margin-bottom: 8px; }
.brand-sub { color: #b8c0cc; font-size: 0.92rem; margin-bottom: 22px; }
.nav-group { margin-bottom: 20px; }
.nav-title { color: #a7b0bc; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 10px; }
.nav-link { display: block; text-decoration: none; padding: 10px 12px; border-radius: 10px; color: #eef2f7; margin-bottom: 6px; transition: background-color 0.2s ease; }
.nav-link:hover, .nav-link.active { background: rgba(255, 255, 255, 0.1); }
.content { padding: 32px; }

.hero, .card, .callout, .table-wrap {
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(20, 30, 50, 0.06);
}

.hero { padding: 28px; margin-bottom: 24px; }
.eyebrow { color: var(--accent); font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 800; margin-bottom: 10px; }
h1, h2, h3 { margin-top: 0; }
h1 { font-size: 2rem; margin-bottom: 10px; }
h2 { font-size: 1.3rem; margin-bottom: 12px; }
h3 { font-size: 1.05rem; margin-bottom: 8px; }
p, li { line-height: 1.6; }
.muted { color: var(--muted); }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-bottom: 18px; }
.card { padding: 22px; margin-bottom: 18px; }
.callout { padding: 18px 20px; margin: 18px 0; }
.callout.ok { border-left: 6px solid var(--ok); }
.callout.warn { border-left: 6px solid var(--warn); }
.callout.danger { border-left: 6px solid var(--accent); }
.callout.info { border-left: 6px solid var(--blue); }
.steps { padding-left: 18px; }
.steps li { margin-bottom: 10px; }
.table-wrap { overflow-x: auto; margin: 18px 0; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px 14px; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
th { background: #f8fafc; }
.chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
.chip { display: inline-block; background: #eef2f7; border: 1px solid var(--border); border-radius: 999px; padding: 7px 12px; font-size: 0.92rem; }
.chip.ok { background: #edf7f1; color: #146c43; border-color: #b7dfc5; }
.chip.warn { background: #fff7ed; color: #9a5b13; border-color: #fed7aa; }
.chip.blue { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
code { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 5px; }
pre { background: #111827; color: #f8fafc; border-radius: 14px; padding: 16px; overflow-x: auto; line-height: 1.5; }
.footer-note { color: var(--muted); font-size: 0.92rem; margin-top: 28px; }

@media (max-width: 920px) {
    .layout { grid-template-columns: 1fr; }
    .sidebar { position: static; height: auto; }
    .grid { grid-template-columns: 1fr; }
    .content { padding: 20px; }
}
""",
        encoding="utf-8",
    )


def build():
    WIKI.mkdir(exist_ok=True)
    write_styles()

    page(
        "index.html",
        "Resumen general",
        "Guia del sistema",
        "Sistema de almacen Croram",
        "Referencia de funcionalidades operativas y cambios aplicados al sistema de almacen, inventario, ordenes, reportes, QR y API movil.",
        grid(
            card("Modulos principales", ul([
                "<strong>Articulos:</strong> catalogo, inventario, ubicacion, costos, QR e historial.",
                "<strong>Ordenes de entrada:</strong> compra, autorizacion, validacion QR, recepcion y PEPS.",
                "<strong>Ordenes de salida:</strong> consumo por area, observaciones, validacion QR, costos PEPS y aprobacion.",
                "<strong>Reportes:</strong> inventario, entradas, salidas, consumos, compras, sugeridos y obsoletos.",
                "<strong>Catalogos:</strong> familias, proveedores, areas y usuarios.",
                "<strong>API movil:</strong> consulta y cierre de ordenes desde app externa.",
            ])),
            card("Cambios recientes integrados", ul([
                "Inventario ya no se duplica al recibir entradas.",
                "Salidas muestran costo PEPS, unitario y subtotal.",
                "Reportes calculan valor de inventario y costos con respaldo de ultima compra.",
                "Etiquetas QR imprimen descripcion del articulo junto al SKU.",
                "Lista de articulos usa paginacion por API y busqueda en tiempo real.",
                "API movil permite buscar, validar, listar pendientes y finalizar ordenes.",
            ])),
        )
        + '<div class="chips">' + "".join([
            badge("PEPS", "ok"),
            badge("QR 4x4 cm", "blue"),
            badge("CORS abierto API movil", "blue"),
            badge("Reportes exportables", "ok"),
            badge("Validacion por QR", "warn"),
        ]) + "</div>"
        + card("Flujo operativo recomendado", ol([
            "Registrar catalogos base: familias, areas, proveedores y usuarios.",
            "Dar de alta articulos con nombre, SKU, familia, descripcion, ubicacion, unidad, inventario y costo.",
            "Generar QR de articulos cuando se requiera identificacion fisica.",
            "Crear ordenes de entrada para compras y autorizar antes de ingresar partes.",
            "Validar productos con QR o SKU antes de recibir o entregar.",
            "Consultar reportes para revisar inventario, consumos, compras y costos.",
        ])),
    )

    page(
        "inicio-sesion.html",
        "Acceso",
        "Seguridad",
        "Acceso al sistema",
        "El sistema requiere autenticacion para operar modulos internos y APIs de gestion web.",
        grid(
            card("Inicio de sesion", ol([
                "Abrir la URL del sistema.",
                "Capturar usuario y contrasena asignados.",
                "Entrar al panel principal.",
                "Validar que el menu corresponda a las tareas del usuario.",
            ])),
            card("Cierre de sesion", ul([
                "Cerrar sesion al terminar turno o al usar equipos compartidos.",
                "No compartir credenciales.",
                "Solicitar alta o baja de usuarios al administrador.",
            ])),
        )
        + card("Permisos y seguridad", ul([
            "Los endpoints web protegidos usan sesion activa.",
            "El API movil usa token por encabezado <code>X-CRORAM-Mobile-Token</code>.",
            "Las acciones criticas de ordenes usan validaciones de estado para evitar recepciones o aprobaciones repetidas.",
        ])),
    )

    page(
        "articulos.html",
        "Articulos e inventario",
        "Inventario",
        "Articulos, existencias, costos y QR",
        "El modulo de articulos concentra la informacion base de cada producto y su existencia actual.",
        grid(
            card("Datos del articulo", ul([
                "SKU o codigo interno. Si no se captura, el sistema puede generar consecutivo.",
                "Nombre, descripcion y ubicacion fisica.",
                "Familia y subfamilia.",
                "Unidad de medida y condicion: NUEVO o USADO.",
                "Inventario actual o inicial.",
                "Costo de reposicion por unidad o costo total al editar.",
            ])),
            card("Busqueda y listado", ul([
                "La lista usa paginacion por API para cargar mas rapido.",
                "El buscador filtra en tiempo real por nombre, SKU, descripcion o ubicacion.",
                "Se puede cambiar el numero de articulos por pagina.",
                "Cada registro permite editar o consultar historial.",
            ])),
        )
        + card("Costos al editar", ul([
            "En alta se captura costo por unidad.",
            "En edicion se captura costo total actual.",
            "Al guardar edicion, el sistema calcula costo por unidad dividiendo costo total entre inventario actual.",
            "El costo por unidad se usa como respaldo en reportes si no hay PEPS o ultima compra.",
        ]))
        + card("QR de articulos", ul([
            "Se pueden seleccionar articulos desde la lista y generar PDF de QR.",
            "Formato termico: etiqueta horizontal con QR de 4x4 cm a la izquierda.",
            "Formato carta: etiquetas en hoja carta con QR de 4x4 cm.",
            "La etiqueta imprime descripcion hasta 50 caracteres y SKU.",
            "El QR codifica el SKU para mantener compatibilidad con validacion de ordenes.",
        ]))
        + card("Historial e inventario", ul([
            "El historial muestra entradas y salidas asociadas al articulo.",
            "El inventario se calcula con stock actual.",
            "El valor de inventario considera lotes PEPS, stock sin lote y respaldo por ultima compra.",
            "Los lotes PEPS guardan cantidad inicial, disponible y costo unitario.",
        ])),
    )

    page(
        "ordenes.html",
        "Ordenes",
        "Movimientos",
        "Ordenes de entrada y salida",
        "Las ordenes controlan compras, recepciones y consumos internos del almacen.",
        grid(
            card("Orden de entrada", ol([
                "Crear orden seleccionando proveedor y productos.",
                "Capturar cantidad y precio unitario.",
                "Autorizar orden antes de recibir partes.",
                "Antes de autorizar se muestra ultima compra por producto: proveedor, fecha y precio.",
                "Al ingresar partes se validan QR o SKU y precios reales.",
                "Al pasar a RECIBIDA, la base de datos suma inventario mediante trigger.",
            ])),
            card("Orden de salida", ol([
                "Seleccionar articulos disponibles.",
                "Capturar area obligatoria y observaciones.",
                "Validar QR o SKU de cada producto.",
                "Aprobar salida para confirmar consumo.",
                "Al pasar a CONFIRMADA, la base de datos descuenta inventario mediante trigger.",
                "El sistema registra costo PEPS, unitario y subtotal.",
            ])),
        )
        + '<div class="callout info"><strong>Validacion QR:</strong> en entradas y salidas se puede leer SKU o ID del producto para confirmar que la parte recibida o entregada corresponde a la orden.</div>'
        + card("Estados principales", table(
            ["Tipo", "Estado", "Significado"],
            [
                ["Entrada", "PENDIENTE", "Orden creada, aun sin autorizacion."],
                ["Entrada", "AUTORIZADA", "Lista para ingresar partes al inventario."],
                ["Entrada", "RECIBIDA", "Entrada concluida e inventario actualizado."],
                ["Salida", "BORRADOR", "Salida pendiente de aprobacion."],
                ["Salida", "CONFIRMADA", "Salida aprobada e inventario descontado."],
            ],
        ))
        + card("PEPS en salidas", ul([
            "El sistema consume lotes disponibles por orden de entrada mas antigua.",
            "Si un detalle historico no tiene costo, los reportes usan respaldo por costo unitario, costo de reposicion o ultima compra.",
            "El sistema evita crear lotes legacy cuando el producto ya tiene lotes de compra registrados.",
            "La salida guarda costo PEPS promedio por producto y subtotal.",
        ])),
    )

    page(
        "reportes.html",
        "Reportes",
        "Analisis",
        "Reportes del sistema",
        "Los reportes permiten revisar existencias, costos, consumos, compras, movimientos y sugerencias de compra.",
        grid(
            card("Inventario", ul([
                "Muestra saldo inicial, entradas, salidas y existencia.",
                "Incluye valor de inventario y costo por unidad.",
                "Calcula costo promedio usando lotes PEPS, stock sin lote, costo de reposicion y ultima compra.",
                "Permite filtrar por familia y subfamilia.",
            ])),
            card("Entradas y salidas", ul([
                "Entradas: proveedor, articulo, descripcion, unidades, precio unitario y total.",
                "Salidas: area, observacion, articulo, unidades, costo PEPS y total.",
                "Los formatos muestran unidades sin decimales cuando corresponde.",
            ])),
        )
        + card("Reportes disponibles", table(
            ["Reporte", "Contenido"],
            [
                ["Inventario", "Existencias, saldo inicial, entradas, salidas, valor inventario, costo por unidad y precio promedio."],
                ["Proveedor", "Compras por proveedor con articulo, fecha, precio unitario y total."],
                ["Entradas", "Unidades, descripcion, precio unitario y compra total."],
                ["Salidas", "Observacion, area, costo PEPS y total en pesos."],
                ["Consumos por area", "Consumo por area, familia, subfamilia, unidades, costo promedio PEPS y total."],
                ["Compras sugeridas", "Calculo de pedido sugerido con dias de stock requeridos y periodo de analisis."],
                ["Obsoletos", "Articulos con existencia y sin movimiento durante el periodo definido."],
                ["Bajo stock", "Articulos cuya existencia se encuentra debajo del limite definido."],
            ],
        ))
        + card("Exportacion", ul([
            "Los reportes pueden exportarse desde las pantallas correspondientes.",
            "La informacion exportada respeta filtros aplicados por fecha, proveedor, area o catalogo.",
            "Los costos en reportes usan respaldo para evitar totales en cero cuando hay ultima compra registrada.",
        ])),
    )

    page(
        "catalogos.html",
        "Catalogos",
        "Configuracion",
        "Catalogos operativos",
        "Los catalogos organizan la informacion base del sistema y deben mantenerse actualizados para que reportes y ordenes sean correctos.",
        grid(
            card("Familias", ul([
                "Agrupan articulos por tipo o uso.",
                "Se usan en reportes de inventario y consumos.",
                "Pueden estar activas o inactivas.",
            ])),
            card("Proveedores", ul([
                "Se usan en ordenes de compra.",
                "Permiten reportes de compras por proveedor.",
                "La ultima compra muestra proveedor, fecha y precio.",
            ])),
        )
        + grid(
            card("Areas", ul([
                "Son obligatorias en salidas.",
                "Permiten registrar el gasto o consumo por area.",
                "Alimentan reportes de consumos por area, familia y periodo.",
            ])),
            card("Subfamilias", ul([
                "El menu de subfamilias fue retirado de la navegacion principal.",
                "El sistema conserva subfamilias como dato de clasificacion cuando existen.",
                "En reportes puede mostrarse 'Sin familia' cuando no hay subfamilia asignada.",
            ])),
        ),
    )

    page(
        "usuarios.html",
        "Usuarios",
        "Administracion",
        "Usuarios y accesos",
        "El modulo de usuarios controla quien puede ingresar al sistema y operar los modulos.",
        grid(
            card("Gestion de usuarios", ul([
                "Alta de usuario con datos de acceso.",
                "Edicion de informacion.",
                "Activacion o desactivacion segun corresponda.",
                "Uso de sesion para proteger endpoints web.",
            ])),
            card("Recomendaciones", ul([
                "Crear usuarios individuales, no compartidos.",
                "Desactivar usuarios que ya no operan el sistema.",
                "Cerrar sesion en equipos compartidos.",
                "Evitar compartir contrasenas o tokens.",
            ])),
        ),
    )

    page(
        "api-movil.html",
        "API movil",
        "Integracion",
        "API para aplicacion movil",
        "El sistema cuenta con un endpoint para app movil que permite consultar ordenes, validar escaneos, listar pendientes y finalizar operaciones.",
        card("Endpoint", '<pre>POST https://almacen.grupocroram.com/api/mobileOrdenes.php\nHeader: Content-Type: application/json\nHeader: X-CRORAM-Mobile-Token: croram-mobile-dev-2026-cambiar</pre>')
        + grid(
            card("Acciones disponibles", ul([
                "<code>health</code>: valida disponibilidad del API.",
                "<code>listarPendientes</code>: muestra ordenes pendientes de ingresar o aprobar.",
                "<code>buscarOrden</code>: obtiene informacion y detalle de una orden.",
                "<code>validarEscaneos</code>: compara escaneos contra productos esperados.",
                "<code>finalizarOrden</code>: registra entrada o confirma salida despues de validar.",
            ])),
            card("CORS", ul([
                "El endpoint responde con <code>Access-Control-Allow-Origin: *</code>.",
                "Acepta preflight <code>OPTIONS</code>.",
                "Permite encabezados solicitados por navegador, incluyendo token movil.",
            ])),
        )
        + card("Listar pendientes", '<pre>{\n  "accion": "listarPendientes",\n  "limite": 50,\n  "tipo_orden": "",\n  "texto": ""\n}</pre>')
        + card("Buscar orden", '<pre>{\n  "accion": "buscarOrden",\n  "tipo_orden": "entrada",\n  "id_orden": 35\n}</pre>')
        + card("Finalizar orden", ul([
            "Entrada: requiere orden AUTORIZADA, validacion completa y que no tenga lotes ya registrados.",
            "Salida: requiere validacion completa y que la orden no este CONFIRMADA.",
            "Los escaneos pueden usar SKU o ID de producto.",
            "El flujo movil usa las mismas reglas de inventario y PEPS del sistema web.",
        ])),
    )

    page(
        "cambios.html",
        "Cambios aplicados",
        "Bitacora",
        "Cambios y mejoras implementadas",
        "Registro de cambios aplicados al sistema durante las ultimas entregas.",
        card("Inventario y PEPS", ul([
            "Se corrigio duplicidad de entradas retirando suma manual cuando el trigger de base de datos ya suma al cambiar a RECIBIDA.",
            "Se retiro descuento manual en aprobaciones de salida cuando el trigger ya descuenta al cambiar a CONFIRMADA.",
            "Se corrigio consumo PEPS para no crear lote legacy si el producto ya tiene lotes de compra.",
            "Se actualizaron costos historicos en cero cuando existia ultima compra o costo de reposicion.",
            "Caso validado: salida OS-20260630103851 de Volante creta 2022 con PEPS de $1,280.00 y subtotal de $2,560.00.",
        ]))
        + card("Articulos y QR", ul([
            "SKU automatico por consecutivo cuando el usuario no captura SKU.",
            "Campo ubicacion agregado a articulos y reportes.",
            "Listado de articulos cambiado a paginacion por API con busqueda en tiempo real.",
            "QR termico y carta con QR de 4x4 cm, descripcion impresa y SKU.",
            "Edicion de articulo calcula costo por unidad desde costo total actual / unidades.",
        ]))
        + card("Ordenes", ul([
            "Entrada requiere autorizacion antes de recibir.",
            "Antes de autorizar entrada se muestra ultima compra con proveedor, fecha y precio.",
            "Entrada y salida validan QR/SKU por producto.",
            "Salidas requieren area y observacion.",
            "Salida guarda costo PEPS y subtotal para reportes.",
            "Ordenes concluidas bloquean acciones para evitar duplicados.",
        ]))
        + card("Reportes", ul([
            "Inventario muestra saldo inicial, entradas, salidas, existencia, valor inventario, costo por unidad y precio promedio.",
            "Reportes de entradas muestran unidades, descripcion, precio unitario y total.",
            "Reportes de salidas muestran area, observacion, costo PEPS y total.",
            "Consumos por area incluyen costo PEPS promedio y total por periodo.",
            "Compras sugeridas usan fecha de inicio de analisis y calculo por dias de stock requeridos.",
            "Reporte de obsoletos lista articulos con existencia y sin movimiento en el periodo definido.",
        ]))
        + card("API movil", ul([
            "Endpoint movil con token.",
            "CORS abierto para permitir pruebas desde web y app.",
            "Acciones para listar pendientes, buscar orden, validar escaneos y finalizar orden.",
            "Listado de pendientes muestra entradas autorizadas sin lotes y salidas no confirmadas.",
        ])),
    )

    page(
        "buenas-practicas.html",
        "Buenas practicas",
        "Operacion",
        "Buenas practicas de captura",
        "Recomendaciones para mantener inventario, costos y reportes confiables.",
        grid(
            card("Antes de capturar", ul([
                "Buscar articulo por SKU, nombre, descripcion y ubicacion antes de crearlo.",
                "Verificar proveedor y area.",
                "Confirmar unidades fisicas antes de guardar.",
                "Usar descripcion clara para que el QR y la busqueda sean utiles.",
            ])),
            card("Durante movimientos", ul([
                "Escanear o validar SKU/ID de cada producto.",
                "Revisar precio real de compra al ingresar una entrada.",
                "No aprobar salidas si falta area u observacion.",
                "No repetir una orden si ya esta RECIBIDA o CONFIRMADA.",
            ])),
        )
        + card("Revision periodica", ul([
            "Revisar reporte de inventario contra conteos fisicos.",
            "Revisar salidas sin costo cuando no exista compra previa.",
            "Mantener costos de reposicion actualizados para articulos sin historial de compra.",
            "Usar reportes de consumos por area para detectar patrones o desviaciones.",
            "Desactivar articulos obsoletos cuando ya no deban usarse.",
        ])),
    )


if __name__ == "__main__":
    build()
