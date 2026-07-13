<?php
namespace App\Book\Domain\Repository;

use App\Book\Domain\Entity\Category;

interface CategoryRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Category;
    public function findByName(string $name): ?Category; // ✅ new
    public function save(Category $category): Category; // ✅ new
    public function delete(int $id): void; // ✅ new
}