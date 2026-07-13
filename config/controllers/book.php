<?php

// ================================================================
// 📚 Book Controllers
// ================================================================
use App\Shared\Core\ErrorHandler;
use App\Book\Presentation\Controller\BookController;

$container->set(BookController::class, function($c) {
    return new BookController($c);
});

ErrorHandler::log('✅ BookController registered', 'DEBUG');