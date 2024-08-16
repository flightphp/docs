# Wruczek/PHP-File-Cache

Light, simple and standalone PHP in-file caching class

**Advantages** 
- Light, standalone and simple
- All code in one file - no pointless drivers.
- Secure - every generated cache file have a php header with die, making direct access impossible even if someone knows the path and your server is not configured properly
- Well documented and tested
- Handles concurrency correctly via flock
- Supports PHP 5.4.0 - 7.1+
- Free under a MIT license

Click [here](https://github.com/Wruczek/PHP-File-Cache) to view the code.

## Installation

Install via composer:

```bash
composer require wruczek/php-file-cache
```

## Usage

Usage is fairly straightforward.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// You pass the directory the cache will be stored in into the constructor
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// This ensures that the cache is only used when in production mode
	// ENVIRONMENT is a constant that is set in your bootstrap file or elsewhere in your app
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Then you can use it in your code like this:

```php

// Get cache instance
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // return data to be cached
}, 10); // 10 seconds

// or
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 seconds
}
```

## Documentation

Visit [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) for full documentation and make sure you see the [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) folder.