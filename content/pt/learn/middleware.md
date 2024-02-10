# Middleware de Rota

O Flight suporta middleware de rota e de grupo de rotas. O middleware é uma função que é executada antes (ou depois) do retorno da rota. Esta é uma ótima maneira de adicionar verificações de autenticação de API em seu código, ou para validar se o usuário tem permissão para acessar a rota.

## Middleware Básico

Aqui está um exemplo básico:

```php
// Se você fornecer apenas uma função anônima, ela será executada antes do retorno da rota.
// não há funções de middleware "after" exceto para classes (veja abaixo)
Flight::route('/caminho', function() { echo ' Aqui estou!'; })->addMiddleware(function() {
	echo 'Middleware primeiro!';
});

Flight::start();

// Isso irá produzir "Middleware primeiro! Aqui estou!"
```

Existem algumas notas muito importantes sobre middleware que você deve estar ciente antes de usá-los:
- As funções de middleware são executadas na ordem em que são adicionadas à rota. A execução é semelhante a como [Slim Framework lida com isso](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Os "befores" são executados na ordem adicionada, e os "afters" são executados em ordem reversa.
- Se sua função de middleware retornar false, toda a execução é interrompida e um erro 403 Proibido é lançado. Provavelmente você vai querer lidar com isso de forma mais graciosa com um `Flight::redirect()` ou algo similar.
- Se você precisar de parâmetros da sua rota, eles serão passados em um único array para a sua função de middleware. (`function($params) { ... }` ou `public function before($params) {}`). A razão para isso é que você pode estruturar seus parâmetros em grupos e em alguns desses grupos, seus parâmetros podem realmente aparecer em uma ordem diferente que quebraria a função de middleware ao se referir ao parâmetro errado. Desta forma, você pode acessá-los pelo nome em vez da posição.

## Classes de Middleware

O middleware também pode ser registrado como uma classe. Se você precisar da funcionalidade "after", você **deve** usar uma classe.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware primeiro!';
	}

	public function after($params) {
		echo 'Middleware último!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/caminho', function() { echo ' Aqui estou! '; })->addMiddleware($MyMiddleware); // também ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Isso exibirá "Middleware primeiro! Aqui estou! Middleware último!"
```

## Agrupando Middleware

Você pode adicionar um grupo de rotas e, em seguida, cada rota nesse grupo terá o mesmo middleware também. Isso é útil se você precisar agrupar várias rotas, por exemplo, em um middleware Auth para verificar a chave da API no cabeçalho.

```php

// adicionado no final do método de grupo
Flight::group('/api', function() {

	// Essa rota com aparência "vazia" na verdade corresponderá a /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_usuario');
}, [ new ApiAuthMiddleware() ]);
```

Se você deseja aplicar um middleware global a todas as suas rotas, pode adicionar um grupo "vazio":

```php

// adicionado no final do método de grupo
Flight::group('', function() {
	Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_usuario');
}, [ new ApiAuthMiddleware() ]);
```