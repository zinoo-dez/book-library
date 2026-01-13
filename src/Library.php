<?php
namespace App;
use PDO;
use App\Book;
use App\EBook;
class Library
{
    private PDO $pdo;  // ← Add backslash here
    private array $books = [];

    public function __construct() {
        $config = include __DIR__ . '/../config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $options = $config['options'];

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (\PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }

        $this->loadAllBooks();
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    private function loadAllBooks(): void
    {
        $stmt = $this->pdo->query("SELECT * FROM books ORDER BY title");
        while ($row = $stmt->fetch()) {
            if (isset($row['type']) && $row['type'] === 'ebook') {
                $book = EBook::fromArray($row);
            } else {
                $book = Book::fromArray($row);
            }
            $this->books[$book->getId()] = $book;
        }
    }

    // ==================== Book Management ====================

    public function getAllBooks(): array
    {
        return $this->books;
    }

    public function getBookById(string $id): ?Book
    {
        return $this->books[$id] ?? null;
    }

    // Dependency Injection(DI)
    public function handleCoverUpload(Book $book, array $files): bool
    {
        if (isset($files['cover_image']) && $files['cover_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($files['cover_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $files['cover_image']['size'] <= 2_000_000) {
                // Remove old cover if exists
                if ($book->getCoverImage()) {
                    $oldPath = __DIR__ . '/../public/uploads/covers/' . $book->getCoverImage();
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $coverFilename = $book->getId() . '.' . $ext;
                $uploadPath = __DIR__ . '/../public/uploads/covers/' . $coverFilename;
                
                if (move_uploaded_file($files['cover_image']['tmp_name'], $uploadPath)) {
                    $book->setCoverImage($coverFilename);
                    return true;
                }
            }
        }
        return false;
    }

    public function addBook(Book $book): void
    {
        $sql = "INSERT INTO books (
            id, title, author, year, cover_image, category, total_copies, available_copies, type, file_size, download_link
        ) VALUES (
            :id, :title, :author, :year, :cover_image, :category, :total_copies, :available_copies, :type, :file_size, :download_link
        )";

        $stmt = $this->pdo->prepare($sql);
        $data = $book->toArray();
        $stmt->execute([
            ':id'               => $data['id'],
            ':title'            => $data['title'],
            ':author'           => $data['author'],
            ':year'             => $data['year'],
            ':cover_image'      => $data['cover_image'],
            ':category'         => $data['category'],
            ':total_copies'     => $data['total_copies'],
            ':available_copies' => $data['available_copies'],
            ':type'             => $data['type'] ?? 'physical',
            ':file_size'        => $data['file_size'] ?? null,
            ':download_link'    => $data['download_link'] ?? null,
        ]);

        $this->books[$book->getId()] = $book;
    }

    public function updateBook(Book $book): void
    {
        $data = $book->toArray();
        $stmt = $this->pdo->prepare("UPDATE books SET 
            title = :title,
            author = :author,
            year = :year,
            cover_image = :cover_image,
            category = :category,
            total_copies = :total_copies,
            available_copies = :available_copies,
            type = :type,
            file_size = :file_size,
            download_link = :download_link
            WHERE id = :id");
        $stmt->execute([
            ':id'               => $data['id'],
            ':title'            => $data['title'],
            ':author'           => $data['author'],
            ':year'             => $data['year'],
            ':cover_image'      => $data['cover_image'],
            ':category'         => $data['category'],
            ':total_copies'     => $data['total_copies'],
            ':available_copies' => $data['available_copies'],
            ':type'             => $data['type'] ?? 'physical',
            ':file_size'        => $data['file_size'] ?? null,
            ':download_link'    => $data['download_link'] ?? null,
        ]);
        $this->books[$book->getId()] = $book;
    }

    public function deleteBook(string $id): bool
    {
        if (!isset($this->books[$id])) return false;

        $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = :id");
        $stmt->execute([':id' => $id]);
        unset($this->books[$id]);
        return true;
    }

    // ==================== Borrowing & Due Dates ====================
    // ============ business logic ================
    public function borrowBook(string $bookId, int $userId): bool
    {
        $book = $this->getBookById($bookId);
        if (!$book || !$book->borrowCopy()) return false;
    
        $dueDate = date('Y-m-d', strtotime('+14 days')); // 14-day loan
        $stmt = $this->pdo->prepare("INSERT INTO borrowing_history (user_id, book_id, due_date) 
        VALUES (:user_id, :book_id, :due_date)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':book_id', $bookId, PDO::PARAM_STR);
        $stmt->bindParam(':due_date', $dueDate, PDO::PARAM_STR);
        $stmt->execute();

        $this->updateBook($book);
        $this->sendNotification($_SESSION['email'], $book->getTitle(), 'borrowed', $dueDate);
        return true;
    }

    public function returnBook(string $bookId, int $userId): void
    {
        $book = $this->getBookById($bookId);
        if ($book) {
            // Find the active borrowing record
            $stmt = $this->pdo->prepare("SELECT id, due_date FROM borrowing_history 
                WHERE user_id = ? AND book_id = ? AND returned_at IS NULL LIMIT 1");
            $stmt->execute([$userId, $bookId]);
            $record = $stmt->fetch();

            if ($record) {
                $fine = 0;
                $dueDate = $record['due_date'];
                
                // Calculate fine ($0.50 per day overdue)
                if (strtotime($dueDate) < time()) {
                    $days = floor((time() - strtotime($dueDate)) / (60 * 60 * 24));
                    if ($days > 0) {
                        $fine = $days * 0.50;
                    }
                }

                $book->returnCopy();
                $this->updateBook($book);

                $stmt = $this->pdo->prepare("UPDATE borrowing_history 
                    SET returned_at = NOW(), fine_amount = ? 
                    WHERE id = ?");
                $stmt->execute([$fine, $record['id']]);

                $msg = "returned";
                if ($fine > 0) {
                    $msg .= " with a fine of $" . number_format($fine, 2);
                }
                $this->sendNotification($_SESSION['email'], $book->getTitle(), $msg);
            }
        }
    }

    // ==================== Reviews & Ratings ====================

    public function addReview(int $userId, string $bookId, int $rating, ?string $text): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO reviews 
            (user_id, book_id, rating, review_text) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = ?, review_text = ?");
        $stmt->execute([$userId, $bookId, $rating, $text, $rating, $text]);
    }

    public function getReviews(string $bookId): array
    {
        $stmt = $this->pdo->prepare("SELECT u.username, r.user_id, r.rating, r.review_text, r.created_at 
            FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.book_id = ? ORDER BY r.created_at DESC");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll();
    }

    // ==================== Reservations ====================

    public function reserveBook(int $userId, string $bookId): bool
    {
        $book = $this->getBookById($bookId);
        if ($book && !$book->isAvailable()) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO reservations 
                (user_id, book_id) VALUES (?, ?)");
            return $stmt->execute([$userId, $bookId]);
        }
        return false;
    }

    // ==================== Notifications ====================

    private function sendNotification(string $email, string $bookTitle, string $action, ?string $extra = null): void
    {
        $subject = "Library Notification: Book {$action}";
        $message = "Dear user,\n\nYou have {$action} the book: \"{$bookTitle}\".\n";
        if ($extra) $message .= "Due date: {$extra}\n";
        $message .= "\nThank you!\nLibrary Team";
        $headers = "From: no-reply@library.com";

        @mail($email, $subject, $message, $headers);
    }

    // ==================== User History ====================

    public function getUserHistory(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT bh.book_id, b.title, b.author, bh.borrowed_at, bh.returned_at, bh.due_date, bh.fine_amount
            FROM borrowing_history bh
            JOIN books b ON bh.book_id = b.id
            WHERE bh.user_id = ?
            ORDER BY bh.borrowed_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // ==================== Statistics ====================

    public function getStats(): array
    {
        $stats = [];

        // Total Titles
        $stats['total_titles'] = count($this->books);

        // Total Available Copies
        $stmt = $this->pdo->query("SELECT SUM(available_copies) FROM books");
        $stats['total_available_books'] = (int)$stmt->fetchColumn();

        // Total Borrowed Books (Active Loans)
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM borrowing_history WHERE returned_at IS NULL");
        $stats['total_borrowed_books'] = (int)$stmt->fetchColumn();

        // Unique Users with Active Loans
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT user_id) FROM borrowing_history WHERE returned_at IS NULL");
        $stats['total_borrowing_users'] = (int)$stmt->fetchColumn();

        // Total Registered Users
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = (int)$stmt->fetchColumn();

        // Total Fines Collected
        $stmt = $this->pdo->query("SELECT SUM(fine_amount) FROM borrowing_history");
        $stats['total_fines'] = (float)$stmt->fetchColumn();

        return $stats;
    }

    /**
     * Check if a specific user currently has a specific book borrowed (not yet returned)
     */
    public function isCurrentlyBorrowing(int $userId, string $bookId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrowing_history 
            WHERE user_id = ? AND book_id = ? AND returned_at IS NULL");
        $stmt->execute([$userId, $bookId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Get all active reservations for a specific user
     */
    public function getUserReservations(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT r.id, r.book_id, b.title, b.author, r.reserved_at, r.status 
            FROM reservations r
            JOIN books b ON r.book_id = b.id
            WHERE r.user_id = ? AND r.status IN ('waiting', 'available')
            ORDER BY r.reserved_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Cancel a reservation
     */
    public function cancelReservation(int $reservationId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
        return $stmt->execute([$reservationId, $userId]);
    }
}