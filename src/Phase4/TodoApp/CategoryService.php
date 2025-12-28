<?php

declare(strict_types=1);

namespace App\Phase4\TodoApp;

/**
 * カテゴリーサービス（ビジネスロジック層）
 */
class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly TaskRepository $taskRepository,
    ) {}

    /**
     * カテゴリーを作成
     */
    public function createCategory(string $name, string $color = '#3B82F6'): Category
    {
        // 重複チェック
        $existing = $this->categoryRepository->findByName($name);
        if ($existing !== null) {
            throw new \RuntimeException("カテゴリー「{$name}」は既に存在します");
        }

        $category = new Category(
            id: $this->categoryRepository->getNextId(),
            name: $name,
            color: $color,
        );

        $this->categoryRepository->save($category);
        return $category;
    }

    /**
     * カテゴリーを更新
     */
    public function updateCategory(int $id, ?string $name = null, ?string $color = null): Category
    {
        $category = $this->categoryRepository->findById($id);
        if ($category === null) {
            throw new \RuntimeException("カテゴリーID {$id} が見つかりません");
        }

        if ($name !== null) {
            // 重複チェック（自分以外）
            $existing = $this->categoryRepository->findByName($name);
            if ($existing !== null && $existing->getId() !== $id) {
                throw new \RuntimeException("カテゴリー「{$name}」は既に存在します");
            }
            $category->updateName($name);
        }

        if ($color !== null) {
            $category->updateColor($color);
        }

        $this->categoryRepository->save($category);
        return $category;
    }

    /**
     * カテゴリーを削除
     */
    public function deleteCategory(int $id, bool $deleteAssociatedTasks = false): bool
    {
        $category = $this->categoryRepository->findById($id);
        if ($category === null) {
            return false;
        }

        // 関連タスクの処理
        $associatedTasks = $this->taskRepository->findByCategory($id);
        if (!empty($associatedTasks)) {
            if ($deleteAssociatedTasks) {
                // 関連タスクを削除
                foreach ($associatedTasks as $task) {
                    $this->taskRepository->delete($task->getId());
                }
            } else {
                // 関連タスクのカテゴリーをnullに設定
                foreach ($associatedTasks as $task) {
                    $task->updateCategory(null);
                    $this->taskRepository->save($task);
                }
            }
        }

        return $this->categoryRepository->delete($id);
    }

    /**
     * カテゴリーを取得
     */
    public function getCategory(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    /**
     * すべてのカテゴリーを取得
     */
    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    /**
     * カテゴリー別のタスク数を取得
     */
    public function getCategoryTaskCounts(): array
    {
        $categories = $this->categoryRepository->findAll();
        $counts = [];

        foreach ($categories as $category) {
            $tasks = $this->taskRepository->findByCategory($category->getId());
            $counts[$category->getId()] = [
                'category' => $category->toArray(),
                'task_count' => count($tasks),
            ];
        }

        return $counts;
    }
}
