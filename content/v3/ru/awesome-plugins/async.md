# Async

Async — это небольшой пакет для фреймворка Flight, который позволяет запускать приложения Flight внутри асинхронных серверов и рантаймов, таких как Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman и т.д. Из коробки он включает адаптеры для Swoole и AdapterMan.

Цель: разрабатывать и отлаживать с PHP-FPM (или встроенным сервером) и переключаться на Swoole (или другой асинхронный драйвер) для продакшена с минимальными изменениями.

## Требования

- PHP 7.4 или выше  
- Фреймворк Flight 3.16.1 или выше  
- [Расширение Swoole](https://www.openswoole.com)

## Установка

Установите через composer:

```bash
composer require flightphp/async
```

Если вы планируете запускать с Swoole, установите расширение:

```bash
# используя pecl
pecl install swoole
# или openswoole
pecl install openswoole

# или с помощью менеджера пакетов (пример для Debian/Ubuntu)
sudo apt-get install php-swoole
```

## Быстрый пример Swoole

Ниже приведена минимальная настройка, которая показывает, как поддерживать как PHP-FPM (или встроенный сервер), так и Swoole, используя один и тот же код.

Файлы, которые вам понадобятся в проекте:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

Этот файл представляет собой простой переключатель, который заставляет приложение работать в режиме PHP для разработки.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

Этот файл инициализирует ваше приложение Flight и запустит драйвер Swoole, когда NOT_SWOOLE не определен.

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

Краткий драйвер, показывающий, как связывать запросы Swoole с Flight с использованием AsyncBridge и адаптеров Swoole.

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

## Запуск сервера

- Разработка (встроенный сервер PHP / PHP-FPM):
  - php -S localhost:8000 (или добавьте -t public/, если ваш index находится в public/)
- Продакшен (Swoole):
  - php swoole_server.php

Совет: Для продакшена используйте обратный прокси (Nginx) перед Swoole для обработки TLS, статических файлов и балансировки нагрузки.

## Заметки по конфигурации

Драйвер Swoole предоставляет несколько опций конфигурации:
- worker_num: количество рабочих процессов
- max_request: запросов на работника перед перезапуском
- enable_coroutine: использование корутин для параллелизма
- buffer_output_size: размер буфера вывода

Настройте эти параметры в соответствии с ресурсами хоста и шаблонами трафика.

## Обработка ошибок

AsyncBridge преобразует ошибки Flight в правильные HTTP-ответы. Вы также можете добавить обработку ошибок на уровне маршрута:

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

## AdapterMan и другие рантаймы

[AdapterMan](https://github.com/joanhey/adapterman) поддерживается как альтернативный адаптер рантайма. Пакет спроектирован для адаптивности — добавление или использование других адаптеров в целом следует тому же шаблону: преобразование запроса/ответа сервера в запрос/ответ Flight через AsyncBridge и адаптеры, специфичные для рантайма.