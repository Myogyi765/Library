<?php
namespace App\Book\Application\UseCase;

use App\Book\Domain\Entity\Book;
use App\Book\Domain\Repository\BookRepositoryInterface;

class GetBook
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?Book
    {
        return $this->repository->findById($id);
    }
}