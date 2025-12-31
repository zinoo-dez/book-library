<?php
// admin/delete-book.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';

use App\Auth;
use App\Library;

Auth::guardAdmin();

$id = $_GET['id'] ?? '';

if ($id) {
    $library = new Library();
    if ($library->deleteBook($id)) {
        setFlashMessage("Book deleted successfully.", 'success');
    } else {
        setFlashMessage("Failed to delete book or book not found.", 'danger');
    }
}

redirect('index.php');
