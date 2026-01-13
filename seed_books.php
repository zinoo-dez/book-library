<?php
// seed_books.php

require_once 'vendor/autoload.php';

$config = include 'config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$books = [
    // Physical Books
    [
        'id' => 'book_phys_001',
        'title' => 'The Art of Computer Programming',
        'author' => 'Donald Knuth',
        'year' => 1968,
        'category' => 'Non-Fiction',
        'total_copies' => 2,
        'available_copies' => 2,
        'type' => 'physical',
        'file_size' => null,
        'download_link' => null
    ],
    [
        'id' => 'book_phys_002',
        'title' => 'Design Patterns',
        'author' => 'Erich Gamma et al.',
        'year' => 1994,
        'category' => 'Non-Fiction',
        'total_copies' => 5,
        'available_copies' => 5,
        'type' => 'physical',
        'file_size' => null,
        'download_link' => null
    ],
    [
        'id' => 'book_phys_003',
        'title' => 'The Pragmatic Programmer',
        'author' => 'Andrew Hunt & David Thomas',
        'year' => 1999,
        'category' => 'Non-Fiction',
        'total_copies' => 3,
        'available_copies' => 3,
        'type' => 'physical',
        'file_size' => null,
        'download_link' => null
    ],
    // E-Books
    [
        'id' => 'book_eb_001',
        'title' => 'Modern PHP',
        'author' => 'Josh Lockhart',
        'year' => 2015,
        'category' => 'Non-Fiction',
        'total_copies' => 1,
        'available_copies' => 1,
        'type' => 'ebook',
        'file_size' => '2.5 MB',
        'download_link' => 'https://example.com/downloads/modern-php.pdf'
    ],
    [
        'id' => 'book_eb_002',
        'title' => 'Learning JavaScript Design Patterns',
        'author' => 'Addy Osmani',
        'year' => 2012,
        'category' => 'Non-Fiction',
        'total_copies' => 1,
        'available_copies' => 1,
        'type' => 'ebook',
        'file_size' => '3.8 MB',
        'download_link' => 'https://example.com/downloads/js-patterns.pdf'
    ],
    [
        'id' => 'book_eb_003',
        'title' => 'Deep Work',
        'author' => 'Cal Newport',
        'year' => 2016,
        'category' => 'Self-Help',
        'total_copies' => 1,
        'available_copies' => 1,
        'type' => 'ebook',
        'file_size' => '1.2 MB',
        'download_link' => 'https://example.com/downloads/deep-work.pdf'
    ],
    [
        'id' => 'book_eb_004',
        'title' => 'Atomic Habits',
        'author' => 'James Clear',
        'year' => 2018,
        'category' => 'Self-Help',
        'total_copies' => 1,
        'available_copies' => 1,
        'type' => 'ebook',
        'file_size' => '1.5 MB',
        'download_link' => 'https://example.com/downloads/atomic-habits.pdf'
    ]
];

$sql = "INSERT INTO books (id, title, author, year, category, total_copies, available_copies, type, file_size, download_link) 
        VALUES (:id, :title, :author, :year, :category, :total_copies, :available_copies, :type, :file_size, :download_link)
        ON DUPLICATE KEY UPDATE 
        title = VALUES(title), 
        author = VALUES(author), 
        year = VALUES(year), 
        category = VALUES(category), 
        total_copies = VALUES(total_copies), 
        available_copies = VALUES(available_copies), 
        type = VALUES(type), 
        file_size = VALUES(file_size), 
        download_link = VALUES(download_link)";

$stmt = $pdo->prepare($sql);

$count = 0;
foreach ($books as $book) {
    try {
        $stmt->execute($book);
        $count++;
    } catch (PDOException $e) {
        echo "Error inserting {$book['title']}: " . $e->getMessage() . "\n";
    }
}

echo "Successfully seeded $count books (including ebooks).\n";
