<?php

require_once __DIR__ . '/../../includes/bootstrap.php';
requireAdmin();

$categoryId = 0;
if (isset($_GET['category']) && $_GET['category'] != '') {
    $categoryId = (int) $_GET['category'];
}

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

// جلب المنتجات مع إمكانية الفلترة حسب التصنيف
if ($categoryId > 0) {
    $stmt = $conn->prepare(
        "SELECT products.*, categories.name AS category_name
         FROM products
         LEFT JOIN categories ON categories.id = products.category_id
         WHERE products.category_id = ?
         ORDER BY products.id DESC"
    );
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT products.*, categories.name AS category_name
         FROM products
         LEFT JOIN categories ON categories.id = products.category_id
         ORDER BY products.id DESC"
    );
}

$status = $_GET['status'] ?? '';

// رسائل الحالة
$alerts = [
    'created' => ['success', 'Product created successfully.'],
    'updated' => ['success', 'Product updated successfully.'],
    'deleted' => ['success', 'Product deleted successfully.'],
    'not-found' => ['warning', 'The requested product was not found.'],
    'error' => ['danger', 'Something went wrong. Please try again.'],
];
$alert = $alerts[$status] ?? null;

$pageTitle = 'Products';
$adminLayout = true;
require __DIR__ . '/../../includes/header.php';
?>

<?php if ($alert): ?>
    <div class="alert alert-<?= $alert[0] ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= e($alert[1]) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <p class="text-primary fw-semibold mb-1">INVENTORY</p>
        <h1 class="h2 fw-bold mb-1">Products</h1>
        <p class="text-secondary mb-0">Manage your product catalog and stock.</p>
    </div>
    <a href="create.php" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg me-2"></i>Add Product
    </a>
</div>

<!-- فلتر حسب التصنيف -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5 col-lg-4">
                <label for="category" class="form-label fw-semibold">Filter by category</label>
                <select id="category" name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option value="<?= (int) $category['id'] ?>"
                            <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php if ($categoryId > 0): ?>
                <div class="col-auto">
                    <a href="index.php" class="btn btn-light border">
                        <i class="bi bi-x-lg me-1"></i>Clear filter
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- جدول المنتجات -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Product</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($row['image'])): ?>
                                            <img class="product-thumbnail"
                                                 src="<?= e(baseUrl('uploads/products/' . rawurlencode($row['image']))) ?>"
                                                 alt="<?= e($row['name']) ?>">
                                        <?php else: ?>
                                            <div class="product-icon">
                                                <i class="bi bi-box"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semibold"><?= e($row['name']) ?></div>
                                            <small class="text-secondary">#<?= (int) $row['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge category-badge rounded-pill">
                                        <?= e($row['category_name'] ?: 'Uncategorized') ?>
                                    </span>
                                </td>
                                <td class="description-cell">
                                    <?= e($row['description'] ?: 'No description') ?>
                                </td>
                                <td class="fw-semibold">$<?= number_format((float) $row['price'], 2) ?></td>
                                <td>
                                    <?php $quantity = (int) $row['quantity']; ?>
                                    <span class="badge rounded-pill <?= $quantity > 0 ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                        <?= $quantity ?> in stock
                                    </span>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a href="edit.php?id=<?= (int) $row['id'] ?>"
                                       class="btn btn-light btn-sm border">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm ms-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-product-id="<?= (int) $row['id'] ?>"
                                            data-product-name="<?= e($row['name']) ?>">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state text-center p-5">
                <div class="empty-icon mx-auto mb-3"><i class="bi bi-inboxes"></i></div>
                <h2 class="h5 fw-bold">No products yet</h2>
                <p class="text-secondary">Add your first product to start managing inventory.</p>
                <a href="create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Add Product
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- نافذة تأكيد الحذف -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="delete-icon mx-auto mb-3"><i class="bi bi-exclamation-triangle"></i></div>
                <h2 class="h4 fw-bold mb-2">Delete product?</h2>
                <p class="text-secondary mb-4">
                    Are you sure you want to delete <strong id="deleteProductName"></strong>?
                    This action cannot be undone.
                </p>
                <form method="POST" action="delete.php">
                    <input type="hidden" name="id" id="deleteProductId">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger flex-fill">
                            <i class="bi bi-trash3 me-2"></i>Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('deleteProductId').value = button.getAttribute('data-product-id');
        document.getElementById('deleteProductName').textContent = button.getAttribute('data-product-name');
    });
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
