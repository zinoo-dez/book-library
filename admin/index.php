<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';

use App\User;
use App\Library;

if (!User::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$library = new Library();
$books = $library->getAllBooks();

include '../views/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Admin Dashboard - Manage Books</h1>
    <a href="add-book.php" class="btn btn-success mb-3">Add New Book</a>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Cover</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Copies (Avail/Total)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td>
                        <?php if ($book->getCoverImage()): ?>
                            <img src="<?= baseUrl() ?>/public/uploads/covers/<?= htmlspecialchars($book->getCoverImage()) ?>"
                                alt="Cover" style="width:60px; height:80px; object-fit:cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                style="width:60px; height:80px;">No Cover</div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($book->getTitle()) ?></td>
                    <td><?= htmlspecialchars($book->getAuthor()) ?></td>
                    <td><span class="badge bg-primary"><?= htmlspecialchars($book->getCategory()) ?></span></td>
                    <td><?= $book->getAvailableCopies() ?> / <?= $book->getTotalCopies() ?></td>
                    <td>
                        <a href="add-book.php?id=<?= $book->getId() ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete-book.php?id=<?= $book->getId() ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete forever?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../views/footer.php'; ?>