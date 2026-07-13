<?php

namespace App\Invoice\Infrastructure\Persistence;

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoice\Domain\ValueObject\InvoiceStatus;
use App\Invoice\Infrastructure\Mapper\InvoiceMapper;
use PDO;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    private PDO $db;
    private InvoiceMapper $mapper;

    public function __construct(PDO $db, InvoiceMapper $mapper)
    {
        $this->db = $db;
        $this->mapper = $mapper;
    }

    public function save(Invoice $invoice): void
    {
        $data = $this->mapper->toPersistence($invoice);

        if ($invoice->getId()) {
            $sql = "UPDATE invoices SET 
                        invoice_number = :invoice_number,
                        payment_id = :payment_id,
                        loan_id = :loan_id,
                        user_id = :user_id,
                        book_id = :book_id,
                        amount = :amount,
                        currency = :currency,
                        payment_method = :payment_method,
                        transaction_reference = :transaction_reference,
                        borrowed_at = :borrowed_at,
                        due_date = :due_date,
                        status = :status
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $invoice->getId(),
                ':invoice_number' => $data['invoice_number'],
                ':payment_id' => $data['payment_id'],
                ':loan_id' => $data['loan_id'],
                ':user_id' => $data['user_id'],
                ':book_id' => $data['book_id'],
                ':amount' => $data['amount'],
                ':currency' => $data['currency'],
                ':payment_method' => $data['payment_method'],
                ':transaction_reference' => $data['transaction_reference'],
                ':borrowed_at' => $data['borrowed_at'],
                ':due_date' => $data['due_date'],
                ':status' => $data['status'],
            ]);
        } else {
            $sql = "INSERT INTO invoices (
                        invoice_number, payment_id, loan_id, user_id, book_id,
                        amount, currency, payment_method, transaction_reference,
                        borrowed_at, due_date, status
                    ) VALUES (
                        :invoice_number, :payment_id, :loan_id, :user_id, :book_id,
                        :amount, :currency, :payment_method, :transaction_reference,
                        :borrowed_at, :due_date, :status
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':invoice_number' => $data['invoice_number'],
                ':payment_id' => $data['payment_id'],
                ':loan_id' => $data['loan_id'],
                ':user_id' => $data['user_id'],
                ':book_id' => $data['book_id'],
                ':amount' => $data['amount'],
                ':currency' => $data['currency'],
                ':payment_method' => $data['payment_method'],
                ':transaction_reference' => $data['transaction_reference'],
                ':borrowed_at' => $data['borrowed_at'],
                ':due_date' => $data['due_date'],
                ':status' => $data['status'],
            ]);
            $invoice->setId((int)$this->db->lastInsertId());
        }
    }

    public function findById(int $id): ?Invoice
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE invoice_number = :invoice_number");
        $stmt->execute([':invoice_number' => $invoiceNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findByPaymentId(int $paymentId): ?Invoice
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE payment_id = :payment_id");
        $stmt->execute([':payment_id' => $paymentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapper->toDomain($row) : null;
    }

    public function findByLoanId(int $loanId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE loan_id = :loan_id");
        $stmt->execute([':loan_id' => $loanId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this->mapper, 'toDomain'], $rows);
    }
}