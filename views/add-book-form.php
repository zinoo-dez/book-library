<?php
// views/add-book-form.php
// Reusable form for adding/editing books (used in admin/add-book.php or standalone)

// $book variable expected (null for add, Book object for edit)
// $errors array optional
// $isAdmin boolean optional (for showing extra fields)

$isEdit = $book !== null;
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><?= $isEdit ? 'Edit Book' : 'Add New Book' ?></h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= e($book->getId()) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required
                           value="<?= $isEdit ? e($book->getTitle()) : '' ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Year <span class="text-danger">*</span></label>
                    <input type="number" name="year" class="form-control" min="1000" max="<?= date('Y') + 10 ?>" required
                           value="<?= $isEdit ? $book->getYear() : date('Y') ?>">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Author <span class="text-danger">*</span></label>
                <input type="text" name="author" class="form-control" required
                       value="<?= $isEdit ? e($book->getAuthor()) : '' ?>">
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach (getCategories() as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($isEdit && $book->getCategory() === $cat) ? 'selected' : '' ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($isAdmin ?? false): ?>
                    <div class="col-md-6">
                        <label class="form-label">Total Copies</label>
                        <input type="number" name="total_copies" class="form-control" min="1" required
                               value="<?= $isEdit ? $book->getTotalCopies() : 1 ?>">
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-3">
                <label class="form-label">Cover Image (JPG/PNG/GIF - max 2MB)</label>
                <input type="file" name="cover_image" class="form-control" accept="image/*">
                <div class="form-text">Leave empty to keep current cover.</div>
            </div>

            <?php if ($isEdit && $book->getCoverImage()): ?>
                <div class="mt-3">
                    <strong>Current Cover:</strong><br>
                    <img src="public/uploads/covers/<?= e($book->getCoverImage()) ?>"
                         alt="Current cover" class="img-thumbnail mt-2" style="max-height: 200px;">
                </div>
            <?php endif; ?>

            <div class="mt-4 text-end">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary ms-2">
                    <?= $isEdit ? 'Update Book' : 'Add Book' ?>
                </button>
            </div>
        </form>
    </div>
</div>