<?php

use App\Book\Infrastructure\Persistence\BookRepository;
use App\Book\Infrastructure\Persistence\CategoryRepository;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use App\Book\Application\UseCase\GetBook;
use App\Book\Application\UseCase\GetBooks;
use App\Book\Presentation\Controller\BookController;

return function ($container) {

    $container->singleton(BookRepository::class, function ($c) {
        return new BookRepository($c->get('db'));
    });
    $container->singleton(BookRepositoryInterface::class, fn($c) => $c->get(BookRepository::class));
    $container->set('book.repository', fn($c) => $c->get(BookRepositoryInterface::class));

    $container->singleton(CategoryRepository::class, function ($c) {
        return new CategoryRepository($c->get('db'));
    });
    $container->singleton(CategoryRepositoryInterface::class, fn($c) => $c->get(CategoryRepository::class));
    $container->set('category.repository', fn($c) => $c->get(CategoryRepositoryInterface::class));

    $container->singleton(GetBook::class, function ($c) {
        return new GetBook($c->get(BookRepositoryInterface::class));
    });
    $container->singleton(GetBooks::class, function ($c) {
        return new GetBooks($c->get(BookRepositoryInterface::class));
    });

    $container->singleton(BookController::class, function ($c) {
        return new BookController($c);
    });
};