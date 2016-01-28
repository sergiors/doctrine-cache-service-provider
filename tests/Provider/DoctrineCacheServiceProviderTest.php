<?php

namespace Sergiors\Silex\Tests\Provider;

use Silex\Application;
use Silex\WebTestCase;
use Doctrine\Common\Cache\ApcCache;
use Sergiors\Silex\Provider\DoctrineCacheServiceProvider;

class DoctrineCacheServiceProviderTest extends WebTestCase
{
    /**
     * @test
     */
    public function register()
    {
        $app = $this->createApplication();
        $app->register(new DoctrineCacheServiceProvider(), [
            'cache.options' => [
                'driver' => 'apc',
            ],
        ]);

        $this->assertInstanceOf(ApcCache::class, $app['cache']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldReturnInvalidArgumentException()
    {
        $app = $this->createApplication();
        $app->register(new DoctrineCacheServiceProvider(), [
            'cache.options' => [
                'driver' => 'filesystem',
            ],
        ]);
        $app['cache'];
    }

    /**
     * @test
     */
    public function multipleConnections()
    {
        $app = $this->createApplication();
        $app->register(new DoctrineCacheServiceProvider());
        $app['caches.options'] = [
            'conn1' => 'xcache',
            'conn2' => [
                'driver' => 'redis',
            ],
            'conn3' => [
                'driver' => 'array',
                'namespace' => 'test',
            ],
        ];

        $this->assertSame($app['caches']['conn1'], $app['cache']);
        $this->assertEquals('test', $app['caches']['conn3']->getNamespace());
    }

    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }
}
