<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    public function testListTasks(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();
        $client->request('GET', '/api/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testCreateTask(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Test Task',
            'description' => 'Task description',
            'priority' => 3,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $content = $client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('Test Task', $data['title']);
    }

    public function testCreateInvalidTask(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => '', // Invalid - empty title
            'priority' => 10, // Invalid - priority out of range
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetTask(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();

        // First create a task
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Task to retrieve',
            'description' => 'Task description',
        ]));

        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);
        $taskId = $data['id'];

        // Now get the task
        $client->request('GET', '/api/tasks/' . $taskId);

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertEquals($taskId, $data['id']);
        $this->assertEquals('Task to retrieve', $data['title']);
    }

    public function testUpdateTask(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();

        // First create a task
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Original title',
            'description' => 'Original description',
        ]));

        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);
        $taskId = $data['id'];

        // Now update the task
        $client->request('PUT', '/api/tasks/' . $taskId, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Updated title',
            'description' => 'Updated description',
            'completed' => true,
        ]));

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);

        $this->assertEquals('Updated title', $data['title']);
        $this->assertEquals('Updated description', $data['description']);
        $this->assertTrue($data['completed']);
    }

    public function testDeleteTask(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();

        // First create a task
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Task to delete',
        ]));

        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);
        $taskId = $data['id'];

        // Now delete the task
        $client->request('DELETE', '/api/tasks/' . $taskId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Try to get the deleted task
        $client->request('GET', '/api/tasks/' . $taskId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
