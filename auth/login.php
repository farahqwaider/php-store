<?php

require_once __DIR__ . '/../includes/bootstrap.php';
redirectLoggedInUser();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    // البحث عن المستخدم في قاعدة البيانات
    $stmt = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = 'The email or password is incorrect.';
    } else {
        // تخزين بيانات المستخدم في الجلسة
        unset($user['password']);
        $_SESSION['user'] = $user;

        // تحويل المستخدم حسب صلاحياته
        if (isAdmin()) {
            redirect(baseUrl('admin/dashboard.php'));
        } else {
            redirect(baseUrl('index.php'));
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-card card border-0 shadow-sm mx-auto">
    <div class="card-body p-4 p-md-5">
        <h1 class="h3 fw-bold">Welcome back</h1>
        <p class="text-secondary mb-4">Log in to continue shopping.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" id="email" name="email" class="form-control form-control-lg" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" id="password" name="password" class="form-control form-control-lg" required>
            </div>
            <button class="btn btn-primary btn-lg w-100">Log In</button>
        </form>
        <p class="text-center text-secondary mt-4 mb-0">Need an account? <a href="<?= e(baseUrl('auth/register.php')) ?>">Register</a></p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
