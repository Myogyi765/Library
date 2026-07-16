<?php

namespace App\User\Infrastructure\Persistence;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\Phone;
use App\User\Domain\ValueObject\UserStatus;
use DateTime;
use PDO;
use Exception;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public function save(User $user): User
    {
        if ($user->getId() === null) {
            return $this->insert($user);
        }
        return $this->update($user);
    }
    
    private function insert(User $user): User
    {
        $sql = "INSERT INTO users (
            name, email, phone, password_hash, role_id, role, status,
            email_verified, phone_verified, login_method,
            email_verification_token, phone_verification_code,
            verification_code_expires_at, email_verified_at, phone_verified_at,
            profile_image, created_at, updated_at
        ) VALUES (
            :name, :email, :phone, :password_hash, :role_id, :role, :status,
            :email_verified, :phone_verified, :login_method,
            :email_verification_token, :phone_verification_code,
            :verification_code_expires_at, :email_verified_at, :phone_verified_at,
            :profile_image, :created_at, :updated_at
        )";
        
        $stmt = $this->db->prepare($sql);
        $passwordHash = $user->getPassword()->getHash();
        
        $params = [
            ':name' => $user->getName(),
            ':email' => $user->getEmail()->getValue(),
            ':phone' => $user->getPhone()?->getValue(),
            ':password_hash' => $passwordHash,
            ':role_id' => $user->getRoleId(),
            ':role' => $user->getRole() ?? 'user',
            ':status' => $user->getStatus()->getValue(),
            ':email_verified' => $user->isEmailVerified() ? 1 : 0,
            ':phone_verified' => $user->isPhoneVerified() ? 1 : 0,
            ':login_method' => $user->getLoginMethod(),
            ':email_verification_token' => $user->getVerificationToken(),
            ':phone_verification_code' => $user->getVerificationCode(),
            ':verification_code_expires_at' => $user->getVerificationExpiresAt(),
            ':email_verified_at' => $user->getEmailVerifiedAt()?->format('Y-m-d H:i:s'),
            ':phone_verified_at' => $user->getPhoneVerifiedAt()?->format('Y-m-d H:i:s'),
            ':profile_image' => $user->getProfileImage(), // ✅ Added
            ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
        
        $stmt->execute($params);
        $id = (int)$this->db->lastInsertId();
        return $this->findById($id);
    }
    
    private function update(User $user): User
    {
        $sql = "UPDATE users SET
            name = :name,
            email = :email,
            phone = :phone,
            password_hash = :password_hash,
            role_id = :role_id,
            role = :role,
            status = :status,
            email_verified = :email_verified,
            phone_verified = :phone_verified,
            login_method = :login_method,
            email_verification_token = :email_verification_token,
            phone_verification_code = :phone_verification_code,
            verification_code_expires_at = :verification_code_expires_at,
            email_verified_at = :email_verified_at,
            phone_verified_at = :phone_verified_at,
            profile_image = :profile_image,
            remember_token = :remember_token,
            updated_at = :updated_at,
            last_login_at = :last_login_at
        WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $passwordHash = $user->getPassword()->getHash();
        
        $params = [
            ':id' => $user->getId(),
            ':name' => $user->getName(),
            ':email' => $user->getEmail()->getValue(),
            ':phone' => $user->getPhone()?->getValue(),
            ':password_hash' => $passwordHash,
            ':role_id' => $user->getRoleId(),
            ':role' => $user->getRole() ?? 'user',
            ':status' => $user->getStatus()->getValue(),
            ':email_verified' => $user->isEmailVerified() ? 1 : 0,
            ':phone_verified' => $user->isPhoneVerified() ? 1 : 0,
            ':login_method' => $user->getLoginMethod(),
            ':email_verification_token' => $user->getVerificationToken(),
            ':phone_verification_code' => $user->getVerificationCode(),
            ':verification_code_expires_at' => $user->getVerificationExpiresAt(),
            ':email_verified_at' => $user->getEmailVerifiedAt()?->format('Y-m-d H:i:s'),
            ':phone_verified_at' => $user->getPhoneVerifiedAt()?->format('Y-m-d H:i:s'),
            ':profile_image' => $user->getProfileImage(),
            ':remember_token' => $user->getRememberToken(),
            ':updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            ':last_login_at' => $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
        ];
        
        $stmt->execute($params);
        return $user;
    }
    
    public function findById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            error_log("❌ User ID {$id} not found in database.");
            return null;
        }
        try {
            return $this->hydrate($data);
        } catch (Exception $e) {
            error_log("❌ Hydration failed for user ID {$id}: " . $e->getMessage());
            return null;
        }
    }
    
    public function findByEmail(Email $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email->getValue()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        try {
            return $this->hydrate($data);
        } catch (Exception $e) {
            error_log("❌ Hydration failed for email {$email->getValue()}: " . $e->getMessage());
            return null;
        }
    }
    
    public function findByPhone(Phone $phone): ?User
    {
        $sql = "SELECT * FROM users WHERE phone = :phone";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':phone' => $phone->getValue()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        try {
            return $this->hydrate($data);
        } catch (Exception $e) {
            error_log("❌ Hydration failed for phone {$phone->getValue()}: " . $e->getMessage());
            return null;
        }
    }
    
    public function findByIdentifier(string $identifier): ?User
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->findByEmail(new Email($identifier));
        } else {
            try {
                return $this->findByPhone(new Phone($identifier));
            } catch (Exception $e) {
                return null;
            }
        }
    }
    
    public function findByRememberToken(string $token): ?User
    {
        $sql = "SELECT * FROM users WHERE remember_token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        try {
            return $this->hydrate($data);
        } catch (Exception $e) {
            error_log("❌ Hydration failed for remember token: " . $e->getMessage());
            return null;
        }
    }
    
    public function findByEmailVerificationToken(string $token): ?User
    {
        $sql = "SELECT * FROM users WHERE email_verification_token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        try {
            return $this->hydrate($data);
        } catch (Exception $e) {
            error_log("❌ Hydration failed for email verification token: " . $e->getMessage());
            return null;
        }
    }
    
    public function findByPhoneVerificationCode(string $code): ?User
    {
        $sql = "SELECT * FROM users WHERE phone_verification_code = :code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        try {
            return $this->hydrate($data);
        } catch (Exception $e) {
            error_log("❌ Hydration failed for phone verification code: " . $e->getMessage());
            return null;
        }
    }
    
    public function emailExists(Email $email): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email->getValue()]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    public function phoneExists(Phone $phone): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE phone = :phone";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':phone' => $phone->getValue()]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    public function updateRememberToken(int $userId, ?string $token): void
    {
        $sql = "UPDATE users SET remember_token = :token, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token, ':id' => $userId]);
    }
    
    public function updateLastLogin(int $userId): void
    {
        $sql = "UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
    }
    
    public function getRoleIdByName(string $roleName): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = :name");
        $stmt->execute([':name' => $roleName]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }
    
    public function findAll(): array
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("📚 [UserRepository] findAll() fetched " . count($results) . " rows from DB");
        
        $users = [];
        foreach ($results as $data) {
            try {
                $user = $this->hydrate($data);
                $users[] = $user;
                error_log("✅ Hydrated user ID: " . $user->getId() . " - " . $user->getName());
            } catch (Exception $e) {
                error_log("❌ Hydration failed for user ID " . ($data['id'] ?? 'unknown') . ": " . $e->getMessage());
            }
        }
        
        error_log("📚 [UserRepository] Successfully hydrated " . count($users) . " users");
        return $users;
    }
    
    public function findByRole(string $roleName): array
    {
        $sql = "SELECT u.* 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE r.name = :role_name
                ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':role_name' => $roleName]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $users = [];
        foreach ($results as $data) {
            try {
                $users[] = $this->hydrate($data);
            } catch (Exception $e) {
                error_log("❌ Hydration failed for user ID " . ($data['id'] ?? 'unknown') . " in findByRole: " . $e->getMessage());
            }
        }
        return $users;
    }
    
    public function findByEmailString(string $email): ?User
    {
        return $this->findByEmail(new Email($email));
    }
    
    public function findByPhoneString(string $phone): ?User
    {
        try {
            return $this->findByPhone(new Phone($phone));
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function getAllRoles(): array
    {
        $sql = "SELECT name FROM roles ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Hydrate a User object from database row
     * 
     * @throws Exception If hydration fails
     */
    private function hydrate(array $data): User
    {
        $createdAt = !empty($data['created_at']) ? new DateTime($data['created_at']) : new DateTime();
        $updatedAt = !empty($data['updated_at']) ? new DateTime($data['updated_at']) : new DateTime();
        $lastLoginAt = !empty($data['last_login_at']) ? new DateTime($data['last_login_at']) : null;
        $emailVerifiedAt = !empty($data['email_verified_at']) ? new DateTime($data['email_verified_at']) : null;
        $phoneVerifiedAt = !empty($data['phone_verified_at']) ? new DateTime($data['phone_verified_at']) : null;
        $verificationExpiresAt = !empty($data['verification_code_expires_at']) ? $data['verification_code_expires_at'] : null;
        
        $roleName = $data['role'] ?? 'user';
        $roleId = $data['role_id'] ?? null;
        
        // ✅ Safe status creation
        try {
            $status = UserStatus::fromString($data['status'] ?? 'pending');
        } catch (Exception $e) {
            error_log("⚠️ Invalid status '{$data['status']}' for user ID {$data['id']}, defaulting to 'pending'. Error: " . $e->getMessage());
            $status = UserStatus::pending();
        }
        
        // ✅ Safe email creation (email should always exist)
        $email = new Email($data['email']);
        
        // ✅ Safe phone creation – handle null values
        $phone = null;
        if (!empty($data['phone'])) {
            try {
                $phone = new Phone($data['phone']);
            } catch (Exception $e) {
                error_log("⚠️ Invalid phone '{$data['phone']}' for user ID {$data['id']}, setting to null. Error: " . $e->getMessage());
            }
        }
        
        // ✅ Password is already hashed
        $password = new Password($data['password_hash'], true);
        
        return new User(
            (int)$data['id'],
            $data['name'],
            $email,
            $phone,
            $password,
            $status,
            $roleId,
            $roleName,
            (bool)($data['email_verified'] ?? false),
            (bool)($data['phone_verified'] ?? false),
            $data['login_method'] ?? 'email',
            $data['remember_token'] ?? null,
            $createdAt,
            $updatedAt,
            $lastLoginAt,
            $data['email_verification_token'] ?? null,
            $data['phone_verification_code'] ?? null,
            $verificationExpiresAt,
            $emailVerifiedAt,
            $phoneVerifiedAt,
            $data['profile_image'] ?? null // ✅ Added profile_image
        );
    }


    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int)$stmt->fetchColumn();
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
        $stmt->execute([':role' => $role]);
        return (int)$stmt->fetchColumn();
    }
}