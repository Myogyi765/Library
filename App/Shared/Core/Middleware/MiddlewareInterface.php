<?php
namespace App\Shared\Core\Middleware;

interface MiddlewareInterface
{
    
    public function handle(): bool;
}