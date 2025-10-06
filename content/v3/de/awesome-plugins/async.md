# Async

Async ist ein kleines Paket für das Flight-Framework, das es Ihnen ermöglicht, Ihre Flight-Apps in asynchronen Servern und Runtimes wie Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman usw. auszuführen. Out of the box enthält es Adapter für Swoole und AdapterMan.

Das Ziel: Entwickeln und Debuggen mit PHP-FPM (oder dem integrierten Server) und Wechseln zu Swoole (oder einem anderen asynchronen Treiber) für die Produktion mit minimalen Änderungen.

## Requirements

- PHP 7.4 oder höher  
- Flight-Framework 3.16.1 oder höher  
- [Swoole-Erweiterung](https://www.openswoole.com)

## Installation

Installieren Sie es über Composer:

```bash
composer require flightphp/async
```

Falls Sie mit Swoole ausführen möchten, installieren Sie die Erweiterung:

```bash
# using pecl
pecl install swoole
# or openswoole
pecl install openswoole

# or with a package manager (Debian/Ubuntu example)
sudo apt-get install php-swoole
```

## Quick Swoole example

Unten ist eine minimale Einrichtung zu sehen, die zeigt, wie Sie sowohl PHP-FPM (oder den integrierten Server) als auch Swoole mit demselben Codebase unterstützen können.

Dateien, die Sie in Ihrem Projekt benötigen:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

Diese Datei ist ein einfacher Schalter, der die App im PHP-Modus für die Entwicklung erzwingt.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

Diese Datei bootstrapt Ihre Flight-App und startet den Swoole-Treiber, wenn NOT_SWOOLE nicht definiert ist.

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

Ein knapper Treiber, der zeigt, wie man Swoole-Anfragen in Flight über die AsyncBridge und die Swoole-Adapter überbrückt.

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

- Entwicklung (PHP integrierter Server / PHP-FPM):
  - php -S localhost:8000 (oder fügen Sie -t public/ hinzu, wenn Ihr index in public/ liegt)
- Produktion (Swoole):
  - php swoole_server.php

Tipp: Für die Produktion verwenden Sie einen Reverse-Proxy (Nginx) vor Swoole, um TLS, statische Dateien und Lastverteilung zu handhaben.

## Configuration notes

Der Swoole-Treiber stellt mehrere Konfigurationsoptionen zur Verfügung:
- worker_num: Anzahl der Worker-Prozesse
- max_request: Anfragen pro Worker vor dem Neustart
- enable_coroutine: Coroutines für Parallelität verwenden
- buffer_output_size: Ausgabepuffer-Größe

Passen Sie diese an Ihre Host-Ressourcen und Traffic-Muster an.

## Error handling

AsyncBridge übersetzt Flight-Fehler in korrekte HTTP-Antworten. Sie können auch fehlerbehandlung auf Routenebene hinzufügen:

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

[AdapterMan](https://github.com/joanhey/adapterman) wird als alternativer Runtime-Adapter unterstützt. Das Paket ist so konzipiert, dass es anpassbar ist – das Hinzufügen oder Verwenden anderer Adapter folgt im Allgemeinen demselben Muster: Konvertieren Sie die Server-Anfrage/Antwort in die Flight-Anfrage/Antwort über die AsyncBridge und die runtime-spezifischen Adapter.