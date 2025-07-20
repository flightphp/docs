<?php

namespace app\utils; 

use flight\adapter\SwooleAsyncRequest;
use flight\adapter\SwooleAsyncResponse;
use flight\AsyncBridge;
use flight\Engine;
use Swoole\HTTP\Server as SwooleServer;
use Swoole\HTTP\Request as SwooleRequest;
use Swoole\HTTP\Response as SwooleResponse;

class SwooleServerDriver {

    //use ConnectionPoolTrait;

    /** @var SwooleServer */
    protected $Swoole;

    /** @var Engine */
    protected $app;

    public function __construct(string $host, int $port, Engine $app) {
        $this->Swoole = new SwooleServer($host, $port);
        $this->app = $app;

        $this->setDefault();
        $this->bindWorkerEvents();
        $this->bindHttpEvent();
    }

    protected function setDefault() {
        // A bunch of default settings for the Swoole server.
        // You can customize these settings based on your needs.
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
            'document_root'         => dirname(__DIR__, 2) . '/public',
            'static_handler_locations' => ['/css', '/js', '/images', '/.well-known'],
            'buffer_output_size'    => 4 * 1024 * 1024,
            'worker_num'            => 4, // Each worker holds a connection pool
        ]);

		// Custom little hack
		// Makes it so the app doesn't stop when it runs.
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
        $this->Swoole->on("Start", function(SwooleServer $server) {
            echo "Swoole http server is started at http://127.0.0.1:9501\n";
        });

        // This is where the magic happens, the request is processed by the AsyncBridge
        $this->Swoole->on('Request', function (SwooleRequest $request, SwooleResponse $response) use ($AsyncBridge) {
            $SwooleAsyncRequest = new SwooleAsyncRequest($request);
            $SwooleAsyncResponse = new SwooleAsyncResponse($response);
            $AsyncBridge->processRequest($SwooleAsyncRequest, $SwooleAsyncResponse);
            $response->end();

            gc_collect_cycles(); // Collect garbage to free memory (optional)
        });
    }

    protected function bindWorkerEvents() {
        // You can use this to set custom events for the workers, such as creating connection pools.
        $createPools = function() {
            // Create connection pools for each worker
            // This is useful for managing database connections or other resources that need to be shared across requests.
        };
        $closePools = function() {
            // Close connection pools for each worker
            // This is useful for cleaning up resources when the worker stops or encounters an error.
        };
        $this->Swoole->on('WorkerStart', $createPools);
        $this->Swoole->on('WorkerStop', $closePools);
        $this->Swoole->on('WorkerError', $closePools);
    }

    public function start() {
        $this->Swoole->start();
    }
}