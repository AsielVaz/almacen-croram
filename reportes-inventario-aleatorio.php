<?php
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Almacén Croram - Conciliación de Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Conciliación física de inventario" name="description" />
    <meta content="HoppingJet Studio." name="author" />

    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <script src="assets/js/config.js"></script>

    <!-- Datatables css -->
    <link href="assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .page-header-custom {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            border-left: 6px solid #495057;
        }

        .config-card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            margin-bottom: 2rem;
        }

        .config-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .config-input {
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            font-size: 1.125rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .config-input:focus {
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15);
        }

        .btn-modern {
            padding: 0.75rem 2rem;
            border-radius: 0.375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-load {
            background: #495057;
            color: white;
            border: 2px solid #343a40;
        }

        .btn-load:hover {
            background: #343a40;
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .btn-finish {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            border: 2px solid #1e7e34;
        }

        .btn-finish:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(40, 167, 69, 0.3);
        }

        .table-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            padding: 1.5rem;
        }

        .table-modern thead {
            background: #495057;
            color: white;
        }

        .table-modern thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fa;
        }

        .cantidad-input {
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.5rem;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .cantidad-input:focus {
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.15);
        }

        .diferencia-positiva {
            color: #28a745;
            font-weight: 700;
        }

        .diferencia-negativa {
            color: #dc3545;
            font-weight: 700;
        }

        .diferencia-cero {
            color: #6c757d;
            font-weight: 600;
        }

        .info-badge {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #495057;
        }

        .info-badge strong {
            color: #212529;
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
$articulos = json_decode($adminArticulos->listarArticulos(false), true);
$totalArticulos = count($articulos);

// Verificar si hay parámetro de artículos
$cantidadMostrar = isset($_GET['articulos']) ? intval($_GET['articulos']) : 0;

// Si hay cantidad, cargar artículos con cantidades
$articulosConciliacion = [];
if ($cantidadMostrar > 0) {
    // Mezclar aleatoriamente
    shuffle($articulos);
    $articulosSeleccionados = array_slice($articulos, 0, min($cantidadMostrar, $totalArticulos));
    
    foreach ($articulosSeleccionados as $articulo) {
    $articulo['cantidad_sistema'] = (int)round((float)($articulo['cantidad'] ?? 0));
        $articulosConciliacion[] = $articulo;
    }
}
?>

<div class="page-content">

<div class="page-header-custom">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fs-18 fw-bold mb-0">
                <i class="ri-file-list-check-line me-2"></i>
                Conciliación de Inventario
            </h4>
        </div>
        <div class="text-end">
            <ol class="breadcrumb m-0 py-0 fs-13">
                <li class="breadcrumb-item">Inventario</li>
                <li class="breadcrumb-item active">Conciliación</li>
            </ol>
        </div>
    </div>
</div>

<div class="page-container">

<?php if ($cantidadMostrar === 0): ?>
<!-- Sección de Configuración (cuando NO hay parámetro) -->
<div class="config-card">
    <h5 class="fw-bold mb-4">
        <i class="ri-settings-3-line me-2"></i>
        Configuración de Conciliación
    </h5>
    
    <form method="GET" action="">
        <div class="row align-items-end">
            <div class="col-md-6">
                <label class="config-label">
                    <i class="ri-box-3-line me-1"></i>
                    Cantidad de Artículos a Conciliar
                </label>
                <input 
                    type="number" 
                    name="articulos"
                    id="cantidadArticulos" 
                    class="form-control config-input" 
                    min="1" 
                    max="<?php echo $totalArticulos; ?>"
                    value="50"
                    placeholder="Ej: 50, 100, 200..."
                    required
                >
                <small class="text-muted mt-1 d-block">
                    Se seleccionarán artículos de forma aleatoria
                </small>
            </div>
            <div class="col-md-3">
                <div class="info-badge">
                    <strong>Total disponible:</strong><br>
                    <?php echo $totalArticulos; ?> artículos
                </div>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-modern btn-load w-100">
                    <i class="ri-refresh-line me-2"></i>
                    Cargar Conciliación
                </button>
            </div>
        </div>
    </form>
</div>

<?php else: ?>
<!-- Sección de Conciliación (cuando SÃ hay parámetro) -->
<div class="alert alert-info d-flex align-items-center mb-3">
    <i class="ri-information-line fs-20 me-2"></i>
    <div class="flex-grow-1">
        Se están mostrando <strong><?php echo count($articulosConciliacion); ?> artículos</strong> seleccionados aleatoriamente
    </div>
    <a href="?" class="btn btn-sm btn-secondary">
        <i class="ri-arrow-left-line me-1"></i>Nueva Conciliación
    </a>
</div>

<div class="table-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            <i class="ri-file-list-3-line me-2"></i>
            Productos para Conciliar
            <span class="badge bg-secondary ms-2"><?php echo count($articulosConciliacion); ?></span>
        </h5>
        <button class="btn btn-modern btn-finish" id="btnTerminar">
            <i class="ri-check-line me-2"></i>
            Terminar Conciliación
        </button>
    </div>

    <div class="table-responsive">
        <table id="tablaConciliacion" class="table table-modern table-striped dt-responsive nowrap w-100">
            <thead>
                <tr>
                    <th width="8%">ID</th>
                    <th width="35%">Artículo</th>
                    <th width="15%">SKU</th>
                    <th width="12%" class="text-center">Sistema</th>
                    <th width="15%" class="text-center">Real</th>
                    <th width="15%" class="text-center">Diferencia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articulosConciliacion as $articulo): ?>
                <tr data-id="<?php echo $articulo['id']; ?>">
                    <td class="fw-bold"><?php echo $articulo['id']; ?></td>
                    <td><?php echo htmlspecialchars($articulo['nombre']); ?></td>
                    <td>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($articulo['sku']); ?></span>
                    </td>
                    <td class="text-center cantidad-sistema fw-bold"><?php echo $articulo['cantidad_sistema']; ?></td>
                    <td class="text-center">
                        <input type="number"
                               class="form-control cantidad-input cantidad-real"
                               min="0"
                               value="<?php echo $articulo['cantidad_sistema']; ?>">
                    </td>
                    <td class="text-center diferencia diferencia-cero">0</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

</div>

<?php include 'templates/footer.php'; ?>
</div>
</div>

<?php include_once 'templates/theme.php'; ?>

<script src="assets/js/vendor.min.js"></script>
<script src="assets/js/app.js"></script>

<script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
<?php if ($cantidadMostrar > 0): ?>
/* =============================
   INIT - Solo cuando hay tabla
============================= */
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaConciliacion').DataTable({
        pageLength: 25,
        ordering: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });
    
    // Event listeners para cálculo de diferencias
    document.querySelectorAll('.cantidad-real').forEach(input => {
        input.addEventListener('input', calcularDiferencia);
    });
    
    // Botón terminar
    document.getElementById('btnTerminar').addEventListener('click', terminarConciliacion);
});

/* =============================
   CALCULAR DIFERENCIAS
============================= */
function calcularDiferencia(e) {
    const row = e.target.closest('tr');
    const sistema = parseFloat(row.querySelector('.cantidad-sistema').textContent);
    const real = parseFloat(e.target.value || 0);
    const diferencia = real - sistema;
    
    const diferenciaCell = row.querySelector('.diferencia');
    diferenciaCell.textContent = diferencia;
    
    // Aplicar estilos según diferencia
    diferenciaCell.classList.remove('diferencia-positiva', 'diferencia-negativa', 'diferencia-cero');
    
    if (diferencia > 0) {
        diferenciaCell.classList.add('diferencia-positiva');
        diferenciaCell.textContent = '+' + diferencia;
    } else if (diferencia < 0) {
        diferenciaCell.classList.add('diferencia-negativa');
    } else {
        diferenciaCell.classList.add('diferencia-cero');
    }
}

/* =============================
TERMINAR CONCILIACIÓN
============================= */
function terminarConciliacion() {
    // Recopilar datos
    const conciliacion = [];
    
    document.querySelectorAll('#tablaConciliacion tbody tr').forEach(row => {
        const nombreCell = row.querySelector('td:nth-child(2)');
        const skuBadge = row.querySelector('.badge');
        
        conciliacion.push({
            id_articulo: row.dataset.id,
            nombre_articulo: nombreCell.textContent.trim(),
            sku: skuBadge.textContent.trim(),
            sistema: parseFloat(row.querySelector('.cantidad-sistema').textContent),
            real: parseFloat(row.querySelector('.cantidad-real').value || 0),
            diferencia: parseFloat(row.querySelector('.diferencia').textContent)
        });
    });
    
    // Confirmación
    Swal.fire({
            title: '¿Terminar conciliación?',
        html: `
            <p>Se generará el reporte de conciliación con:</p>
            <p><strong>${conciliacion.length}</strong> artículos revisados</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, generar reporte',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarConciliacion(conciliacion);
        }
    });
}

/* =============================
   ENVIAR A API
============================= */
function enviarConciliacion(conciliacion) {
    Swal.fire({
        title: 'Generando reporte...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar a generaReporteInventario.php
    fetch('api/generaReporteInventario.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            fecha: new Date().toISOString().split('T')[0],
            conciliacion: conciliacion
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta');
        }
        return response.blob();
    })
    .then(blob => {
        Swal.close();
        
        // Abrir PDF en nueva ventana
        const url = window.URL.createObjectURL(blob);
        window.open(url, '_blank');
        
        Swal.fire({
            icon: 'success',
            title: '¡Reporte generado!',
            text: 'La conciliación se ha completado correctamente',
            confirmButtonColor: '#495057'
        }).then(() => {
            window.location.href = '?';
        });
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al generar el reporte',
            confirmButtonColor: '#6c757d'
        });
    });
}
<?php endif; ?>
</script>

</body>
</html>

