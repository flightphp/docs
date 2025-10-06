# Async

Async est un petit package pour le framework Flight qui vous permet d'exécuter vos applications Flight dans des serveurs et des runtimes asynchrones comme Swoole, AdapterMan, ReactPHP, Amp, RoadRunner, Workerman, etc. Par défaut, il inclut des adaptateurs pour Swoole et AdapterMan.

L'objectif : développer et déboguer avec PHP-FPM (ou le serveur intégré) et passer à Swoole (ou un autre pilote asynchrone) pour la production avec des changements minimaux.

## Exigences

- PHP 7.4 ou supérieur  
- Framework Flight 3.16.1 ou supérieur  
- [Extension Swoole](https://www.openswoole.com)

## Installation

Installez via Composer :

```bash
composer require flightphp/async
```

Si vous prévoyez d'exécuter avec Swoole, installez l'extension :

```bash
# en utilisant pecl
pecl install swoole
# ou openswoole
pecl install openswoole

# ou avec un gestionnaire de paquets (exemple Debian/Ubuntu)
sudo apt-get install php-swoole
```

## Exemple rapide avec Swoole

Voici ci-dessous une configuration minimale qui montre comment supporter à la fois PHP-FPM (ou le serveur intégré) et Swoole en utilisant le même code source.

Fichiers dont vous aurez besoin dans votre projet :

- index.php
- swoole_server.php
- SwooleServerDriver.php

### index.php

Ce fichier est un simple interrupteur qui force l'application à s'exécuter en mode PHP pour le développement.

```php
// index.php
<?php

define('NOT_SWOOLE', true);

include 'swoole_server.php';
```

### swoole_server.php

Ce fichier initialise votre application Flight et démarrera le pilote Swoole lorsque NOT_SWOOLE n'est pas défini.

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

Un pilote concis montrant comment relier les requêtes Swoole à Flight en utilisant AsyncBridge et les adaptateurs Swoole.

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

## Exécution du serveur

- Développement (serveur intégré PHP / PHP-FPM) :
  - php -S localhost:8000 (ou ajoutez -t public/ si votre index est dans public/)
- Production (Swoole) :
  - php swoole_server.php

Astuce : Pour la production, utilisez un proxy inverse (Nginx) devant Swoole pour gérer TLS, les fichiers statiques et l'équilibrage de charge.

## Notes de configuration

Le pilote Swoole expose plusieurs options de configuration :
- worker_num : nombre de processus workers
- max_request : requêtes par worker avant redémarrage
- enable_coroutine : utilisation des coroutines pour la concurrence
- buffer_output_size : taille du tampon de sortie

Ajustez ces paramètres en fonction des ressources de votre hôte et des schémas de trafic.

## Gestion des erreurs

AsyncBridge traduit les erreurs Flight en réponses HTTP appropriées. Vous pouvez également ajouter une gestion d'erreurs au niveau des routes :

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

## AdapterMan et autres runtimes

[AdapterMan](https://github.com/joanhey/adapterman) est supporté en tant qu'adaptateur de runtime alternatif. Le package est conçu pour être adaptable — ajouter ou utiliser d'autres adaptateurs suit généralement le même schéma : convertir la requête/réponse du serveur en requête/réponse Flight via AsyncBridge et les adaptateurs spécifiques au runtime.