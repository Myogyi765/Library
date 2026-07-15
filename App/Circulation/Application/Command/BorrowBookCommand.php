<?php

namespace App\Circulation\Application\Command;

class BorrowBookCommand
{
    public function __construct(
        public  int $userId,
        public  int $bookId
    ) {}
}