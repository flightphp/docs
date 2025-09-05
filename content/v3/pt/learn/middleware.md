# Middleware de Rota

Flight suporta middleware de rota e grupo de rota. Middleware é uma função que é executada antes (ou depois) do callback da rota. Esta é uma ótima maneira de adicionar verificações de autenticação de API no seu código, ou para validar que o usuário tem permissão para acessar a rota.

## Middleware Básica

Aqui vai um exemplo básico:

```php
// Se você fornecer apenas uma função anônima, ela será executada antes do callback da rota. 
// não há funções de middleware "after" exceto para classes (veja abaixo)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Isso vai exibir "Middleware first! Here I am!"
```

Há algumas notas muito importantes sobre middleware que você deve saber antes de usá-las:
- Funções de middleware são executadas na ordem em que são adicionadas à rota. A execução é semelhante a como o [Slim Framework lida com isso](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Befores são executados na ordem adicionada, e Afters são executados em ordem inversa.
- Se a função de middleware retornar false, toda a execução é parada e um erro 403 Forbidden é lançado. Você provavelmente vai querer lidar com isso de forma mais elegante com um `Flight::redirect()` ou algo semelhante.
- Se você precisar de parâmetros da sua rota, eles serão passados em um único array para a função de middleware. (`function($params) { ... }` or `public function before($params) {}`). O motivo para isso é que você pode estruturar seus parâmetros em grupos e em alguns desses grupos, seus parâmetros podem aparecer em uma ordem diferente, o que quebraria a função de middleware ao se referir ao parâmetro errado. Dessa forma, você pode acessá-los pelo nome em vez de posição.
- Se você passar apenas o nome do middleware, ele será automaticamente executado pelo [contêiner de injeção de dependência](dependency-injection-container) e o middleware será executado com os parâmetros de que precisa. Se você não tiver um contêiner de injeção de dependência registrado, ele passará a instância de `flight\Engine` para o `__construct()`.

## Classes de Middleware

Middleware também pode ser registrado como uma classe. Se você precisar da funcionalidade "after", você **deve** usar uma classe.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // também ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Isso vai exibir "Middleware first! Here I am! Middleware last!"
```

## Tratamento de Erros de Middleware

Digamos que você tenha um middleware de autenticação e deseja redirecionar o usuário para uma página de login se ele não estiver autenticado. Você tem algumas opções à sua disposição:

1. Você pode retornar false da função de middleware e Flight vai automaticamente retornar um erro 403 Forbidden, mas sem personalização.
1. Você pode redirecionar o usuário para uma página de login usando `Flight::redirect()`.
1. Você pode criar um erro personalizado dentro do middleware e parar a execução da rota.

### Exemplo Básico

Aqui vai um exemplo simples de return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// como é true, tudo continua normalmente
	}
}
```

### Exemplo de Redirecionamento

Aqui vai um exemplo de redirecionar o usuário para uma página de login:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Exemplo de Erro Personalizado

Digamos que você precise lançar um erro JSON porque está construindo uma API. Você pode fazer isso assim:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
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

## Agrupando Middleware

Você pode adicionar um grupo de rotas, e então toda rota nesse grupo terá o mesmo middleware também. Isso é útil se você precisar agrupar um monte de rotas por, digamos, um middleware de Autenticação para verificar a chave da API no cabeçalho.

```php

// adicionado no final do método group
Flight::group('/api', function() {

	// Esta rota "vazia" vai realmente corresponder a /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Isso vai corresponder a /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Isso vai corresponder a /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Se você quiser aplicar um middleware global a todas as suas rotas, você pode adicionar um grupo "vazio":

```php

// adicionado no final do método group
Flight::group('', function() {

	// Isso ainda é /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// E isso ainda é /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // ou [ new ApiAuthMiddleware() ], mesma coisa
```