<?php
// views/review-form.php
// Reusable review form partial
// Expected variables from book-details.php:
// - $book (App\Book object)
// - $existingReview (array|null) - user's current review if exists

use App\Auth;

// Prevent direct access or use without login
if (!Auth::check()) {
    echo '<div class="alert alert-info mt-4">
            Please <a href="login.php" class="alert-link">log in</a> to leave a review.
          </div>';
    return;
}

$isEdit = $existingReview !== null;
$currentRating = $existingReview['rating'] ?? 0;
$currentText = $existingReview['review_text'] ?? '';
?>

<div class="card shadow-sm mt-5 border-0">
    <div class="card-header bg-primary text-white rounded-top">
        <h5 class="mb-0 fw-semibold">
            <?= $isEdit ? 'Edit Your Review' : 'Write a Review' ?>
        </h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="book-details.php">
            <!-- Hidden fields -->
            <input type="hidden" name="id" value="<?= e($book->getId()) ?>">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update_review' : 'add_review' ?>">

            <!-- Star Rating -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    Your Rating <span class="text-danger">*</span>
                </label>
                <div class="star-rating d-flex flex-row-reverse justify-content-end gap-3" style="font-size: 2.5rem;">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input
                            type="radio"
                            name="rating"
                            value="<?= $i ?>"
                            id="star<?= $i ?>"
                            <?= $currentRating == $i ? 'checked' : '' ?>
                            required
                            class="d-none"
                        >
                        <label
                            for="star<?= $i ?>"
                            class="star-label cursor-pointer"
                            title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>"
                        >
                            ★
                        </label>
                    <?php endfor; ?>
                </div>
                <small class="form-text text-muted">Click to rate • Required</small>
            </div>

            <!-- Review Text -->
            <div class="mb-4">
                <label for="review_text" class="form-label fw-bold">
                    Your Review <small class="text-muted">(optional)</small>
                </label>
                <textarea
                    name="review_text"
                    id="review_text"
                    rows="5"
                    class="form-control border"
                    placeholder="What did you think of this book? Share your thoughts..."><?= e($currentText) ?></textarea>
                <small class="form-text text-muted mt-2">
                    Be kind and constructive • Max 1000 characters
                </small>
            </div>

            <!-- Submit Button -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">
                    <?= $isEdit ? 'Update Review' : 'Submit Review' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Interactive Star Rating */
.star-rating {
    direction: rtl;
    unicode-bidi: bidi-override;
}

.star-label {
    color: #ddd;
    transition: all 0.2s ease;
    padding: 0 5px;
}

.star-label:hover,
.star-label:hover ~ .star-label,
input:checked ~ .star-label {
    color: #ffc107 !important;
    transform: scale(1.1);
    text-shadow: 0 0 10px rgba(255, 193, 7, 0.6);
}

/* Responsive adjustment */
@media (max-width: 576px) {
    .star-rating {
        font-size: 2rem !important;
        gap: 10px;
    }
}
</style>