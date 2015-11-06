Doctrine Cache Service Provider
-------------------------------
[![Build Status](https://travis-ci.org/sergiors/doctrine-cache-service-provider.svg?branch=1.0.0)](https://travis-ci.org/sergiors/doctrine-cache-service-provider)

To see the complete documentation, check out [Doctrine Cache](http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/caching.html)

Install
-------
```
composer require sergiors/doctrine-cache-service-provider
```

How to use
----------
```php
use Sergiors\Silex\Provider\DoctrineCacheServiceProvider;

$app->register(new DoctrineCacheServiceProvider(), [
    'cache.options' => [
        'driver' => 'redis',
        'namespace' => 'myapp',
        'host' => '{your_host}',
        'port' => '{your_portt}',
        // 'password' => ''
    ]
]);

// $app['cache']->save('cache_id', 'my_data');
// $app['cache']->fetch('cache_id');
```

## Multiple instances

Something like this:
```php
use Sergiors\Silex\Provider\DoctrineCacheServiceProvider;

$app->register(new DoctrineCacheServiceProvider(), [
    'caches.options' = [
        'conn1' => 'xcache',
        'conn2' => [
            'driver' => 'redis'
        ],
        'conn3' => [
            'driver' => 'array',
            'namespace' => 'test'
        ]
    ]
]);

// $app['caches']['conn1'];
// $app['caches']['conn2'];
// $app['caches']['conn3'];
```

Be Happy!

License
-------
MIT
