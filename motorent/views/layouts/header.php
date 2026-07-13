<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloPagina) ? e($tituloPagina) . ' - MotoRent Costa Rica' : 'MotoRent Costa Rica' ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/">
            </i> MotoRent <span class="text-warning">CR</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/catalogo">Catálogo</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/galeria">Galería</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contacto">Contacto</a></li>
                <?php if (is_logueado()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/historial">Mis reservas</a></li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard">Administración</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_logueado()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($_SESSION['usuario_nombre']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil">Mi perfil</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/historial">Mis reservas</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout">Cerrar sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login">Iniciar sesión</a></li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm ms-2 mt-1" href="<?= BASE_URL ?>/registro">Registrarse</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= e($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
