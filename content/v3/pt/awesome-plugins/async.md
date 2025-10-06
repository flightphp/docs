# Async

Async é um pequeno pacote para o framework Flight que permite executar seus aplicativos Flight dentro de servidores e runtimes assíncronos como Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman, etc. De fábrica, ele inclui adaptadores para Swoole e AdapterMan.

O objetivo: desenvolver e depurar com PHP-FPM (ou o servidor integrado) e alternar para Swoole (ou outro driver assíncrono) para produção com mudanças mínimas.

## Requisitos

- PHP 7.4 ou superior  
- Framework Flight 3.16.1 ou superior  
- [Extensão Swoole](https://www.openswoole.com)

## Instalação

Instale via composer:

```bash
composer require flightphp/async
```

Se você planeja executar com Swoole, instale a extensão:

```bash
# usando pecl
pecl install swoole
# ou openswoole
pecl install openswoole

# ou com um gerenciador de pacotes (exemplo Debian/Ubuntu)
sudo apt-get install php-swoole
```

## Exemplo rápido com Swoole

Abaixo está uma configuração mínima que mostra como suportar tanto PHP-FPM (ou servidor integrado) quanto Swoole usando o mesmo código base.

Arquivos que você precisará no seu projeto:

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

Este arquivo é um simples interruptor que força o aplicativo a executar no modo PHP para desenvolvimento.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

Este arquivo inicializa seu aplicativo Flight e iniciará o driver Swoole quando NOT_SWOOLE não estiver definido.

```php
// swoole_server.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Flight::app();

$app->route('/', function() use ($app) {
	$app->json(['hello' => 'world']);
});

if (!defined('NOT_SWOOLE')) {
	// Require a classe SwooleServerDriver quando executando no modo Swoole.
	require_once __DIR__ . '/SwooleServerDriver.php';

	Swoole\Runtime::enableCoroutine();
	$Swoole_Server = new SwooleServerDriver('127.0.0.1', 9501, $app);
	$Swoole_Server->start();
} else {
	$app->start();
}
```

### SwooleServerDriver.php

Um driver conciso que mostra como conectar requisições Swoole ao Flight usando o AsyncBridge e os adaptadores Swoole.

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
			echo "Servidor HTTP Swoole iniciado em http://127.0.0.1:9501\n";
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
			// criar pools de conexão específicos do worker aqui
		};
		$closePools = function() {
			// fechar pools / limpeza aqui
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

## Executando o servidor

- Desenvolvimento (servidor integrado PHP / PHP-FPM):
  - php -S localhost:8000 (ou adicione -t public/ se seu index estiver em public/)
- Produção (Swoole):
  - php swoole_server.php

Dica: Para produção, use um proxy reverso (Nginx) na frente do Swoole para lidar com TLS, arquivos estáticos e balanceamento de carga.

## Notas de configuração

O driver Swoole expõe várias opções de configuração:
- worker_num: número de processos de worker
- max_request: requisições por worker antes do reinício
- enable_coroutine: usar corrotinas para concorrência
- buffer_output_size: tamanho do buffer de saída

Ajuste esses valores para se adequar aos recursos do seu host e padrões de tráfego.

## Tratamento de erros

AsyncBridge traduz erros do Flight em respostas HTTP adequadas. Você também pode adicionar tratamento de erros em nível de rota:

```php
$app->route('/*', function() use ($app) {
	try {
		// lógica da rota
	} catch (Exception $e) {
		$app->response()->status(500);
		$app->json(['error' => $e->getMessage()]);
	}
});
```

## AdapterMan e outros runtimes

[AdapterMan](https://github.com/joanhey/adapterman) é suportado como um adaptador de runtime alternativo. O pacote é projetado para ser adaptável — adicionar ou usar outros adaptadores geralmente segue o mesmo padrão: converter a requisição/resposta do servidor em requisição/resposta do Flight via AsyncBridge e os adaptadores específicos do runtime.