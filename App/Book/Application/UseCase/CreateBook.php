<?php
namespace App\Book\Application\UseCase;

use App\Book\Domain\Entity\Book;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Application\DTO\BookDTO;

class CreateBook
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(BookDTO $dto): Book
    {
        $book = new Book(
            null,
            $dto->title,
            $dto->author,
            $dto->isbn,
            $dto->categoryId,
            $dto->description,
            $dto->coverImage,
            $dto->quantity,
            $dto->availableQuantity
        );
        return $this->repository->save($book);
    }
}