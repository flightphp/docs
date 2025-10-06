# Async

Async — це невеликий пакет для фреймворку Flight, який дозволяє запускати ваші додатки Flight у асинхронних серверах та середовищах виконання, таких як Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman тощо. З коробки він включає адаптери для Swoole та AdapterMan.

Мета: розробка та налагодження з PHP-FPM (або вбудованим сервером) та перехід на Swoole (або інший асинхронний драйвер) для продакшену з мінімальними змінами.

## Вимоги

- PHP 7.4 або вище  
- Фреймворк Flight 3.16.1 або вище  
- [Розширення Swoole](https://www.openswoole.com)

## Встановлення

Встановіть через composer:

```bash
composer require flightphp/async
```

Якщо плануєте запускати з Swoole, встановіть розширення:

```bash
# за допомогою pecl
pecl install swoole
# або openswoole
pecl install openswoole

# або з менеджером пакетів (приклад для Debian/Ubuntu)
sudo apt-get install php-swoole
```

## Швидкий приклад Swoole

Нижче наведено мінімальну конфігурацію, яка показує, як підтримувати як PHP-FPM (або вбудований сервер), так і Swoole, використовуючи один і той самий код.

Файли, які знадобляться у вашому проєкті:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

Цей файл — проста перемикачка, яка змушує додаток працювати в режимі PHP для розробки.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

Цей файл ініціалізує ваш додаток Flight і запустить драйвер Swoole, коли NOT_SWOOLE не визначено.

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

Стислий драйвер, який показує, як передавати запити Swoole у Flight за допомогою AsyncBridge та адаптерів Swoole.

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

- Розробка (вбудований сервер PHP / PHP-FPM):
  - php -S localhost:8000 (або додайте -t public/ якщо ваш index у public/)
- Продакшен (Swoole):
  - php swoole_server.php

Порада: Для продакшену використовуйте реверс-проксі (Nginx) перед Swoole для обробки TLS, статичних файлів та балансування навантаження.

## Нотатки щодо конфігурації

Драйвер Swoole надає кілька опцій конфігурації:
- worker_num: кількість процесів робочих
- max_request: запити на робочий перед перезапуском
- enable_coroutine: використання корутин для конкурентності
- buffer_output_size: розмір буфера виводу

Налаштуйте ці параметри відповідно до ресурсів вашого хоста та шаблонів трафіку.

## Обробка помилок

AsyncBridge перетворює помилки Flight у правильні HTTP-відповіді. Ви також можете додати обробку помилок на рівні маршруту:

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

## AdapterMan та інші середовища виконання

[AdapterMan](https://github.com/joanhey/adapterman) підтримується як альтернативний адаптер середовища виконання. Пакет розроблено для адаптивності — додавання або використання інших адаптерів загалом слідує тому ж шаблону: перетворення серверного запиту/відповіді у запит/відповідь Flight через AsyncBridge та адаптери, специфічні для середовища.