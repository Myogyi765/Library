<?php

use App\Shared\Core\ErrorHandler;
use App\Book\Infrastructure\Persistence\BookRepository;
use App\Book\Infrastructure\Persistence\CategoryRepository;
use App\Book\Domain\Repository\BookRepositoryInterface;
use App\Book\Domain\Repository\CategoryRepositoryInterface;

$container->singleton('book.repository', function() use ($pdo) {
    return new BookRepository($pdo);
});
$container->singleton('category.repository', function() use ($pdo) {
    return new CategoryRepository($pdo);
});

$container->set(BookRepositoryInterface::class, function($c) {
    return $c->get('book.repository');
});
$container->set(CategoryRepositoryInterface::class, function($c) {
    return $c->get('category.repository');
});

ErrorHandler::log('✅ Book services registered', 'DEBUG');