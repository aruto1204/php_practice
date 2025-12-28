<?php

declare(strict_types=1);

namespace Tests\Exercises;

use App\Phase4\Exercises\TodoItem;
use App\Phase4\Exercises\TodoList;
use PHPUnit\Framework\TestCase;

/**
 * TodoList クラスのテスト（TDD演習）
 */
class TodoListTest extends TestCase
{
    private TodoList $todoList;

    protected function setUp(): void
    {
        $this->todoList = new TodoList();
    }

    // ============================================
    // TodoItem のテスト
    // ============================================

    public function testTodoItemCreation(): void
    {
        $item = new TodoItem(1, 'Test Task', 'Test Description');

        $this->assertEquals(1, $item->getId());
        $this->assertEquals('Test Task', $item->getTitle());
        $this->assertEquals('Test Description', $item->getDescription());
        $this->assertFalse($item->isCompleted());
        $this->assertInstanceOf(\DateTimeImmutable::class, $item->getCreatedAt());
    }

    public function testCompleteItem(): void
    {
        $item = new TodoItem(1, 'Test Task', 'Test Description');

        $this->assertFalse($item->isCompleted());

        $item->complete();

        $this->assertTrue($item->isCompleted());
    }

    public function testUncompleteItem(): void
    {
        $item = new TodoItem(1, 'Test Task', 'Test Description');
        $item->complete();

        $this->assertTrue($item->isCompleted());

        $item->uncomplete();

        $this->assertFalse($item->isCompleted());
    }

    public function testUpdateTitle(): void
    {
        $item = new TodoItem(1, 'Old Title', 'Description');

        $item->updateTitle('New Title');

        $this->assertEquals('New Title', $item->getTitle());
    }

    public function testUpdateTitleWithEmptyStringThrowsException(): void
    {
        $item = new TodoItem(1, 'Title', 'Description');

        $this->expectException(\InvalidArgumentException::class);
        $item->updateTitle('');
    }

    public function testIsOverdueWhenNotCompleted(): void
    {
        $pastDate = new \DateTimeImmutable('-1 day');
        $item = new TodoItem(1, 'Task', 'Description', false, $pastDate);

        $this->assertTrue($item->isOverdue());
    }

    public function testIsNotOverdueWhenCompleted(): void
    {
        $pastDate = new \DateTimeImmutable('-1 day');
        $item = new TodoItem(1, 'Task', 'Description', true, $pastDate);

        $this->assertFalse($item->isOverdue());
    }

    public function testIsNotOverdueWhenFutureDate(): void
    {
        $futureDate = new \DateTimeImmutable('+1 day');
        $item = new TodoItem(1, 'Task', 'Description', false, $futureDate);

        $this->assertFalse($item->isOverdue());
    }

    // ============================================
    // TodoList のテスト
    // ============================================

    public function testAddItem(): void
    {
        $item = $this->todoList->addItem('Test Task', 'Test Description');

        $this->assertEquals(1, $this->todoList->count());
        $this->assertInstanceOf(TodoItem::class, $item);
    }

    public function testAddItemWithEmptyTitleThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->todoList->addItem('', 'Description');
    }

    public function testGetItem(): void
    {
        $item = $this->todoList->addItem('Test Task');
        $retrieved = $this->todoList->getItem($item->getId());

        $this->assertSame($item, $retrieved);
    }

    public function testGetItemReturnsNullWhenNotFound(): void
    {
        $item = $this->todoList->getItem(999);

        $this->assertNull($item);
    }

    public function testGetAllItems(): void
    {
        $this->todoList->addItem('Task 1');
        $this->todoList->addItem('Task 2');
        $this->todoList->addItem('Task 3');

        $items = $this->todoList->getAllItems();

        $this->assertCount(3, $items);
    }

    public function testGetCompletedItems(): void
    {
        $item1 = $this->todoList->addItem('Task 1');
        $item2 = $this->todoList->addItem('Task 2');
        $item3 = $this->todoList->addItem('Task 3');

        $item1->complete();
        $item3->complete();

        $completed = $this->todoList->getCompletedItems();

        $this->assertCount(2, $completed);
    }

    public function testGetIncompleteItems(): void
    {
        $item1 = $this->todoList->addItem('Task 1');
        $item2 = $this->todoList->addItem('Task 2');
        $item3 = $this->todoList->addItem('Task 3');

        $item1->complete();

        $incomplete = $this->todoList->getIncompleteItems();

        $this->assertCount(2, $incomplete);
    }

    public function testRemoveItem(): void
    {
        $item = $this->todoList->addItem('Task');

        $this->assertEquals(1, $this->todoList->count());

        $result = $this->todoList->removeItem($item->getId());

        $this->assertTrue($result);
        $this->assertEquals(0, $this->todoList->count());
    }

    public function testRemoveNonExistentItemReturnsFalse(): void
    {
        $result = $this->todoList->removeItem(999);

        $this->assertFalse($result);
    }

    public function testCount(): void
    {
        $this->assertEquals(0, $this->todoList->count());

        $this->todoList->addItem('Task 1');
        $this->assertEquals(1, $this->todoList->count());

        $this->todoList->addItem('Task 2');
        $this->assertEquals(2, $this->todoList->count());
    }

    public function testClear(): void
    {
        $this->todoList->addItem('Task 1');
        $this->todoList->addItem('Task 2');
        $this->todoList->addItem('Task 3');

        $this->assertEquals(3, $this->todoList->count());

        $this->todoList->clear();

        $this->assertEquals(0, $this->todoList->count());
    }

    public function testClearCompleted(): void
    {
        $item1 = $this->todoList->addItem('Task 1');
        $item2 = $this->todoList->addItem('Task 2');
        $item3 = $this->todoList->addItem('Task 3');

        $item1->complete();
        $item2->complete();

        $this->assertEquals(3, $this->todoList->count());

        $this->todoList->clearCompleted();

        $this->assertEquals(1, $this->todoList->count());
        $this->assertEquals(0, $this->todoList->countCompleted());
    }
}
