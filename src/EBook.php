<?php
// src/EBook.php

namespace App;

class EBook extends Book
{
    private string $fileSize;
    private ?string $downloadLink = null;

    public function __construct(
        string $title,
        string $author,
        int $year,
        string $fileSize,
        int $totalCopies = 1,
        ?string $coverImage = null,
        string $category = 'Uncategorized',
        ?string $downloadLink = null,
        ?string $id = null
    ) {
        parent::__construct($title, $author, $year, $totalCopies, $coverImage, $category, $id);
        $this->fileSize = $fileSize;
        $this->downloadLink = $downloadLink;
    }

    public function getFileSize(): string
    {
        return $this->fileSize;
    }
    public function getDownloadLink(): ?string
    {
        return $this->downloadLink;
    }

    public function setDownloadLink(?string $link): void
    {
        $this->downloadLink = $link;
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'type' => 'ebook',
            'file_size' => $this->fileSize,
            'download_link' => $this->downloadLink
        ];
    }

    public static function fromArray(array $data): self
    {
        $ebook = new self(
            $data['title'],
            $data['author'],
            $data['year'],
            $data['file_size'] ?? 'Unknown',
            $data['total_copies'] ?? 1,
            $data['cover_image'] ?? null,
            $data['category'] ?? 'Uncategorized',
            $data['download_link'] ?? null,
            $data['id'] ?? null
        );
        $ebook->availableCopies = $data['available_copies'] ?? $ebook->getTotalCopies();
        return $ebook;
    }
}
