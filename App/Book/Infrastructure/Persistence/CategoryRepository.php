<?php
namespace App\Book\Infrastructure\Persistence;

use App\Book\Domain\Entity\Category;
use App\Book\Domain\Repository\CategoryRepositoryInterface;
use PDO;

class CategoryRepository implements CategoryRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM categories ORDER BY name";
        $stmt = $this->db->query($sql);
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $this->hydrate($row);
        }
        return $categories;
    }

    public function findById(int $id): ?Category
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) return null;
        return $this->hydrate($data);
    }

    private function hydrate(array $data): Category
    {
        return new Category(
            (int)$data['id'],
            $data['name'],
            $data['description'] ?? null,
            new \DateTimeImmutable($data['created_at']),
            new \DateTimeImmutable($data['updated_at'])
        );
    }

    public function findByName(string $name): ?Category
{
    $sql = "SELECT * FROM categories WHERE name = :name";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':name' => $name]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) return null;
    return $this->hydrate($data);
}

public function save(Category $category): Category
{
    if ($category->getId() === null) {
        return $this->insert($category);
    }
    return $this->update($category);
}

private function insert(Category $category): Category
{
    $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':name' => $category->getName(),
        ':description' => $category->getDescription(),
    ]);
    $category->setId((int)$this->db->lastInsertId());
    return $category;
}

private function update(Category $category): Category
{
    $sql = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':id' => $category->getId(),
        ':name' => $category->getName(),
        ':description' => $category->getDescription(),
    ]);
    return $category;
}

public function delete(int $id): void
{
    $sql = "DELETE FROM categories WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
}
}