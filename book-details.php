<?php
// book-details.php
$pageTitle = "Book Details";
require_once 'includes/session.php';
require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

use App\Auth;
use App\Library;

$bookId = $_POST['id'] ?? $_GET['id'] ?? '';

if (empty($bookId)) {
    setFlashMessage('Invalid book ID.', 'danger');
    redirect('index.php');
}

$library = new Library();
$book = $library->getBookById($bookId);

if (!$book) {
    setFlashMessage('Book not found.', 'danger');
    redirect('index.php');
}

$reviews = $library->getReviews($bookId);

// Check if current user has already reviewed
$existingReview = null;
if (Auth::check()) {
    foreach ($reviews as $rev) {
        if ($rev['user_id'] == Auth::id()) {
            $existingReview = $rev;
            break;
        }
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!Auth::check()) {
        setFlashMessage('Please log in to review.', 'warning');
        redirect("login.php?redirect=book-details.php?id=$bookId");
    }

    $action = $_POST['action'];
    if ($action === 'add_review' || $action === 'update_review') {
        $rating = (int)($_POST['rating'] ?? 0);
        $text = trim($_POST['review_text'] ?? '');

        if ($rating < 1 || $rating > 5) {
            setFlashMessage('Invalid rating.', 'danger');
        } else {
            $library->addReview(Auth::id(), $bookId, $rating, $text);
            setFlashMessage('Your review has been saved!', 'success');
            redirect("book-details.php?id=$bookId");
        }
    }

    // Handle borrow/return/reserve
    if ($action === 'borrow' && $book->isAvailable()) {
        if ($library->borrowBook($bookId, Auth::id())) {
            setFlashMessage("You successfully borrowed '{$book->getTitle()}'!", 'success');
        } else {
            setFlashMessage('Sorry, no copies available right now.', 'warning');
        }
        redirect("book-details.php?id=$bookId");
    }

    if ($action === 'return') {
        $library->returnBook($bookId, Auth::id());
        setFlashMessage("Thank you for returning '{$book->getTitle()}'!", 'success');
        redirect("book-details.php?id=$bookId");
    }

    if ($action === 'reserve' && !$book->isAvailable()) {
        if ($library->reserveBook(Auth::id(), $bookId)) {
            setFlashMessage('You are now on the waiting list.', 'info');
        } else {
            setFlashMessage('You are already reserved.', 'warning');
        }
        redirect("book-details.php?id=$bookId");
    }
}

include 'views/header.php';
?>

<div class="row mt-4">
    <div class="col-lg-4 text-center mb-4">
        <?php if ($book->getCoverImage()): ?>
            <img src="<?= baseUrl() ?>/public/uploads/covers/<?= e($book->getCoverImage()) ?>"
                 alt="<?= e($book->getTitle()) ?> cover"
                 class="img-fluid rounded shadow" style="max-height: 500px;">
        <?php else: ?>
            <div class="bg-light border rounded d-flex align-items-center justify-content-center mx-auto"
                 style="height: 500px; width: 350px;">
                <h3 class="text-muted">No Cover</h3>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <h4>Availability</h4>
            <p class="fs-5">
                <?php if ($book instanceof \App\EBook): ?>
                    <span class="text-primary fw-bold">Digital E-Book</span>
                    <br><small class="text-muted">Size: <?= e($book->getFileSize()) ?></small>
                <?php elseif ($book->isAvailable()): ?>
                    <span class="text-success fw-bold">Available</span>
                    <br><small><?= $book->getAvailableCopies() ?> of <?= $book->getTotalCopies() ?> copies</small>
                <?php else: ?>
                    <span class="text-danger fw-bold">All Borrowed</span>
                <?php endif; ?>
            </p>

            <?php if (Auth::check()): ?>
                <?php if ($book instanceof \App\EBook): ?>
                    <?php if ($book->getDownloadLink()): ?>
                        <a href="<?= e($book->getDownloadLink()) ?>" target="_blank" class="btn btn-primary btn-lg w-100 mb-2">
                             📥 Download PDF
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>No Link Available</button>
                    <?php endif; ?>
                <?php else: ?>
                    <?php $isBorrowing = $library->isCurrentlyBorrowing(Auth::id(), $bookId); ?>
                    
                    <?php if ($isBorrowing): ?>
                        <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                            📖 You already have this book
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= e($book->getId()) ?>">
                            <input type="hidden" name="action" value="return">
                            <button type="submit" class="btn btn-outline-danger w-100">Return Book Now</button>
                        </form>
                    <?php elseif ($book->isAvailable()): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= e($book->getId()) ?>">
                            <input type="hidden" name="action" value="borrow">
                            <button type="submit" class="btn btn-success btn-lg w-100">Borrow Now</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= e($book->getId()) ?>">
                            <input type="hidden" name="action" value="reserve">
                            <button type="submit" class="btn btn-outline-info btn-lg w-100">Reserve / Join Waitlist</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg w-100">
                    <?= ($book instanceof \App\EBook) ? 'Login to Download' : 'Login to Borrow' ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-8">
        <h1 class="display-5"><?= e($book->getTitle()) ?></h1>
        <p class="lead text-muted">by <strong><?= e($book->getAuthor()) ?></strong> • <?= $book->getYear() ?></p>
        <p>
            <span class="badge bg-secondary fs-6"><?= e($book->getCategory()) ?></span>
        </p>

        <hr>

        <h3>Reviews (<?= count($reviews) ?>)</h3>

        <?php if (empty($reviews)): ?>
            <p class="text-muted">No reviews yet. Be the first!</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="border-start border-primary border-4 ps-3 mb-4">
                    <div class="d-flex justify-content-between">
                        <strong><?= e($review['username']) ?></strong>
                        <small class="text-muted"><?= formatDate($review['created_at']) ?></small>
                    </div>
                    <div class="text-warning mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $review['rating'] ? '★' : '☆' ?>
                        <?php endfor; ?>
                    </div>
                    <p><?= nl2br(e($review['review_text'] ?? 'No comment.')) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

       <?php if (Auth::check()): ?>
    <?php include 'views/review-form.php'; ?>
<?php else: ?>
    <div class="alert alert-info mt-5 text-center">
        <a href="login.php" class="alert-link fw-bold">Log in</a> to leave a review or borrow books.
    </div>
<?php endif; ?>
    </div>
</div>

<?php include 'views/footer.php'; ?>