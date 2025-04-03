<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\TaskDTO;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class TaskServiceTest extends TestCase
{
    private TaskRepository $taskRepository;

    private ArrayAdapter $cache;

    private TaskService $taskService;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->cache = new ArrayAdapter();
        $this->taskService = new TaskService($this->taskRepository, $this->cache);
    }

    public function testFindAll(): void
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');

        $task2 = new Task();
        $task2->setTitle('Task 2');

        $expectedTasks = [$task1, $task2];

        $this->taskRepository
            ->expects($this->once())
            ->method('findAllOrderedByPriority')
            ->willReturn($expectedTasks);

        $tasks = $this->taskService->findAll();

        $this->assertSame($expectedTasks, $tasks);

        // Test cache hit
        $this->taskService->findAll();
    }

    public function testFindById(): void
    {
        $task = new Task();
        $task->setTitle('Task 1');

        $this->taskRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($task);

        $foundTask = $this->taskService->findById(1);

        $this->assertSame($task, $foundTask);

        // Test cache hit
        $cachedTask = $this->taskService->findById(1);
        $this->assertEquals($task, $cachedTask);
    }

    public function testCreate(): void
    {
        $taskDTO = new TaskDTO();
        $taskDTO->title = 'New Task';
        $taskDTO->description = 'Description';
        $taskDTO->priority = 3;

        $this->taskRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function (Task $task) use ($taskDTO) {
                    return $task->getTitle() === $taskDTO->title
                        && $task->getDescription() === $taskDTO->description
                        && $task->getPriority() === $taskDTO->priority;
                }),
                true
            );

        $task = $this->taskService->create($taskDTO);

        $this->assertEquals($taskDTO->title, $task->getTitle());
        $this->assertEquals($taskDTO->description, $task->getDescription());
        $this->assertEquals($taskDTO->priority, $task->getPriority());
    }

    public function testUpdate(): void
    {
        $task = new Task();
        $task->setTitle('Original Title');
        $task->setDescription('Original Description');

        $taskDTO = new TaskDTO();
        $taskDTO->title = 'Updated Title';
        $taskDTO->description = 'Updated Description';
        $taskDTO->completed = true;

        $this->taskRepository
            ->expects($this->once())
            ->method('save')
            ->with($task, true);

        $updatedTask = $this->taskService->update($task, $taskDTO);

        $this->assertSame($task, $updatedTask);
        $this->assertEquals($taskDTO->title, $updatedTask->getTitle());
        $this->assertEquals($taskDTO->description, $updatedTask->getDescription());
        $this->assertEquals($taskDTO->completed, $updatedTask->isCompleted());
    }

    public function testDelete(): void
    {
        $task = new Task();
        $task->setTitle('Task to delete');

        $this->taskRepository
            ->expects($this->once())
            ->method('remove')
            ->with($task, true);

        $this->taskService->delete($task);
    }
}
