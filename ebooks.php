<?php
// ebooks.php
$pageTitle = "E-Books";
require_once 'includes/session.php';
require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

use App\Auth;
use App\Library;
use App\EBook;

$library = new Library();
$allBooks = $library->getAllBooks();
$books = array_filter($allBooks, fn($b) => $b instanceof EBook);

// Optional: Category filter
$category = $_GET['cat'] ?? null;
if ($category && in_array($category, getCategories())) {
    $books = array_filter($books, fn($b) => $b->getCategory() === $category);
    $pageTitle = $category . " E-Books";
}

include 'views/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h1 class="display-6">Digital Library <?= Auth::check() ? ', ' . e(Auth::user()) : '' ?>!</h1>
        <p class="lead">Browse our collection of <?= count($books) ?> amazing e-books available for download.</p>
    </div>
</div>

<!-- Category Filter Buttons -->
<div class="mb-4">
    <a href="ebooks.php" class="btn <?= !$category ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">All</a>
    <?php foreach (getCategories() as $cat): ?>
        <?php if ($cat === 'Uncategorized') continue; ?>
        <a href="ebooks.php?cat=<?= urlencode($cat) ?>"
           class="btn <?= $category === $cat ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
            <?= e($cat) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Book Grid -->
<?php if (empty($books)): ?>
    <div class="alert alert-info text-center">
        No e-books available yet.
        <?php if (Auth::isAdmin()): ?>
            <a href="admin/add-book.php" class="btn btn-primary mt-3">Add the First E-Book</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($books as $book): ?>
            <?php include 'views/book-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>
