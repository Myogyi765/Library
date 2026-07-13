<?php

namespace App\User\Domain\Service;

use App\User\Domain\Entity\User;

interface VerificationServiceInterface
{
    public function generateVerificationToken(User $user): string;
    public function generateVerificationCode(): string;
    public function sendVerificationEmail(User $user, string $token, string $code): void;
    public function sendVerificationSMS(User $user, string $code): void;
    public function verifyEmail(string $token): ?User;
    public function verifyPhone(string $code): ?User;
    public function isTokenValid(string $token): bool;
    public function isCodeValid(string $code): bool;
        public function verifyEmailByCode(string $code): ?User;
}