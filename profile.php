<?php
// profile.php
// User Profile Page - View and edit personal information

$pageTitle = "My Profile";
require_once 'includes/session.php';
require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

use App\Auth;
use App\Library;

Auth::guard();  // Must be logged in to access profile

$userId = Auth::id();
$username = Auth::user();
$email = $_SESSION['email'] ?? '';  // Assuming email is stored in session on login

$library = new Library();
$history = $library->getUserHistory($userId);

// Handle profile update
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($newUsername) || empty($newEmail)) {
        $errors[] = "Username and email are required.";
    }

    // Check if username/email changed and is unique
    if ($newUsername !== $username || $newEmail !== $email) {
        $stmt = $library->pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$newUsername, $newEmail, $userId]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email is already taken.";
        }
    }

    // Password change (optional)
    if (!empty($newPassword) || !empty($currentPassword)) {
        if (empty($currentPassword)) {
            $errors[] = "Current password required to change password.";
        } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match.";
        } else {
            // Verify current password
            $stmt = $library->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $hash = $stmt->fetchColumn();
            if (!password_verify($currentPassword, $hash)) {
                $errors[] = "Current password is incorrect.";
            }
        }
    }

    if (empty($errors)) {
        // Update basic info
        $stmt = $library->pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newEmail, $userId]);

        // Update password if provided
        if (!empty($newPassword)) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $library->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $userId]);
        }

        // Update session
        $_SESSION['username'] = $newUsername;
        $_SESSION['email'] = $newEmail;

        $success = true;
        $username = $newUsername;
        $email = $newEmail;
        setFlashMessage("Profile updated successfully!", 'success');
    } else {
        setFlashMessage("Please fix the errors below.", 'danger');
    }
}

include 'views/header.php';
?>

<div class="row mt-4">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">👤 My Profile</h4>
            </div>
            <div class="card-body">
                <?php displayFlashMessage(); ?>

                <div class="row">
                    <!-- Profile Form -->
                    <div class="col-md-6">
                        <h5>Account Information</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required
                                       value="<?= e($username) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?= e($email) ?>">
                            </div>

                            <hr>

                            <h6>Change Password (optional)</h6>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    Save Changes
                                </button>
                            </div>
                        </form>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= e($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Stats & History Summary -->
                    <div class="col-md-6">
                        <h5>Borrowing Stats</h5>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Total Books Borrowed</span>
                                <strong><?= count($history) ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Currently Borrowed</span>
                                <strong><?= count(array_filter($history, fn($h) => $h['returned_at'] === null)) ?></strong>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Returned On Time</span>
                                <strong><?= count(array_filter($history, fn($h) => $h['returned_at'] && !isOverdue($h['due_date']))) ?></strong>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="history.php" class="btn btn-outline-primary w-100">
                                View Full Borrowing History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>