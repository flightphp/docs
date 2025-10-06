# 异步

Async 是 Flight 框架的一个小型包，它允许您在异步服务器和运行时环境中运行 Flight 应用，例如 Swoole、AdapterMan、ReactPHP、Amp、RoadRunner、Workerman 等。开箱即用，它包含 Swoole 和 AdapterMan 的适配器。

目标：使用 PHP-FPM（或内置服务器）进行开发和调试，并在生产环境中切换到 Swoole（或其他异步驱动程序），只需最少的更改。

## 要求

- PHP 7.4 或更高版本  
- Flight 框架 3.16.1 或更高版本  
- [Swoole 扩展](https://www.openswoole.com)

## 安装

通过 Composer 安装：

```bash
composer require flightphp/async
```

如果您计划使用 Swoole 运行，请安装扩展：

```bash
# 使用 pecl
pecl install swoole
# 或 openswoole
pecl install openswoole

# 或使用包管理器（Debian/Ubuntu 示例）
sudo apt-get install php-swoole
```

## Swoole 快速示例

下面是一个最小设置示例，展示如何使用相同的代码库支持 PHP-FPM（或内置服务器）和 Swoole。

您项目中需要以下文件：

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

此文件是一个简单的开关，用于在开发模式下强制应用以 PHP 模式运行。

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

此文件引导您的 Flight 应用，并在 NOT_SWOOLE 未定义时启动 Swoole 驱动程序。

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// 在 Swoole 模式下运行时要求 SwooleServerDriver 类。
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

一个简洁的驱动程序，展示如何使用 AsyncBridge 和 Swoole 适配器将 Swoole 请求桥接到 Flight。

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
			// 在此处创建特定于工作进程的连接池
		};
		$closePools = function() {
			// 在此处关闭池 / 清理
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

## 运行服务器

- 开发（PHP 内置服务器 / PHP-FPM）：
  - php -S localhost:8000（如果您的 index 在 public/ 中，请添加 -t public/）
- 生产（Swoole）：
  - php swoole_server.php

提示：对于生产环境，请在 Swoole 前使用反向代理（Nginx）来处理 TLS、静态文件和负载均衡。

## 配置说明

Swoole 驱动程序暴露了几个配置选项：
- worker_num：工作进程数量
- max_request：每个工作进程的重启前请求数
- enable_coroutine：使用协程进行并发
- buffer_output_size：输出缓冲区大小

根据您的主机资源和流量模式调整这些设置。

## 错误处理

AsyncBridge 将 Flight 错误转换为正确的 HTTP 响应。您还可以添加路由级别的错误处理：

```php
$app->route('/*', function() use ($app) {
	try {
		// 路由逻辑
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan 和其他运行时

[AdapterMan](https://github.com/joanhey/adapterman) 被支持作为替代运行时适配器。该包设计为可适配的——添加或使用其他适配器通常遵循相同的模式：通过 AsyncBridge 和特定于运行时的适配器将服务器请求/响应转换为 Flight 的请求/响应。