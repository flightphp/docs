# Roteamento

> **Nota:** Quer entender mais sobre roteamento? Confira a página ["por que um framework?"](/learn/why-frameworks) para uma explicação mais detalhada.

O roteamento básico no Flight é feito combinando um padrão de URL com uma função de retorno de chamada ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

> As rotas são combinadas na ordem em que são definidas. A primeira rota que corresponder a uma solicitação será invocada.

### Retornos de chamada/Funções
O retorno de chamada pode ser qualquer objeto que seja chamável. Então você pode usar uma função regular:

```php
function hello() {
    echo 'olá mundo!';
}

Flight::route('/', 'hello');
```

### Classes
Você também pode usar um método estático de uma classe:

```php
class Greeting {
    public static function hello() {
        echo 'olá mundo!';
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
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Olá, {$this->name}!";
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
único tipo de rotas onde isso está disponível é criar o objeto você mesmo
e usar o container para criar seu objeto ou você pode usar strings para definir a classe e
método a serem chamados. Você pode acessar a página [Injeção de Dependência](/learn/extending) para 
mais informações. 

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
		echo "Olá, mundo! Meu nome é {$name}!";
	}
}

// index.php

// Configure o container com quaisquer parâmetros que você precisar
// Consulte a página de Injeção de Dependência para mais informações sobre PSR-11
$dice = new \Dice\Dice();

// Não se esqueça de reatribuir a variável com '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'senha'
	]
]);

// Registre o manipulador do container
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

## Roteamento de Método

Por padrão, os padrões de rota são combinados com todos os métodos de solicitação. Você pode responder
a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'Recebi uma solicitação GET.';
});

Flight::route('POST /', function () {
  echo 'Recebi uma solicitação POST.';
});

// Você não pode usar Flight::get() para rotas, pois esse é um método 
//    para obter variáveis, não criar uma rota.
// Flight::post('/', function() { /* código */ });
// Flight::patch('/', function() { /* código */ });
// Flight::put('/', function() { /* código */ });
// Flight::delete('/', function() { /* código */ });
```

Você também pode mapear múltiplos métodos para um único retorno de chamada usando um delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi uma solicitação de GET ou POST.';
});
```

Além disso, você pode obter o objeto Router que possui alguns métodos auxiliares para você usar:

```php

$router = Flight::router();

// mapeia todos os métodos
$router->map('/', function() {
	echo 'olá mundo!';
});

// solicitação GET
$router->get('/users', function() {
	echo 'usuários';
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
  // Isso corresponderá a /user/1234
});
```

Embora esse método esteja disponível, é recomendado usar parâmetros nomeados, ou 
parâmetros nomeados com expressões regulares, pois são mais legíveis e mais fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para
sua função de retorno de chamada. **Isso é mais para a legibilidade da rota do que qualquer outra coisa.
Por favor, veja a seção abaixo sobre a importante caveat.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "olá, $name ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando
o delimitador `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Isso corresponderá a /bob/123
  // Mas não corresponderá a /bob/12345
});
```

> **Nota:** Grupos regex correspondentes `()` com parâmetros posicionais não são suportados. :'\(

### Importante Caveat

Embora no exemplo acima, pareça que `@name` está diretamente ligado à variável `$name`, não está. A ordem dos parâmetros na função de retorno de chamada é o que determina o que é passado para ela. Portanto, se você mudar a ordem dos parâmetros na função de retorno de chamada, as variáveis também serão trocadas. Aqui está um exemplo:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "olá, $name ($id)!";
});
```

E se você fosse para a seguinte URL: `/bob/123`, a saída seria `olá, 123 (bob)!`. 
Por favor, tenha cuidado ao configurar suas rotas e suas funções de retorno de chamada.

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para correspondência envolvendo
segmentos entre parênteses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Isso corresponderá às seguintes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Quaisquer parâmetros opcionais que não corresponderem serão passados como `NULL`.

## Coringas

A correspondência é feita apenas em segmentos de URL individuais. Se você quiser combinar múltiplos
segmentos, pode usar o coringa `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para direcionar todas as solicitações para um único retorno de chamada, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

## Passando

Você pode passar a execução para a próxima rota correspondente retornando `true` de
sua função de retorno de chamada.

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

## Alias para Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como um modelo, por exemplo).

```php
Flight::route('/users/@id', function($id) { echo 'usuário:'.$id; }, false, 'user_view');

// depois em algum lugar no código
Flight::getUrl('user_view', [ 'id' => 5 ]); // retornará '/users/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, suponha que os usuários foram movidos para `/admin/users/@id` em vez disso.
Com o alias em prática, você não precisa mudar em nenhum lugar onde você referenciar o alias porque o alias agora retornará `/admin/users/5`, como no 
exemplo acima.

O alias da rota ainda funciona em grupos também:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'user_view');
});

// depois em algum lugar no código
Flight::getUrl('user_view', [ 'id' => 5 ]); // retornará '/users/5'
```

## Informações da Rota

Se você quiser inspecionar as informações da rota correspondente, pode solicitar que o objeto de rota
seja passado para seu retorno de chamada passando `true` como o terceiro parâmetro no
método de rota. O objeto de rota sempre será o último parâmetro passado para sua
função de retorno de chamada.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP correspondentes
  $route->methods;

  // Array de parâmetros nomeados
  $route->params;

  // Expressão regular correspondente
  $route->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $route->splat;

  // Mostra o caminho da URL....se você realmente precisar
  $route->pattern;

  // Mostra qual middleware está atribuído a isso
  $route->middleware;

  // Mostra o alias atribuído a esta rota
  $route->alias;
}, true);
```

## Agrupamento de Rotas

Pode haver momentos em que você queira agrupar rotas relacionadas (como `/api/v1`).
Você pode fazer isso usando o método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Corresponde a /api/v1/users
  });

  Flight::route('/posts', function () {
	// Corresponde a /api/v1/posts
  });
});
```

Você pode até aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto do objeto abaixo
	Flight::route('GET /users', function () {
	  // Corresponde a GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Corresponde a POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Corresponde a PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto do objeto abaixo
	Flight::route('GET /users', function () {
	  // Corresponde a GET /api/v2/users
	});
  });
});
```

### Agrupamento com Contexto de Objeto

Você ainda pode usar o agrupamento de rotas com o objeto `Engine` da seguinte forma:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // use a variável $router
  $router->get('/users', function () {
	// Corresponde a GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Corresponde a POST /api/v1/posts
  });
});
```

## Roteamento de Recursos

Você pode criar um conjunto de rotas para um recurso usando o método `resource`. Isso criará
um conjunto de rotas para um recurso que segue as convenções RESTful.

Para criar um recurso, faça o seguinte:

```php
Flight::resource('/users', UsersController::class);
```

E o que acontecerá nos bastidores é que ele criará as seguintes rotas:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
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
}
```

> **Nota**: Você pode visualizar as novas rotas adicionadas com `runway` executando `php runway routes`.

### Personalizando Rotas de Recursos

Existem algumas opções para configurar as rotas de recursos.

#### Alias Base

Você pode configurar o `aliasBase`. Por padrão, o alias é a última parte da URL especificada.
Por exemplo, `/users/` resultaria em um `aliasBase` de `users`. Quando essas rotas são criadas,
os aliases são `users.index`, `users.create`, etc. Se você quiser mudar o alias, defina o `aliasBase`
para o valor que deseja.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Somente e Exceto

Você também pode especificar quais rotas deseja criar usando as opções `only` e `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Essas são basicamente opções de listagem e blacklist para que você possa especificar quais rotas deseja criar.

#### Middleware

Você também pode especificar middleware para ser executado em cada uma das rotas criadas pelo método `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Streaming

Agora você pode transmitir respostas para o cliente usando o método `streamWithHeaders()`. 
Isso é útil para enviar arquivos grandes, processos longos em execução ou gerar grandes respostas. 
Transmitir uma rota é tratado de forma um pouco diferente de uma rota regular.

> **Nota:** Transmissões de respostas só estão disponíveis se você tiver [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) definido como falso.

### Transmitir com Cabeçalhos Manuais

Você pode transmitir uma resposta para o cliente usando o método `stream()` em uma rota. Se você 
fizer isso, você deve definir todos os métodos à mão antes de emitir qualquer coisa para o cliente.
Isso é feito com a função `header()` do php ou o método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// obviamente você sanitizaria o caminho e outras coisas.
	$fileNameSafe = basename($filename);

	// Se você tiver cabeçalhos adicionais para definir aqui após a execução da rota
	// você deve defini-los antes que qualquer coisa seja ecoada.
	// Todos devem ser uma chamada bruta à função header() ou 
	// uma chamada para Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// ou
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Captura de erros e outras coisas
	if(empty($fileData)) {
		Flight::halt(404, 'Arquivo não encontrado');
	}

	// defina manualmente o comprimento do conteúdo, se desejar
	header('Content-Length: '.filesize($filename));

	// Transmita os dados para o cliente
	echo $fileData;

// Esta é a linha mágica aqui
})->stream();
```

### Transmitir com Cabeçalhos

Você também pode usar o método `streamWithHeaders()` para definir os cabeçalhos antes de começar a transmitir.

```php
Flight::route('/stream-users', function() {

	// você pode adicionar quaisquer cabeçalhos adicionais que desejar aqui
	// você deve usar header() ou Flight::response()->setRealHeader()

	// como você obtém seus dados, apenas como exemplo...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Isso é exigido para enviar os dados ao cliente
		ob_flush();
	}
	echo '}';

// Esta é a forma como você definirá os cabeçalhos antes de começar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// status code opcional, padrão é 200
	'status' => 200
]);
```