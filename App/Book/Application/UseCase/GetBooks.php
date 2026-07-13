<?php
namespace App\Book\Application\UseCase;

use App\Book\Domain\Repository\BookRepositoryInterface;

class GetBooks
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): array
    {
        return $this->repository->findAll();
    }
}