<?php
// includes/functions.php
// Global Helper Functions for the Book Library System

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

/**
 * Get the list of available book categories
 * @return array
 */
function getCategories(): array
{
    return [
        'Fiction',
        'Non-Fiction',
        'Mystery',
        'Romance',
        'Sci-Fi',
        'Fantasy',
        'Biography',
        'History',
        'Self-Help',
        'Children',
        'Horror',
        'Thriller',
        'Poetry',
        'Uncategorized'
    ];
}

/**
 * Flash message helper - set a one-time message
 * @param string $message
 * @param string $type 'success', 'danger', 'warning', 'info' (Bootstrap classes)
 */
function setFlashMessage(string $message, string $type = 'info'): void
{
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type
    ];
}

/**
 * Display and clear flash message
 */
function displayFlashMessage(): void
{
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        echo '<div class="alert alert-' . htmlspecialchars($msg['type']) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($msg['text']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['flash_message']);
    }
}

/**
 * Redirect to a URL
 * @param string $path
 */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Check if user is logged in (wrapper)
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Sanitize output (prevent XSS)
 * @param string $data
 * @return string
 */
function e($data): string
{
    if ($data === null || $data === '') {
        return '';
    }
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

/**
 * Get base URL for assets (helpful for subfolders or live hosting)
 * @return string
 */
function baseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // For local development with php -S or at root domain
    return $protocol . $host;
}

/**
 * Format date nicely
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate(?string $date, string $format = 'M j, Y'): string
{
    if ($date === null || $date === '' || $date === '0000-00-00') {
        return '<em>Not returned</em>';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '<em>Invalid date</em>';
}

/**
 * Generate random book ID (if needed outside Book class)
 * @return string
 */
function generateBookId(): string
{
    return 'book_' . uniqid();
}

/**
 * Check if a book is overdue
 * @param string $dueDate YYYY-MM-DD
 * @return bool
 */
function isOverdue(string $dueDate): bool
{
    return strtotime($dueDate) < time();
}

/**
 * Calculate overdue days
 * @param string $dueDate
 * @return int
 */
function overdueDays(string $dueDate): int
{
    $due = strtotime($dueDate);
    $now = time();
    if ($due >= $now) return 0;
    return floor(($now - $due) / (60 * 60 * 24));
}

/**
 * date time format to myanmar 
 * @param string $date
 * @param string $format
 * @param string $time
 * @return string
 */
function myanmarDateTime(string $date, string $format = 'Y-m-d', string $time = 'H:i:s'): string
{
    // Asia-Yangon
    $date = new DateTime($date);
    $date->setTimezone(new DateTimeZone('Asia/Yangon'));
    return $date->format($format . ' ' . $time);
}