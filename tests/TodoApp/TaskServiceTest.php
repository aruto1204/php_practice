<?php

declare(strict_types=1);

namespace Tests\TodoApp;

use App\Phase4\TodoApp\TaskService;
use App\Phase4\TodoApp\TaskRepository;
use App\Phase4\TodoApp\CategoryRepository;
use App\Phase4\TodoApp\Category;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private CategoryRepository $categoryRepository;

    protected function setUp(): void
    {
        $this->taskRepository = new TaskRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->taskService = new TaskService($this->taskRepository, $this->categoryRepository);

        // テスト用カテゴリーを作成
        $category = new Category(1, '仕事', '#3B82F6');
        $this->categoryRepository->save($category);
    }

    public function testCreateTask(): void
    {
        $task = $this->taskService->createTask('テストタスク', '説明', 1, '+3 days');

        $this->assertEquals('テストタスク', $task->getTitle());
        $this->assertEquals('説明', $task->getDescription());
        $this->assertEquals(1, $task->getCategoryId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getDueDate());
    }

    public function testCreateTaskWithInvalidCategoryThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("カテゴリーID 999 が見つかりません");

        $this->taskService->createTask('タスク', '説明', 999);
    }

    public function testUpdateTask(): void
    {
        $task = $this->taskService->createTask('元のタイトル');

        $updated = $this->taskService->updateTask(
            $task->getId(),
            title: '新しいタイトル',
            description: '新しい説明',
        );

        $this->assertEquals('新しいタイトル', $updated->getTitle());
        $this->assertEquals('新しい説明', $updated->getDescription());
    }

    public function testUpdateNonExistentTaskThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("タスクID 999 が見つかりません");

        $this->taskService->updateTask(999, title: '新しいタイトル');
    }

    public function testCompleteTask(): void
    {
        $task = $this->taskService->createTask('タスク');

        $this->assertFalse($task->isCompleted());

        $completed = $this->taskService->completeTask($task->getId());

        $this->assertTrue($completed->isCompleted());
    }

    public function testUncompleteTask(): void
    {
        $task = $this->taskService->createTask('タスク');
        $this->taskService->completeTask($task->getId());

        $uncompleted = $this->taskService->uncompleteTask($task->getId());

        $this->assertFalse($uncompleted->isCompleted());
    }

    public function testDeleteTask(): void
    {
        $task = $this->taskService->createTask('タスク');

        $this->assertEquals(1, $this->taskRepository->count());

        $result = $this->taskService->deleteTask($task->getId());

        $this->assertTrue($result);
        $this->assertEquals(0, $this->taskRepository->count());
    }

    public function testGetAllTasks(): void
    {
        $this->taskService->createTask('タスク1');
        $this->taskService->createTask('タスク2');
        $this->taskService->createTask('タスク3');

        $tasks = $this->taskService->getAllTasks();

        $this->assertCount(3, $tasks);
    }

    public function testGetCompletedTasks(): void
    {
        $task1 = $this->taskService->createTask('タスク1');
        $task2 = $this->taskService->createTask('タスク2');
        $task3 = $this->taskService->createTask('タスク3');

        $this->taskService->completeTask($task1->getId());
        $this->taskService->completeTask($task3->getId());

        $completed = $this->taskService->getCompletedTasks();

        $this->assertCount(2, $completed);
    }

    public function testGetIncompleteTasks(): void
    {
        $task1 = $this->taskService->createTask('タスク1');
        $task2 = $this->taskService->createTask('タスク2');
        $task3 = $this->taskService->createTask('タスク3');

        $this->taskService->completeTask($task1->getId());

        $incomplete = $this->taskService->getIncompleteTasks();

        $this->assertCount(2, $incomplete);
    }

    public function testGetOverdueTasks(): void
    {
        $this->taskService->createTask('タスク1', '', null, '-1 day');
        $this->taskService->createTask('タスク2', '', null, '+1 day');

        $overdue = $this->taskService->getOverdueTasks();

        $this->assertCount(1, $overdue);
    }

    public function testGetStatistics(): void
    {
        $task1 = $this->taskService->createTask('タスク1');
        $task2 = $this->taskService->createTask('タスク2');
        $task3 = $this->taskService->createTask('タスク3');

        $this->taskService->completeTask($task1->getId());

        $stats = $this->taskService->getStatistics();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['completed']);
        $this->assertEquals(2, $stats['incomplete']);
        $this->assertEquals(33.3, $stats['completion_rate']);
    }
}
