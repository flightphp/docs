# Middleware

## Visão Geral

Flight suporta middleware de rota e middleware de grupo de rotas. O middleware é uma parte da sua aplicação onde o código é executado antes 
(ou depois) do callback da rota. Essa é uma ótima maneira de adicionar verificações de autenticação de API no seu código, ou para validar que 
o usuário tem permissão para acessar a rota.

## Entendendo

O middleware pode simplificar greatly sua app. Em vez de herança complexa de classes abstratas ou sobrescritas de métodos, o middleware 
permite que você controle suas rotas atribuindo sua lógica de app personalizada a elas. Você pode pensar no middleware como
um sanduíche. Você tem pão por fora, e então camadas de ingredientes como alface, tomates, carnes e queijo. Então imagine
que cada requisição é como dar uma mordida no sanduíche onde você come as camadas externas primeiro e trabalha seu caminho até o núcleo.

Aqui está uma visualização de como o middleware funciona. Em seguida, mostraremos um exemplo prático de como isso funciona.

```text
Requisição do usuário na URL /api ----> 
	Middleware->before() executado ----->
		Callable/método anexado a /api executado e resposta gerada ------>
	Middleware->after() executado ----->
Usuário recebe resposta do servidor
```

E aqui está um exemplo prático:

```text
Usuário navega para a URL /dashboard
	LoggedInMiddleware->before() executa
		before() verifica sessão logada válida
			se sim, não faz nada e continua a execução
			se não, redireciona o usuário para /login
				Callable/método anexado a /api executado e resposta gerada
	LoggedInMiddleware->after() não tem nada definido, então deixa a execução continuar
Usuário recebe HTML do dashboard do servidor
```

### Ordem de Execução

As funções de middleware são executadas na ordem em que são adicionadas à rota. A execução é semelhante a como [Slim Framework lida com isso](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

Os métodos `before()` são executados na ordem adicionada, e os métodos `after()` são executados em ordem reversa.

Ex: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Uso Básico

Você pode usar middleware como qualquer método callable, incluindo uma função anônima ou uma classe (recomendado)

### Função Anônima

Aqui está um exemplo simples:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Isso exibirá "Middleware first! Here I am!"
```

> **Nota:** Ao usar uma função anônima, o único método interpretado é um método `before()`. Você **não pode** definir comportamento `after()` com uma classe anônima.

### Usando Classes

O middleware pode (e deve) ser registrado como uma classe. Se você precisar da funcionalidade "after", você **deve** usar uma classe.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// também ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Isso exibirá "Middleware first! Here I am! Middleware last!"
```

Você também pode apenas definir o nome da classe de middleware e ela instanciará a classe.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Nota:** Se você passar apenas o nome do middleware, ele será automaticamente executado pelo [container de injeção de dependência](dependency-injection-container) e o middleware será executado com os parâmetros que precisa. Se você não tiver um container de injeção de dependência registrado, ele passará por padrão a instância de `flight\Engine` no `__construct(Engine $app)`.

### Usando Rotas com Parâmetros

Se você precisar de parâmetros da sua rota, eles serão passados em um único array para a função de middleware. (`function($params) { ... }` ou `public function before($params) { ... }`). A razão para isso é que você pode estruturar seus parâmetros em grupos e em alguns desses grupos, seus parâmetros podem aparecer em uma ordem diferente, o que quebraria a função de middleware ao se referir ao parâmetro errado. Dessa forma, você pode acessá-los por nome em vez de posição.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId pode ou não ser passado
		$jobId = $params['jobId'] ?? 0;

		// talvez se não houver ID de job, você não precise buscar nada.
		if($jobId === 0) {
			return;
		}

		// execute uma busca de algum tipo no seu banco de dados
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// Este grupo abaixo ainda recebe o middleware do pai
	// Mas os parâmetros são passados em um único array 
	// no middleware.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// mais rotas...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Agrupando Rotas com Middleware

Você pode adicionar um grupo de rota, e então toda rota nesse grupo terá o mesmo middleware também. Isso é 
útil se você precisar agrupar um monte de rotas por, digamos, um middleware de Auth para verificar a chave de API no header.

```php

// adicionado no final do método de grupo
Flight::group('/api', function() {

	// Esta rota "vazia" na verdade corresponderá a /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Isso corresponderá a /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Isso corresponderá a /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Se você quiser aplicar um middleware global a todas as suas rotas, você pode adicionar um grupo "vazio":

```php

// adicionado no final do método de grupo
Flight::group('', function() {

	// Isso ainda é /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// E isso ainda é /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // ou [ new ApiAuthMiddleware() ], mesma coisa
```

### Casos de Uso Comuns

#### Validação de Chave de API
Se você quiser proteger suas rotas `/api` verificando se a chave de API está correta, você pode lidar com isso facilmente com middleware.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// faça uma busca no seu banco de dados pela chave de api
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// mais rotas...
}, [ ApiMiddleware::class ]);
```

Agora todas as suas rotas de API estão protegidas por este middleware de validação de chave de API que você configurou! Se você adicionar mais rotas ao grupo do roteador, elas terão instantaneamente a mesma proteção!

#### Validação de Login

Você quer proteger algumas rotas para que estejam disponíveis apenas para usuários logados? Isso pode ser facilmente alcançado com middleware!

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// mais rotas...
}, [ LoggedInMiddleware::class ]);
```

#### Validação de Parâmetro de Rota

Você quer proteger seus usuários de alterar valores na URL para acessar dados que não deveriam? Isso pode ser resolvido com middleware!

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// execute uma busca de algum tipo no seu banco de dados
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// mais rotas...
}, [ RouteSecurityMiddleware::class ]);
```

## Lidando com a Execução de Middleware

Digamos que você tenha um middleware de autenticação e queira redirecionar o usuário para uma página de login se ele não 
estiver autenticado. Você tem algumas opções à sua disposição:

1. Você pode retornar false da função de middleware e Flight retornará automaticamente um erro 403 Forbidden, mas sem customização.
1. Você pode redirecionar o usuário para uma página de login usando `Flight::redirect()`.
1. Você pode criar um erro customizado dentro do middleware e interromper a execução da rota.

### Simples e Direto

Aqui está um exemplo simples de `return false;` :

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// como é true, tudo continua
	}
}
```

### Exemplo de Redirecionamento

Aqui está um exemplo de redirecionar o usuário para uma página de login:
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Exemplo de Erro Customizado

Digamos que você precise lançar um erro JSON porque está construindo uma API. Você pode fazer isso assim:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// ou
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Veja Também
- [Routing](/learn/routing) - Como mapear rotas para controladores e renderizar views.
- [Requests](/learn/requests) - Entendendo como lidar com requisições de entrada.
- [Responses](/learn/responses) - Como customizar respostas HTTP.
- [Dependency Injection](/learn/dependency-injection-container) - Simplificando a criação e gerenciamento de objetos em rotas.
- [Why a Framework?](/learn/why-frameworks) - Entendendo os benefícios de usar um framework como Flight.
- [Middleware Execution Strategy Example](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Solução de Problemas
- Se você tiver um redirecionamento no seu middleware, mas sua app não parecer estar redirecionando, certifique-se de adicionar uma declaração `exit;` no seu middleware.

## Changelog
- v3.1: Adicionado suporte para middleware.