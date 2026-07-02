<?php

require_once __DIR__ . '/../../includes/bootstrap.php';
requireAdmin();

$errors = [];
$status = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // إضافة تصنيف جديد
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $errors[] = 'Category name is required.';
        } else {
            $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header("Location: index.php?status=created");
            } else {
                header("Location: index.php?status=duplicate");
            }
            exit;
        }
    }

    // تعديل تصنيف
    if ($action === 'update') {
        $id = (int) $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');

        if ($id > 0 && $name !== '') {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $id);
            $stmt->execute();
            header("Location: index.php?status=updated");
            exit;
        } else {
            header("Location: index.php?status=error");
            exit;
        }
    }

    // حذف تصنيف
    if ($action === 'delete') {
        $id = (int) $_POST['id'];

        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header("Location: index.php?status=deleted");
            } else {
                header("Location: index.php?status=error");
            }
            exit;
        }
    }
}

// جلب جميع التصنيفات مع عدد المنتجات لكل تصنيف
$categories = $conn->query(
    "SELECT categories.id, categories.name, categories.description, COUNT(products.id) AS product_count
     FROM categories
     LEFT JOIN products ON products.category_id = categories.id
     GROUP BY categories.id, categories.name, categories.description
     ORDER BY categories.name"
);

// رسائل الحالة
$alerts = [
    'created' => ['success', 'Category created successfully.'],
    'updated' => ['success', 'Category updated successfully.'],
    'deleted' => ['success', 'Category deleted. Its products are now uncategorized.'],
    'duplicate' => ['warning', 'A category with this name already exists.'],
    'error' => ['danger', 'Something went wrong. Please try again.'],
];
$alert = $alerts[$status] ?? null;

$pageTitle = 'Categories';
$adminLayout = true;
require __DIR__ . '/../../includes/header.php';
?>

<?php if ($alert): ?>
    <div class="alert alert-<?= $alert[0] ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= e($alert[1]) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="mb-4">
    <p class="text-primary fw-semibold mb-1">ORGANIZATION</p>
    <h1 class="h2 fw-bold mb-1">Categories</h1>
    <p class="text-secondary mb-0">Create and manage product categories.</p>
</div>

<div class="row g-4">
    <!-- نموذج إضافة تصنيف -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Add category</h2>

                <?php if ($errors): ?>
                    <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <label for="name" class="form-label fw-semibold">Category name</label>
                    <input type="text" id="name" name="name" maxlength="100"
                           class="form-control form-control-lg mb-3" required>
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea id="description" name="description" maxlength="500"
                              class="form-control mb-3" rows="3"></textarea>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-2"></i>Add Category
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- جدول التصنيفات -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Category</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold">
                                        <i class="bi bi-tag text-primary me-2"></i>
                                        <?= e($category['name']) ?>
                                    </td>
                                    <td class="text-secondary"><?= e($category['description'] ?: 'No description') ?></td>
                                    <td><?= (int) $category['product_count'] ?></td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-light border btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                data-category-id="<?= (int) $category['id'] ?>"
                                                data-category-name="<?= e($category['name']) ?>"
                                                data-category-description="<?= e($category['description'] ?? '') ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"
                                                data-category-id="<?= (int) $category['id'] ?>"
                                                data-category-name="<?= e($category['name']) ?>">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- نافذة تعديل التصنيف -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <div class="modal-body p-4">
                    <h2 class="h4 fw-bold mb-3">Edit category</h2>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editCategoryId">
                    <label class="form-label fw-semibold" for="editCategoryName">Name</label>
                    <input class="form-control mb-3" id="editCategoryName" name="name" maxlength="100" required>
                    <label class="form-label fw-semibold" for="editCategoryDescription">Description</label>
                    <textarea class="form-control mb-4" id="editCategoryDescription" name="description" maxlength="500" rows="3"></textarea>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary flex-fill">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نافذة حذف التصنيف -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="delete-icon mx-auto mb-3"><i class="bi bi-exclamation-triangle"></i></div>
                <h2 class="h4 fw-bold">Delete category?</h2>
                <p class="text-secondary">
                    Delete <strong id="deleteCategoryName"></strong>? Products in it will become uncategorized.
                </p>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteCategoryId">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger flex-fill">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('deleteCategoryModal').addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('deleteCategoryId').value = button.getAttribute('data-category-id');
        document.getElementById('deleteCategoryName').textContent = button.getAttribute('data-category-name');
    });
    document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('editCategoryId').value = button.getAttribute('data-category-id');
        document.getElementById('editCategoryName').value = button.getAttribute('data-category-name');
        document.getElementById('editCategoryDescription').value = button.getAttribute('data-category-description');
    });
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
