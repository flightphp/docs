# Async

Async adalah paket kecil untuk framework Flight yang memungkinkan Anda menjalankan aplikasi Flight di dalam server dan runtime asinkron seperti Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman, dll. Secara default, ia menyertakan adapter untuk Swoole dan AdapterMan.

Tujuan: mengembangkan dan mendebug dengan PHP-FPM (atau server bawaan) dan beralih ke Swoole (atau driver asinkron lainnya) untuk produksi dengan perubahan minimal.

## Persyaratan

- PHP 7.4 atau lebih tinggi  
- Framework Flight 3.16.1 atau lebih tinggi  
- [Ekstensi Swoole](https://www.openswoole.com)

## Instalasi

Instal melalui composer:

```bash
composer require flightphp/async
```

Jika Anda berencana menjalankan dengan Swoole, instal ekstensi tersebut:

```bash
# menggunakan pecl
pecl install swoole
# atau openswoole
pecl install openswoole

# atau dengan pengelola paket (contoh Debian/Ubuntu)
sudo apt-get install php-swoole
```

## Contoh Cepat Swoole

Berikut adalah pengaturan minimal yang menunjukkan cara mendukung baik PHP-FPM (atau server bawaan) maupun Swoole menggunakan kode dasar yang sama.

File yang Anda butuhkan dalam proyek Anda:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

File ini adalah saklar sederhana yang memaksa aplikasi berjalan dalam mode PHP untuk pengembangan.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

File ini memulai aplikasi Flight Anda dan akan memulai driver Swoole ketika NOT_SWOOLE tidak didefinisikan.

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// Require kelas SwooleServerDriver ketika berjalan dalam mode Swoole.
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

Driver ringkas yang menunjukkan cara menjembatani permintaan Swoole ke Flight menggunakan AsyncBridge dan adapter Swoole.

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

## Menjalankan Server

- Pengembangan (server bawaan PHP / PHP-FPM):
  - php -S localhost:8000 (atau tambahkan -t public/ jika index Anda berada di public/)
- Produksi (Swoole):
  - php swoole_server.php

Tips: Untuk penggunaan produksi, gunakan proxy terbalik (Nginx) di depan Swoole untuk menangani TLS, file statis, dan penyeimbangan beban.

## Catatan Konfigurasi

Driver Swoole mengekspos beberapa opsi konfigurasi:
- worker_num: jumlah proses pekerja
- max_request: permintaan per pekerja sebelum restart
- enable_coroutine: gunakan coroutine untuk konkurensi
- buffer_output_size: ukuran buffer output

Sesuaikan ini dengan sumber daya host dan pola lalu lintas Anda.

## Penanganan Kesalahan

AsyncBridge menerjemahkan kesalahan Flight menjadi respons HTTP yang tepat. Anda juga dapat menambahkan penanganan kesalahan pada tingkat rute:

```php
$app->route('/*', function() use ($app) {
	try {
		// logika rute
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan dan Runtime Lainnya

[AdapterMan](https://github.com/joanhey/adapterman) didukung sebagai adapter runtime alternatif. Paket ini dirancang untuk dapat diadaptasi — menambahkan atau menggunakan adapter lain umumnya mengikuti pola yang sama: mengonversi permintaan/respons server menjadi permintaan/respons Flight melalui AsyncBridge dan adapter khusus runtime.