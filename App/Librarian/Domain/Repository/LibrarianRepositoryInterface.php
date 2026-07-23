<?php
namespace App\Librarian\Domain\Repository;

use App\Librarian\Domain\Entity\Librarian;
use App\Librarian\Domain\ValueObject\Email;

interface LibrarianRepositoryInterface
{
    public function save(Librarian $librarian): void;
    public function findById(int $id): ?Librarian;
    public function findByEmail(Email $email): ?Librarian;
    public function findAll(): array;
    public function delete(Librarian $librarian): void;
    
}