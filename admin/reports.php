<?php
// admin/reports.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';

use App\Auth;
use App\Library;

Auth::guardAdmin();

$library = new Library();

include '../views/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">📊 Library Reports</h1>
    
    <div class="row g-4">
        <?php $stats = $library->getStats(); ?>
        
        <div class="col-md-4">
            <div class="card text-white bg-primary h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75 text-uppercase fw-bold">Total Book Titles</h6>
                    <h2 class="card-title mb-0 display-4"><?= $stats['total_titles'] ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75 text-uppercase fw-bold">Available Books</h6>
                    <h2 class="card-title mb-0 display-4"><?= $stats['total_available_books'] ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75 text-uppercase fw-bold">Borrowed Books</h6>
                    <h2 class="card-title mb-0 display-4"><?= $stats['total_borrowed_books'] ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-info h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75 text-uppercase fw-bold">Borrowing Users</h6>
                    <h2 class="card-title mb-0 display-4"><?= $stats['total_borrowing_users'] ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-secondary h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75 text-uppercase fw-bold">Total Users</h6>
                    <h2 class="card-title mb-0 display-4"><?= $stats['total_users'] ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-dark h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75 text-uppercase fw-bold">Total Fines Collected</h6>
                    <h2 class="card-title mb-0 display-4">$<?= number_format($stats['total_fines'], 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        More detailed reporting coming soon...
    </div>
</div>

<?php include '../views/footer.php'; ?>
