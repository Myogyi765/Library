<?php
namespace App\Book\Domain\Repository;

use App\Book\Domain\Entity\Book;

interface BookRepositoryInterface
{
    public function save(Book $book): Book;
    public function findById(int $id): ?Book;
    public function findAll(): array;
    public function findByCategory(int $categoryId): array;
    public function delete(int $id): bool;

    public function decrementQuantity(int $bookId, int $amount = 1): void;

    public function incrementQuantity(int $bookId, int $amount = 1): void;

    public function count(): int;
    public function getTotalAvailableQuantity(): int;
    public function getTotalBorrowedQuantity(): int;

    public function getLatestBooks(int $limit): array;

    public function findAllPaginated(int $offset, int $limit): array;
    public function countAll(): int;
    public function findFilteredPaginated(?string $search, ?int $categoryId, int $offset, int $limit): array;
    public function countFiltered(?string $search, ?int $categoryId): int;
}