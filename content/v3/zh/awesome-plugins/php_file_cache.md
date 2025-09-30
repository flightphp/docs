# flightphp/cache

Light, simple and standalone PHP in-file caching class forked from [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Advantages** 
- Light, standalone and simple
- All code in one file - no pointless drivers.
- Secure - every generated cache file have a php header with die, making direct access impossible even if someone knows the path and your server is not configured properly
- Well documented and tested
- Handles concurrency correctly via flock
- Supports PHP 7.4+
- Free under a MIT license

This docs site is using this library to cache each of the pages!

Click [here](https://github.com/flightphp/cache) to view the code.

## Installation

Install via composer:

```bash
composer require flightphp/cache
```

## Usage

Usage is fairly straightforward. This saves a cache file in the cache directory.

```php
use flight\Cache;

$app = Flight::app();

// You pass the directory the cache will be stored in into the constructor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// This ensures that the cache is only used when in production mode
	// ENVIRONMENT is a constant that is set in your bootstrap file or elsewhere in your app
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Get a Cache Value

You use the `get()` method to get a cached value. If you want a convenience method that will refresh the cache if it is expired, you can use `refreshIfExpired()`.

```php

// Get cache instance
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // return data to be cached
}, 10); // 10 seconds

// or
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 seconds
}
```

### Store a Cache Value

You use the `set()` method to store a value in the cache.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 seconds
```

### Erase a Cache Value

You use the `delete()` method to erase a value in the cache.

```php
Flight::cache()->delete('simple-cache-test');
```

### Check if a Cache Value Exists

You use the `exists()` method to check if a value exists in the cache.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// do something
}
```

### Clear the Cache
You use the `flush()` method to clear the entire cache.

```php
Flight::cache()->flush();
```

### Pull out meta data with cache

If you want to pull out timestamps and other meta data about a cache entry, make sure you pass `true` as the correct parameter.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // return data to be cached
}, 10, true); // true = return with metadata
// or
$data = $cache->get("simple-cache-meta-test", true); // true = return with metadata

/*
Example cached item retrieved with metadata:
{
    "time":1511667506, <-- save unix timestamp
    "expire":10,       <-- expire time in seconds
    "data":"04:38:26", <-- unserialized data
    "permanent":false
}

Using metadata, we can, for example, calculate when item was saved or when it expires
We can also access the data itself with the "data" key
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // get unix timestamp when data expires and subtract current timestamp from it
$cacheddate = $data["data"]; // we access the data itself with the "data" key

echo "Latest cache save: $cacheddate, expires in $expiresin seconds";
```

## Documentation

Visit [https://github.com/flightphp/cache](https://github.com/flightphp/cache) to view the code. Make sure you see the [examples](https://github.com/flightphp/cache/tree/master/examples) folder for additional ways to use the cache.