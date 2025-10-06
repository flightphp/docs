# Async

Async is a small package for the Flight framework that lets you run your Flight apps inside asynchronous servers and runtimes like Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman, etc. Out of the box it includes adapters for Swoole and AdapterMan.

The goal: develop and debug with PHP-FPM (or the built-in server) and switch to Swoole (or another async driver) for production with minimal changes.

## Requirements

- PHP 7.4 or higher  
- Flight framework 3.16.1 or higher  
- [Swoole extension](https://www.openswoole.com)

## Installation

Install via composer:

```bash
composer require flightphp/async
```

If you plan to run with Swoole, install the extension:

```bash
# using pecl
pecl install swoole
# or openswoole
pecl install openswoole

# or with a package manager (Debian/Ubuntu example)
sudo apt-get install php-swoole
```

## Quick Swoole example

Below is a minimal setup that shows how to support both PHP-FPM (or built-in server) and Swoole using the same codebase.

Files you will need in your project:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

This file is a simple switch that forces the app to run in PHP mode for development.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

This file bootstraps your Flight app and will start the Swoole driver when NOT_SWOOLE is not defined.

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// Require the SwooleServerDriver class when running in Swoole mode.
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

A concise driver showing how to bridge Swoole requests into Flight using the AsyncBridge and Swoole adapters.

```php
// SwooleServerDriver.php
<?php

use flight\adapter\SwooleAsyncRequest;
use flight\adapter\SwooleAsyncResponse;
use flight\AsyncBridge;
use flight\Engine;
use Swoole\HTTP\Server as SwooleServer;
use Swoole\HTTP\Request as SwooleRequest;
use Swoole\HTTP\Response as SwooleResponse;

class SwooleServerDriver {
	protected $Swoole;
	protected $app;

	public function __construct(string $host, int $port, Engine $app) {
		$this->Swoole = new SwooleServer($host, $port);
		$this->app = $app;

		$this->setDefault();
		$this->bindWorkerEvents();
		$this->bindHttpEvent();
	}

	protected function setDefault() {
		$this->Swoole->set([
			'daemonize'             => false,
			'dispatch_mode'         => 1,
			'max_request'           => 8000,
			'open_tcp_nodelay'      => true,
			'reload_async'          => true,
			'max_wait_time'         => 60,
			'enable_reuse_port'     => true,
			'enable_coroutine'      => true,
			'http_compression'      => false,
			'enable_static_handler' => true,
			'document_root'         => __DIR__,
			'static_handler_locations' => ['/css', '/js', '/images', '/.well-known'],
			'buffer_output_size'    => 4 * 1024 * 1024,
			'worker_num'            => 4,
		]);

		$app = $this->app;
		$app->map('stop', function (?int $code = null) use ($app) {
			if ($code !== null) {
				$app->response()->status($code);
			}
		});
	}

	protected function bindHttpEvent() {
		$app = $this->app;
		$AsyncBridge = new AsyncBridge($app);

		$this->Swoole->on('Start', function(SwooleServer $server) {
			echo "Swoole http server is started at http://127.0.0.1:9501\n";
		});

		$this->Swoole->on('Request', function (SwooleRequest $request, SwooleResponse $response) use ($AsyncBridge) {
			$SwooleAsyncRequest = new SwooleAsyncRequest($request);
			$SwooleAsyncResponse = new SwooleAsyncResponse($response);

			$AsyncBridge->processRequest($SwooleAsyncRequest, $SwooleAsyncResponse);

			$response->end();
			gc_collect_cycles();
		});
	}

	protected function bindWorkerEvents() {
		$createPools = function() {
			// create worker-specific connection pools here
		};
		$closePools = function() {
			// close pools / cleanup here
		};
		$this->Swoole->on('WorkerStart', $createPools);
		$this->Swoole->on('WorkerStop', $closePools);
		$this->Swoole->on('WorkerError', $closePools);
	}

	public function start() {
		$this->Swoole->start();
	}
}
```

## Running the server

- Development (PHP built-in server / PHP-FPM):
  - php -S localhost:8000 (or add -t public/ if your index is in public/)
- Production (Swoole):
  - php swoole_server.php

Tip: For production use a reverse proxy (Nginx) in front of Swoole to handle TLS, static files, and load-balancing.

## Configuration notes

The Swoole driver exposes several config options:
- worker_num: number of worker processes
- max_request: requests per worker before restart
- enable_coroutine: use coroutines for concurrency
- buffer_output_size: output buffer size

Adjust these to fit your host resources and traffic patterns.

## Error handling

AsyncBridge translates Flight errors into proper HTTP responses. You can also add route-level error handling:

```php
$app->route('/*', function() use ($app) {
	try {
		// route logic
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan and other runtimes

[AdapterMan](https://github.com/joanhey/adapterman) is supported as an alternative runtime adapter. The package is designed to be adaptable — adding or using other adapters generally follows the same pattern: convert the server request/response into Flight's request/response via the AsyncBridge and the runtime-specific adapters.
