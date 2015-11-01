<?php
namespace Sergiors\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\XcacheCache;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class DoctrineCacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['cache.options'] = [
            'driver' => 'array'
        ];

        $app['cache.array'] = $app->protect(function () {
            return new ArrayCache();
        });

        $app['cache.apc'] = $app->protect(function () {
            return new ApcCache();
        });

        $app['cache.redis'] = $app->protect(function ($options) {
            if (empty($options['host']) || empty($options['port'])) {
                throw new \RuntimeException('You must specify "host" and "port" for Redis.');
            }

            $redis = new \Redis();
            $redis->connect($options['host'], $options['port']);

            if (isset($options['password'])) {
                $redis->auth($options['password']);
            }

            $cache = new RedisCache();
            $cache->setRedis($redis);
            return $cache;
        });

        $app['cache.xcache'] = $app->protect(function () {
            return new XcacheCache();
        });

        $app['cache.factory'] = $app->protect(function ($driver, $options) use ($app) {
            switch ($driver) {
                case 'array':
                    return $app['cache.array']();
                    break;
                case 'apc':
                    return $app['cache.apc']();
                    break;
                case 'redis':
                    return $app['cache.redis']($options);
                    break;
                case 'xcache':
                    return $app['cache.xcache']();
                    break;
            }

            throw new \RuntimeException();
        });

        $app['cache'] = $app->share(function (Application $app) {
            return $app['cache.factory']($app['cache.options']['driver'], $app['cache.options']);
        });
    }

    public function boot(Application $app)
    {
    }
}
