# Async

Async es un paquete pequeño para el framework Flight que te permite ejecutar tus aplicaciones Flight dentro de servidores y entornos asíncronos como Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman, etc. De fábrica incluye adaptadores para Swoole y AdapterMan.

El objetivo: desarrollar y depurar con PHP-FPM (o el servidor integrado) y cambiar a Swoole (u otro controlador asíncrono) para producción con cambios mínimos.

## Requisitos

- PHP 7.4 o superior  
- Framework Flight 3.16.1 o superior  
- [Extensión Swoole](https://www.openswoole.com)

## Instalación

Instala vía composer:

```bash
composer require flightphp/async
```

Si planeas ejecutar con Swoole, instala la extensión:

```bash
# usando pecl
pecl install swoole
# o openswoole
pecl install openswoole

# o con un administrador de paquetes (ejemplo Debian/Ubuntu)
sudo apt-get install php-swoole
```

## Ejemplo rápido de Swoole

A continuación se muestra una configuración mínima que ilustra cómo soportar tanto PHP-FPM (o servidor integrado) como Swoole utilizando el mismo código base.

Archivos que necesitarás en tu proyecto:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

Este archivo es un simple interruptor que fuerza a la aplicación a ejecutarse en modo PHP para desarrollo.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

Este archivo inicializa tu aplicación Flight y comenzará el controlador Swoole cuando NOT_SWOOLE no esté definido.

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// Require la clase SwooleServerDriver cuando se ejecute en modo Swoole.
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

Un controlador conciso que muestra cómo conectar solicitudes Swoole a Flight utilizando el AsyncBridge y los adaptadores de Swoole.

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

## Ejecutando el servidor

- Desarrollo (servidor integrado de PHP / PHP-FPM):
  - php -S localhost:8000 (o agrega -t public/ si tu index está en public/)
- Producción (Swoole):
  - php swoole_server.php

Consejo: Para producción, usa un proxy inverso (Nginx) delante de Swoole para manejar TLS, archivos estáticos y balanceo de carga.

## Notas de configuración

El controlador Swoole expone varias opciones de configuración:
- worker_num: número de procesos de worker
- max_request: solicitudes por worker antes del reinicio
- enable_coroutine: usar corutinas para concurrencia
- buffer_output_size: tamaño del búfer de salida

Ajusta estos para adaptarlos a los recursos de tu host y patrones de tráfico.

## Manejo de errores

AsyncBridge traduce los errores de Flight en respuestas HTTP adecuadas. También puedes agregar manejo de errores a nivel de ruta:

```php
$app->route('/*', function() use ($app) {
	try {
		// lógica de la ruta
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan y otros entornos

[AdapterMan](https://github.com/joanhey/adapterman) está soportado como un adaptador de entorno alternativo. El paquete está diseñado para ser adaptable — agregar o usar otros adaptadores generalmente sigue el mismo patrón: convertir la solicitud/respuesta del servidor en la solicitud/respuesta de Flight a través del AsyncBridge y los adaptadores específicos del entorno.