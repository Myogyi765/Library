<?php
namespace App\Book\Infrastructure\Persistence;

use App\Book\Domain\Entity\Book;
use App\Book\Domain\Repository\BookRepositoryInterface;
use PDO;

class BookRepository implements BookRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(Book $book): Book
    {
        if ($book->getId() === null) {
            return $this->insert($book);
        }
        return $this->update($book);
    }

    private function insert(Book $book): Book
    {
        $sql = "INSERT INTO books (title, author, isbn, category_id, description, cover_image, quantity, available_quantity)
                VALUES (:title, :author, :isbn, :category_id, :description, :cover_image, :quantity, :available_quantity)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title' => $book->getTitle(),
            ':author' => $book->getAuthor(),
            ':isbn' => $book->getIsbn(),
            ':category_id' => $book->getCategoryId(),
            ':description' => $book->getDescription(),
            ':cover_image' => $book->getCoverImage(),
            ':quantity' => $book->getQuantity(),
            ':available_quantity' => $book->getAvailableQuantity(),
        ]);
        $book->setId((int)$this->db->lastInsertId());
        return $book;
    }

    private function update(Book $book): Book
    {
        $sql = "UPDATE books SET
            title = :title,
            author = :author,
            isbn = :isbn,
            category_id = :category_id,
            description = :description,
            cover_image = :cover_image,
            quantity = :quantity,
            available_quantity = :available_quantity,
            updated_at = NOW()
            WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $book->getId(),
            ':title' => $book->getTitle(),
            ':author' => $book->getAuthor(),
            ':isbn' => $book->getIsbn(),
            ':category_id' => $book->getCategoryId(),
            ':description' => $book->getDescription(),
            ':cover_image' => $book->getCoverImage(),
            ':quantity' => $book->getQuantity(),
            ':available_quantity' => $book->getAvailableQuantity(),
        ]);
        return $book;
    }

    public function findById(int $id): ?Book
    {
        $sql = "SELECT * FROM books WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) return null;
        return $this->hydrate($data);
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM books ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        $books = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $books[] = $this->hydrate($row);
        }
        return $books;
    }

    public function findByCategory(int $categoryId): array
    {
        $sql = "SELECT * FROM books WHERE category_id = :category_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category_id' => $categoryId]);
        $books = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $books[] = $this->hydrate($row);
        }
        return $books;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM books WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    private function hydrate(array $data): Book
    {
        return new Book(
            (int)$data['id'],
            $data['title'],
            $data['author'],
            $data['isbn'] ?? null,
            (int)$data['category_id'],
            $data['description'] ?? null,
            $data['cover_image'] ?? null,
            (int)$data['quantity'],
            (int)$data['available_quantity'],
            new \DateTimeImmutable($data['created_at']),
            new \DateTimeImmutable($data['updated_at'])
        );
    }

    // ✅ New method: decrement available quantity with safety check
    public function decrementQuantity(int $bookId, int $amount = 1): void
    {
        $sql = "UPDATE books 
                SET available_quantity = available_quantity - :amount 
                WHERE id = :book_id AND available_quantity >= :amount";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':amount' => $amount,
            ':book_id' => $bookId
        ]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Not enough copies available for this book.');
        }
    }
}