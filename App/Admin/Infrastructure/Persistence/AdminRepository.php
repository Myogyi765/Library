<?php
namespace App\Admin\Infrastructure\Persistence;

use App\Admin\Domain\Entity\Admin;
use App\Admin\Domain\Repository\AdminRepositoryInterface;
use App\Admin\Domain\ValueObject\Email;
use App\Admin\Domain\ValueObject\Password;
use App\Admin\Domain\ValueObject\AdminRole;
use PDO;

class AdminRepository implements AdminRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    public function findByEmail(Email $email): ?Admin
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email AND role = "admin"');
        $stmt->execute([':email' => $email->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToAdmin($row) : null;
    }

    public function findById(int $id): ?Admin
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id AND role = "admin"');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToAdmin($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM users WHERE role = "admin"');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'mapRowToAdmin'], $rows);
    }


    public function save(Admin $admin): void
    {
        if ($admin->getId()) {
            $stmt = $this->db->prepare('UPDATE users SET name = :name, email = :email, password_hash = :password, last_login_at = :last_login WHERE id = :id AND role = "admin"');
            $stmt->execute([
                ':name' => $admin->getName(),
                ':email' => $admin->getEmail()->getValue(),
                ':password' => $admin->getPassword()->getHash(),
                ':last_login' => $admin->getLastLogin() ? $admin->getLastLogin()->format('Y-m-d H:i:s') : null,
                ':id' => $admin->getId()
            ]);
        } else {
            $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (:name, :email, :password, "admin", NOW())');
            $stmt->execute([
                ':name' => $admin->getName(),
                ':email' => $admin->getEmail()->getValue(),
                ':password' => $admin->getPassword()->getHash()
            ]);
            $admin->setId((int)$this->db->lastInsertId());
        }
    }

    public function delete(Admin $admin): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id AND role = "admin"');
        $stmt->execute([':id' => $admin->getId()]);
    }

    private function mapRowToAdmin(array $row): Admin
    {
        
        $admin = new Admin(
            $row['name'],
            new Email($row['email']),
            Password::fromHash($row['password_hash']),
            new AdminRole('admin')   
        );
        $admin->setId((int)$row['id']);
        if ($row['last_login_at']) {
            $admin->setLastLogin(new \DateTimeImmutable($row['last_login_at']));
        }
        return $admin;
    }
}