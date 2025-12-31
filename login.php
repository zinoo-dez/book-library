<?php
// login.php
$pageTitle = "Login";
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
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $errors[] = "Please enter both username/email and password.";
    } elseif ($auth->attempt($login, $password)) {
        setFlashMessage("Welcome back, " . e(Auth::user()) . "!", 'success');
        redirect('index.php');
    } else {
        $errors[] = "Invalid username/email or password.";
    }
}

include 'views/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">🔐 Login to Your Library</h2>

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
                        <label class="form-label">Username or Email</label>
                        <input type="text" name="login" class="form-control" required autofocus
                               value="<?= isset($_POST['login']) ? e($_POST['login']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>