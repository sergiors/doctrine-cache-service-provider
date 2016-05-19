<?php

namespace Sergiors\Silex\Tests\Provider;

use Pimple\Container;
use Doctrine\Common\Cache\ApcuCache;
use Sergiors\Silex\Provider\DoctrineCacheServiceProvider;

class DoctrineCacheServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function register()
    {
        $app = $this->createApplication();
        $app->register(new DoctrineCacheServiceProvider(), [
            'cache.options' => [
                'driver' => 'apcu',
            ],
        ]);

        $this->assertInstanceOf(ApcuCache::class, $app['cache']);
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
        return new Container();
    }
}
