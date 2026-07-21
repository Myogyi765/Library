<?php

use App\Book\Infrastructure\Persistence\BookRepository;
use App\Book\Infrastructure\Persistence\CategoryRepository;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\Book\Application\UseCase\CreateBook;
use App\Book\Application\UseCase\UpdateBook;
use App\Book\Application\UseCase\DeleteBook;
use App\Book\Application\UseCase\GetBook;
use App\Book\Application\UseCase\GetBooks;
use App\Book\Presentation\Controller\BookController;

return function ($container) {

    // ─── Book Repository ─────────────────────────────────────
    $container->singleton(BookRepository::class, function ($c) {
        return new BookRepository($c->get('db'));
    });
    $container->singleton(BookRepositoryInterface::class, fn($c) => $c->get(BookRepository::class));
    $container->set('book.repository', fn($c) => $c->get(BookRepositoryInterface::class));

    // ─── Category Repository ────────────────────────────────
    $container->singleton(CategoryRepository::class, function ($c) {
        return new CategoryRepository($c->get('db'));
    });
    $container->singleton(CategoryRepositoryInterface::class, fn($c) => $c->get(CategoryRepository::class));
    $container->set('category.repository', fn($c) => $c->get(CategoryRepositoryInterface::class));

    // ─── Use Cases ──────────────────────────────────────────
    // ✅ CreateBook – required for AdminBookController
    $container->singleton(CreateBook::class, function ($c) {
        return new CreateBook($c->get(BookRepositoryInterface::class));
    });

    // ✅ UpdateBook – required for AdminBookController
    $container->singleton(UpdateBook::class, function ($c) {
        return new UpdateBook($c->get(BookRepositoryInterface::class));
    });

    // ✅ DeleteBook – required for AdminBookController
    $container->singleton(DeleteBook::class, function ($c) {
        return new DeleteBook($c->get(BookRepositoryInterface::class));
    });

    // GetBook – already exists
    $container->singleton(GetBook::class, function ($c) {
        return new GetBook($c->get(BookRepositoryInterface::class));
    });

    // GetBooks – already exists
    $container->singleton(GetBooks::class, function ($c) {
        return new GetBooks($c->get(BookRepositoryInterface::class));
    });

    // ─── Controller ─────────────────────────────────────────
    $container->singleton(BookController::class, function ($c) {
        return new BookController($c);
    });
};