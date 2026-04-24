<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Nueva Orden</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .page-header { background: linear-gradient(135deg, #495057 0%, #343a40 100%); color: white; padding: 2rem; border-radius: .5rem; margin-bottom: 2rem; box-shadow: 0 .25rem .75rem rgba(0,0,0,.1); }
        .page-header h4 { margin: 0; font-weight: 700; display: flex; align-items: center; gap: .75rem; }
        .filter-section, .section-card { background: white; border-radius: .5rem; box-shadow: 0 .125rem .5rem rgba(0,0,0,.05); border: 1px solid #e0e0e0; }
        .filter-section { padding: 1.5rem; margin-bottom: 2rem; }
        .filter-section label { font-weight: 600; color: #495057; margin-bottom: .5rem; display: block; font-size: .875rem; text-transform: uppercase; letter-spacing: .5px; }
        .filter-section .form-control, .filter-section .form-select, .product-input { border: 2px solid #dee2e6; border-radius: .375rem; }
        .section-header { background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 2px solid #dee2e6; border-radius: .5rem .5rem 0 0; }
        .section-header h5 { margin: 0; font-weight: 700; color: #212529; display: flex; align-items: center; gap: .5rem; }
        .section-body { padding: 1.5rem; max-height: 600px; overflow-y: auto; }
        .product-card { border: 2px solid #e0e0e0; border-radius: .5rem; transition: all .2s ease; background: white; height: 100%; }
        .product-card:hover { border-color: #6c757d; box-shadow: 0 .25rem .75rem rgba(0,0,0,.1); transform: translateY(-2px); }
        .product-card .card-body { padding: 1.25rem; }
        .sku-badge { background-color: #e9ecef; color: #495057; padding: .25rem .625rem; border-radius: .25rem; font-size: .75rem; font-weight: 600; display: inline-block; }
        .btn-add-product { background: #495057; color: white; border: none; font-weight: 600; text-transform: uppercase; font-size: .875rem; letter-spacing: .5px; }
        .btn-add-product:hover { background: #343a40; color: white; }
        .order-item { border: 1px solid #e0e0e0; border-radius: .375rem; margin-bottom: .75rem; padding: 1rem; background: #f8f9fa; }
        .order-item-header { font-weight: 700; color: #212529; margin-bottom: .25rem; font-size: .95rem; }
        .order-item-details { color: #6c757d; font-size: .875rem; display: flex; align-items: center; gap: .5rem; }
        .order-item-price { font-weight: 700; color: #212529; font-size: 1.125rem; }
        .total-card { background: linear-gradient(135deg, #495057 0%, #343a40 100%); color: white; padding: 1.5rem; border-radius: .5rem; margin-bottom: 1.5rem; }
        .total-label { font-size: 1rem; font-weight: 500; opacity: .9; margin-bottom: .5rem; }
        .total-amount { font-size: 2rem; font-weight: 700; }
        .btn-submit { background: #495057; color: white; border: 2px solid #343a40; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 1rem; border-radius: .5rem; }
        .empty-state { text-align: center; padding: 3rem 1.5rem; color: #6c757d; }
        .empty-state-icon { font-size: 4rem; color: #dee2e6; margin-bottom: 1rem; }
        .badge-count { background: #495057; color: white; padding: .25rem .625rem; border-radius: 1rem; font-size: .875rem; font-weight: 600; margin-left: .5rem; }
    </style>
</head>
<body>
<div class="wrapper">
<?php
include_once 'templates/barra.php';
include_once 'templates/headder.php';
include_once 'api/adminCatalogos.php';
include_once 'api/adminProveedores.php';
$adminProveedores = new AdministradorProveedores();
$adminCatalogos = new AdministradorCatalogos();
$proveedores = json_decode($adminProveedores->listarProveedores(true)) ?: [];
$familias = json_decode($adminCatalogos->listarFamilias(true)) ?: [];
$usuarioActualId = usuario_id_actual();
?>
<div class="page-content">
<div class="page-container">
<div class="page-header">
    <h4><i class="ri-shopping-cart-line"></i>Nueva Orden de Compra</h4>
    <p class="mb-0 mt-2 opacity-75">Seleccione productos y configure los detalles de su orden</p>
</div>
<div class="filter-section">
    <div class="row g-3">
        <div class="col-md-3">
            <label><i class="ri-folder-line me-1"></i>Familia</label>
            <select id="filtroFamilia" class="form-select"><option value="">Todas las familias</option></select>
        </div>
        <div class="col-md-3">
            <label><i class="ri-folder-open-line me-1"></i>Subfamilia</label>
            <select id="filtroSubfamilia" class="form-select"><option value="">Todas las subfamilias</option></select>
        </div>
        <div class="col-md-3">
            <label><i class="ri-search-line me-1"></i>Buscar Producto</label>
            <input type="text" id="filtroTexto" class="form-control" placeholder="Nombre, SKU o descripcion...">
        </div>
        <div class="col-md-3">
            <label><i class="ri-building-line me-1"></i>Proveedor</label>
            <select id="filtroProveedor" class="form-select">
                <option value="">Seleccione un proveedor</option>
                <?php foreach ($proveedores as $p): ?>
                    <option value="<?= (int)$p->id ?>"><?= htmlspecialchars($p->nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="section-card">
            <div class="section-header">
                <h5><i class="ri-box-3-line"></i>Catalogo de Productos <span class="badge-count" id="countProductos">0</span></h5>
            </div>
            <div class="section-body">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3" id="listaArticulos"></div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted" id="articulosResumen">Mostrando 0 resultados</small>
                    <div class="btn-group" id="articulosPaginacion"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="section-card">
            <div class="section-header">
                <h5><i class="ri-file-list-3-line"></i>Orden Actual <span class="badge-count" id="countOrden">0</span></h5>
            </div>
            <div class="section-body">
                <div id="listaOrden"></div>
                <div class="total-card">
                    <div class="total-label">TOTAL DE LA ORDEN</div>
                    <div class="total-amount">$<span id="totalOrden">0.00</span></div>
                </div>
                <button class="btn btn-submit w-100" id="btnEnviar"><i class="ri-send-plane-fill me-2"></i>Enviar Orden</button>
            </div>
        </div>
    </div>
</div>
</div>
<?php include 'templates/footer.php'; ?>
</div>
</div>
<?php include_once 'templates/theme.php'; ?>
<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const familias = <?= json_encode(array_map(fn($f) => ['id' => (int)$f->id, 'nombre' => $f->nombre], $familias), JSON_UNESCAPED_UNICODE) ?>;
let subfamilias = [];
let articulosPagina = [];
let orden = [];
let paginaActual = 1;
const ARTICULOS_POR_PAGINA = 10;
let totalArticulos = 0;
let debounceBusqueda = null;
const USUARIO_ACTUAL_ID = <?= (int)$usuarioActualId ?>;

const filtroFamilia = document.getElementById('filtroFamilia');
const filtroSubfamilia = document.getElementById('filtroSubfamilia');
const filtroTexto = document.getElementById('filtroTexto');

function estadoVacioCatalogo(mensaje = 'Cargando productos...') {
    document.getElementById('listaArticulos').innerHTML = `
        <div class="col-12"><div class="empty-state"><div class="empty-state-icon"><i class="ri-box-3-line"></i></div><div class="empty-state-text">${mensaje}</div></div></div>
    `;
}

function estadoVacioOrden() {
    document.getElementById('listaOrden').innerHTML = `
        <div class="empty-state"><div class="empty-state-icon"><i class="ri-shopping-basket-line"></i></div><div class="empty-state-text">No hay productos en la orden</div><small class="text-muted">Agregue productos desde el catalogo</small></div>
    `;
}

function cargarFamilias() {
    filtroFamilia.innerHTML = '<option value="">Todas las familias</option>';
    familias.forEach(f => filtroFamilia.innerHTML += `<option value="${f.id}">${f.nombre}</option>`);
}

async function cargarSubFamilias() {
    const idFamilia = filtroFamilia.value;
    filtroSubfamilia.innerHTML = '<option value="">Todas las subfamilias</option>';
    subfamilias = [];
    if (!idFamilia) return;

    const body = new URLSearchParams({ accion: 'listarSubFamilias', id_familia: idFamilia, soloActivas: 1 });
    const response = await fetch('api/apiCatalogos.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
    const data = await response.json();
    subfamilias = Array.isArray(data) ? data : [];
    subfamilias.forEach(sf => filtroSubfamilia.innerHTML += `<option value="${sf.id}">${sf.nombre}</option>`);
}

async function buscarArticulos(pagina = 1) {
    paginaActual = pagina;
    estadoVacioCatalogo('Cargando productos...');

    const formData = new FormData();
    formData.append('accion', 'listarArticulosPaginados');
    formData.append('pagina', paginaActual);
    formData.append('porPagina', ARTICULOS_POR_PAGINA);
    formData.append('texto', filtroTexto.value.trim());
    formData.append('id_familia', filtroFamilia.value || '');
    formData.append('id_subfamilia', filtroSubfamilia.value || '');
    formData.append('soloActivos', '1');
    formData.append('soloConStock', '0');

    try {
        const response = await fetch('api/apiArticulos.php', { method: 'POST', body: formData });
        const payload = await response.json();
        articulosPagina = Array.isArray(payload.data) ? payload.data : [];
        totalArticulos = Number(payload.pagination?.total || 0);
        renderArticulos(payload.pagination || { page: 1, total_pages: 1, total: 0, per_page: ARTICULOS_POR_PAGINA });
    } catch (error) {
        articulosPagina = [];
        totalArticulos = 0;
        estadoVacioCatalogo('No fue posible cargar los productos');
        document.getElementById('articulosResumen').textContent = 'Mostrando 0 resultados';
        document.getElementById('articulosPaginacion').innerHTML = '';
        document.getElementById('countProductos').textContent = '0';
    }
}

function renderArticulos(pagination) {
    const container = document.getElementById('listaArticulos');
    const resumen = document.getElementById('articulosResumen');
    const paginacion = document.getElementById('articulosPaginacion');

    if (articulosPagina.length === 0) {
        estadoVacioCatalogo('No se encontraron productos');
        resumen.textContent = 'Mostrando 0 resultados';
        paginacion.innerHTML = '';
        document.getElementById('countProductos').textContent = '0';
        return;
    }

    container.innerHTML = '';
    articulosPagina.forEach(p => {
        const costo = Number(p.costo_reposicion || 0).toFixed(2);
        container.innerHTML += `
            <div class="col">
                <div class="card product-card h-100">
                    <div class="card-body">
                        <h6>${p.nombre}</h6>
                        <span class="sku-badge"><i class="ri-barcode-line"></i> ${p.sku || 'Sin SKU'}</span>
                        <div class="mt-2 text-muted small">${p.descripcion || 'Sin descripcion'}</div>
                        <div class="mt-2 small"><strong>Stock:</strong> ${Math.trunc(Number(p.cantidad || 0))}</div>
                        <div class="mt-3">
                            <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;color:#6c757d;">Cantidad (${p.unidad_medida || 'pz'})</label>
                            <input type="number" min="1" value="1" class="form-control product-input mb-2" id="qty_${p.id}">
                            <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;color:#6c757d;">Precio unitario</label>
                            <input type="number" min="0" step="0.01" class="form-control product-input" id="price_${p.id}" value="${costo}">
                        </div>
                        <button class="btn btn-add-product mt-3 w-100" onclick="agregar(${p.id})"><i class="ri-add-circle-line me-1"></i>Agregar</button>
                    </div>
                </div>
            </div>`;
    });

    const inicio = totalArticulos === 0 ? 0 : ((pagination.page - 1) * pagination.per_page) + 1;
    const fin = inicio + articulosPagina.length - 1;
    document.getElementById('countProductos').textContent = totalArticulos;
    resumen.textContent = `Mostrando ${inicio}-${fin} de ${totalArticulos} articulos`;
    renderPaginacion(pagination.total_pages || 1, pagination.page || 1);
}

function renderPaginacion(totalPaginas, pagina) {
    const paginacion = document.getElementById('articulosPaginacion');
    paginacion.innerHTML = '';
    if (totalPaginas <= 1) return;

    const maxBotones = 10;
    let inicio = Math.max(1, pagina - Math.floor(maxBotones / 2));
    let fin = inicio + maxBotones - 1;

    if (fin > totalPaginas) {
        fin = totalPaginas;
        inicio = Math.max(1, fin - maxBotones + 1);
    }

    const crearBtn = (label, target, disabled = false, active = false) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `btn btn-sm ${active ? 'btn-dark' : 'btn-outline-secondary'}`;
        btn.textContent = label;
        btn.disabled = disabled;
        btn.addEventListener('click', () => buscarArticulos(target));
        paginacion.appendChild(btn);
    };

    crearBtn('Anterior', pagina - 1, pagina <= 1);

    for (let i = inicio; i <= fin; i++) {
        crearBtn(String(i), i, false, i === pagina);
    }

    crearBtn('Siguiente', pagina + 1, pagina >= totalPaginas);
}

function agregar(id) {
    const prod = articulosPagina.find(p => Number(p.id) === Number(id));
    if (!prod) return;

    const qty = parseInt(document.getElementById(`qty_${id}`).value, 10);
    const precio = parseFloat(document.getElementById(`price_${id}`).value);

    if (!qty || qty <= 0 || Number.isNaN(precio) || precio < 0) {
        Swal.fire({ icon: 'warning', title: 'Datos incompletos', text: 'Capture cantidad y precio validos', confirmButtonColor: '#495057' });
        return;
    }

    const existente = orden.find(o => Number(o.id) === Number(id));
    if (existente) {
        existente.cantidad += qty;
        existente.precio = precio;
        existente.total = existente.cantidad * existente.precio;
    } else {
        orden.push({ id: Number(prod.id), sku: prod.sku || '', nombre: prod.nombre, cantidad: qty, unidad: prod.unidad_medida || 'pz', precio, total: qty * precio });
    }

    renderOrden();
    updateCounts();
    document.getElementById(`qty_${id}`).value = 1;
}

function renderOrden() {
    const container = document.getElementById('listaOrden');
    let totalOrden = 0;

    if (orden.length === 0) {
        estadoVacioOrden();
        document.getElementById('totalOrden').textContent = '0.00';
        return;
    }

    container.innerHTML = '';
    orden.forEach(o => {
        totalOrden += o.total;
        container.innerHTML += `
            <div class="order-item">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1">
                        <div class="order-item-header">${o.nombre}</div>
                        <div class="order-item-details"><i class="ri-checkbox-multiple-line"></i><span>${o.cantidad} x $${o.precio.toFixed(2)}</span></div>
                    </div>
                    <div class="text-end">
                        <div class="order-item-price">$${o.total.toFixed(2)}</div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="remover(${o.id})">Quitar</button>
                    </div>
                </div>
            </div>`;
    });
    document.getElementById('totalOrden').textContent = totalOrden.toFixed(2);
}

function remover(id) {
    orden = orden.filter(item => Number(item.id) !== Number(id));
    renderOrden();
    updateCounts();
}

function updateCounts() {
    document.getElementById('countOrden').textContent = orden.length;
}

async function enviarOrden(proveedor) {
    const formData = new FormData();
    formData.append('accion', 'altaOrdenCompra');
    formData.append('proveedor', proveedor);
    formData.append('id_usuario', USUARIO_ACTUAL_ID);
    formData.append('orden', JSON.stringify(orden));

    const response = await fetch('api/apiOrdenes.php', { method: 'POST', body: formData });
    return response.json();
}

function programarBusqueda() {
    clearTimeout(debounceBusqueda);
    debounceBusqueda = setTimeout(() => buscarArticulos(1), 250);
}

document.addEventListener('DOMContentLoaded', async () => {
    cargarFamilias();
    estadoVacioCatalogo();
    estadoVacioOrden();
    updateCounts();
    await buscarArticulos(1);
});

filtroTexto.addEventListener('input', programarBusqueda);
filtroFamilia.addEventListener('change', async () => {
    await cargarSubFamilias();
    buscarArticulos(1);
});
filtroSubfamilia.addEventListener('change', () => buscarArticulos(1));

document.getElementById('btnEnviar').addEventListener('click', async () => {
    const proveedor = document.getElementById('filtroProveedor').value;
    if (!proveedor) {
        Swal.fire({ icon: 'warning', title: 'Proveedor requerido', text: 'Seleccione un proveedor antes de enviar la orden', confirmButtonColor: '#495057' });
        return;
    }
    if (orden.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Orden vacia', text: 'Agregue al menos un producto a la orden', confirmButtonColor: '#495057' });
        return;
    }

    const confirmacion = await Swal.fire({
        title: 'Enviar orden?',
        text: `Se enviara la orden con ${orden.length} producto(s)`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#495057',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, enviar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) return;

    Swal.fire({ title: 'Procesando...', text: 'Enviando orden de compra', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const resultado = await enviarOrden(proveedor);
        if (resultado.status === 'success') {
            await Swal.fire({ icon: 'success', title: 'Orden enviada', text: 'La orden de compra ha sido registrada correctamente', confirmButtonColor: '#495057' });
            window.location.href = 'ordenes-entrada.php';
        } else {
            throw new Error(resultado.message || 'No fue posible registrar la orden');
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Ocurrio un error al enviar la orden', confirmButtonColor: '#6c757d' });
    }
});
</script>
</body>
</html>

