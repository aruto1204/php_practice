<?php

declare(strict_types=1);

namespace App\Phase4\TodoApp;

/**
 * カテゴリーエンティティ
 */
class Category
{
    public function __construct(
        private readonly int $id,
        private string $name,
        private string $color,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException("カテゴリー名は空にできません");
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException("カテゴリー名は空にできません");
        }
        $this->name = $name;
    }

    public function updateColor(string $color): void
    {
        $this->color = $color;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
