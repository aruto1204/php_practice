<?php

declare(strict_types=1);

namespace Tests\TodoApp;

use App\Phase4\TodoApp\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testCreateTask(): void
    {
        $task = new Task(
            id: 1,
            title: 'テストタスク',
            description: 'テストの説明',
            categoryId: 1,
            dueDate: new \DateTimeImmutable('+3 days'),
        );

        $this->assertEquals(1, $task->getId());
        $this->assertEquals('テストタスク', $task->getTitle());
        $this->assertEquals('テストの説明', $task->getDescription());
        $this->assertEquals(1, $task->getCategoryId());
        $this->assertFalse($task->isCompleted());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
    }

    public function testCreateTaskWithEmptyTitleThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("タイトルは空にできません");

        new Task(1, '', 'Description', null, null);
    }

    public function testCompleteTask(): void
    {
        $task = new Task(1, 'タスク', 'Description', null, null);

        $this->assertFalse($task->isCompleted());
        $this->assertNull($task->getCompletedAt());

        $task->complete();

        $this->assertTrue($task->isCompleted());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCompletedAt());
    }

    public function testUncompleteTask(): void
    {
        $task = new Task(1, 'タスク', 'Description', null, null);
        $task->complete();

        $this->assertTrue($task->isCompleted());

        $task->uncomplete();

        $this->assertFalse($task->isCompleted());
        $this->assertNull($task->getCompletedAt());
    }

    public function testUpdateTitle(): void
    {
        $task = new Task(1, '元のタイトル', 'Description', null, null);

        $task->updateTitle('新しいタイトル');

        $this->assertEquals('新しいタイトル', $task->getTitle());
    }

    public function testUpdateTitleWithEmptyStringThrowsException(): void
    {
        $task = new Task(1, 'タイトル', 'Description', null, null);

        $this->expectException(\InvalidArgumentException::class);
        $task->updateTitle('');
    }

    public function testIsOverdueWhenPastDueAndIncomplete(): void
    {
        $pastDate = new \DateTimeImmutable('-1 day');
        $task = new Task(1, 'タスク', 'Description', null, $pastDate);

        $this->assertTrue($task->isOverdue());
    }

    public function testIsNotOverdueWhenCompleted(): void
    {
        $pastDate = new \DateTimeImmutable('-1 day');
        $task = new Task(1, 'タスク', 'Description', null, $pastDate);
        $task->complete();

        $this->assertFalse($task->isOverdue());
    }

    public function testIsNotOverdueWhenFutureDate(): void
    {
        $futureDate = new \DateTimeImmutable('+1 day');
        $task = new Task(1, 'タスク', 'Description', null, $futureDate);

        $this->assertFalse($task->isOverdue());
    }

    public function testGetDaysUntilDue(): void
    {
        $futureDate = new \DateTimeImmutable('+3 days');
        $task = new Task(1, 'タスク', 'Description', null, $futureDate);

        $days = $task->getDaysUntilDue();

        $this->assertEquals(3, $days);
    }

    public function testToArray(): void
    {
        $dueDate = new \DateTimeImmutable('+3 days');
        $task = new Task(1, 'タスク', '説明', 1, $dueDate);

        $array = $task->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('category_id', $array);
        $this->assertArrayHasKey('due_date', $array);
        $this->assertArrayHasKey('completed', $array);
        $this->assertArrayHasKey('created_at', $array);
    }
}
