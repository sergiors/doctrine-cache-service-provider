<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MongoDBCache;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class DoctrineCacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['caches.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['caches.options'])) {
                $app['caches.options'] = [
                    'default' => isset($app['cache.options'])
                        ? $app['cache.options']
                        : []
                ];
            }

            $app['caches.options'] = array_map(function ($options) use ($app) {
                return array_replace($app['cache.default_options'], is_array($options)
                    ? $options
                    : ['driver' => $options]
                );
            }, $app['caches.options']);

            if (!isset($app['caches.default'])) {
                $app['caches.default'] = array_keys(array_slice($app['caches.options'], 0, 1))[0];
            }
        });

        $app['caches'] = function (Container $app) {
            $app['caches.options.initializer']();

            $container = new Container();
            foreach ($app['caches.options'] as $name => $options) {
                $container[$name] = function () use ($app, $options) {
                    $cache = $app['cache_factory']($options['driver'], $options);
                    if (isset($options['namespace'])) {
                        $cache->setNamespace($options['namespace']);
                    }

                    return $cache;
                };
            }

            return $container;
        };

        $app['cache_factory.filesystem'] = $app->protect(function ($options) {
            if (empty($options['cache_dir']) || false === is_dir($options['cache_dir'])) {
                throw new \InvalidArgumentException(
                    'You must specify "cache_dir" for Filesystem.'
                );
            }

            return new FilesystemCache($options['cache_dir']);
        });

        $app['cache_factory.array'] = $app->protect(function ($options) {
            return new ArrayCache();
        });

        $app['cache_factory.apcu'] = $app->protect(function ($options) {
            return new ApcuCache();
        });

        $app['cache_factory.mongodb'] = $app->protect(function ($options) {
            if (empty($options['server'])
                || empty($options['name'])
                || empty($options['collection'])
            ) {
                throw new \InvalidArgumentException(
                    'You must specify "server", "name" and "collection" for MongoDB.'
                );
            }

            $client = new \MongoClient($options['server']);
            $db = new \MongoDB($client, $options['name']);
            $collection = new \MongoCollection($db, $options['collection']);

            return new MongoDBCache($collection);
        });

        $app['cache_factory.redis'] = $app->protect(function ($options) {
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

        $app['cache_factory.xcache'] = $app->protect(function ($options) {
            return new XcacheCache();
        });

        $app['cache_factory'] = $app->protect(function ($driver, $options) use ($app) {
            if (isset($app['cache_factory.' . $driver])) {
                return $app['cache_factory.' . $driver]($options);
            }
            throw new \RuntimeException();
        });

        // shortcuts for the "first" cache
        $app['cache'] = function (Container $app) {
            $caches = $app['caches'];

            return $caches[$app['caches.default']];
        };

        $app['cache.default_options'] = [
            'driver' => 'array',
            'namespace' => null,
        ];
    }
}
