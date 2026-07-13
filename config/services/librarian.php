<?php

// ================================================================
// 👨‍🏫 Librarian Services
// ================================================================
use App\Shared\Core\ErrorHandler;
use App\Librarian\Infrastructure\Persistence\LibrarianRepository;
use App\Librarian\Application\Service\LibrarianService;

if (interface_exists('App\Librarian\Domain\Repository\LibrarianRepositoryInterface')) {
    $container->set('librarian.repository', function() use ($pdo) {
        return new LibrarianRepository($pdo);
    });
    $container->set('librarian.service', function() use ($container) {
        return new LibrarianService($container->get('librarian.repository'));
    });
    ErrorHandler::log('✅ Librarian services registered', 'DEBUG');
} else {
    ErrorHandler::log('⚠️ LibrarianRepositoryInterface not found – Librarian services skipped', 'WARNING');
}