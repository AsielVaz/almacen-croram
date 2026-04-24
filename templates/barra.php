<?php
require_once dirname(__DIR__) . '/auth.php';
$usuarioSesion = usuario_actual();
?>
      <div class="sidenav-menu">
          <a href="index.php" class="logo">
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
                  <li class="side-nav-title">Navegacion</li>
                  <li class="side-nav-item">
                      <a href="index.php" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="layout-dashboard"></i></span>
                          <span class="menu-text">Inicio</span>
                      </a>
                  </li>

                  <li class="side-nav-title">Inventario</li>
                  <li class="side-nav-item">
                      <a href="articulos.php" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="package"></i></span>
                          <span class="menu-text">Articulos</span>
                      </a>
                  </li>

                  <li class="side-nav-title">Catalogos</li>
                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#sidebarFamilias" aria-expanded="false" aria-controls="sidebarFamilias" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="layers"></i></span>
                          <span class="menu-text">Familias</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="sidebarFamilias">
                          <ul class="sub-menu">
                              <li class="side-nav-item"><a href="catalogos-familias.php" class="side-nav-link"><span class="menu-text">Familias principales</span></a></li>
                              <li class="side-nav-item"><a href="catalogos-sub-familias.php" class="side-nav-link"><span class="menu-text">Sub familias</span></a></li>
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

                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#areasSide" aria-expanded="false" aria-controls="areasSide" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="building-2"></i></span>
                          <span class="menu-text">Areas</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="areasSide">
                          <ul class="sub-menu">
                              <li class="side-nav-item"><a href="areas-alta.php" class="side-nav-link"><span class="menu-text">Alta nueva area</span></a></li>
                              <li class="side-nav-item"><a href="areas-ver.php" class="side-nav-link"><span class="menu-text">Ver areas</span></a></li>
                          </ul>
                      </div>
                  </li>

                  <li class="side-nav-item">
                      <a data-bs-toggle="collapse" href="#sidebarOrdenes" aria-expanded="false" aria-controls="sidebarOrdenes" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="clipboard-list"></i></span>
                          <span class="menu-text">Ordenes</span>
                          <span class="menu-arrow"></span>
                      </a>
                      <div class="collapse" id="sidebarOrdenes">
                          <ul class="sub-menu">
                              <li class="side-nav-item"><a href="ordenes-salida.php" class="side-nav-link"><span class="menu-text">Ordenes salida</span></a></li>
                              <li class="side-nav-item"><a href="ordenes-entrada.php" class="side-nav-link"><span class="menu-text">Ordenes entrada</span></a></li>
                              <li class="side-nav-item"><a href="ordenes-salida-form-fast.php" class="side-nav-link"><span class="menu-text">Capturador</span></a></li>
                              <li class="side-nav-item"><a href="ordenes-salida-escaner.php" class="side-nav-link"><span class="menu-text">Capturador ordenes</span></a></li>
                          </ul>
                      </div>
                  </li>

                  <li class="side-nav-title">Reportes</li>
                  <li class="side-nav-item"><a href="reportes.php" class="side-nav-link"><span class="menu-icon"><i data-lucide="table-properties"></i></span><span class="menu-text">Reportes generales</span></a></li>
                  <li class="side-nav-item"><a href="reportes-bajo-stock.php?limite=5" class="side-nav-link"><span class="menu-icon"><i data-lucide="triangle-alert"></i></span><span class="menu-text">Items con bajo stock</span></a></li>
                  <li class="side-nav-item"><a href="reportes-bajo-stock.php?limite=0" class="side-nav-link"><span class="menu-icon"><i data-lucide="badge-alert"></i></span><span class="menu-text">Items sin stock</span></a></li>
                  <li class="side-nav-item"><a href="reportes-inventario-aleatorio.php" class="side-nav-link"><span class="menu-icon"><i data-lucide="file-bar-chart"></i></span><span class="menu-text">Rep. Articulos</span></a></li>

                  <li class="side-nav-title">Administracion</li>
                  <li class="side-nav-item">
                      <a href="usuarios.php" class="side-nav-link">
                          <span class="menu-icon"><i data-lucide="users"></i></span>
                          <span class="menu-text">Administrar usuarios</span>
                      </a>
                  </li>
                  <li class="side-nav-item">
                      <a href="wiki/index.html" class="side-nav-link" target="_blank" rel="noopener noreferrer">
                          <span class="menu-icon"><i data-lucide="book-open"></i></span>
                          <span class="menu-text">Wiki de uso</span>
                      </a>
                  </li>
                  <li class="side-nav-item">
                      <a href="logout.php" class="side-nav-link text-danger">
                          <span class="menu-icon"><i data-lucide="log-out"></i></span>
                          <span class="menu-text">Cerrar sesion</span>
                      </a>
                  </li>
              </ul>

              <?php if (!empty($usuarioSesion)): ?>
                  <div class="p-3 border-top mt-3">
                      <div class="small text-muted">Sesion activa</div>
                      <div class="fw-semibold"><?= htmlspecialchars($usuarioSesion['nombre'] ?: $usuarioSesion['usuario']) ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($usuarioSesion['rol'] ?: 'Sin rol') ?></div>
                  </div>
              <?php endif; ?>

              <div class="clearfix"></div>
          </div>
      </div>
