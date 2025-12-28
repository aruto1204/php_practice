<?php

declare(strict_types=1);

namespace App\Phase4\TodoApp;

/**
 * タスクエンティティ
 */
class Task
{
    private bool $completed = false;
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct(
        private readonly int $id,
        private string $title,
        private string $description,
        private ?int $categoryId,
        private ?\DateTimeImmutable $dueDate,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException("タイトルは空にできません");
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function updateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new \InvalidArgumentException("タイトルは空にできません");
        }
        $this->title = $title;
    }

    public function updateDescription(string $description): void
    {
        $this->description = $description;
    }

    public function updateCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function updateDueDate(?\DateTimeImmutable $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function complete(): void
    {
        if (!$this->completed) {
            $this->completed = true;
            $this->completedAt = new \DateTimeImmutable();
        }
    }

    public function uncomplete(): void
    {
        if ($this->completed) {
            $this->completed = false;
            $this->completedAt = null;
        }
    }

    public function isOverdue(): bool
    {
        if ($this->completed || $this->dueDate === null) {
            return false;
        }
        return $this->dueDate < new \DateTimeImmutable();
    }

    public function getDaysUntilDue(): ?int
    {
        if ($this->dueDate === null) {
            return null;
        }
        $now = new \DateTimeImmutable();
        return (int)$now->diff($this->dueDate)->format('%r%a');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'completed' => $this->completed,
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
