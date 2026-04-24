<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Almacen Croram - Orden de Salida</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/config.js"></script>
    <style>
        body { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
        .page-header { background: white; color: #212529; padding: 2rem; border-radius: .5rem; margin-bottom: 2rem; box-shadow: 0 .25rem .75rem rgba(0,0,0,.08); border: 2px solid #e9ecef; border-left: 6px solid #dc3545; }
        .page-header h4 { margin: 0; font-weight: 700; display: flex; align-items: center; gap: .75rem; }
        .subtitle { color: #6c757d; font-size: .9rem; margin-top: .5rem; }
        .filter-section, .section-card { background: white; border-radius: .5rem; box-shadow: 0 .125rem .5rem rgba(0,0,0,.05); border: 1px solid #e9ecef; }
        .filter-section { padding: 1.75rem; margin-bottom: 2rem; }
        .filter-section label { font-weight: 600; color: #495057; margin-bottom: .5rem; display: block; font-size: .875rem; text-transform: uppercase; letter-spacing: .5px; }
        .filter-section .form-control, .filter-section .form-select, .product-input { background: #f8f9fa; border: 2px solid #e9ecef; border-radius: .375rem; }
        .section-header { background: #f8f9fa; padding: 1.25rem 1.5rem; border-bottom: 2px solid #e9ecef; border-radius: .5rem .5rem 0 0; }
        .section-header h5 { margin: 0; font-weight: 700; color: #212529; display: flex; align-items: center; gap: .5rem; }
        .section-body { padding: 1.5rem; max-height: 600px; overflow-y: auto; }
        .product-card { border: 2px solid #e9ecef; border-radius: .5rem; transition: all .2s ease; background: white; height: 100%; }
        .product-card:hover { border-color: #dc3545; box-shadow: 0 .25rem .75rem rgba(220,53,69,.15); transform: translateY(-2px); }
        .product-card .card-body { padding: 1.25rem; }
        .sku-badge { background-color: #f8f9fa; color: #495057; padding: .25rem .625rem; border-radius: .25rem; font-size: .75rem; font-weight: 600; display: inline-block; border: 1px solid #e9ecef; }
        .btn-remove-product { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; font-weight: 600; text-transform: uppercase; font-size: .875rem; letter-spacing: .5px; }
        .btn-remove-product:hover { color: white; }
        .exit-item { border: 1px solid #e9ecef; border-radius: .375rem; margin-bottom: .75rem; padding: 1rem; background: #f8f9fa; border-left: 4px solid #dc3545; }
        .exit-item-header { font-weight: 700; color: #212529; margin-bottom: .25rem; font-size: .95rem; }
        .exit-item-details { color: #6c757d; font-size: .875rem; display: flex; align-items: center; gap: .5rem; }
        .exit-badge { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: .25rem .75rem; border-radius: .25rem; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .alert-card { background: white; padding: 1.5rem; border-radius: .5rem; margin-bottom: 1.5rem; box-shadow: 0 .125rem .25rem rgba(0,0,0,.05); border: 2px solid #e9ecef; border-left: 6px solid #dc3545; }
        .alert-label { font-size: .875rem; font-weight: 600; color: #6c757d; margin-bottom: .25rem; text-transform: uppercase; letter-spacing: .5px; }
        .alert-amount { font-size: 1.5rem; font-weight: 700; color: #212529; }
        .btn-submit-exit { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: 2px solid #bd2130; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 1rem; border-radius: .5rem; font-size: 1rem; }
        .empty-state { text-align: center; padding: 3rem 1.5rem; color: #6c757d; }
        .empty-state-icon { font-size: 4rem; color: #e9ecef; margin-bottom: 1rem; }
        .empty-state-text { font-size: 1.125rem; font-weight: 600; color: #6c757d; }
        .badge-count { background: white; color: #dc3545; padding: .25rem .625rem; border-radius: 1rem; font-size: .875rem; font-weight: 700; margin-left: .5rem; border: 2px solid #dc3545; }
        .warning-note { background: #fff3cd; border: 1px solid #ffc107; border-left: 4px solid #ffc107; padding: 1rem; border-radius: .375rem; margin-bottom: 1rem; display: flex; align-items: start; gap: .75rem; }
        .warning-note-text { flex: 1; font-size: .875rem; color: #856404; }
    </style>
</head>
<body>
<div class="wrapper">
<?php
include_once 'templates/barra.php';
include_once 'templates/headder.php';
include_once 'api/adminCatalogos.php';
$adminCatalogos = new AdministradorCatalogos();
$familias = json_decode($adminCatalogos->listarFamilias(true)) ?: [];
$usuarioActualId = usuario_id_actual();
?>
<div class="page-content">
<div class="page-container">
<div class="page-header">
    <h4><i class="ri-logout-box-line"></i>Nueva Orden de Salida</h4>
    <p class="subtitle mb-0"><i class="ri-alert-line"></i>Registre los productos que saldran del inventario</p>
</div>
<div class="filter-section">
    <div class="row g-3">
        <div class="col-md-4">
            <label><i class="ri-folder-line me-1"></i>Familia</label>
            <select id="filtroFamilia" class="form-select"><option value="">Todas las familias</option></select>
        </div>
        <div class="col-md-4">
            <label><i class="ri-folder-open-line me-1"></i>Subfamilia</label>
            <select id="filtroSubfamilia" class="form-select"><option value="">Todas las subfamilias</option></select>
        </div>
        <div class="col-md-4">
            <label><i class="ri-search-line me-1"></i>Buscar Producto</label>
            <input type="text" id="filtroTexto" class="form-control" placeholder="Nombre, SKU o descripcion...">
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="section-card">
            <div class="section-header">
                <h5><i class="ri-box-3-line"></i>Articulos Disponibles <span class="badge-count" id="countProductos">0</span></h5>
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
                <h5><i class="ri-file-list-3-line"></i>Orden de Salida <span class="badge-count" id="countSalida">0</span></h5>
            </div>
            <div class="section-body">
                <div class="warning-note"><i class="ri-information-line"></i><div class="warning-note-text"><strong>Importante:</strong> Los productos agregados se restaran del inventario al registrar la salida.</div></div>
                <div id="listaSalida"></div>
                <div class="alert-card">
                    <div class="alert-label">Total de productos</div>
                    <div class="alert-amount"><span id="totalProductos">0</span> articulos</div>
                </div>
                <button class="btn btn-submit-exit w-100" id="btnEnviar"><i class="ri-logout-box-line me-2"></i>Registrar Salida</button>
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
let salida = [];
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

function estadoVacioSalida() {
    document.getElementById('listaSalida').innerHTML = `
        <div class="empty-state"><div class="empty-state-icon"><i class="ri-inbox-line"></i></div><div class="empty-state-text">No hay productos en la salida</div><small class="text-muted">Agregue productos desde el catalogo</small></div>
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
    formData.append('soloConStock', '1');

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
        const cantidad = Math.trunc(Number(p.cantidad || 0));
        container.innerHTML += `
            <div class="col">
                <div class="card product-card h-100">
                    <div class="card-body">
                        <h6>${p.nombre}</h6>
                        <span class="sku-badge"><i class="ri-barcode-line"></i> ${p.sku || 'Sin SKU'}</span>
                        <div class="mt-2 text-muted small">${p.descripcion || 'Sin descripcion'}</div>
                        <div class="mt-2 small"><strong>Disponible:</strong> ${cantidad} ${p.unidad_medida || 'pz'}</div>
                        <div class="mt-3">
                            <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;color:#6c757d;">Cantidad a extraer</label>
                            <input type="number" min="1" max="${cantidad}" value="1" class="form-control product-input" id="qty_${p.id}">
                        </div>
                        <button class="btn btn-remove-product mt-3 w-100" onclick="agregar(${p.id})"><i class="ri-logout-box-line me-1"></i>Agregar a Salida</button>
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
    const disponible = Math.trunc(Number(prod.cantidad || 0));
    if (!qty || qty <= 0 || qty > disponible) {
        Swal.fire({ icon: 'warning', title: 'Cantidad invalida', text: 'La cantidad debe ser mayor a 0 y no exceder el stock disponible', confirmButtonColor: '#6c757d' });
        return;
    }

    const existente = salida.find(item => Number(item.id) === Number(id));
    const nuevaCantidad = (existente ? existente.cantidad : 0) + qty;
    if (nuevaCantidad > disponible) {
        Swal.fire({ icon: 'warning', title: 'Stock insuficiente', text: 'La salida acumulada supera el stock disponible para este articulo', confirmButtonColor: '#6c757d' });
        return;
    }

    if (existente) {
        existente.cantidad = nuevaCantidad;
    } else {
        salida.push({ id: Number(prod.id), sku: prod.sku || '', nombre: prod.nombre, cantidad: qty, unidad: prod.unidad_medida || 'pz' });
    }

    renderSalida();
    updateCounts();
    document.getElementById(`qty_${id}`).value = 1;
}

function renderSalida() {
    const container = document.getElementById('listaSalida');
    if (salida.length === 0) {
        estadoVacioSalida();
        return;
    }

    container.innerHTML = '';
    salida.forEach(item => {
        container.innerHTML += `
            <div class="exit-item">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1">
                        <div class="exit-item-header">${item.nombre}</div>
                        <div class="exit-item-details"><i class="ri-subtract-line"></i><span>${item.cantidad} ${item.unidad}</span></div>
                    </div>
                    <div class="text-end">
                        <span class="exit-badge">Salida</span>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-block" onclick="remover(${item.id})">Quitar</button>
                    </div>
                </div>
            </div>`;
    });
}

function remover(id) {
    salida = salida.filter(item => Number(item.id) !== Number(id));
    renderSalida();
    updateCounts();
}

function updateCounts() {
    const totalItems = salida.reduce((sum, item) => sum + Math.trunc(Number(item.cantidad || 0)), 0);
    document.getElementById('countSalida').textContent = salida.length;
    document.getElementById('totalProductos').textContent = totalItems;
}

async function registrarSalida() {
    const formData = new FormData();
    formData.append('accion', 'altaOrdenSalida');
    formData.append('id_usuario', USUARIO_ACTUAL_ID);
    formData.append('orden', JSON.stringify(salida));
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
    estadoVacioSalida();
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
    if (salida.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Salida vacia', text: 'Agregue al menos un producto a la salida', confirmButtonColor: '#6c757d' });
        return;
    }

    const totalItems = salida.reduce((sum, item) => sum + Math.trunc(Number(item.cantidad || 0)), 0);
    const confirmacion = await Swal.fire({
        title: 'Registrar salida?',
        html: `<p>Se registrara la salida de <strong>${salida.length}</strong> producto(s)</p><p>Total de unidades: <strong>${totalItems}</strong></p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, registrar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) return;

    Swal.fire({ title: 'Procesando...', text: 'Registrando orden de salida', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const resultado = await registrarSalida();
        if (resultado.status === 'success') {
            await Swal.fire({ icon: 'success', title: 'Salida registrada', text: 'La orden de salida ha sido registrada correctamente', confirmButtonColor: '#495057' });
            window.location.href = 'ordenes-salida.php';
        } else {
            throw new Error(resultado.message || 'No fue posible registrar la salida');
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Ocurrio un error al registrar la salida', confirmButtonColor: '#6c757d' });
    }
});
</script>
</body>
</html>

