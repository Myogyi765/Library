<?php

namespace App\Circulation\Infrastructure\Persistence;

use App\Circulation\Domain\Entity\Loan;
use App\Circulation\Domain\Repository\LoanRepositoryInterface;
use App\Circulation\Infrastructure\Mapper\LoanMapper;
use PDO;

class LoanRepository implements LoanRepositoryInterface
{
    private PDO $db;
    private LoanMapper $mapper;

    public function __construct(PDO $db, LoanMapper $mapper)
    {
        $this->db = $db;
        $this->mapper = $mapper;
    }

    public function save(Loan $loan): void
    {
        $data = $this->mapper->toPersistence($loan);

        if ($loan->getId()) {
            $sql = "UPDATE loans SET 
                        user_id = :user_id, 
                        book_id = :book_id, 
                        status = :status, 
                        borrowed_at = :borrowed_at, 
                        due_date = :due_date, 
                        returned_at = :returned_at
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $loan->getId(),
                ':user_id' => $data['user_id'],
                ':book_id' => $data['book_id'],
                ':status' => $data['status'],
                ':borrowed_at' => $data['borrowed_at'],
                ':due_date' => $data['due_date'],
                ':returned_at' => $data['returned_at'],
            ]);
        } else {
            $sql = "INSERT INTO loans (user_id, book_id, status, borrowed_at, due_date, returned_at)
                    VALUES (:user_id, :book_id, :status, :borrowed_at, :due_date, :returned_at)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':book_id' => $data['book_id'],
                ':status' => $data['status'],
                ':borrowed_at' => $data['borrowed_at'],
                ':due_date' => $data['due_date'],
                ':returned_at' => $data['returned_at'],
            ]);
            $loan->setId((int)$this->db->lastInsertId());
        }
    }

    public function findById(int $id): ?Loan
    {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findByUserAndBook(int $userId, int $bookId): ?Loan
    {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE user_id = :user_id AND book_id = :book_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findActiveOrPendingByUserAndBook(int $userId, int $bookId): ?Loan
    {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE user_id = :user_id AND book_id = :book_id AND status IN ('pending', 'active', 'awaiting_payment') ORDER BY id DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findPendingLoans(): array
    {
        $stmt = $this->db->query("SELECT * FROM loans WHERE status = 'pending' ORDER BY id DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    public function findLoansByUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM loans ORDER BY id DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("📚 [LoanRepository] findAll() returned " . count($rows) . " loans");
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    
    public function findActiveByUserId(int $userId): array
    {
        $sql = "SELECT * FROM loans 
                WHERE user_id = :user_id 
                AND status IN ('active', 'awaiting_payment', 'pending')
                ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM loans WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM loans");
        return (int)$stmt->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM loans WHERE status = :status");
        $stmt->execute([':status' => $status]);
        return (int)$stmt->fetchColumn();
    }

    public function countOverdue(): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM loans WHERE status = 'active' AND due_date < :now");
        $stmt->execute([':now' => $now]);
        return (int)$stmt->fetchColumn();
    }

    public function findRecent(int $limit): array
    {
        $stmt = $this->db->prepare("SELECT * FROM loans ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    public function findByUserId(int $userId): array
{
    $sql = "SELECT * FROM loans WHERE user_id = :user_id ORDER BY id DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    $rows = $stmt->fetchAll();
    $loans = [];
    foreach ($rows as $row) {
        $loans[] = $this->mapper->toEntity($row);
    }
    return $loans;
}
}
