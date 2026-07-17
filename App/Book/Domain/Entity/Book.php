<?php
namespace App\Book\Domain\Entity;

class Book
{
    private ?int $id;
    private string $title;
    private string $author;
    private ?string $isbn;
    private int $categoryId;
    private ?string $description;
    private ?string $coverImage;
    private int $quantity;
    private int $availableQuantity;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        string $title,
        string $author,
        ?string $isbn,
        int $categoryId,
        ?string $description,
        ?string $coverImage,
        int $quantity = 1,
        int $availableQuantity = 1,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->categoryId = $categoryId;
        $this->description = $description;
        $this->coverImage = $coverImage;
        $this->quantity = $quantity;
        $this->availableQuantity = $availableQuantity;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): string { return $this->author; }
    public function getIsbn(): ?string { return $this->isbn; }
    public function getCategoryId(): int { return $this->categoryId; }
    public function getDescription(): ?string { return $this->description; }
    public function getCoverImage(): ?string { return $this->coverImage; }
    public function getQuantity(): int { return $this->quantity; }
    public function getAvailableQuantity(): int { return $this->availableQuantity; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function setAuthor(string $author): self { $this->author = $author; return $this; }
    public function setIsbn(?string $isbn): self { $this->isbn = $isbn; return $this; }
    public function setCategoryId(int $categoryId): self { $this->categoryId = $categoryId; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setCoverImage(?string $coverImage): self { $this->coverImage = $coverImage; return $this; }
    public function setQuantity(int $quantity): self { $this->quantity = $quantity; return $this; }
    public function setAvailableQuantity(int $availableQuantity): self { $this->availableQuantity = $availableQuantity; return $this; }

    public function decreaseAvailableQuantity(): void
{
    if ($this->availableQuantity > 0) {
        $this->availableQuantity--;
    } else {
        throw new \Exception('No copies available to borrow.');
    }
}

public function increaseAvailableQuantity(): void
{
    $this->availableQuantity++;
}
}