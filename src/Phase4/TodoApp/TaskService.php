<?php

declare(strict_types=1);

namespace App\Phase4\TodoApp;

/**
 * タスクサービス（ビジネスロジック層）
 */
class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    /**
     * タスクを作成
     */
    public function createTask(
        string $title,
        string $description = '',
        ?int $categoryId = null,
        ?string $dueDate = null,
    ): Task {
        // カテゴリーの存在確認
        if ($categoryId !== null) {
            $category = $this->categoryRepository->findById($categoryId);
            if ($category === null) {
                throw new \InvalidArgumentException("カテゴリーID {$categoryId} が見つかりません");
            }
        }

        // 期限日の変換
        $dueDateObj = $dueDate ? new \DateTimeImmutable($dueDate) : null;

        // タスク作成
        $task = new Task(
            id: $this->taskRepository->getNextId(),
            title: $title,
            description: $description,
            categoryId: $categoryId,
            dueDate: $dueDateObj,
        );

        $this->taskRepository->save($task);
        return $task;
    }

    /**
     * タスクを更新
     */
    public function updateTask(
        int $id,
        ?string $title = null,
        ?string $description = null,
        ?int $categoryId = null,
        ?string $dueDate = null,
    ): Task {
        $task = $this->taskRepository->findById($id);
        if ($task === null) {
            throw new \RuntimeException("タスクID {$id} が見つかりません");
        }

        if ($title !== null) {
            $task->updateTitle($title);
        }

        if ($description !== null) {
            $task->updateDescription($description);
        }

        if ($categoryId !== null) {
            $category = $this->categoryRepository->findById($categoryId);
            if ($category === null) {
                throw new \InvalidArgumentException("カテゴリーID {$categoryId} が見つかりません");
            }
            $task->updateCategory($categoryId);
        }

        if ($dueDate !== null) {
            $dueDateObj = new \DateTimeImmutable($dueDate);
            $task->updateDueDate($dueDateObj);
        }

        $this->taskRepository->save($task);
        return $task;
    }

    /**
     * タスクを完了
     */
    public function completeTask(int $id): Task
    {
        $task = $this->taskRepository->findById($id);
        if ($task === null) {
            throw new \RuntimeException("タスクID {$id} が見つかりません");
        }

        $task->complete();
        $this->taskRepository->save($task);
        return $task;
    }

    /**
     * タスクを未完了に戻す
     */
    public function uncompleteTask(int $id): Task
    {
        $task = $this->taskRepository->findById($id);
        if ($task === null) {
            throw new \RuntimeException("タスクID {$id} が見つかりません");
        }

        $task->uncomplete();
        $this->taskRepository->save($task);
        return $task;
    }

    /**
     * タスクを削除
     */
    public function deleteTask(int $id): bool
    {
        return $this->taskRepository->delete($id);
    }

    /**
     * タスクを取得
     */
    public function getTask(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    /**
     * すべてのタスクを取得
     */
    public function getAllTasks(): array
    {
        return $this->taskRepository->findAll();
    }

    /**
     * カテゴリー別タスクを取得
     */
    public function getTasksByCategory(int $categoryId): array
    {
        return $this->taskRepository->findByCategory($categoryId);
    }

    /**
     * 完了タスクを取得
     */
    public function getCompletedTasks(): array
    {
        return $this->taskRepository->findCompleted();
    }

    /**
     * 未完了タスクを取得
     */
    public function getIncompleteTasks(): array
    {
        return $this->taskRepository->findIncomplete();
    }

    /**
     * 期限切れタスクを取得
     */
    public function getOverdueTasks(): array
    {
        return $this->taskRepository->findOverdue();
    }

    /**
     * 統計情報を取得
     */
    public function getStatistics(): array
    {
        $all = $this->taskRepository->findAll();
        $completed = $this->taskRepository->findCompleted();
        $incomplete = $this->taskRepository->findIncomplete();
        $overdue = $this->taskRepository->findOverdue();

        return [
            'total' => count($all),
            'completed' => count($completed),
            'incomplete' => count($incomplete),
            'overdue' => count($overdue),
            'completion_rate' => count($all) > 0
                ? round((count($completed) / count($all)) * 100, 1)
                : 0,
        ];
    }
}
