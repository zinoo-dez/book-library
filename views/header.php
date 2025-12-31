<?php
// views/header.php
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Book Library System' ?> - My Library</title>

    <!-- Bootstrap 5 CSS -->
    <link href="<?= baseUrl() ?>/public/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= baseUrl() ?>/public/css/custom.css" rel="stylesheet">

    <!-- Favicon (optional - add your own) -->
    <!-- <link rel="icon" href="<?= baseUrl() ?>/public/images/favicon.ico"> -->

    <style>
        body {
            background-color: #f8f9fa;
        }
        .cover-img {
            object-fit: cover;
            width: 80px;
            height: 120px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container mt-4">
        <?php displayFlashMessage(); ?>