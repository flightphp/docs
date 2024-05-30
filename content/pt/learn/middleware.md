# Middleware de Rota

O Flight suporta middleware de rota e de grupo de rotas. O middleware é uma função que é executada antes (ou depois) da chamada de rota. Esta é uma ótima maneira de adicionar verificações de autenticação da API em seu código, ou para validar se o usuário tem permissão para acessar a rota.

## Middleware Básico

Aqui está um exemplo básico:

```php
// Se você fornecer apenas uma função anônima, ela será executada antes da chamada de rota. 
// não existem funções de middleware "depois" exceto para classes (veja abaixo)
Flight::route('/caminho', function() { echo ' Aqui estou!'; })->addMiddleware(function() {
	echo 'Middleware primeiro!';
});

Flight::start();

// Isso irá exibir "Middleware primeiro! Aqui estou!"
```

Existem algumas notas muito importantes sobre middleware que você deve estar ciente antes de usá-los:
- As funções de middleware são executadas na ordem em que são adicionadas à rota. A execução é semelhante a como [Slim Framework lida com isso](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - As seções "antes" são executadas na ordem adicionada, e as seções "depois" são executadas na ordem inversa.
- Se a sua função de middleware retornar false, toda a execução será interrompida e um erro 403 Proibido será lançado. Você provavelmente vai querer lidar com isso de forma mais graciosa com um `Flight::redirect()` ou algo semelhante.
- Se precisar de parâmetros da sua rota, eles serão passados em um único array para a função de middleware. (`function($params) { ... }` ou `public function before($params) {}`). A razão para isso é que você pode estruturar seus parâmetros em grupos e em alguns desses grupos, seus parâmetros podem realmente aparecer em uma ordem diferente, o que quebraria a função de middleware referindo-se ao parâmetro errado. Dessa forma, você pode acessá-los pelo nome em vez da posição.
- Se você passar apenas o nome do middleware, ele será automaticamente executado pelo [contêiner de injeção de dependência](dependency-injection-container) e o middleware será executado com os parâmetros necessários. Se você não tiver um contêiner de injeção de dependência registrado, ele passará a instância `flight\Engine` para o `__construct()`.

## Classes de Middleware

O middleware também pode ser registrado como uma classe. Se precisar da funcionalidade "depois", você **deve** usar uma classe.

```php
class MeuMiddleware {
	public function before($params) {
		echo 'Middleware primeiro!';
	}

	public function after($params) {
		echo 'Middleware último!';
	}
}

$MeuMiddleware = new MeuMiddleware();
Flight::route('/caminho', function() { echo ' Aqui estou! '; })->addMiddleware($MeuMiddleware); // também ->addMiddleware([ $MeuMiddleware, $MeuMiddleware2 ]);

Flight::start();

// Isso exibirá "Middleware primeiro! Aqui estou! Middleware último!"
```

## Lidando com Erros de Middleware

Digamos que você tenha um middleware de autenticação e deseje redirecionar o usuário para uma página de login se ele não estiver autenticado. Você tem algumas opções à sua disposição:

1. Você pode retornar false a partir da função de middleware e o Flight retornará automaticamente um erro 403 Proibido, mas sem personalização.
1. Você pode redirecionar o usuário para uma página de login usando `Flight::redirect()`.
1. Você pode criar um erro personalizado dentro do middleware e interromper a execução da rota.

### Exemplo Básico

Aqui está um exemplo simples de retorno de false:
```php
class MeuMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// como é true, tudo continua
	}
}
```

### Exemplo de Redirecionamento

Aqui está um exemplo de redirecionamento do usuário para uma página de login:
```php
class MeuMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Exemplo de Erro Personalizado

Digamos que você precise retornar um erro JSON porque está construindo uma API. Você pode fazer isso assim:
```php
class MeuMiddleware {
	public function before($params) {
		$autorizacao = Flight::request()->headers['Authorization'];
		if(empty($autorizacao)) {
			Flight::json(['erro' => 'Você precisa estar logado para acessar esta página.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'Você precisa estar logado para acessar esta página.']);
		}
	}
}
```

## Agrupamento de Middleware

Você pode adicionar um grupo de rota e, em seguida, cada rota nesse grupo terá o mesmo middleware também. Isso é útil se você precisar agrupar um monte de rotas, por exemplo, por um middleware de autenticação para verificar a chave da API no cabeçalho.

```php

// adicionado ao final do método do grupo
Flight::group('/api', function() {

	// Esta rota com aparência "vazia" corresponderá na verdade a /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/usuarios', function() { echo 'usuários'; }, false, 'usuários');
	Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizar_usuario');
}, [ new ApiAuthMiddleware() ]);
```

Se você deseja aplicar um middleware global a todas as suas rotas, você pode adicionar um grupo "vazio":

```php

// adicionado ao final do método do grupo
Flight::group('', function() {
	Flight::route('/usuarios', function() { echo 'usuários'; }, false, 'usuários');
	Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizar_usuario');
}, [ new ApiAuthMiddleware() ]);
```