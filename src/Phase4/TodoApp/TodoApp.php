<?php

declare(strict_types=1);

namespace App\Phase4\TodoApp;

/**
 * Todoアプリケーションのメインクラス
 */
class TodoApp
{
    private TaskService $taskService;
    private CategoryService $categoryService;

    public function __construct()
    {
        $taskRepository = new TaskRepository();
        $categoryRepository = new CategoryRepository();

        $this->taskService = new TaskService($taskRepository, $categoryRepository);
        $this->categoryService = new CategoryService($categoryRepository, $taskRepository);

        // デフォルトカテゴリーの作成
        $this->initializeDefaultCategories();
    }

    /**
     * デフォルトカテゴリーの初期化
     */
    private function initializeDefaultCategories(): void
    {
        $this->categoryService->createCategory('仕事', '#3B82F6');
        $this->categoryService->createCategory('個人', '#10B981');
        $this->categoryService->createCategory('学習', '#F59E0B');
    }

    public function getTaskService(): TaskService
    {
        return $this->taskService;
    }

    public function getCategoryService(): CategoryService
    {
        return $this->categoryService;
    }

    /**
     * アプリケーションの実行
     */
    public function run(): void
    {
        echo "=== Todoアプリケーション ===\n\n";

        // サンプルタスクの作成
        echo "【タスク作成】\n";
        $task1 = $this->taskService->createTask(
            title: 'データベース設計書を作成',
            description: 'ER図とテーブル定義書を作成する',
            categoryId: 1,
            dueDate: '+3 days',
        );
        echo "✓ タスク作成: {$task1->getTitle()}\n";

        $task2 = $this->taskService->createTask(
            title: 'API実装',
            description: 'RESTful APIエンドポイントを実装する',
            categoryId: 1,
            dueDate: '+7 days',
        );
        echo "✓ タスク作成: {$task2->getTitle()}\n";

        $task3 = $this->taskService->createTask(
            title: 'PHPUnitの学習',
            description: 'テスト駆動開発について学ぶ',
            categoryId: 3,
            dueDate: '+1 day',
        );
        echo "✓ タスク作成: {$task3->getTitle()}\n";

        $task4 = $this->taskService->createTask(
            title: '週報を提出',
            description: '今週の作業内容をまとめる',
            categoryId: 1,
            dueDate: '-1 day', // 期限切れ
        );
        echo "✓ タスク作成: {$task4->getTitle()}\n\n";

        // タスク一覧表示
        echo "【すべてのタスク】\n";
        $this->displayTasks($this->taskService->getAllTasks());
        echo "\n";

        // タスク完了
        echo "【タスク完了】\n";
        $this->taskService->completeTask($task1->getId());
        echo "✓ タスク完了: {$task1->getTitle()}\n\n";

        // 未完了タスク表示
        echo "【未完了タスク】\n";
        $this->displayTasks($this->taskService->getIncompleteTasks());
        echo "\n";

        // 期限切れタスク表示
        echo "【期限切れタスク】\n";
        $overdueTasks = $this->taskService->getOverdueTasks();
        if (empty($overdueTasks)) {
            echo "  期限切れのタスクはありません\n";
        } else {
            $this->displayTasks($overdueTasks);
        }
        echo "\n";

        // カテゴリー別タスク表示
        echo "【カテゴリー別タスク】\n";
        $categories = $this->categoryService->getAllCategories();
        foreach ($categories as $category) {
            $tasks = $this->taskService->getTasksByCategory($category->getId());
            echo "  {$category->getName()} ({$category->getColor()}): " . count($tasks) . "件\n";
        }
        echo "\n";

        // 統計情報表示
        echo "【統計情報】\n";
        $stats = $this->taskService->getStatistics();
        echo "  総タスク数: {$stats['total']}\n";
        echo "  完了: {$stats['completed']}\n";
        echo "  未完了: {$stats['incomplete']}\n";
        echo "  期限切れ: {$stats['overdue']}\n";
        echo "  完了率: {$stats['completion_rate']}%\n\n";

        // カテゴリー管理
        echo "【カテゴリー管理】\n";
        $newCategory = $this->categoryService->createCategory('緊急', '#EF4444');
        echo "✓ カテゴリー作成: {$newCategory->getName()}\n";

        $categoryTaskCounts = $this->categoryService->getCategoryTaskCounts();
        echo "\nカテゴリー別タスク数:\n";
        foreach ($categoryTaskCounts as $data) {
            $cat = $data['category'];
            echo "  {$cat['name']}: {$data['task_count']}件\n";
        }
        echo "\n";

        echo "=== アプリケーション終了 ===\n";
    }

    /**
     * タスク一覧を表示
     */
    private function displayTasks(array $tasks): void
    {
        if (empty($tasks)) {
            echo "  タスクがありません\n";
            return;
        }

        foreach ($tasks as $task) {
            $status = $task->isCompleted() ? '✓' : ' ';
            $overdue = $task->isOverdue() ? ' ⚠️' : '';
            $categoryName = '';

            if ($task->getCategoryId() !== null) {
                $category = $this->categoryService->getCategory($task->getCategoryId());
                $categoryName = $category ? " [{$category->getName()}]" : '';
            }

            $dueInfo = '';
            if ($task->getDueDate()) {
                $daysUntilDue = $task->getDaysUntilDue();
                if ($daysUntilDue !== null) {
                    if ($daysUntilDue < 0) {
                        $dueInfo = " (期限: " . abs($daysUntilDue) . "日前){$overdue}";
                    } elseif ($daysUntilDue === 0) {
                        $dueInfo = " (期限: 本日)";
                    } else {
                        $dueInfo = " (期限: あと{$daysUntilDue}日)";
                    }
                }
            }

            echo "  [{$status}] {$task->getId()}. {$task->getTitle()}{$categoryName}{$dueInfo}\n";
            if (!empty($task->getDescription())) {
                echo "      {$task->getDescription()}\n";
            }
        }
    }
}
