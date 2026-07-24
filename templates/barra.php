<?php
require_once dirname(__DIR__) . '/auth.php';
$usuarioSesion = usuario_actual();
$rutaInicio = ruta_inicio_usuario();
$puedePanel = usuario_tiene_permiso('panel_ver');
$puedeVerArticulos = usuario_tiene_permiso('articulos_ver');
$puedeCatalogos = usuario_tiene_permiso('catalogos_operativos');
$puedeAreas = usuario_tiene_permiso('areas_administrar');
$puedeVerCompras = usuario_tiene_permiso('ordenes_compra_ver');
$puedeCrearSalida = usuario_tiene_permiso('ordenes_salida_crear');
$puedeVerSalidas = usuario_tiene_permiso('ordenes_salida_ver');
$puedeEscanear = usuario_tiene_permiso('ordenes_validar_qr');
$puedeReportes = usuario_tiene_permiso('reportes_ver');
$puedeAutorizarCompras = usuario_tiene_permiso('ordenes_compra_autorizar');
$puedeUsuarios = usuario_tiene_permiso('usuarios_administrar');
?>
      <div class="sidenav-menu">
          <a href="<?= htmlspecialchars($rutaInicio) ?>" class="logo">
              <span class="logo-light">
                  <span class="logo-lg"><img src="assets/images/logo_almacen.png" alt="logo"></span>
                  <span class="logo-sm"><img src="assets/images/logo_almacen.png" alt="small logo"></span>
              </span>
              <span class="logo-dark">
                  <span class="logo-lg"><img src="assets/images/logo_almacen.png" alt="dark logo"></span>
                  <span class="logo-sm"><img src="assets/images/logo_almacen.png" alt="small logo"></span>
              </span>
          </a>

          <button class="button-close-fullsidebar">
              <i class="ri-close-line align-middle"></i>
          </button>

          <div data-simplebar>
              <ul class="side-nav">
                  <li class="side-nav-title">Navegaci&oacute;n</li>
                  <?php if ($puedePanel): ?>
                  <li class="side-nav-item">
                      <a href="index.php" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="layout-dashboard"></i></span>
                          <span class="menu-text">Inicio</span>
                      </a>
                  </li>
                  <?php endif; ?>

                  <?php if ($puedeVerArticulos): ?>
                  <li class="side-nav-title">Inventario</li>
                  <li class="side-nav-item">
                      <a href="articulos.php" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="package"></i></span>
                          <span class="menu-text">Art&iacute;culos</span>
                      </a>
                  </li>
                  <?php endif; ?>

                  <?php if ($puedeCatalogos || $puedeAreas): ?>
                  <li class="side-nav-title">Cat&aacute;logos</li>
                  <?php endif; ?>
                  <?php if ($puedeCatalogos): ?>
                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#sidebarFamilias" aria-expanded="false" aria-controls="sidebarFamilias" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="layers"></i></span>
                          <span class="menu-text">Familias</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="sidebarFamilias">
                          <ul class="sub-menu">
                              <li class="side-nav-item"><a href="catalogos-familias.php" class="side-nav-link"><span class="menu-text">Familias principales</span></a></li>
                          </ul>
                      </div>
                  </li>

                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#sidebarProveedores" aria-expanded="false" aria-controls="sidebarProveedores" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="truck"></i></span>
                          <span class="menu-text">Proveedores</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="sidebarProveedores">
                          <ul class="sub-menu">
                              <li class="side-nav-item"><a href="proveedores-alta.php" class="side-nav-link"><span class="menu-text">Alta nuevo proveedor</span></a></li>
                              <li class="side-nav-item"><a href="proveedores-ver.php" class="side-nav-link"><span class="menu-text">Ver proveedores</span></a></li>
                          </ul>
                      </div>
                  </li>
                  <?php endif; ?>

                  <?php if ($puedeAreas): ?>
                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#areasSide" aria-expanded="false" aria-controls="areasSide" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="building-2"></i></span>
                          <span class="menu-text">&Aacute;reas</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="areasSide">
                          <ul class="sub-menu">
                              <li class="side-nav-item"><a href="areas-alta.php" class="side-nav-link"><span class="menu-text">Alta nueva &aacute;rea</span></a></li>
                              <li class="side-nav-item"><a href="areas-ver.php" class="side-nav-link"><span class="menu-text">Ver &aacute;reas</span></a></li>
                          </ul>
                      </div>
                  </li>
                  <?php endif; ?>

                  <?php if ($puedeVerSalidas || $puedeVerCompras || $puedeCrearSalida || $puedeEscanear): ?>
                  <li class="side-nav-title">&Oacute;rdenes</li>
                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#sidebarOrdenes" aria-expanded="false" aria-controls="sidebarOrdenes" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="clipboard-list"></i></span>
                          <span class="menu-text">&Oacute;rdenes</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="sidebarOrdenes">
                          <ul class="sub-menu">
                              <?php if ($puedeVerSalidas): ?>
                              <li class="side-nav-item"><a href="ordenes-salida.php" class="side-nav-link"><span class="menu-text">&Oacute;rdenes de salida</span></a></li>
                              <?php endif; ?>
                              <?php if ($puedeVerCompras): ?>
                              <li class="side-nav-item"><a href="ordenes-entrada.php" class="side-nav-link"><span class="menu-text">&Oacute;rdenes de entrada</span></a></li>
                              <?php endif; ?>
                              <?php if ($puedeCrearSalida): ?>
                              <li class="side-nav-item"><a href="ordenes-salida-form-fast.php" class="side-nav-link"><span class="menu-text">Capturador</span></a></li>
                              <?php endif; ?>
                              <?php if ($puedeEscanear): ?>
                              <li class="side-nav-item"><a href="ordenes-salida-escaner.php" class="side-nav-link"><span class="menu-text">Capturador de ordenes</span></a></li>
                              <?php endif; ?>
                          </ul>
                      </div>
                  </li>
                  <?php endif; ?>

                  <?php if ($puedeReportes || $puedeAutorizarCompras): ?>
                  <li class="side-nav-title">Reportes</li>
                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#sidebarReportes" aria-expanded="false" aria-controls="sidebarReportes" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="table-properties"></i></span>
                          <span class="menu-text">Reportes</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="sidebarReportes">
                          <ul class="sub-menu">
                              <?php if ($puedeReportes): ?>
                              <li class="side-nav-item"><a href="reportes.php?seccion=inventario" class="side-nav-link"><span class="menu-text">Inventario</span></a></li>
                              <li class="side-nav-item"><a href="reportes.php?seccion=obsoletos" class="side-nav-link"><span class="menu-text">Obsoletos</span></a></li>
                              <li class="side-nav-item"><a href="reportes.php?seccion=proveedores" class="side-nav-link"><span class="menu-text">Proveedor</span></a></li>
                              <li class="side-nav-item"><a href="reportes.php?seccion=entradas_salidas" class="side-nav-link"><span class="menu-text">Entradas y salidas</span></a></li>
                              <li class="side-nav-item"><a href="reportes.php?seccion=log_inventario" class="side-nav-link"><span class="menu-text">Log de Inventario</span></a></li>
                              <li class="side-nav-item"><a href="reportes-compras-sugeridas.php" class="side-nav-link"><span class="menu-text">Compras sugeridas</span></a></li>
                              <li class="side-nav-item"><a href="reportes-inventario-aleatorio.php" class="side-nav-link"><span class="menu-text">Rep. art&iacute;culos</span></a></li>
                              <?php endif; ?>
                              <?php if ($puedeAutorizarCompras): ?>
                              <li class="side-nav-item"><a href="reportes-ordenes-entrada-sin-autorizar.php" class="side-nav-link"><span class="menu-text">Entradas sin autorizar</span></a></li>
                              <?php endif; ?>
                          </ul>
                      </div>
                  </li>
                  <?php endif; ?>

                  <?php if ($puedeUsuarios): ?>
                  <li class="side-nav-title">Administraci&oacute;n</li>
                  <li class="side-nav-item">
                      <a href="usuarios.php" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="users"></i></span>
                          <span class="menu-text">Administrar usuarios</span>
                      </a>
                  </li>
                  <?php endif; ?>
                  <li class="side-nav-item">
                      <a href="wiki/index.html" class="side-nav-link" target="_blank" rel="noopener noreferrer">
                          <span class="menu-icon"><i data-lucide="book-open"></i></span>
                          <span class="menu-text">Wiki de uso</span>
                      </a>
                  </li>
                  <li class="side-nav-item">
                      <a href="logout.php" class="side-nav-link text-danger">
                          <span class="menu-icon"><i data-lucide="log-out"></i></span>
                          <span class="menu-text">Cerrar sesi&oacute;n</span>
                      </a>
                  </li>
              </ul>

              <?php if (!empty($usuarioSesion)): ?>
                  <div class="p-3 border-top mt-3">
                      <div class="small text-muted">Sesi&oacute;n activa</div>
                      <div class="fw-semibold"><?= htmlspecialchars($usuarioSesion['nombre'] ?: $usuarioSesion['usuario']) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($usuarioSesion['rol'] ?: 'Sin rol') ?></div>
                  </div>
              <?php endif; ?>

              <div class="clearfix"></div>
          </div>
      </div>
