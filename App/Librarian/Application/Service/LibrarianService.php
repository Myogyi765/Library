<?php
namespace App\Librarian\Application\Service;

use App\Librarian\Domain\Repository\LibrarianRepositoryInterface;
use App\Librarian\Domain\ValueObject\Email;
use App\Librarian\Domain\ValueObject\Password;
use App\Librarian\Domain\ValueObject\Department;
use App\Librarian\Application\DTO\RegisterDTO;

class LibrarianService
{
    private LibrarianRepositoryInterface $repo;

    public function __construct(LibrarianRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

   public function login(string $email, string $password): ?\App\Librarian\Domain\Entity\Librarian
{
    error_log("🔍 Librarian login attempt: email=$email");
    $librarian = $this->repo->findByEmail(new Email($email));
    if ($librarian) {
        error_log("✅ Librarian found: " . $librarian->getEmail()->getValue());
        if ($librarian->getPassword()->verify($password)) {
            error_log("✅ Password verified");
            return $librarian;
        } else {
            error_log("❌ Password mismatch");
        }
    } else {
        error_log("❌ Librarian not found in database");
    }
    return null;
}

    public function register(RegisterDTO $dto): \App\Librarian\Domain\Entity\Librarian
    {
        $librarian = new \App\Librarian\Domain\Entity\Librarian(
            $dto->name,
            new Email($dto->email),
            new Password($dto->password),
            new Department($dto->department)
        );
        $this->repo->save($librarian);
        return $librarian;
    }

    public function getAllLibrarians(): array
    {
        return $this->repo->findAll();
    }

    public function getLibrarian(int $id): ?\App\Librarian\Domain\Entity\Librarian
    {
        return $this->repo->findById($id);
    }

    public function updateLibrarian(int $id, string $name, string $department): void
    {
        $librarian = $this->repo->findById($id);
        if (!$librarian) {
            throw new \Exception('Librarian not found');
        }
        $librarian->setName($name);
        $librarian->setDepartment(new Department($department));
        $this->repo->save($librarian);
    }

    public function deleteLibrarian(int $id): void
    {
        $librarian = $this->repo->findById($id);
        if (!$librarian) {
            throw new \Exception('Librarian not found');
        }
        $this->repo->delete($librarian);
    }
}