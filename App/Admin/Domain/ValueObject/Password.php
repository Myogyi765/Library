<?php
namespace App\Admin\Domain\ValueObject;

class Password
{
    private string $hash;

    public function __construct(string $plainPassword)
    {
        $this->hash = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public static function fromHash(string $hash): self
    {
        $instance = new self('dummy');
        $instance->hash = $hash;
        return $instance;
    }

    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hash);
    }

    public function getHash(): string { return $this->hash; }
}