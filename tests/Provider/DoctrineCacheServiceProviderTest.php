<?php
namespace Sergiors\Silex\Provider;

use Silex\Application;
use Silex\WebTestCase;
use Silex\Provider\DoctrineServiceProvider;
use Doctrine\Common\Cache\ApcCache;

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
                'driver' => 'apc'
            ]
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
                'driver' => 'filesystem'
            ]
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
                'driver' => 'redis'
            ],
            'conn3' => [
                'driver' => 'array',
                'namespace' => 'test'
            ]
        ];

        $cache = $app['cache'];
        $this->assertSame($app['caches']['conn1'], $cache);
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
