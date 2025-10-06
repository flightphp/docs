# Async

Async는 Flight 프레임워크를 위한 작은 패키지로, Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman 등의 비동기 서버와 런타임에서 Flight 앱을 실행할 수 있게 합니다. 기본적으로 Swoole과 AdapterMan 어댑터를 포함합니다.

목표: PHP-FPM(또는 내장 서버)으로 개발하고 디버그한 후, 프로덕션에서 Swoole(또는 다른 비동기 드라이버)로 최소한의 변경으로 전환합니다.

## 요구 사항

- PHP 7.4 이상  
- Flight 프레임워크 3.16.1 이상  
- [Swoole 확장](https://www.openswoole.com)

## 설치

Composer를 통해 설치하세요:

```bash
composer require flightphp/async
```

Swoole로 실행할 계획이라면 확장을 설치하세요:

```bash
# pecl 사용
pecl install swoole
# 또는 openswoole
pecl install openswoole

# 또는 패키지 관리자 사용 (Debian/Ubuntu 예시)
sudo apt-get install php-swoole
```

## 간단한 Swoole 예제

아래는 동일한 코드베이스를 사용하여 PHP-FPM(또는 내장 서버)와 Swoole을 모두 지원하는 최소 설정입니다.

프로젝트에서 필요한 파일:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

이 파일은 개발 시 앱을 PHP 모드로 강제 실행하는 간단한 스위치입니다.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

이 파일은 Flight 앱을 부트스트랩하고, NOT_SWOOLE이 정의되지 않으면 Swoole 드라이버를 시작합니다.

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// Swoole 모드에서 실행될 때 SwooleServerDriver 클래스를 요구합니다.
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

AsyncBridge와 Swoole 어댑터를 사용하여 Swoole 요청을 Flight로 연결하는 간결한 드라이버입니다.

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
			// 여기서 워커별 연결 풀을 생성합니다.
		};
		$closePools = function() {
			// 여기서 풀을 닫거나 정리합니다.
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

## 서버 실행

- 개발 (PHP 내장 서버 / PHP-FPM):
  - php -S localhost:8000 (index가 public/에 있는 경우 -t public/ 추가)
- 프로덕션 (Swoole):
  - php swoole_server.php

팁: 프로덕션에서는 TLS, 정적 파일, 로드 밸런싱을 처리하기 위해 Swoole 앞에 리버스 프록시(Nginx)를 사용하세요.

## 구성 노트

Swoole 드라이버는 여러 구성 옵션을 노출합니다:
- worker_num: 워커 프로세스 수
- max_request: 재시작 전 워커당 요청 수
- enable_coroutine: 동시성을 위한 코루틴 사용
- buffer_output_size: 출력 버퍼 크기

호스트 리소스와 트래픽 패턴에 맞게 조정하세요.

## 오류 처리

AsyncBridge는 Flight 오류를 적절한 HTTP 응답으로 변환합니다. 라우트 수준 오류 처리도 추가할 수 있습니다:

```php
$app->route('/*', function() use ($app) {
	try {
		// 라우트 로직
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan 및 기타 런타임

[AdapterMan](https://github.com/joanhey/adapterman)은 대안 런타임 어댑터로 지원됩니다. 이 패키지는 적응 가능하게 설계되었습니다 — 다른 어댑터를 추가하거나 사용하는 것은 일반적으로 동일한 패턴을 따릅니다: AsyncBridge와 런타임별 어댑터를 통해 서버 요청/응답을 Flight의 요청/응답으로 변환합니다.