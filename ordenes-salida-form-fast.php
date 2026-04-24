<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Escáner de salida</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de inventario de CRORAM." name="description" />
    <meta content="HoppingJet Studio." name="author" />

    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <script src="assets/js/config.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .page-header {
            background: white;
            color: #212529;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            border-left: 6px solid #dc3545;
        }

        .page-header h4 {
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #212529;
        }

        .scanner-section {
            background: white;
            padding: 2.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
            text-align: center;
        }

        .scanner-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .scanner-input {
            background: #f8f9fa;
            border: 3px solid #dc3545;
            border-radius: 0.5rem;
            padding: 1.25rem;
            font-size: 1.25rem;
            text-align: center;
            font-weight: 600;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.2);
        }

        .scanner-input:focus {
            background: white;
            border-color: #c82333;
            box-shadow: 0 0 30px rgba(220, 53, 69, 0.4);
            outline: none;
        }

        .scanner-label {
            font-size: 1.125rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
            display: block;
        }

        .scanner-help {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.75rem;
        }

        .section-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .section-header {
            background: #f8f9fa;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid #e9ecef;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .section-header h5 {
            margin: 0;
            font-weight: 700;
            color: #212529;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-body {
            padding: 1.5rem;
            max-height: 500px;
            overflow-y: auto;
        }

        .section-body::-webkit-scrollbar {
            width: 8px;
        }

        .section-body::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 4px;
        }

        .section-body::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 4px;
        }

        .product-item {
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            padding: 1.25rem;
            background: white;
            transition: all 0.3s ease;
            border-left: 5px solid #dc3545;
        }

        .product-item:hover {
            box-shadow: 0 0.25rem 0.75rem rgba(220, 53, 69, 0.1);
            transform: translateX(5px);
        }

        .product-item-new {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .product-name {
            font-weight: 700;
            color: #212529;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .product-sku {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border-radius: 0.375rem;
            border: 2px solid #e9ecef;
            background: white;
            color: #495057;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
            transform: scale(1.1);
        }

        .quantity-btn:active {
            transform: scale(0.95);
        }

        .quantity-input {
            width: 80px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.5rem;
            background: #f8f9fa;
        }

        .quantity-input:focus {
            border-color: #adb5bd;
            background: white;
            outline: none;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #e9ecef;
            margin-bottom: 1rem;
        }

        .empty-state-text {
            font-size: 1.125rem;
            font-weight: 600;
            color: #6c757d;
        }

        .badge-count {
            background: white;
            color: #dc3545;
            padding: 0.25rem 0.625rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 700;
            margin-left: 0.5rem;
            border: 2px solid #dc3545;
        }

        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            border: 2px solid #e9ecef;
            border-left: 6px solid #dc3545;
            margin-bottom: 1.5rem;
        }

        .summary-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
        }

        .btn-submit {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: 2px solid #bd2130;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(220, 53, 69, 0.2);
        }

        .btn-submit:disabled {
            background: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .warning-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .warning-note i {
            color: #ffc107;
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }

        .warning-note-text {
            flex: 1;
            font-size: 0.875rem;
            color: #856404;
        }

        .btn-clear {
            background: #6c757d;
            color: white;
            border: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .btn-clear:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .scanning-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            background: #28a745;
            border-radius: 50%;
            margin-left: 0.5rem;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>

<body>
<div class="wrapper">

<?php
include_once 'templates/barra.php';
include_once 'templates/headder.php';

include_once 'api/adminArticulos.php';

$adminArticulos = new AdministradorArticulos();
$articulos = json_decode($adminArticulos->listarArticulos(false));
?>

<div class="page-content">
<div class="page-container">

<!-- ================= HEADER ================= -->
<div class="page-header">
    <h4>
        <i class="ri-qr-scan-line"></i>
        Escáner de Salida
    </h4>
    <p class="mb-0 mt-2" style="color: #6c757d; font-size: 0.9rem;">
        <i class="ri-alert-line"></i>
        Escanee los productos que saldrán del inventario
    </p>
</div>

<!-- ================= ESCÃNER ================= -->
<div class="scanner-section">
    <div class="scanner-icon">
        <i class="ri-qr-scan-2-line"></i>
    </div>
    <label class="scanner-label">
        Escanear Producto
        <span class="scanning-indicator"></span>
    </label>
    <input 
        type="text" 
        id="scannerInput" 
        class="form-control scanner-input" 
        placeholder="Esperando escaneo..."
        autocomplete="off"
        autofocus
    >
    <div class="scanner-help">
        <i class="ri-information-line"></i>
        Escanee el código del producto o ingrese el ID manualmente
    </div>
</div>

<!-- ================= CONTENIDO ================= -->
<div class="row g-4">
    <!-- PRODUCTOS ESCANEADOS -->
    <div class="col-lg-8">
        <div class="section-card">
            <div class="section-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>
                        <i class="ri-file-list-3-line"></i>
                        Productos Escaneados
                        <span class="badge-count" id="countProductos">0</span>
                    </h5>
                    <button class="btn btn-clear" id="btnClear" style="display: none;">
                        <i class="ri-delete-bin-line me-1"></i>
                        Limpiar Todo
                    </button>
                </div>
            </div>
            <div class="section-body" id="listaProductos">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="ri-inbox-line"></i>
                    </div>
                    <div class="empty-state-text">
                        No hay productos escaneados
                    </div>
                    <small class="text-muted">Escanee productos para comenzar</small>
                </div>
            </div>
        </div>
    </div>

    <!-- RESUMEN Y ENVÃO -->
    <div class="col-lg-4">
        <div class="warning-note">
            <i class="ri-information-line"></i>
            <div class="warning-note-text">
                <strong>Importante:</strong> Los productos escaneados se restarán del inventario al finalizar.
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Total de productos</div>
            <div class="summary-value">
                <span id="totalProductos">0</span> artículos
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Total de unidades</div>
            <div class="summary-value">
                <span id="totalUnidades">0</span> unidades
            </div>
        </div>

        <button class="btn btn-submit w-100" id="btnFinalizar" disabled>
            <i class="ri-send-plane-fill me-2"></i>
            Finalizar Salida
        </button>
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
/* ================= DATOS ================= */

const productos = [
<?php foreach ($articulos as $a) {
    echo "{
        id: {$a->id},
        sku: '{$a->sku}',
        nombre: '" . addslashes($a->nombre) . "',
        unidad: '{$a->unidad_medida}'
    },";
} ?>
];

let salida = [];
const scannerInput = document.getElementById('scannerInput');

/* ================= INIT ================= */
document.addEventListener('DOMContentLoaded', () => {
    // Mantener focus en el input
    scannerInput.focus();
    
    // Escuchar el escaneo
    scannerInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            procesarEscaneo();
        }
    });
    
    // Re-focus si se pierde
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.quantity-input') && !e.target.closest('.quantity-btn')) {
            setTimeout(() => scannerInput.focus(), 100);
        }
    });
    
    // Botón limpiar
    document.getElementById('btnClear').addEventListener('click', limpiarTodo);
    
    // Botón finalizar
    document.getElementById('btnFinalizar').addEventListener('click', finalizarSalida);
});

/* ================= ESCANEO ================= */
function procesarEscaneo() {
    const codigo = scannerInput.value.trim();
    
    if (!codigo) {
        return;
    }
    
    // Buscar producto por ID o SKU
    const producto = productos.find(p => p.id == codigo || p.sku == codigo);
    
    if (!producto) {
        // Producto no encontrado
        Swal.fire({
            icon: 'error',
            title: 'Producto no encontrado',
            text: `No existe producto con el código: ${codigo}`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        
        scannerInput.value = '';
        scannerInput.focus();
        return;
    }
    
    // Verificar si ya está en la lista
    const existe = salida.find(s => s.id === producto.id);
    
    if (existe) {
        // Incrementar cantidad
        existe.cantidad++;
        
        Swal.fire({
            icon: 'info',
            title: 'Cantidad aumentada',
            text: `${producto.nombre}: ${existe.cantidad} unidades`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
    } else {
        // Agregar nuevo producto
        salida.push({
            id: producto.id,
            sku: producto.sku,
            nombre: producto.nombre,
            cantidad: 1,
            unidad: producto.unidad
        });
        
        Swal.fire({
            icon: 'success',
            title: 'Producto agregado',
            text: producto.nombre,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
    }
    
    renderProductos();
    updateSummary();
    
    // Limpiar input y re-focus
    scannerInput.value = '';
    scannerInput.focus();
}

/* ================= RENDER ================= */
function renderProductos() {
    const container = document.getElementById('listaProductos');
    
    if (salida.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="ri-inbox-line"></i>
                </div>
                <div class="empty-state-text">
                    No hay productos escaneados
                </div>
                <small class="text-muted">Escanee productos para comenzar</small>
            </div>
        `;
        document.getElementById('btnClear').style.display = 'none';
        return;
    }
    
    document.getElementById('btnClear').style.display = 'block';
    
    container.innerHTML = '';
    salida.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'product-item product-item-new';
        div.innerHTML = `
            <div class="product-name">${item.nombre}</div>
            <div class="product-sku">
                <i class="ri-barcode-line"></i> ${item.sku}
            </div>
            <div class="quantity-controls">
                <button class="quantity-btn" onclick="cambiarCantidad(${index}, -1)">
                    <i class="ri-subtract-line"></i>
                </button>
                <input 
                    type="number" 
                    class="quantity-input" 
                    value="${item.cantidad}" 
                    min="1"
                    onchange="actualizarCantidad(${index}, this.value)"
                    onclick="this.select()"
                >
                <button class="quantity-btn" onclick="cambiarCantidad(${index}, 1)">
                    <i class="ri-add-line"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });
}

/* ================= CANTIDAD ================= */
function cambiarCantidad(index, delta) {
    const item = salida[index];
    const nuevaCantidad = item.cantidad + delta;
    
    if (nuevaCantidad < 1) {
        // Eliminar producto
        Swal.fire({
            icon: 'warning',
            title: 'Producto eliminado',
            text: item.nombre,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        
        salida.splice(index, 1);
    } else {
        item.cantidad = nuevaCantidad;
    }
    
    renderProductos();
    updateSummary();
    scannerInput.focus();
}

function actualizarCantidad(index, valor) {
    const cantidad = parseInt(valor);
    
    if (cantidad < 1 || isNaN(cantidad)) {
        // Eliminar producto
        salida.splice(index, 1);
    } else {
        salida[index].cantidad = cantidad;
    }
    
    renderProductos();
    updateSummary();
    scannerInput.focus();
}

/* ================= RESUMEN ================= */
function updateSummary() {
    const totalProductos = salida.length;
            const totalUnidades = salida.reduce((sum, item) => sum + Math.trunc(Number(item.cantidad || 0)), 0);
    
    document.getElementById('countProductos').textContent = totalProductos;
    document.getElementById('totalProductos').textContent = totalProductos;
    document.getElementById('totalUnidades').textContent = totalUnidades;
    
    // Habilitar/deshabilitar botón
    const btnFinalizar = document.getElementById('btnFinalizar');
    btnFinalizar.disabled = totalProductos === 0;
}

/* ================= LIMPIAR ================= */
function limpiarTodo() {
    if (salida.length === 0) return;
    
    Swal.fire({
            title: '¿Limpiar todo?',
        text: 'Se eliminarán todos los productos escaneados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            salida = [];
            renderProductos();
            updateSummary();
            
            Swal.fire({
                icon: 'success',
                title: 'Lista limpiada',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
            
            scannerInput.focus();
        }
    });
}

/* ================= FINALIZAR ================= */
function finalizarSalida() {
    if (salida.length === 0) return;
    
            const totalUnidades = salida.reduce((sum, item) => sum + Math.trunc(Number(item.cantidad || 0)), 0);
    
    Swal.fire({
            title: '¿Finalizar salida?',
        html: `
            <p>Se registrará la salida de <strong>${salida.length}</strong> producto(s)</p>
            <p>Total de unidades: <strong>${totalUnidades}</strong></p>
                <p class="text-danger mb-0">Esta acción reducirá el inventario</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            registrarSalida();
        }
    });
}

function registrarSalida() {
    const formData = new FormData();
    formData.append('accion', 'altaOrdenSalida');
    formData.append('orden', JSON.stringify(salida));

    Swal.fire({
        title: 'Procesando...',
        text: 'Registrando orden de salida',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('api/apiOrdenes.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(() => {
        Swal.fire({
            icon: 'success',
            title: '¡Salida registrada!',
            text: 'La orden de salida ha sido registrada correctamente',
            confirmButtonColor: '#495057'
        }).then(() => {
            window.location.href = 'ordenes-salida.php';
        });
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al registrar la salida',
            confirmButtonColor: '#6c757d'
        });
        scannerInput.focus();
    });
}
</script>

</body>
</html>
