<?php

declare(strict_types=1);

namespace App\Phase4\TodoApp;

/**
 * カテゴリーリポジトリ（データアクセス層）
 */
class CategoryRepository
{
    /** @var Category[] */
    private array $categories = [];
    private int $nextId = 1;

    public function save(Category $category): void
    {
        $this->categories[$category->getId()] = $category;
    }

    public function findById(int $id): ?Category
    {
        return $this->categories[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->categories);
    }

    public function findByName(string $name): ?Category
    {
        foreach ($this->categories as $category) {
            if ($category->getName() === $name) {
                return $category;
            }
        }
        return null;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->categories[$id])) {
            return false;
        }
        unset($this->categories[$id]);
        return true;
    }

    public function getNextId(): int
    {
        return $this->nextId++;
    }

    public function count(): int
    {
        return count($this->categories);
    }

    public function clear(): void
    {
        $this->categories = [];
        $this->nextId = 1;
    }
}
