<?php

use App\Shared\Core\ErrorHandler;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    ErrorHandler::log('✅ Session started', 'INFO');
}