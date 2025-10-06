# Async

Async は、Flight フレームワーク用の小さなパッケージで、Swoole、AdapterMan、ReactPHP、Amp、RoadRunner、Workerman などの非同期サーバーおよびランタイム内で Flight アプリを実行できるようにします。デフォルトで Swoole と AdapterMan のアダプターが含まれています。

目標：PHP-FPM（または組み込みサーバー）で開発およびデバッグを行い、本番環境では最小限の変更で Swoole（または他の非同期ドライバー）に切り替えることです。

## 要件

- PHP 7.4 以上  
- Flight フレームワーク 3.16.1 以上  
- [Swoole 拡張](https://www.openswoole.com)

## インストール

Composer を使用してインストールします：

```bash
composer require flightphp/async
```

Swoole で実行する予定の場合、拡張をインストールします：

```bash
# pecl を使用
pecl install swoole
# または openswoole
pecl install openswoole

# またはパッケージマネージャー（Debian/Ubuntu の例）
sudo apt-get install php-swoole
```

## Swoole の簡単な例

以下は、PHP-FPM（または組み込みサーバー）と Swoole の両方を同じコードベースでサポートする方法を示す最小限のセットアップです。

プロジェクトで必要なファイル：

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

このファイルは、開発時にアプリを PHP モードで実行するように強制するシンプルなスイッチです。

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

このファイルは Flight アプリをブートストラップし、NOT_SWOOLE が定義されていない場合に Swoole ドライバーを開始します。

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// Swoole モードで実行する場合に SwooleServerDriver クラスを require します。
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

AsyncBridge と Swoole アダプターを使用して Swoole リクエストを Flight にブリッジする方法を示す簡潔なドライバーです。

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
			// ここでワーカー固有の接続プールを作成します
		};
		$closePools = function() {
			// ここでプールを閉じてクリーンアップします
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

## サーバーの実行

- 開発（PHP 組み込みサーバー / PHP-FPM）:
  - php -S localhost:8000 （index が public/ にある場合は -t public/ を追加）
- 本番（Swoole）:
  - php swoole_server.php

ヒント：本番環境では、TLS、静的ファイル、負荷分散を処理するために Swoole の前にリバースプロキシ（Nginx）を使用してください。

## 設定の注意点

Swoole ドライバーはいくつかの設定オプションを公開しています：
- worker_num: ワーカープロセスの数
- max_request: 再起動前のワーカーあたりのリクエスト数
- enable_coroutine: 並行性のためにコルーチンを使用
- buffer_output_size: 出力バッファサイズ

これらをホストのリソースとトラフィックパターンに合わせて調整してください。

## エラーハンドリング

AsyncBridge は Flight のエラーを適切な HTTP レスポンスに変換します。ルートレベルのエラーハンドリングも追加できます：

```php
$app->route('/*', function() use ($app) {
	try {
		// ルートロジック
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan および他のランタイム

[AdapterMan](https://github.com/joanhey/adapterman) は代替ランタイムアダプターとしてサポートされています。このパッケージは適応性が高く設計されており、他のアダプターを追加または使用する場合も、通常同じパターンを踏襲します：サーバーリクエスト/レスポンスを AsyncBridge とランタイム固有のアダプター経由で Flight のリクエスト/レスポンスに変換します。