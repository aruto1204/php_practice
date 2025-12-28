<?php

declare(strict_types=1);

namespace App\Phase4\TodoApp;

/**
 * タスクリポジトリ（データアクセス層）
 */
class TaskRepository
{
    /** @var Task[] */
    private array $tasks = [];
    private int $nextId = 1;

    public function save(Task $task): void
    {
        $this->tasks[$task->getId()] = $task;
    }

    public function findById(int $id): ?Task
    {
        return $this->tasks[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->tasks);
    }

    public function findByCategory(int $categoryId): array
    {
        return array_values(array_filter(
            $this->tasks,
            fn(Task $task) => $task->getCategoryId() === $categoryId
        ));
    }

    public function findCompleted(): array
    {
        return array_values(array_filter(
            $this->tasks,
            fn(Task $task) => $task->isCompleted()
        ));
    }

    public function findIncomplete(): array
    {
        return array_values(array_filter(
            $this->tasks,
            fn(Task $task) => !$task->isCompleted()
        ));
    }

    public function findOverdue(): array
    {
        return array_values(array_filter(
            $this->tasks,
            fn(Task $task) => $task->isOverdue()
        ));
    }

    public function delete(int $id): bool
    {
        if (!isset($this->tasks[$id])) {
            return false;
        }
        unset($this->tasks[$id]);
        return true;
    }

    public function getNextId(): int
    {
        return $this->nextId++;
    }

    public function count(): int
    {
        return count($this->tasks);
    }

    public function clear(): void
    {
        $this->tasks = [];
        $this->nextId = 1;
    }
}
