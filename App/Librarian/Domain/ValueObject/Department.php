<?php
namespace App\Librarian\Domain\ValueObject;

class Department
{
    private string $value;

    private const ALLOWED = [
        'General',       
        'Fiction',
        'Non-Fiction',
        'Science',
        'History',
       
    ];

    public function __construct(string $value)
    {
        $value = trim($value);
        if (!in_array($value, self::ALLOWED)) {
            throw new \InvalidArgumentException('Invalid department: ' . $value);
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}