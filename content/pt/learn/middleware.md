# Middleware de Rota

O Flight suporta middleware de rota e de grupo de rotas. O middleware é uma função que é executada antes (ou depois) do retorno da rota. Esta é uma ótima maneira de adicionar verificações de autenticação da API em seu código, ou para validar se o usuário tem permissão para acessar a rota.

## Middleware Básico

Aqui está um exemplo básico:

```php
// Se você fornecer apenas uma função anônima, ela será executada antes do retorno da rota.
// não existem funções de middleware "after" exceto para classes (veja abaixo)
Flight::route('/caminho', function() { echo ' Aqui estou!'; })->addMiddleware(function() {
	echo 'Middleware primeiro!';
});

Flight::start();

// Isso irá produzir "Middleware primeiro! Aqui estou!"
```

Existem algumas notas muito importantes sobre middleware que você deve ter em mente antes de usá-los:
- As funções do middleware são executadas na ordem em que são adicionadas à rota. A execução é semelhante a como [o Framework Slim lida com isso](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Os "Befores" são executados na ordem adicionada, e os "Afters" são executados na ordem inversa.
- Se sua função de middleware retornar falso, toda a execução será interrompida e um erro 403 Forbidden será lançado. Provavelmente você vai querer lidar com isso de maneira mais graciosa com um `Flight::redirect()` ou algo similar.
- Se você precisar de parâmetros de sua rota, eles serão passados em um único array para sua função de middleware. (`function($params) { ... }` ou `public function before($params) {}`). A razão para isso é que você pode estruturar seus parâmetros em grupos e em alguns desses grupos, seus parâmetros podem realmente aparecer em uma ordem diferente que quebraria a função de middleware ao se referir ao parâmetro errado. Dessa forma, você pode acessá-los pelo nome em vez da posição.
- Se você passar apenas o nome do middleware, ele será executado automaticamente pelo [contêiner de injeção de dependência](dependency-injection-container) e o middleware será executado com os parâmetros necessários. Se você não tiver um contêiner de injeção de dependência registrado, ele passará a instância `flight\Engine` para o `__construct()`.

## Classes de Middleware

O middleware pode ser registrado como uma classe também. Se você precisa da funcionalidade "after", você **deve** usar uma classe.

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

// Isso irá exibir "Middleware primeiro! Aqui estou! Middleware último!"
```

## Lidando com Erros de Middleware

Digamos que você tenha um middleware de autenticação e deseje redirecionar o usuário para uma página de login se ele não estiver autenticado. Você tem algumas opções à sua disposição:

1. Você pode retornar falso da função do middleware e o Flight retornará automaticamente um erro 403 Forbidden, mas sem personalização.
1. Você pode redirecionar o usuário para uma página de login usando `Flight::redirect()`.
1. Você pode criar um erro personalizado dentro do middleware e interromper a execução da rota.

### Exemplo Básico

Aqui está um exemplo simples de retorno falso:
```php
class MeuMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// como é verdadeiro, tudo continua normalmente
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

Digamos que você precise lançar um erro JSON porque está construindo uma API. Você pode fazer isso assim:
```php
class MeuMiddleware {
	public function before($params) {
		$autorizacao = Flight::request()->headers['Authorization'];
		if(empty($autorizacao)) {
			Flight::jsonHalt(['error' => 'Você deve estar logado para acessar esta página.'], 403);
			// ou
			Flight::json(['error' => 'Você deve estar logado para acessar esta página.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'Você deve estar logado para acessar esta página.']);
		}
	}
}
```

## Agrupando Middleware

Você pode adicionar um grupo de rota e, em seguida, cada rota nesse grupo terá o mesmo middleware também. Isso é útil se você precisar agrupar um monte de rotas, digamos por um middleware de Autenticação para verificar a chave da API no cabeçalho.

```php

// adicionado no final do método de grupo
Flight::group('/api', function() {

	// Esta rota com aparência "vazia" na verdade corresponderá a /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Esta corresponderá a /api/usuarios
    Flight::route('/usuarios', function() { echo 'usuários'; }, false, 'usuários');
	// Esta corresponderá a /api/usuarios/1234
	Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_de_usuario');
}, [ new MiddlewaredeAutenticacaoApi() ]);
```

Se você deseja aplicar um middleware global a todas as suas rotas, pode adicionar um grupo "vazio":

```php

// adicionado no final do método de grupo
Flight::group('', function() {

	// Isto ainda é /usuários
	Flight::route('/usuários', function() { echo 'usuários'; }, false, 'usuários');
	// E isto ainda é /usuários/1234
	Flight::route('/usuários/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_de_usuario');
}, [ new MiddlewaredeAutenticacaoApi() ]);
```  