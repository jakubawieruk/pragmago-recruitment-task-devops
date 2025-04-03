<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthCheckControllerTest extends WebTestCase
{
    public function testHealthCheckEndpoint(): void
    {
        $this->markTestSkipped();
        $client = static::createClient();
        $client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
        $this->assertArrayHasKey('version', $responseData);
        $this->assertArrayHasKey('services', $responseData);

        $this->assertIsArray($responseData['services']);
        $this->assertArrayHasKey('app', $responseData['services']);
        $this->assertArrayHasKey('database', $responseData['services']);
        $this->assertArrayHasKey('cache', $responseData['services']);
    }
}
