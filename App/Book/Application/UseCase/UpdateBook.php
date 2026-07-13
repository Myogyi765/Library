<?php
namespace App\Book\Application\UseCase;

use App\Book\Domain\Entity\Book;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Application\DTO\BookDTO;

class UpdateBook
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(BookDTO $dto): Book
    {
        $book = $this->repository->findById($dto->id);
        if (!$book) {
            throw new \RuntimeException('Book not found');
        }
        $book->setTitle($dto->title)
             ->setAuthor($dto->author)
             ->setIsbn($dto->isbn)
             ->setCategoryId($dto->categoryId)
             ->setDescription($dto->description)
             ->setCoverImage($dto->coverImage ?? $book->getCoverImage())
             ->setQuantity($dto->quantity)
             ->setAvailableQuantity($dto->availableQuantity);
        return $this->repository->save($book);
    }
}