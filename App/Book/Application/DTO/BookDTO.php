<?php
namespace App\Book\Application\DTO;

class BookDTO
{
    public ?int $id = null;
    public string $title;
    public string $author;
    public ?string $isbn = null;
    public int $categoryId;
    public ?string $description = null;
    public ?string $coverImage = null;
    public int $quantity = 1;
    public int $availableQuantity = 1;
}