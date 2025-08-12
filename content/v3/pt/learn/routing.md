# Roteamento

> **Nota:** Quer entender mais sobre roteamento? Confira a página ["why a framework?"](/learn/why-frameworks) para uma explicação mais detalhada.

Roteamento básico no Flight é feito ao combinar um padrão de URL com uma função de callback ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'hello world!'; // Isso é um comentário de exemplo
});
```

> As rotas são combinadas na ordem em que são definidas. A primeira rota que combinar uma solicitação será invocada.

### Callbacks/Funções
O callback pode ser qualquer objeto que seja chamável. Então você pode usar uma função regular:

```php
function hello() {
    echo 'hello world!'; // Isso é um comentário de exemplo
}

Flight::route('/', 'hello');
```

### Classes
Você também pode usar um método estático de uma classe:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!'; // Isso é um comentário de exemplo
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

Ou criando um objeto primeiro e depois chamando o método:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe'; // Isso é um comentário de exemplo
    }

    public function hello() {
        echo "Hello, {$this->name}!"; // Isso é um comentário de exemplo
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// Você também pode fazer isso sem criar o objeto primeiro
// Nota: Nenhum argumento será injetado no construtor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Além disso, você pode usar esta sintaxe mais curta
Flight::route('/', 'Greeting->hello');
// ou
Flight::route('/', Greeting::class.'->hello');
```

#### Injeção de Dependência via DIC (Container de Injeção de Dependência)
Se você quiser usar injeção de dependência via um container (PSR-11, PHP-DI, Dice, etc), o
único tipo de rotas onde isso está disponível é criando o objeto diretamente você mesmo
e usando o container para criar seu objeto ou você pode usar strings para definir a classe e
o método a chamar. Você pode ir para a página [Dependency Injection](/learn/extending) para 
mais informações. 

Aqui vai um exemplo rápido:

```php
use flight\database\PdoWrapper; // Isso é um comentário de exemplo

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper; // Isso é um comentário de exemplo
	}

	public function hello(int $id) {
		// faça algo com $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]); // Isso é um comentário de exemplo
		echo "Hello, world! My name is {$name}!"; // Isso é um comentário de exemplo
	}
}

// index.php

// Configure o container com os parâmetros que você precisa
// Veja a página de Dependency Injection para mais informações sobre PSR-11
$dice = new \Dice\Dice();

// Não esqueça de reatribuir a variável com '$dice = '!!!!! // Isso é um comentário de exemplo
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Registre o manipulador do container
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params); // Isso é um comentário de exemplo
});

// Rotas como de costume
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// ou
Flight::route('/hello/@id', 'Greeting->hello');
// ou
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Roteamento por Método

Por padrão, os padrões de rota são combinados contra todos os métodos de solicitação. Você pode responder
a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.'; // Isso é um comentário de exemplo
});

Flight::route('POST /', function () {
  echo 'I received a POST request.'; // Isso é um comentário de exemplo
});

// Você não pode usar Flight::get() para rotas, pois isso é um método 
//    para obter variáveis, não para criar uma rota.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

Você também pode mapear vários métodos para um único callback usando um delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.'; // Isso é um comentário de exemplo
});
```

Além disso, você pode obter o objeto Router, que tem alguns métodos auxiliares para você usar:

```php
$router = Flight::router();

// mapeia todos os métodos
$router->map('/', function() {
	echo 'hello world!'; // Isso é um comentário de exemplo
});

// Solicitação GET
$router->get('/users', function() {
	echo 'users'; // Isso é um comentário de exemplo
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Expressões Regulares

Você pode usar expressões regulares em suas rotas:

```php
Flight::route('/user/[0-9]+', function () {
  // Isso combinará /user/1234 // Isso é um comentário de exemplo
});
```

Embora este método esteja disponível, é recomendável usar parâmetros nomeados, ou 
parâmetros nomeados com expressões regulares, pois eles são mais legíveis e fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para
sua função de callback. **Isso é mais para a legibilidade da rota do que qualquer outra coisa. Por favor, veja a seção abaixo sobre ressalva importante.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!"; // Isso é um comentário de exemplo
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando
o delimitador `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Isso combinará /bob/123
  // Mas não combinará /bob/12345 // Isso é um comentário de exemplo
});
```

> **Nota:** Combinar grupos de regex `()` com parâmetros posicionais não é suportado. :'\(

### Ressalva Importante

Embora no exemplo acima, pareça que `@name` está diretamente ligado à variável `$name`, não está. A ordem dos parâmetros na função de callback é o que determina o que é passado para ela. Então, se você inverter a ordem dos parâmetros na função de callback, as variáveis também serão invertidas. Aqui vai um exemplo:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!"; // Isso é um comentário de exemplo
});
```

E se você acessar a URL seguinte: `/bob/123`, a saída seria `hello, 123 (bob)!`. 
Por favor, tenha cuidado ao configurar suas rotas e funções de callback.

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para combinação, envolvendo
segmentos em parênteses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Isso combinará as seguintes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog // Isso é um comentário de exemplo
  }
);
```

Quaisquer parâmetros opcionais que não forem combinados serão passados como `NULL`.

## Coringas

A combinação é feita apenas em segmentos individuais de URL. Se você quiser combinar vários
segmentos, você pode usar o curinga `*`.

```php
Flight::route('/blog/*', function () {
  // Isso combinará /blog/2000/02/01 // Isso é um comentário de exemplo
});
```

Para rotear todas as solicitações para um único callback, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo // Isso é um comentário de exemplo
});
```

## Passagem

Você pode passar a execução para a próxima rota que combina retornando `true` da
sua função de callback.

```php
Flight::route('/user/@name', function (string $name) {
  // Verifique alguma condição
  if ($name !== "Bob") {
    // Continue para a próxima rota
    return true; // Isso é um comentário de exemplo
  }
});

Flight::route('/user/*', function () {
  // Isso será chamado // Isso é um comentário de exemplo
});
```

## Alias de Rotas

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde no seu código (como em um template, por exemplo).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view'); // Isso é um comentário de exemplo

// mais tarde no código em algum lugar
Flight::getUrl('user_view', [ 'id' => 5 ]); // isso retornará '/users/5' // Isso é um comentário de exemplo
```

Isso é especialmente útil se sua URL acontecer de mudar. No exemplo acima, digamos que users foi movido para `/admin/users/@id` em vez disso.
Com aliasing no lugar, você não precisa mudar em nenhum lugar onde você referencia o alias porque o alias agora retornará `/admin/users/5` como no 
exemplo acima.

Alias de rota ainda funciona em grupos também:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view'); // Isso é um comentário de exemplo
});


// mais tarde no código em algum lugar
Flight::getUrl('user_view', [ 'id' => 5 ]); // isso retornará '/users/5' // Isso é um comentário de exemplo
```

## Informações de Rota

Se você quiser inspecionar as informações da rota que combina, você pode solicitar que o objeto da rota seja passado para seu callback passando `true` como o terceiro parâmetro no
método de rota. O objeto da rota sempre será o último parâmetro passado para sua
função de callback.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP combinados contra
  $route->methods;

  // Array de parâmetros nomeados
  $route->params;

  // Expressão regular de combinação
  $route->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $route->splat;

  // Mostra o caminho da URL....se você realmente precisar
  $route->pattern;

  // Mostra o que middleware está atribuído a isso
  $route->middleware;

  // Mostra o alias atribuído a esta rota
  $route->alias; // Isso é um comentário de exemplo
}, true);
```

## Agrupamento de Rotas

Pode haver momentos em que você deseja agrupar rotas relacionadas juntas (como `/api/v1`).
Você pode fazer isso usando o método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Combina /api/v1/users // Isso é um comentário de exemplo
  });

  Flight::route('/posts', function () {
	// Combina /api/v1/posts // Isso é um comentário de exemplo
  });
});
```

Você pode até aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtém variáveis, ele não define uma rota! Veja o contexto de objeto abaixo
	Flight::route('GET /users', function () {
	  // Combina GET /api/v1/users // Isso é um comentário de exemplo
	});

	Flight::post('/posts', function () {
	  // Combina POST /api/v1/posts // Isso é um comentário de exemplo
	});

	Flight::put('/posts/1', function () {
	  // Combina PUT /api/v1/posts // Isso é um comentário de exemplo
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtém variáveis, ele não define uma rota! Veja o contexto de objeto abaixo
	Flight::route('GET /users', function () {
	  // Combina GET /api/v2/users // Isso é um comentário de exemplo
	});
  });
});
```

### Agrupamento com Contexto de Objeto

Você ainda pode usar agrupamento de rotas com o objeto `Engine` da seguinte forma:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // use a variável $router
  $router->get('/users', function () {
	// Combina GET /api/v1/users // Isso é um comentário de exemplo
  });

  $router->post('/posts', function () {
	// Combina POST /api/v1/posts // Isso é um comentário de exemplo
  });
});
```

### Agrupamento com Middleware

Você também pode atribuir middleware a um grupo de rotas:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Combina /api/v1/users // Isso é um comentário de exemplo
  });
}, [ MyAuthMiddleware::class ]); // ou [ new MyAuthMiddleware() ] se você quiser usar uma instância // Isso é um comentário de exemplo
```

Veja mais detalhes na página [group middleware](/learn/middleware#grouping-middleware).

## Roteamento de Recursos

Você pode criar um conjunto de rotas para um recurso usando o método `resource`. Isso criará
um conjunto de rotas para um recurso que segue as convenções RESTful.

Para criar um recurso, faça o seguinte:

```php
Flight::resource('/users', UsersController::class); // Isso é um comentário de exemplo
```

E o que acontecerá em segundo plano é que ele criará as seguintes rotas:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
] // Isso é um comentário de exemplo
```

E seu controlador ficará assim:

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
} // Isso é um comentário de exemplo
```

> **Nota**: Você pode visualizar as rotas recém-adicionadas com `runway` executando `php runway routes`.

### Personalizando Rotas de Recursos

Existem algumas opções para configurar as rotas de recursos.

#### Base de Alias

Você pode configurar o `aliasBase`. Por padrão, o alias é a última parte da URL especificada.
Por exemplo, `/users/` resultaria em um `aliasBase` de `users`. Quando essas rotas são criadas,
os aliases são `users.index`, `users.create`, etc. Se você quiser mudar o alias, defina o `aliasBase`
para o valor que você deseja.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]); // Isso é um comentário de exemplo
```

#### Only e Except

Você também pode especificar quais rotas você deseja criar usando as opções `only` e `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]); // Isso é um comentário de exemplo
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]); // Isso é um comentário de exemplo
```

Essas são basicamente opções de lista branca e lista negra, para que você possa especificar quais rotas você deseja criar.

#### Middleware

Você também pode especificar middleware para ser executado em cada uma das rotas criadas pelo método `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]); // Isso é um comentário de exemplo
```

## Streaming

Agora você pode transmitir respostas para o cliente usando o método `streamWithHeaders()`. 
Isso é útil para enviar arquivos grandes, processos de longa duração ou gerar respostas grandes. 
Transmitir uma rota é tratado de forma um pouco diferente de uma rota regular.

> **Nota:** Respostas de streaming só estão disponíveis se você tiver [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) definido como false.

### Stream com Cabeçalhos Manuais

Você pode transmitir uma resposta para o cliente usando o método `stream()` em uma rota. Se você 
fizer isso, você deve definir todos os métodos manualmente antes de enviar qualquer coisa para o cliente.
Isso é feito com a função `header()` do php ou o método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// obviamente você sanitizaria o caminho e o que mais.
	$fileNameSafe = basename($filename); // Isso é um comentário de exemplo

	// Se você tiver cabeçalhos adicionais para definir aqui depois que a rota foi executada
	// você deve defini-los antes de qualquer coisa ser ecoada.
	// Eles devem ser uma chamada bruta para a função header() ou 
	// uma chamada para Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"'); // Isso é um comentário de exemplo
	// ou
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Captura de erros e o que mais
	if(empty($fileData)) {
		Flight::halt(404, 'File not found'); // Isso é um comentário de exemplo
	}

	// defina manualmente o comprimento do conteúdo se você quiser
	header('Content-Length: '.filesize($filename));

	// Transmita os dados para o cliente
	echo $fileData;

// Esta é a linha mágica aqui // Isso é um comentário de exemplo
})->stream();
```

### Stream com Cabeçalhos

Você também pode usar o método `streamWithHeaders()` para definir os cabeçalhos antes de começar a transmitir.

```php
Flight::route('/stream-users', function() {

	// você pode adicionar qualquer cabeçalho adicional que quiser aqui
	// você só deve usar header() ou Flight::response()->setRealHeader()

	// no entanto, como você puxa seus dados, apenas como um exemplo...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{'; // Isso é um comentário de exemplo
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Isso é necessário para enviar os dados para o cliente
		ob_flush(); // Isso é um comentário de exemplo
	}
	echo '}';

// Esta é como você definirá os cabeçalhos antes de começar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// código de status opcional, padrão para 200
	'status' => 200 // Isso é um comentário de exemplo
]);
```