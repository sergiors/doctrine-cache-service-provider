<?php

namespace Sergiors\Pimple\Tests\Provider;

use Pimple\Container;
use Doctrine\Common\Cache\ApcuCache;
use Sergiors\Pimple\Provider\DoctrineCacheServiceProvider;

class DoctrineCacheServiceProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function register()
    {
        $app = $this->createContainer();
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
        $app = $this->createContainer();
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
        $app = $this->createContainer();
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

    public function createContainer()
    {
        return new Container();
    }
}
