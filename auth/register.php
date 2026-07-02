<?php

require_once __DIR__ . '/../includes/bootstrap.php';
redirectLoggedInUser();

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $passwordConfirmation = $_POST['password_confirmation'];

    // التحقق من البيانات
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $passwordConfirmation) {
        $errors[] = 'Password confirmation does not match.';
    }

    // التحقق من أن الإيميل غير مسجل مسبقاً
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    // إنشاء الحساب
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        $stmt->execute();

        // تسجيل دخول المستخدم بعد التسجيل
        $_SESSION['user'] = [
            'id' => $conn->insert_id,
            'name' => $name,
            'email' => $email,
            'role' => 'user',
        ];
        flash('success', 'Your account has been created.');
        redirect(baseUrl('index.php'));
    }
}

$pageTitle = 'Register';
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-card card border-0 shadow-sm mx-auto">
    <div class="card-body p-4 p-md-5">
        <h1 class="h3 fw-bold">Create an account</h1>
        <p class="text-secondary mb-4">Register to save your cart and place orders.</p>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Name</label>
                <input type="text" id="name" name="name" class="form-control form-control-lg" maxlength="100" value="<?= e($name) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" id="email" name="email" class="form-control form-control-lg" maxlength="190" value="<?= e($email) ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" id="password" name="password" class="form-control form-control-lg" minlength="8" required>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg" minlength="8" required>
            </div>
            <button class="btn btn-primary btn-lg w-100">Create Account</button>
        </form>
        <p class="text-center text-secondary mt-4 mb-0">Already registered? <a href="<?= e(baseUrl('auth/login.php')) ?>">Log in</a></p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
