<?php
// admin/add-book.php
// Admin: Add or Edit Book Form

session_start();
require_once '../includes/session.php';
require_once '../vendor/autoload.php';
require_once '../includes/functions.php';

use App\Auth;
use App\Library;
use App\Book;
use App\EBook;

Auth::guardAdmin();  // Only admins can access

$library = new Library();

// Handle form submission
$errors = [];
$book = null;
$isEdit = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'] ?? '';
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $year        = (int)($_POST['year'] ?? 0);
    $category    = $_POST['category'] ?? 'Uncategorized';
    $totalCopies = max(1, (int)($_POST['total_copies'] ?? 1));
    $type        = $_POST['type'] ?? 'physical';
    $fileSize    = trim($_POST['file_size'] ?? '');
    $downloadLink = trim($_POST['download_link'] ?? '');

    if (empty($title)) $errors[] = "Title is required.";
    if (empty($author)) $errors[] = "Author is required.";
    if ($year < 1000 || $year > date('Y') + 5) $errors[] = "Invalid year.";
    if (!in_array($category, getCategories())) $errors[] = "Invalid category.";
    if ($type === 'ebook' && empty($downloadLink)) $errors[] = "Download link is required for e-books.";

    if (empty($errors)) {
        if ($id && $library->getBookById($id)) {
            // Edit existing book
            $book = $library->getBookById($id);
            $book->setCategory($category);
            $book->setTotalCopies($totalCopies);
            if ($book instanceof EBook) {
                $book->setFileSize($fileSize);
                $book->setDownloadLink($downloadLink);
            }
            $isEdit = true;
        } else {
            // New book
            if ($type === 'ebook') {
                $book = new EBook($title, $author, $year, $fileSize, $totalCopies, null, $category, $downloadLink);
            } else {
                $book = new Book($title, $author, $year, $totalCopies, null, $category);
            }
        }

        // Handle cover upload
        $library->handleCoverUpload($book, $_FILES);

        if (empty($errors)) {
            if ($isEdit) {
                $library->updateBook($book);
                setFlashMessage("Book '{$title}' updated successfully!", 'success');
            } else {
                $library->addBook($book);
                setFlashMessage("Book '{$title}' added successfully!", 'success');
            }
            redirect('index.php');
        }
    }
} else {
    // Load book for editing
    $id = $_GET['id'] ?? '';
    if ($id) {
        $book = $library->getBookById($id);
        if (!$book) {
            setFlashMessage("Book not found.", 'danger');
            redirect('index.php');
        }
        $isEdit = true;
    }
}

include '../views/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4"><?= $isEdit ? 'Edit Book' : 'Add New Book' ?></h1>

    <?php displayFlashMessage(); ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if ($isEdit && $book): ?>
                    <input type="hidden" name="id" value="<?= e($book->getId()) ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required
                           value="<?= $book ? e($book->getTitle()) : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Author <span class="text-danger">*</span></label>
                    <input type="text" name="author" class="form-control" required
                           value="<?= $book ? e($book->getAuthor()) : '' ?>">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" min="1000" max="<?= date('Y') + 5 ?>" required
                                   value="<?= $book ? $book->getYear() : date('Y') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Total Copies</label>
                            <input type="number" name="total_copies" class="form-control" min="1" required
                                   value="<?= $book ? $book->getTotalCopies() : 1 ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach (getCategories() as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($book && $book->getCategory() === $cat) ? 'selected' : '' ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Book Type</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="typePhysical" value="physical"
                               <?= (!$book || $book->getType() === 'physical') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="typePhysical">Physical Book</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="typeEbook" value="ebook"
                               <?= ($book && $book->getType() === 'ebook') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="typeEbook">E-Book</label>
                    </div>
                </div>

                <div id="ebookFields" style="<?= ($book && $book->getType() === 'ebook') ? '' : 'display: none;' ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">File Size (e.g., 2.5 MB)</label>
                                <input type="text" name="file_size" class="form-control"
                                       value="<?= ($book instanceof EBook) ? e($book->getFileSize()) : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Download Link (URL)</label>
                                <input type="url" name="download_link" class="form-control"
                                       value="<?= ($book instanceof EBook) ? e($book->getDownloadLink()) : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cover Image (JPG, PNG, GIF - max 2MB)</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">

                    <?php if ($isEdit && $book && $book->getCoverImage()): ?>
                        <div class="mt-3">
                            <p><strong>Current Cover:</strong></p>
                            <img src="<?= baseUrl() ?>/public/uploads/covers/<?= e($book->getCoverImage()) ?>"
                                 alt="Current cover" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <?= $isEdit ? 'Update Book' : 'Add Book' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const typePhysical = document.getElementById('typePhysical');
    const typeEbook = document.getElementById('typeEbook');
    const ebookFields = document.getElementById('ebookFields');

    function toggleEbookFields() {
        if (typeEbook.checked) {
            ebookFields.style.display = 'block';
        } else {
            ebookFields.style.display = 'none';
        }
    }

    if (typePhysical && typeEbook) {
        typePhysical.addEventListener('change', toggleEbookFields);
        typeEbook.addEventListener('change', toggleEbookFields);
    }
});
</script>

<?php include '../views/footer.php'; ?>
