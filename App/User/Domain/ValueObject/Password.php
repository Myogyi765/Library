<?php

namespace App\User\Domain\ValueObject;

class Password
{
    private string $hash;

    /**
     * @param string $value Plain password or already hashed
     * @param bool $alreadyHashed If true, value is treated as hash
     */
    public function __construct(string $value, bool $alreadyHashed = false)
    {
        if ($alreadyHashed) {
            $this->hash = $value;
        } else {
            $this->hash = password_hash($value, PASSWORD_DEFAULT);
        }
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hash);
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}