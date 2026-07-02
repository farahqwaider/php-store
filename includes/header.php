<?php

$pageTitle = $pageTitle ?? 'My Store';
$adminLayout = $adminLayout ?? false;
$flashMessage = pullFlash();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$adminLinks = [
    ['admin/dashboard.php', 'speedometer2', 'Dashboard'],
    ['admin/products/index.php', 'box-seam', 'Products'],
    ['admin/categories/index.php', 'tags', 'Categories'],
    ['admin/orders.php', 'receipt', 'Orders'],
    ['admin/users.php', 'people', 'Users'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | My Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(baseUrl('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<?php if ($adminLayout): ?>
<div class="container-fluid">
    <div class="row">
        <!-- Desktop Sidebar -->
        <nav class="col-lg-3 col-xl-2 bg-dark text-white p-3 d-none d-lg-flex flex-column" style="min-height: 100vh;">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-5 px-2 py-3 mb-3 border-bottom border-secondary text-white text-decoration-none"
               href="<?= e(baseUrl('admin/dashboard.php')) ?>">
                <i class="bi bi-bag-heart fs-4 text-danger"></i>
                <span>Store Admin</span>
            </a>
            <ul class="nav nav-pills flex-column gap-2 mb-auto" aria-label="Admin navigation">
                <?php foreach ($adminLinks as [$path, $icon, $label]): ?>
                    <?php
                    $isActive = str_ends_with($currentPath, '/' . $path)
                        || ($path === 'admin/products/index.php' && str_contains($currentPath, '/admin/products/'));
                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive ? 'active' : 'text-white' ?>"
                           href="<?= e(baseUrl($path)) ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-<?= e($icon) ?> me-2"></i>
                            <?= e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="pt-3 border-top border-secondary mt-auto">
                <a class="btn btn-outline-light btn-sm w-100 mb-2" href="<?= e(baseUrl('index.php')) ?>">
                    <i class="bi bi-shop me-1"></i> View Store
                </a>
                <a class="btn btn-outline-danger btn-sm w-100" href="<?= e(baseUrl('auth/logout.php')) ?>">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
                <div class="d-flex align-items-center gap-2 mt-3 p-2 rounded bg-secondary bg-opacity-25">
                    <div class="rounded-circle bg-light text-dark fw-bold d-grid place-items-center" style="width: 32px; height: 32px; text-align: center; line-height: 32px;">
                        <?= e(mb_strtoupper(mb_substr(currentUser()['name'], 0, 1))) ?>
                    </div>
                    <div class="text-truncate">
                        <small class="d-block fw-bold"><?= e(currentUser()['name']) ?></small>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Area Column -->
        <div class="col-lg-9 col-xl-10 p-0 d-flex flex-column" style="min-height: 100vh;">
            <!-- Mobile Navigation -->
            <header class="navbar navbar-dark bg-dark d-lg-none px-3 py-2">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <span class="navbar-brand mb-0 h1 fs-6"><?= e($pageTitle) ?></span>
                <a href="<?= e(baseUrl('index.php')) ?>" class="btn btn-outline-light btn-sm" aria-label="View store">
                    <i class="bi bi-shop"></i>
                </a>
            </header>

            <!-- Mobile Sidebar Offcanvas -->
            <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
                <div class="offcanvas-header border-bottom border-secondary">
                    <h5 class="offcanvas-title fw-bold" id="adminSidebarLabel">
                        <i class="bi bi-bag-heart me-2 text-danger"></i>Store Admin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column">
                    <ul class="nav nav-pills flex-column gap-2 mb-auto">
                        <?php foreach ($adminLinks as [$path, $icon, $label]): ?>
                            <?php
                            $isActive = str_ends_with($currentPath, '/' . $path)
                                || ($path === 'admin/products/index.php' && str_contains($currentPath, '/admin/products/'));
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $isActive ? 'active' : 'text-white' ?>"
                                   href="<?= e(baseUrl($path)) ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
                                    <i class="bi bi-<?= e($icon) ?> me-2"></i>
                                    <?= e($label) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="pt-3 border-top border-secondary mt-auto">
                        <a class="btn btn-outline-light btn-sm w-100 mb-2" href="<?= e(baseUrl('index.php')) ?>">
                            <i class="bi bi-shop me-1"></i> View Store
                        </a>
                        <a class="btn btn-outline-danger btn-sm w-100" href="<?= e(baseUrl('auth/logout.php')) ?>">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="container-fluid py-4 flex-grow-1">
<?php else: ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= e(baseUrl('index.php')) ?>">
            <i class="bi bi-bag-heart me-2 text-danger"></i>My Store
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <a class="nav-link" href="<?= e(baseUrl('index.php')) ?>">Home</a>
                <a class="nav-link" href="<?= e(baseUrl('shop/products.php')) ?>">Products</a>
                <?php if (isLoggedIn()): ?>
                    <a class="nav-link" href="<?= e(baseUrl('shop/orders.php')) ?>">My Orders</a>
                    <a class="nav-link" href="<?= e(baseUrl('shop/cart.php')) ?>">
                        Cart <span class="badge text-bg-light text-dark ms-1"><?= cartCount($conn) ?></span>
                    </a>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a class="nav-link" href="<?= e(baseUrl('admin/dashboard.php')) ?>">Admin</a>
                    <?php endif; ?>
                    <span class="navbar-text px-lg-2"><?= e(currentUser()['name']) ?></span>
                    <a class="btn btn-outline-light btn-sm" href="<?= e(baseUrl('auth/logout.php')) ?>">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="<?= e(baseUrl('auth/login.php')) ?>">Login</a>
                    <a class="btn btn-light btn-sm text-dark" href="<?= e(baseUrl('auth/register.php')) ?>">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main class="container py-5">
<?php endif; ?>
    <?php if ($flashMessage): ?>
        <div class="alert alert-<?= e($flashMessage['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= e($flashMessage['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
