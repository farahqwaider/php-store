<?php

require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

// جلب عدد المنتجات
$result = $conn->query("SELECT COUNT(*) AS total FROM products");
$row = $result->fetch_assoc();
$productCount = $row['total'];

// جلب عدد التصنيفات
$result = $conn->query("SELECT COUNT(*) AS total FROM categories");
$row = $result->fetch_assoc();
$categoryCount = $row['total'];

// جلب عدد الطلبات
$result = $conn->query("SELECT COUNT(*) AS total FROM orders");
$row = $result->fetch_assoc();
$orderCount = $row['total'];

// جلب عدد المستخدمين
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
$row = $result->fetch_assoc();
$userCount = $row['total'];

// جلب آخر 5 طلبات
$recentOrders = $conn->query(
    "SELECT orders.id, orders.total_price, orders.status, orders.created_at, users.name
     FROM orders JOIN users ON users.id = orders.user_id ORDER BY orders.id DESC LIMIT 5"
);

$pageTitle = 'Dashboard';
$adminLayout = true;
require __DIR__ . '/../includes/header.php';
?>
<div class="mb-4">
    <p class="text-primary fw-semibold mb-1">ADMINISTRATION</p>
    <h1 class="h2 fw-bold">Dashboard</h1>
    <p class="text-secondary">Store activity at a glance.</p>
</div>

<div class="row g-4 mb-5">
    <!-- بطاقة المنتجات -->
    <div class="col-sm-6 col-xl-3">
        <a class="text-decoration-none" href="<?= e(baseUrl('admin/products/index.php')) ?>">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-1">Products</p>
                            <div class="display-6 fw-bold text-dark"><?= $productCount ?></div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-box"></i></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <!-- بطاقة التصنيفات -->
    <div class="col-sm-6 col-xl-3">
        <a class="text-decoration-none" href="<?= e(baseUrl('admin/categories/index.php')) ?>">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-1">Categories</p>
                            <div class="display-6 fw-bold text-dark"><?= $categoryCount ?></div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-tags"></i></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <!-- بطاقة الطلبات -->
    <div class="col-sm-6 col-xl-3">
        <a class="text-decoration-none" href="<?= e(baseUrl('admin/orders.php')) ?>">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-1">Orders</p>
                            <div class="display-6 fw-bold text-dark"><?= $orderCount ?></div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <!-- بطاقة المستخدمين -->
    <div class="col-sm-6 col-xl-3">
        <a class="text-decoration-none" href="<?= e(baseUrl('admin/users.php')) ?>">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-1">Users</p>
                            <div class="display-6 fw-bold text-dark"><?= $userCount ?></div>
                        </div>
                        <div class="stat-icon"><i class="bi bi-people"></i></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- جدول آخر الطلبات -->
<div class="card shadow-sm">
    <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
        <h2 class="h5 fw-bold mb-0">Recent orders</h2>
        <a href="orders.php" class="btn btn-sm btn-outline-primary">View all</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No orders yet.</td>
                        </tr>
                    <?php endif; ?>
                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-semibold">#<?= (int) $order['id'] ?></td>
                            <td><?= e($order['name']) ?></td>
                            <td>$<?= number_format((float) $order['total_price'], 2) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= e(ucfirst($order['status'])) ?></span>
                            </td>
                            <td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
