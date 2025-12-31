<?php
// export.php (add PDF option)
session_start();
require_once 'vendor/autoload.php';
// require_once 'tcpdf/tcpdf.php'; // After placing folder

$type = $_GET['type'] ?? 'books';
$format = $_GET['format'] ?? 'csv';

$library = new Library();
if ($type === 'books') {
    $books = $library->getAllBooks();
    $filename = "library_books_" . date('Y-m-d');
    $headers = ['Title', 'Author', 'Year', 'Category', 'Available/Total Copies'];
    $rows = [];
    foreach ($books as $book) {
        $rows[] = [
            $book->getTitle(),
            $book->getAuthor(),
            $book->getYear(),
            $book->getCategory(),
            $book->getAvailableCopies() . '/' . $book->getTotalCopies()
        ];
    }
}

if ($format === 'csv') {
    // Same as before...
} elseif ($format === 'pdf') {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Library System');
    $pdf->SetTitle('Book List');
    $pdf->SetHeaderData('', 0, 'Book Library Report', 'Generated on ' . date('Y-m-d'));
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->AddPage();

    $html = '<h1>Book List</h1><table border="1"><thead><tr>';
    foreach ($headers as $h) $html .= '<th><strong>' . $h . '</strong></th>';
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filename . '.pdf', 'D'); // Download
    exit;
}
