<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloPagina) ? e($tituloPagina) . ' - Admin MotoRent' : 'Admin MotoRent' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Sidebar -->
    <nav class="admin-sidebar bg-dark text-white p-3 vh-100 position-sticky top-0">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="d-flex align-items-center mb-4 text-white text-decoration-none">
             <span class="fs-5 fw-bold">MotoRent Admin</span>
        </a>
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/motos"><i class="bi bi-scooter"></i> Motocicletas</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/reservas"><i class="bi bi-calendar-check"></i> Reservas</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/usuarios"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/mensajes"><i class="bi bi-envelope"></i> Mensajes</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/publicaciones"><i class="bi bi-images"></i> Publicaciones</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-white" href="<?= BASE_URL ?>/"><i class="bi bi-box-arrow-left"></i> Volver al sitio</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/logout"><i class="bi bi-power"></i> Cerrar sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido -->
    <main class="flex-grow-1 p-4">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= e($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= e($_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
