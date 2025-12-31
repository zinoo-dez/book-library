<?php
// admin/users.php
// Admin Panel: Manage Users (View, Edit Role, Delete)

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';

use App\User;
use App\Library;

// Protect route: Only admins can access
if (!User::isAdmin()) {
    $_SESSION['message'] = "Access denied. Admins only.";
    header('Location: ../login.php');
    exit;
}

$library = new Library();  // Not strictly needed here, but useful if you extend later

// Handle actions
$action = $_GET['action'] ?? '';
$userId = (int)($_GET['id'] ?? 0);

if ($action && $userId) {
    switch ($action) {
        case 'make_admin':
            $stmt = $library->getPdo()->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $stmt->execute([$userId]);
            $_SESSION['message'] = "User promoted to Admin.";
            break;
        case 'remove_admin':
            // Prevent removing the last admin (safety)
            $stmt = $library->getPdo()->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $stmt->execute();
            $adminCount = $stmt->fetchColumn();
            if ($adminCount > 1) {
                $stmt = $library->getPdo()->prepare("UPDATE users SET role = 'user' WHERE id = ? AND role = 'admin'");
                $stmt->execute([$userId]);
                $_SESSION['message'] = "Admin rights removed.";
            } else {
                $_SESSION['message'] = "Cannot remove the last admin.";
            }
            break;
        case 'delete':
            // Prevent deleting self or last admin
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['message'] = "You cannot delete yourself.";
            } else {
                $stmt = $library->getPdo()->prepare("SELECT role FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $role = $stmt->fetchColumn();
                if ($role === 'admin') {
                    $stmt = $library->getPdo()->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                    $stmt->execute();
                    $adminCount = $stmt->fetchColumn();
                    if ($adminCount > 1) {
                        $stmt = $library->getPdo()->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $_SESSION['message'] = "Admin user deleted.";
                    } else {
                        $_SESSION['message'] = "Cannot delete the last admin.";
                    }
                } else {
                    $stmt = $library->getPdo()->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $_SESSION['message'] = "User deleted.";
                }
            }
            break;
    }
    header('Location: users.php');
    exit;
}

// Fetch all users
$stmt = $library->getPdo()->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include '../views/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">👥 Manage Users</h1>

    <?php include '../views/message.php'; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if ($user['role'] === 'user'): ?>
                            <a href="users.php?action=make_admin&id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-success" 
                               onclick="return confirm('Promote to Admin?')">Make Admin</a>
                        <?php elseif ($user['role'] === 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                            <a href="users.php?action=remove_admin&id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-warning" 
                               onclick="return confirm('Remove Admin rights?')">Remove Admin</a>
                        <?php endif; ?>

                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="users.php?action=delete&id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-danger ms-1" 
                               onclick="return confirm('Delete this user permanently? All their data will be lost.')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="index.php" class="btn btn-secondary mt-3">← Back to Admin Dashboard</a>
</div>

<?php include '../views/footer.php'; ?>