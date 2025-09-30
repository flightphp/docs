# Roteamento

## Visão Geral
O roteamento no Flight PHP mapeia padrões de URL para funções de callback ou métodos de classe, permitindo o tratamento rápido e simples de requisições. Ele é projetado para overhead mínimo, uso amigável para iniciantes e extensibilidade sem dependências externas.

## Entendendo
O roteamento é o mecanismo central que conecta requisições HTTP à lógica da sua aplicação no Flight. Ao definir rotas, você especifica como diferentes URLs acionam código específico, seja através de funções, métodos de classe ou ações de controlador. O sistema de roteamento do Flight é flexível, suportando padrões básicos, parâmetros nomeados, expressões regulares e recursos avançados como injeção de dependência e roteamento de recursos. Essa abordagem mantém seu código organizado e fácil de manter, enquanto permanece rápido e simples para iniciantes e extensível para usuários avançados.

> **Nota:** Quer entender mais sobre roteamento? Confira a página ["por que um framework?](/learn/why-frameworks)" para uma explicação mais aprofundada.

## Uso Básico

### Definindo uma Rota Simples
O roteamento básico no Flight é feito combinando um padrão de URL com uma função de callback ou um array de classe e método.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> As rotas são combinadas na ordem em que são definidas. A primeira rota que combinar com uma requisição será invocada.

### Usando Funções como Callbacks
O callback pode ser qualquer objeto que seja chamável. Então você pode usar uma função regular:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Usando Classes e Métodos como um Controlador
Você também pode usar um método (estático ou não) de uma classe:

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// ou
Flight::route('/', [ GreetingController::class, 'hello' ]); // método preferido
// ou
Flight::route('/', [ 'GreetingController::hello' ]);
// ou 
Flight::route('/', [ 'GreetingController->hello' ]);
```

Ou criando um objeto primeiro e depois chamando o método:

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **Nota:** Por padrão, quando um controlador é chamado dentro do framework, a classe `flight\Engine` é sempre injetada, a menos que você especifique através de um [contêiner de injeção de dependência](/learn/dependency-injection-container)

### Roteamento Específico de Método

Por padrão, os padrões de rota são combinados contra todos os métodos de requisição. Você pode responder a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Você não pode usar Flight::get() para rotas, pois esse é um método 
//    para obter variáveis, não para criar uma rota.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

Você também pode mapear múltiplos métodos para um único callback usando o delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Usando o Objeto Router

Adicionalmente, você pode obter o objeto Router, que tem alguns métodos auxiliares para você usar:

```php

$router = Flight::router();

// mapeia todos os métodos assim como Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// Requisição GET
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Expressões Regulares (Regex)
Você pode usar expressões regulares em suas rotas:

```php
Flight::route('/user/[0-9]+', function () {
  // Isso combinará com /user/1234
});
```

Embora esse método esteja disponível, é recomendado usar parâmetros nomeados, ou parâmetros nomeados com expressões regulares, pois eles são mais legíveis e fáceis de manter.

### Parâmetros Nomeados
Você pode especificar parâmetros nomeados em suas rotas, que serão passados para a função de callback. **Isso é mais para legibilidade da rota do que qualquer outra coisa. Por favor, veja a seção abaixo sobre uma ressalva importante.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando o delimitador `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Isso combinará com /bob/123
  // Mas não combinará com /bob/12345
});
```

> **Nota:** Combinar grupos de regex `()` com parâmetros posicionais não é suportado. Ex: `:'\(`

#### Ressalva Importante

Embora no exemplo acima pareça que `@name` está diretamente ligado à variável `$name`, não é. A ordem dos parâmetros na função de callback é o que determina o que é passado para ela. Se você trocar a ordem dos parâmetros na função de callback, as variáveis também serão trocadas. Aqui está um exemplo:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

E se você acessar a seguinte URL: `/bob/123`, a saída seria `hello, 123 (bob)!`. 
_Por favor, tenha cuidado_ ao configurar suas rotas e funções de callback!

### Parâmetros Opcionais
Você pode especificar parâmetros nomeados que são opcionais para combinação envolvendo segmentos em parênteses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Isso combinará com as seguintes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Quaisquer parâmetros opcionais que não forem combinados serão passados como `NULL`.

### Roteamento de Curinga
A combinação é feita apenas em segmentos individuais de URL. Se você quiser combinar múltiplos segmentos, pode usar o curinga `*`.

```php
Flight::route('/blog/*', function () {
  // Isso combinará com /blog/2000/02/01
});
```

Para rotear todas as requisições para um único callback, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

### Manipulador de 404 Não Encontrado

Por padrão, se uma URL não puder ser encontrada, o Flight enviará uma resposta `HTTP 404 Not Found` que é muito simples e simples.
Se você quiser ter uma resposta 404 mais personalizada, você pode [mapear](/learn/extending) seu próprio método `notFound`:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// Você também poderia usar Flight::render() com um template personalizado.
    $output = <<<HTML
		<h1>Meu 404 Não Encontrado Personalizado</h1>
		<h3>A página que você solicitou {$url} não pôde ser encontrada.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

## Uso Avançado

### Injeção de Dependência em Rotas
Se você quiser usar injeção de dependência via um contêiner (PSR-11, PHP-DI, Dice, etc), o único tipo de rotas onde isso está disponível é criando o objeto diretamente você mesmo e usando o contêiner para criar seu objeto ou você pode usar strings para definir a classe e o método a chamar. Você pode ir à página [Injeção de Dependência](/learn/dependency-injection-container) para mais informações. 

Aqui está um exemplo rápido:

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// faça algo com $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Configure o contêiner com os parâmetros que você precisar
// Veja a página de Injeção de Dependência para mais informações sobre PSR-11
$dice = new \Dice\Dice();

// Não esqueça de reatribuir a variável com '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Registre o manipulador de contêiner
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Rotas como normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// ou
Flight::route('/hello/@id', 'Greeting->hello');
// ou
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### Passando Execução para a Próxima Rota
<span class="badge bg-warning">Depreciado</span>
Você pode passar a execução para a próxima rota combinada retornando `true` da sua função de callback.

```php
Flight::route('/user/@name', function (string $name) {
  // Verifique alguma condição
  if ($name !== "Bob") {
    // Continue para a próxima rota
    return true;
  }
});

Flight::route('/user/*', function () {
  // Isso será chamado
});
```

Agora é recomendado usar [middleware](/learn/middleware) para lidar com casos de uso complexos como este.

### Alias de Rota
Ao atribuir um alias a uma rota, você pode chamar esse alias dinamicamente na sua aplicação para ser gerado mais tarde no seu código (ex: um link em um template HTML, ou gerando uma URL de redirecionamento).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// ou 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// mais tarde no código em algum lugar
class UserController {
	public function update() {

		// código para salvar o usuário...
		$id = $user['id']; // 5 por exemplo

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // retornará '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Isso é especialmente útil se a sua URL acontecer de mudar. No exemplo acima, digamos que users foi movido para `/admin/users/@id` em vez disso.
Com o alias no lugar para a rota, você não precisa mais encontrar todas as URLs antigas no seu código e alterá-las porque o alias agora retornará `/admin/users/5` como no exemplo acima.

O alias de rota ainda funciona em grupos também:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// ou
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Inspecionando Informações de Rota
Se você quiser inspecionar as informações da rota combinada, há 2 maneiras de fazer isso:

1. Você pode usar a propriedade `executedRoute` no objeto `Flight::router()`.
2. Você pode solicitar que o objeto de rota seja passado para o seu callback passando `true` como o terceiro parâmetro no método de rota. O objeto de rota sempre será o último parâmetro passado para a sua função de callback.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Faça algo com $route
  // Array de métodos HTTP combinados
  $route->methods;

  // Array de parâmetros nomeados
  $route->params;

  // Expressão regular combinada
  $route->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $route->splat;

  // Mostra o caminho da url....se você realmente precisar
  $route->pattern;

  // Mostra o middleware atribuído a isso
  $route->middleware;

  // Mostra o alias atribuído a esta rota
  $route->alias;
});
```

> **Nota:** A propriedade `executedRoute` só será definida após uma rota ter sido executada. Se você tentar acessá-la antes de uma rota ter sido executada, ela será `NULL`. Você também pode usar executedRoute em [middleware](/learn/middleware)!

#### Passando `true` na definição de rota
```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP combinados
  $route->methods;

  // Array de parâmetros nomeados
  $route->params;

  // Expressão regular combinada
  $route->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $route->splat;

  // Mostra o caminho da url....se você realmente precisar
  $route->pattern;

  // Mostra o middleware atribuído a isso
  $route->middleware;

  // Mostra o alias atribuído a esta rota
  $route->alias;
}, true);// <-- Este parâmetro true é o que faz isso acontecer
```

### Agrupamento de Rotas e Middleware
Pode haver vezes em que você queira agrupar rotas relacionadas juntas (como `/api/v1`).
Você pode fazer isso usando o método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Combina com /api/v1/users
  });

  Flight::route('/posts', function () {
	// Combina com /api/v1/posts
  });
});
```

Você pode até aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto de objeto abaixo
	Flight::route('GET /users', function () {
	  // Combina com GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Combina com POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Combina com PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto de objeto abaixo
	Flight::route('GET /users', function () {
	  // Combina com GET /api/v2/users
	});
  });
});
```

#### Agrupamento com Contexto de Objeto

Você ainda pode usar agrupamento de rotas com o objeto `Engine` da seguinte maneira:

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // use a variável $router
  $router->get('/users', function () {
	// Combina com GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Combina com POST /api/v1/posts
  });
});
```

> **Nota:** Este é o método preferido de definir rotas e grupos com o objeto `$router`.

#### Agrupamento com Middleware

Você também pode atribuir middleware a um grupo de rotas:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Combina com /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // ou [ new MyAuthMiddleware() ] se você quiser usar uma instância
```

Veja mais detalhes na página [middleware de grupo](/learn/middleware#grouping-middleware).

### Roteamento de Recursos
Você pode criar um conjunto de rotas para um recurso usando o método `resource`. Isso criará um conjunto de rotas para um recurso que segue as convenções RESTful.

Para criar um recurso, faça o seguinte:

```php
Flight::resource('/users', UsersController::class);
```

E o que acontecerá em segundo plano é que ele criará as seguintes rotas:

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

E o seu controlador usará os seguintes métodos:

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Nota**: Você pode visualizar as rotas recém-adicionadas com `runway` executando `php runway routes`.

#### Personalizando Rotas de Recursos

Há algumas opções para configurar as rotas de recursos.

##### Base de Alias

Você pode configurar o `aliasBase`. Por padrão, o alias é a última parte da URL especificada.
Por exemplo, `/users/` resultaria em um `aliasBase` de `users`. Quando essas rotas são criadas, os aliases são `users.index`, `users.create`, etc. Se você quiser alterar o alias, defina o `aliasBase` para o valor que deseja.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only e Except

Você também pode especificar quais rotas deseja criar usando as opções `only` e `except`.

```php
// Lista branca apenas desses métodos e lista negra do resto
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Lista negra apenas desses métodos e lista branca do resto
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Essas são basicamente opções de lista branca e lista negra para que você possa especificar quais rotas deseja criar.

##### Middleware

Você também pode especificar middleware para ser executado em cada uma das rotas criadas pelo método `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Respostas em Streaming

Você agora pode transmitir respostas para o cliente usando `stream()` ou `streamWithHeaders()`. 
Isso é útil para enviar arquivos grandes, processos de longa duração ou gerar respostas grandes. 
Transmitir uma rota é tratado um pouco diferente de uma rota regular.

> **Nota:** Respostas em streaming só estão disponíveis se você tiver [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) definido como `false`.

#### Stream com Cabeçalhos Manuais

Você pode transmitir uma resposta para o cliente usando o método `stream()` em uma rota. Se você 
fizer isso, deve definir todos os cabeçalhos manualmente antes de produzir qualquer coisa para o cliente.
Isso é feito com a função php `header()` ou o método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// obviamente você sanitizaria o caminho e o que mais.
	$fileNameSafe = basename($filename);

	// Se você tiver cabeçalhos adicionais para definir aqui após a rota ter sido executada
	// você deve defini-los antes de qualquer coisa ser ecoada.
	// Eles devem ser todos uma chamada crua para a função header() ou 
	// uma chamada para Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// ou
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// defina manualmente o comprimento do conteúdo se quiser
	header('Content-Length: '.filesize($filePath));
	// ou
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Transmita o arquivo para o cliente enquanto ele é lido
	readfile($filePath);

// Esta é a linha mágica aqui
})->stream();
```

#### Stream com Cabeçalhos

Você também pode usar o método `streamWithHeaders()` para definir os cabeçalhos antes de começar a transmitir.

```php
Flight::route('/stream-users', function() {

	// você pode adicionar quaisquer cabeçalhos adicionais que quiser aqui
	// você só deve usar header() ou Flight::response()->setRealHeader()

	// no entanto, da maneira que você puxa seus dados, apenas como exemplo...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Isso é necessário para enviar os dados para o cliente
		ob_flush();
	}
	echo '}';

// É assim que você definirá os cabeçalhos antes de começar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// código de status opcional, padrão para 200
	'status' => 200
]);
```

## Veja Também
- [Middleware](/learn/middleware) - Usando middleware com rotas para autenticação, logging, etc.
- [Injeção de Dependência](/learn/dependency-injection-container) - Simplificando a criação e gerenciamento de objetos em rotas.
- [Por que um Framework?](/learn/why-frameworks) - Entendendo os benefícios de usar um framework como o Flight.
- [Estendendo](/learn/extending) - Como estender o Flight com sua própria funcionalidade, incluindo o método `notFound`.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - Função PHP para combinação de expressões regulares.

## Solução de Problemas
- Parâmetros de rota são combinados por ordem, não por nome. Certifique-se de que a ordem dos parâmetros do callback corresponda à definição da rota.
- Usar `Flight::get()` não define uma rota; use `Flight::route('GET /...')` para roteamento ou o contexto do objeto Router em grupos (ex: `$router->get(...)`).
- A propriedade executedRoute só é definida após uma rota ser executada; ela é NULL antes da execução.
- Streaming requer que a funcionalidade de buffer de saída legado do Flight seja desabilitada (`flight.v2.output_buffering = false`).
- Para injeção de dependência, apenas certas definições de rota suportam instanciação baseada em contêiner.

### 404 Não Encontrado ou Comportamento Inesperado de Rota

Se você estiver vendo um erro 404 Não Encontrado (mas você jura pela sua vida que ele está realmente lá e não é um erro de digitação), isso na verdade pode ser um problema 
com você retornando um valor no seu endpoint de rota em vez de apenas ecoá-lo. A razão para isso é intencional, mas pode surpreender alguns desenvolvedores.

```php

Flight::route('/hello', function(){
	// Isso pode causar um erro 404 Não Encontrado
	return 'Hello World';
});

// O que você provavelmente quer
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

A razão para isso é por causa de um mecanismo especial incorporado no roteador que trata a saída de retorno como um sinal para "ir para a próxima rota". 
Você pode ver o comportamento documentado na seção [Roteamento](/learn/routing#passing).

## Changelog
- v3: Adicionado roteamento de recursos, alias de rota e suporte a streaming, grupos de rota e suporte a middleware.
- v1: A vasta maioria dos recursos básicos disponíveis.