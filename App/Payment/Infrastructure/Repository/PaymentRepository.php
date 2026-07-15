<?php
namespace App\Payment\Infrastructure\Repository;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\Repository\PaymentRepositoryInterface;
use App\Payment\Domain\ValueObject\Money;
use App\Payment\Domain\ValueObject\PaymentStatus;
use App\Payment\Infrastructure\Mapper\PaymentMapper;
use PDO;

class PaymentRepository implements PaymentRepositoryInterface
{
    private PDO $db;
    private PaymentMapper $mapper;

    public function __construct(PDO $db, PaymentMapper $mapper)
    {
        $this->db = $db;
        $this->mapper = $mapper;
    }

    public function save(Payment $payment): void
    {
        $data = $this->mapper->toPersistence($payment);

        if ($payment->getId()) {
            // ✅ UPDATE with refund fields
            $sql = "UPDATE payments SET 
                loan_id = :loan_id,
                user_id = :user_id,
                amount = :amount,
                currency = :currency,
                status = :status,
                payment_method = :payment_method,
                transaction_reference = :transaction_reference,
                screenshot_path = :screenshot_path,
                submitted_at = :submitted_at,
                approved_at = :approved_at,
                rejected_at = :rejected_at,
                idempotency_key = :idempotency_key,
                refund_status = :refund_status,
                refunded_at = :refunded_at,
                refund_reason = :refund_reason
                WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $data['id'],
                ':loan_id' => $data['loan_id'],
                ':user_id' => $data['user_id'],
                ':amount' => $data['amount'],
                ':currency' => $data['currency'],
                ':status' => $data['status'],
                ':payment_method' => $data['payment_method'],
                ':transaction_reference' => $data['transaction_reference'],
                ':screenshot_path' => $data['screenshot_path'],
                ':submitted_at' => $data['submitted_at'],
                ':approved_at' => $data['approved_at'],
                ':rejected_at' => $data['rejected_at'],
                ':idempotency_key' => $data['idempotency_key'],
                ':refund_status' => $data['refund_status'],
                ':refunded_at' => $data['refunded_at'],
                ':refund_reason' => $data['refund_reason'],
            ]);
        } else {
            // ✅ INSERT with refund fields
            $sql = "INSERT INTO payments 
                (loan_id, user_id, amount, currency, status, payment_method, transaction_reference, 
                 screenshot_path, submitted_at, approved_at, rejected_at, idempotency_key,
                 refund_status, refunded_at, refund_reason)
                VALUES 
                (:loan_id, :user_id, :amount, :currency, :status, :payment_method, :transaction_reference,
                 :screenshot_path, :submitted_at, :approved_at, :rejected_at, :idempotency_key,
                 :refund_status, :refunded_at, :refund_reason)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':loan_id' => $data['loan_id'],
                ':user_id' => $data['user_id'],
                ':amount' => $data['amount'],
                ':currency' => $data['currency'],
                ':status' => $data['status'],
                ':payment_method' => $data['payment_method'],
                ':transaction_reference' => $data['transaction_reference'],
                ':screenshot_path' => $data['screenshot_path'],
                ':submitted_at' => $data['submitted_at'],
                ':approved_at' => $data['approved_at'],
                ':rejected_at' => $data['rejected_at'],
                ':idempotency_key' => $data['idempotency_key'],
                ':refund_status' => $data['refund_status'],
                ':refunded_at' => $data['refunded_at'],
                ':refund_reason' => $data['refund_reason'],
            ]);
            $payment->setId((int)$this->db->lastInsertId());
        }
    }

    public function findById(int $id): ?Payment
    {
        $sql = "SELECT * FROM payments WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findByLoanId(int $loanId): ?Payment
    {
        $sql = "SELECT * FROM payments WHERE loan_id = :loan_id ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':loan_id' => $loanId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findPendingApprovals(): array
    {
        $sql = "SELECT * FROM payments WHERE status = 'pending_approval' ORDER BY submitted_at ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM payments ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    public function findByIdempotencyKey(string $key): ?Payment
    {
        $sql = "SELECT * FROM payments WHERE idempotency_key = :key LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    /**
     * Find payments by user ID
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM payments 
            WHERE user_id = :user_id 
            ORDER BY submitted_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payments = [];
        foreach ($rows as $row) {
            $payments[] = $this->mapper->toDomain($row);
        }
        return $payments;
    }

    // App/Payment/Infrastructure/Repository/PaymentRepository.php
public function findPendingApprovalsWithDetails(): array
{
    $sql = "
        SELECT 
            p.*,
            u.name AS user_name,
            u.email AS user_email,
            b.title AS book_title,
            b.id AS book_id
        FROM payments p
        JOIN users u ON p.user_id = u.id
        JOIN loans l ON p.loan_id = l.id
        JOIN books b ON l.book_id = b.id
        WHERE p.status = :status
        ORDER BY p.submitted_at ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':status' => 'pending_approval']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert each row to a Payment entity (the mapper will ignore extra fields)
    return array_map([$this->mapper, 'toDomain'], $rows);
}


public function findAllWithDetails(): array
{
    $sql = "
        SELECT p.*,
               u.name AS user_name,
               u.email AS user_email,
               b.title AS book_title,
               b.id AS book_id
        FROM payments p
        JOIN users u ON p.user_id = u.id
        JOIN loans l ON p.loan_id = l.id
        JOIN books b ON l.book_id = b.id
        ORDER BY p.submitted_at DESC
    ";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function findByStatusWithDetails(string $status): array
{
    $sql = "
        SELECT p.*,
               u.name AS user_name,
               u.email AS user_email,
               b.title AS book_title,
               b.id AS book_id
        FROM payments p
        JOIN users u ON p.user_id = u.id
        JOIN loans l ON p.loan_id = l.id
        JOIN books b ON l.book_id = b.id
        WHERE p.status = :status
        ORDER BY p.submitted_at DESC
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['status' => $status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}