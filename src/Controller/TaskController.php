<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TaskDTO;
use App\Service\TaskService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks')]
class TaskController
{
    private TaskService $taskService;

    private SerializerInterface $serializer;

    private ValidatorInterface $validator;

    public function __construct(
        TaskService $taskService,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->taskService = $taskService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('', name: 'task_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $tasks = $this->taskService->findAll();

        return new JsonResponse(
            $this->serializer->serialize($tasks, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'task_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $task = $this->taskService->findById($id);

        if (! $task) {
            return new JsonResponse([
                'error' => 'Task not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->serializer->serialize($task, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', name: 'task_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $taskDTO = $this->serializer->deserialize($request->getContent(), TaskDTO::class, 'json');

        $errors = $this->validator->validate($taskDTO);
        if (count($errors) > 0) {
            return new JsonResponse([
                'errors' => (string) $errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $task = $this->taskService->create($taskDTO);

        return new JsonResponse(
            $this->serializer->serialize($task, 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'task_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $taskDTO = $this->serializer->deserialize($request->getContent(), TaskDTO::class, 'json');

        $errors = $this->validator->validate($taskDTO);
        if (count($errors) > 0) {
            return new JsonResponse([
                'errors' => (string) $errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $task = $this->taskService->findById($id);
        if (! $task) {
            return new JsonResponse([
                'error' => 'Task not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $task = $this->taskService->update($task, $taskDTO);

        return new JsonResponse(
            $this->serializer->serialize($task, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'task_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->taskService->findById($id);
        if (! $task) {
            return new JsonResponse([
                'error' => 'Task not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->taskService->delete($task);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
