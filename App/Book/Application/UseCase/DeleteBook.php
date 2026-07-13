<?php
namespace App\Book\Application\UseCase;

use App\Book\Domain\Repository\BookRepositoryInterface;

class DeleteBook
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}