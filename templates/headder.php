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
                        <a href="usuarios.php" class="dropdown-item"><i class="ri-account-circle-line me-1 fs-16 align-middle"></i><span class="align-middle">Usuarios</span></a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item fw-semibold text-danger"><i class="ri-logout-box-line me-1 fs-16 align-middle"></i><span class="align-middle">Salir</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
