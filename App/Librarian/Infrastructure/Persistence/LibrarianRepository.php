<?php
namespace App\Librarian\Infrastructure\Persistence;

use App\Librarian\Domain\Entity\Librarian;
use App\Librarian\Domain\Repository\LibrarianRepositoryInterface;
use App\Librarian\Domain\ValueObject\Email;
use App\Librarian\Domain\ValueObject\Password;
use App\Librarian\Domain\ValueObject\Department;
use PDO;

class LibrarianRepository implements LibrarianRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $sql = "SELECT u.* 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'librarian'
                ORDER BY u.created_at DESC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'mapRowToLibrarian'], $rows);
    }

    public function findByEmail(Email $email): ?Librarian
    {
        $sql = "SELECT u.* 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email AND r.name = 'librarian'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToLibrarian($row) : null;
    }

    public function findById(int $id): ?Librarian
    {
        $sql = "SELECT u.* 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id AND r.name = 'librarian'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToLibrarian($row) : null;
    }

   public function save(Librarian $librarian): void
{
    if ($librarian->getId()) {

        $sql = "UPDATE users SET
            name = :name,
            email = :email,
            password_hash = :password,
            role = 'librarian',
            department = :department,
            last_login_at = :last_login
            WHERE id = :id
            AND role_id = (SELECT id FROM roles WHERE name = 'librarian')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $librarian->getName(),
            ':email' => $librarian->getEmail()->getValue(),
            ':password' => $librarian->getPassword()->getHash(),
            ':department' => $librarian->getDepartment()->getValue(),
            ':last_login' => $librarian->getLastLogin()
                ? $librarian->getLastLogin()->format('Y-m-d H:i:s')
                : null,
            ':id' => $librarian->getId()
        ]);

    } else {

        $this->db->beginTransaction();

        try {

            $sql = "INSERT INTO users (
                name,
                email,
                password_hash,
                role,
                role_id,
                department,
                status,
                email_verified,
                phone_verified,
                login_method,
                created_at,
                updated_at
            ) VALUES (
                :name,
                :email,
                :password,
                'librarian',
                (SELECT id FROM roles WHERE name = 'librarian'),
                :department,
                'active',
                1,
                0,
                'email',
                NOW(),
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $librarian->getName(),
                ':email' => $librarian->getEmail()->getValue(),
                ':password' => $librarian->getPassword()->getHash(),
                ':department' => $librarian->getDepartment()->getValue()
            ]);

            $userId = (int)$this->db->lastInsertId();
            $librarian->setId($userId);

            $this->assignLibrarianRole($userId);

            $this->db->commit();

        } catch (\Exception $e) {

            $this->db->rollBack();
            throw $e;
        }
    }
}
    
    private function assignLibrarianRole(int $userId): void
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = 'librarian'");
        $stmt->execute();
        $roleId = $stmt->fetchColumn();

        if (!$roleId) {
            throw new \RuntimeException("Role 'librarian' not found in roles table.");
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_roles WHERE user_id = :user_id AND role_id = :role_id");
        $stmt->execute([
            ':user_id' => $userId,
            ':role_id' => $roleId
        ]);
        $exists = (int)$stmt->fetchColumn();

        if ($exists === 0) {
            $stmt = $this->db->prepare("INSERT INTO user_roles (user_id, role_id, created_at) VALUES (:user_id, :role_id, NOW())");
            $stmt->execute([
                ':user_id' => $userId,
                ':role_id' => $roleId
            ]);
        }
    }

    public function delete(Librarian $librarian): void
    {
        $sql = "DELETE FROM users 
                WHERE id = :id 
                AND role_id = (SELECT id FROM roles WHERE name = 'librarian')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $librarian->getId()]);
    }

    private function mapRowToLibrarian(array $row): Librarian
    {
        $department = $row['department'] ?? 'General';
        $librarian = new Librarian(
            $row['name'],
            new Email($row['email']),
            Password::fromHash($row['password_hash']),
            new Department($department)
        );
        $librarian->setId((int)$row['id']);
        if (!empty($row['created_at'])) {
            $librarian->setHiredAt(new \DateTimeImmutable($row['created_at']));
        }
        if (!empty($row['last_login_at'])) {
            $librarian->setLastLogin(new \DateTimeImmutable($row['last_login_at']));
        }
        return $librarian;
    }
}
