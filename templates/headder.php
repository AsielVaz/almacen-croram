<?php
require_once dirname(__DIR__) . '/auth.php';
$usuarioSesion = usuario_actual();
?>
<style>
    .app-topbar .logo { display: flex; align-items: center; height: 100%; padding: .5rem 0; }
    .app-topbar .logo-lg { display: flex; align-items: center; height: 100%; }
    .app-topbar .logo-lg img { max-height: 60px; height: auto; width: auto; max-width: 200px; object-fit: contain; }
    .app-topbar .logo-sm img { max-height: 40px; height: auto; width: auto; object-fit: contain; }
    @media (max-width: 768px) { .app-topbar .logo-lg img { max-height: 50px; max-width: 150px; } }
    @media (max-width: 576px) { .app-topbar .logo-lg img { max-height: 40px; max-width: 120px; } }
    .app-topbar { display: flex; align-items: center; min-height: 70px; }
    .app-topbar .page-container { width: 100%; display: flex; align-items: center; justify-content: space-between; }
    .notificaciones-menu { position: absolute; top: 100%; right: 0; left: auto; z-index: 1050; width: min(390px, calc(100vw - 24px)); padding: 0; overflow: hidden; }
    .notificaciones-lista { max-height: 390px; overflow-y: auto; }
    .notificacion-item { display: flex; gap: .75rem; align-items: flex-start; padding: .8rem 1rem; white-space: normal; border-bottom: 1px solid var(--bs-border-color); }
    .notificacion-item:last-child { border-bottom: 0; }
    .notificacion-no-leida { background: rgba(var(--bs-primary-rgb), .08); }
    .notificacion-icono { display: grid; place-items: center; flex: 0 0 34px; height: 34px; border-radius: 50%; background: var(--bs-secondary-bg); font-size: 18px; }
    .notificacion-fecha { margin-top: .25rem; color: var(--bs-secondary-color); font-size: .72rem; }
    #notificaciones-badge { position: absolute; top: 7px; right: 5px; min-width: 18px; padding: 2px 4px; font-size: 10px; }
    #centro-notificaciones > summary { list-style: none; cursor: pointer; }
    #centro-notificaciones > summary::-webkit-details-marker { display: none; }
    #centro-notificaciones[open] .notificaciones-menu { display: block; }
</style>
<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">
            <a href="index.php" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="assets/images/logo_almacen.png" alt="logo"></span>
                    <span class="logo-sm"><img src="assets/images/logo_almacen.png" alt="small logo"></span>
                </span>
                <span class="logo-dark">
                    <span class="logo-lg"><img src="assets/images/logo_almacen.png" alt="logo"></span>
                    <span class="logo-sm"><img src="assets/images/logo_almacen.png" alt="small logo"></span>
                </span>
            </a>

            <button class="sidenav-toggle-button px-2"><i class="ri-menu-2-line fs-24"></i></button>
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content"><i class="ri-menu-2-line fs-24"></i></button>

            <div class="topbar-item d-flex d-xl-none">
                <button class="topbar-link" data-bs-toggle="modal" data-bs-target="#searchModal" type="button"><i class="ri-search-line fs-22"></i></button>
            </div>

            <div class="topbar-search d-none d-xl-flex gap-2 me-2 align-items-center" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                <i class="ri-search-line fs-18"></i>
                <span class="me-2">Search something..</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <details class="topbar-item dropdown" id="centro-notificaciones">
                <summary class="topbar-link position-relative" aria-label="Notificaciones">
                    <i class="ri-notification-3-line fs-22"></i>
                    <span id="notificaciones-badge" class="badge bg-danger rounded-pill d-none">0</span>
                </summary>
                <div class="dropdown-menu dropdown-menu-end notificaciones-menu">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <strong>Notificaciones</strong>
                        <button id="notificaciones-marcar-todas" type="button" class="btn btn-sm btn-link text-decoration-none">Marcar leídas</button>
                    </div>
                    <div id="lista-notificaciones" class="notificaciones-lista">
                        <div class="px-3 py-4 text-center text-muted">Cargando...</div>
                    </div>
                    <div class="p-2 border-top">
                        <button id="notificaciones-push" type="button" class="btn btn-sm btn-outline-secondary w-100">Activar en este dispositivo</button>
                    </div>
                </div>
            </details>
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button"><i data-lucide="settings" class="fs-22"></i></button>
            </div>
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button"><i data-lucide="moon" class="light-mode-icon fs-22"></i><i data-lucide="sun" class="dark-mode-icon fs-22"></i></button>
            </div>
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" data-bs-offset="0,25" type="button" aria-haspopup="false" aria-expanded="false">
                        <img src="assets/images/users/avatar-3.jpg" width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                        <span class="d-lg-flex flex-column gap-1 d-none">
                            <span class="fw-semibold"><?= htmlspecialchars($usuarioSesion['nombre'] ?? 'Usuario') ?></span>
                            <small class="text-muted"><?= htmlspecialchars($usuarioSesion['usuario'] ?? '') ?></small>
                        </span>
                        <i class="ri-arrow-down-s-line d-none d-lg-block align-middle ms-2"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header noti-title"><h6 class="text-overflow m-0">Bienvenido</h6></div>
                        <?php if (usuario_tiene_permiso('usuarios_administrar')): ?>
                        <a href="usuarios.php" class="dropdown-item"><i class="ri-account-circle-line me-1 fs-16 align-middle"></i><span class="align-middle">Usuarios</span></a>
                        <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <a href="logout.php" class="dropdown-item fw-semibold text-danger"><i class="ri-logout-box-line me-1 fs-16 align-middle"></i><span class="align-middle">Salir</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<script src="assets/js/notificaciones.js?v=20261013-2" defer></script>
