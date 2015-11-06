<?php
namespace Sergiors\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class DoctrineCacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['caches.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['caches.options'])) {
                $app['caches.options'] = [
                    'default' => isset($app['cache.options']) ? $app['cache.options'] : []
                ];
            }

            $tmp = $app['caches.options'];
            foreach ($tmp as $name => &$options) {
                if (!is_array($options)) {
                    $options = [
                        'driver' => $options
                    ];
                }
                $options = array_replace($app['cache.default_options'], $options);

                if (!isset($app['caches.default'])) {
                    $app['caches.default'] = $name;
                }
            }
            $app['caches.options'] = $tmp;
        });

        $app['caches'] = $app->share(function (Application $app) {
            $app['caches.options.initializer']();

            $container = new \Pimple();
            foreach ($app['caches.options'] as $name => $options) {
                $container[$name] = $container->share(function () use ($app, $options) {
                    $cache = $app['cache.factory']($options['driver'], $options);
                    $cache->setNamespace($options['namespace']);
                    return $cache;
                });
            }

            return $container;
        });

        $app['cache.filesystem'] = $app->protect(function ($options) {
            if (empty($options['cache_dir']) || false === is_dir($options['cache_dir'])) {
                throw new \InvalidArgumentException(
                    'You must specify "cache_dir" for Filesystem.'
                );
            }

            return new FilesystemCache($options['cache_dir']);
        });

        $app['cache.array'] = $app->protect(function () {
            return new ArrayCache();
        });

        $app['cache.apc'] = $app->protect(function () {
            return new ApcCache();
        });

        $app['cache.redis'] = $app->protect(function ($options) {
            if (empty($options['host']) || empty($options['port'])) {
                throw new \InvalidArgumentException('You must specify "host" and "port" for Redis.');
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
                case 'filesystem':
                    return $app['cache.filesystem']($options);
                    break;
            }

            throw new \RuntimeException();
        });

        // shortcuts for the "first" cache
        $app['cache'] = $app->share(function (Application $app) {
            $caches = $app['caches'];

            return $caches[$app['caches.default']];
        });

        $app['cache.default_options'] = [
            'driver' => 'array',
            'namespace' => null
        ];
    }

    public function boot(Application $app)
    {
    }
}
