<?php

namespace App\User\Application\UseCase;

use App\User\Infrastructure\Security\UserAuthenticator;

class LogoutUser
{
    private UserAuthenticator $authenticator;
    
    public function __construct(UserAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }
    
    public function execute(): void
    {
        // Call the authenticator to log out the user
        $this->authenticator->logout();
    }
}