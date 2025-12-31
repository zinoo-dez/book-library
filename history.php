<?php
// history.php
$pageTitle = "My Borrowing History";
require_once 'includes/session.php';
require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

use App\Auth;
use App\Library;

Auth::guard();  // Must be logged in

$library = new Library();
$history = $library->getUserHistory(Auth::id());
$reservations = $library->getUserReservations(Auth::id());

// Handle reservation cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_reservation') {
    $resId = (int)$_POST['reservation_id'];
    if ($library->cancelReservation($resId, Auth::id())) {
        setFlashMessage('Reservation cancelled.', 'info');
    } else {
        setFlashMessage('Failed to cancel reservation.', 'danger');
    }
    redirect('history.php');
}

include 'views/header.php';
?>

<div class="row mt-4">
    <div class="col">
        <h1 class="mb-4">📖 My Borrowing History</h1>

        <?php if (empty($history)): ?>
            <div class="alert alert-info text-center py-5">
                <h4>No borrowing history yet</h4>
                <p>Start exploring books on the <a href="index.php">home page</a>!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrowed On</th>
                            <th>Due Date</th>
                            <th>Returned On</th>
                            <th>Status</th>
                            <th>Penalty (Fine)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $record): ?>
                            <?php
                            $isOverdue = !$record['returned_at'] && isOverdue($record['due_date']);
                            $daysOverdue = !$record['returned_at'] ? overdueDays($record['due_date']) : 0;
                            ?>
                            <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                <td><strong><?= e($record['title']) ?></strong></td>
                                <td><?= e($record['author']) ?></td>
                                <td><?= formatDate($record['borrowed_at']) ?></td>
                                <td><?= formatDate($record['due_date']) ?></td>
                                <td><?= formatDate($record['returned_at']) ?></td>
                                <td>
                                    <?php if ($record['returned_at']): ?>
                                        <span class="badge bg-success">Returned</span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge bg-danger">Overdue (<?= $daysOverdue ?> days)</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Borrowed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($record['fine_amount'] > 0): ?>
                                        <span class="text-danger fw-bold">$<?= number_format($record['fine_amount'], 2) ?></span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="text-danger">Est. $<?= number_format($daysOverdue * 0.50, 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$record['returned_at']): ?>
                                        <form method="POST" action="book-details.php" class="d-inline">
                                            <input type="hidden" name="id" value="<?= e($record['book_id']) ?>">
                                            <input type="hidden" name="action" value="return">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Return Now</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="book-details.php?id=<?= e($record['book_id']) ?>" class="btn btn-sm btn-outline-primary">View Book</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <hr class="my-5">

        <h1 class="mb-4">⏳ My Waiting List (Reservations)</h1>

        <?php if (empty($reservations)): ?>
            <div class="alert alert-light border text-center py-4">
                <p class="mb-0 text-muted">You have no active reservations.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-info">
                        <tr>
                            <th>Book Title</th>
                            <th>Reserved On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><strong><?= e($res['title']) ?></strong></td>
                                <td><?= formatDate($res['reserved_at']) ?></td>
                                <td>
                                    <?php if ($res['status'] === 'available'): ?>
                                        <span class="badge bg-success">Ready for Borrowing!</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-capitalize"><?= $res['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="cancel_reservation">
                                        <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this reservation?')">Cancel</button>
                                    </form>
                                    <a href="book-details.php?id=<?= e($res['book_id']) ?>" class="btn btn-sm btn-primary ms-1">View Book</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/footer.php'; ?>