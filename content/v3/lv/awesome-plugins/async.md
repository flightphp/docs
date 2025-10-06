# Async

Async ir mazs pakotnes Flight ietvaram, kas ļauj palaist jūsu Flight lietotnes asinhronos serveros un vidēs, piemēram, Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman utt. No kastes tas ietver adapterus Swoole un AdapterMan.

Mērķis: izstrādāt un atkļūdot ar PHP-FPM (vai iebūvēto serveri) un pārslēgties uz Swoole (vai citu asinhrono draiveri) ražošanā ar minimālām izmaiņām.

## Prasības

- PHP 7.4 vai augstāka  
- Flight ietvars 3.16.1 vai augstāka  
- [Swoole paplašinājums](https://www.openswoole.com)

## Instalēšana

Instalējiet caur composer:

```bash
composer require flightphp/async
```

Ja plānojat palaist ar Swoole, instalējiet paplašinājumu:

```bash
# izmantojot pecl
pecl install swoole
# vai openswoole
pecl install openswoole

# vai ar pakotņu pārvaldnieku (Debian/Ubuntu piemērs)
sudo apt-get install php-swoole
```

## Ātrs Swoole piemērs

Zemāk ir minimāla iestatīšana, kas parāda, kā atbalstīt gan PHP-FPM (vai iebūvēto serveri), gan Swoole, izmantojot to pašu koda bāzi.

Faili, kas būs nepieciešami jūsu projektā:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

Šis fails ir vienkāršs slēdzis, kas piespiež lietotni palaist PHP režīmā izstrādei.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

Šis fails inicializē jūsu Flight lietotni un sāks Swoole draiveri, kad NOT_SWOOLE nav definēts.

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// Nepieciešams SwooleServerDriver klase, kad darbojas Swoole režīmā.
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

Īss draiveris, kas parāda, kā savienot Swoole pieprasījumus ar Flight, izmantojot AsyncBridge un Swoole adapterus.

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
			// izveidojiet darbinieka specifiskas savienojumu kopas šeit
		};
		$closePools = function() {
			// aizveriet kopas / tīriet šeit
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

## Servera palaišana

- Izstrāde (PHP iebūvētais serveris / PHP-FPM):
  - php -S localhost:8000 (vai pievienojiet -t public/ ja jūsu index ir public/)
- Ražošana (Swoole):
  - php swoole_server.php

Padoms: Ražošanā izmantojiet reverso proxy (Nginx) Swoole priekšā, lai apstrādātu TLS, statiskos failus un slodzes līdzsvarošanu.

## Konfigurācijas piezīmes

Swoole draiveris piedāvā vairākas konfigurācijas opcijas:
- worker_num: darbinieku procesu skaits
- max_request: pieprasījumi uz darbinieku pirms restartēšanas
- enable_coroutine: izmantojiet korutīnas vienlaicībai
- buffer_output_size: izvades bufera izmērs

Pielāgojiet šos, lai atbilstu jūsu resursiem un trafika modeļiem.

## Kļūdu apstrāde

AsyncBridge pārvērš Flight kļūdas pareizās HTTP atbildēs. Jūs varat arī pievienot maršruta līmeņa kļūdu apstrādi:

```php
$app->route('/*', function() use ($app) {
	try {
		// maršruta loģika
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan un citas vidēs

[AdapterMan](https://github.com/joanhey/adapterman) tiek atbalstīts kā alternatīvs vidēs adapteris. Pakotne ir paredzēta pielāgošanai — pievienošana vai izmantošana citu adapteru parasti seko tam pašam modelim: pārveido servera pieprasījumu/atbildi par Flight pieprasījumu/atbildi caur AsyncBridge un vidēs specifiskajiem adapteriem.