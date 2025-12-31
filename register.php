<?php
// register.php
$pageTitle = "Register";
require_once 'includes/session.php';
require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

use App\Auth;

$config = include 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
    $config['username'],
    $config['password'],
    $config['options']
);

$auth = new Auth($pdo);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } elseif ($auth->register($username, $email, $password)) {
        setFlashMessage("Registration successful! Please log in.", 'success');
        redirect('login.php');
    } else {
        $errors[] = "Username or email already taken.";
    }
}

include 'views/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">📝 Create Your Account</h2>

                <?php include 'views/message.php'; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required
                               value="<?= isset($_POST['username']) ? e($_POST['username']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Register</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p>Already have an account? <a href="login.php">Log in here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>