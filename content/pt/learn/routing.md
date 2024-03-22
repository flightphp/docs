# Encaminhamento

> **Nota:** Quer entender mais sobre encaminhamento? Confira a página ["por que um framework?"](/learn/why-frameworks) para uma explicação mais aprofundada.

O encaminhamento básico no Flight é feito correspondendo um padrão de URL com uma função de retorno ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

> As rotas são correspondidas na ordem em que são definidas. A primeira rota a corresponder a uma solicitação será invocada.

### Callbacks/Funções

O retorno pode ser qualquer objeto que seja chamável. Portanto, você pode usar uma função regular:

```php
function hello(){
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

Ou criando um objeto primeiro e então chamando o método:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'Fulano';
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
```

#### Injeção de Dependência via DIC (Contêiner de Injeção de Dependência)
Se você deseja usar injeção de dependência por meio de um contêiner (PSR-11, PHP-DI, Dice, etc), o
único tipo de rotas onde isso está disponível é criando diretamente o objeto sozinho
e usando o contêiner para criar seu objeto ou você pode usar strings para definir a classe e
método a serem chamados. Você pode ir para a página [Injeção de Dependência](/learn/extending) para
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

// Configure o contêiner com os parâmetros necessários
// Consulte a página de Injeção de Dependência para mais informações sobre PSR-11
$dice = new \Dice\Dice();

// Não se esqueça de reatribuir a variável com '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
    'shared' => true,
    'constructParams' => [ 
        'mysql:host=localhost;dbname=test', 
        'root',
        'password'
    ]
]);

// Registre o manipulador do contêiner
Flight::registerContainerHandler(function($class, $params) use ($dice) {
    return $dice->create($class, $params);
});

// Rotas como o normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// ou
Flight::route('/hello/@id', 'Greeting->hello');
// ou
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## Encaminhamento por Método

Por padrão, os padrões de rota são correspondidos a todos os métodos de solicitação. Você pode responder
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

Você também pode mapear vários métodos para um único retorno usando um delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi uma solicitação GET ou POST.';
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

Embora este método esteja disponível, é recomendado usar parâmetros nomeados, ou
parâmetros nomeados com expressões regulares, pois são mais legíveis e mais fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para
sua função de retorno.

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

> **Nota:** Não é suportacio corresponder grupos regex `()` com parâmetros nomeados. :'(

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para corresponder ao
envolver segmentos em parênteses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Isso corresponderá aos seguintes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Quaisquer parâmetros opcionais que não sejam correspondidos serão passados como `NULL`.

## Coringas

A correspondência é feita apenas em segmentos individuais de URL. Se deseja corresponder a vários
segmentos, você pode usar o coringa `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para rotear todas as solicitações a um único retorno, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

## Passando Adiante

Você pode passar a execução para a próxima rota correspondente retornando `true` de
sua função de retorno.

```php
Flight::route('/user/@name', function (string $name) {
  // Verificar alguma condição
  if ($name !== "João") {
    // Continuar para a próxima rota
    return true;
  }
});

Flight::route('/user/*', function () {
  // Isso será chamado
});
```

## Alias de Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como em um modelo, por exemplo).

```php
Flight::route('/users/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');

// mais tarde no código em algum lugar
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/users/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que os usuários foram movidos para `/admin/users/@id`. Com o uso de alias, você não precisa alterar em nenhum lugar que você faz referência ao alias, pois o alias agora retornará `/admin/users/5` como no
exemplo acima.

O alias de rota ainda funciona em grupos também:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualizacao_usuario');
});


// mais tarde no código em algum lugar
Flight::getUrl('visualizacao_usuario', [ 'id' => 5 ]); // retornará '/users/5'
```

## Informações de Rota

Se você deseja inspecionar as informações da rota correspondente, pode solicitar que o objeto de rota seja passado para sua função de retorno passando `true` como terceiro parâmetro no
método de rota. O objeto de rota sempre será o último parâmetro passado para sua
função de retorno.

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

  // Mostra o caminho de URL....se você realmente precisar
  $route->pattern;

  // Mostra qual middleware foi atribuído a isso
  $route->middleware;

  // Mostra o alias atribuído a esta rota
  $route->alias;
}, true);
```

## Agrupamento de Rotas

Pode haver momentos em que você deseja agrupar rotas relacionadas juntas (como `/api/v1`).
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

Você também pode aninhar grupos de grupos:

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

Você ainda pode usar o agrupamento de rotas com o objeto `Engine` da seguinte maneira:

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

## Streaming

Agora você pode enviar respostas em tempo real para o cliente usando o método `streamWithHeaders()`.
Isso é útil para enviar arquivos grandes, processos longos em execução ou gerar respostas grandes.
O streaming de uma rota é tratado um pouco diferente do que uma rota regular.

> **Nota:** As respostas de streaming estão disponíveis somente se você tiver [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) definido como false.

```php
Flight::route('/stream-users', function() {

	// Se você tiver cabeçalhos adicionais para definir aqui após a execução da rota
	// você deve defini-los antes de qualquer coisa ser ecoada.
	// Todos eles devem ser uma chamada bruta à função header() ou
	// uma chamada para Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="users.json"');
	// ou
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="users.json"');

	// como você puxa seus dados, apenas como exemplo...
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

// É assim que você definirá os cabeçalhos antes de iniciar o streaming.
})->streamWithHeaders([
	'Tipo de Conteúdo' => 'application/json',
	// código de status opcional, padrão é 200
	'status' => 200
]);
```