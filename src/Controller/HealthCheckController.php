<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController
{
    private Connection $connection;

    private CacheItemPoolInterface $cache;

    public function __construct(
        Connection $connection,
        CacheItemPoolInterface $cache
    ) {
        $this->connection = $connection;
        $this->cache = $cache;
    }

    /**
     * Simple health check endpoint for monitoring and deployment validation
     */
    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function check(): JsonResponse
    {
        $status = [
            'status' => 'ok',
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'services' => [
                'app' => 'up',
                'database' => 'unknown',
                'cache' => 'unknown',
            ],
        ];

        // Check database connection
        try {
            $this->connection->executeQuery('SELECT 1');
            $status['services']['database'] = 'up';
        } catch (\Exception $e) {
            $status['services']['database'] = 'down';
            $status['status'] = 'degraded';
        }

        // Check redis connection
        try {
            $cacheItem = $this->cache->getItem('health_check');
            $cacheItem->set('ok');
            $this->cache->save($cacheItem);
            $retrievedItem = $this->cache->getItem('health_check');
            if ($retrievedItem->get() === 'ok') {
                $status['services']['cache'] = 'up';
            } else {
                $status['services']['cache'] = 'down';
                $status['status'] = 'degraded';
            }
        } catch (\Exception $e) {
            $status['services']['cache'] = 'down';
            $status['status'] = 'degraded';
        }

        $httpStatus = $status['status'] === 'ok'
            ? Response::HTTP_OK
            : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse($status, $httpStatus);
    }
}
