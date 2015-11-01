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

    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();
        return $app;
    }
}
