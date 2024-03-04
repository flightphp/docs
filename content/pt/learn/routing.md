# Encaminhamento

> **Nota:** Quer entender mais sobre encaminhamento? Confira a página ["por que um framework?"](/learn/why-frameworks) para uma explicação mais detalhada.

O encaminhamento básico no Flight é feito correspondendo um padrão de URL com uma função de retorno ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

O retorno pode ser qualquer objeto que seja invocável. Então você pode usar uma função regular:

```php
function hello(){
    echo 'olá mundo!';
}

Flight::route('/', 'hello');
```

Ou um método de classe:

```php
class Greeting {
    public static function hello() {
        echo 'olá mundo!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

Ou um método de objeto:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'João Silva';
    }

    public function hello() {
        echo "Olá, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

As rotas são correspondidas na ordem em que são definidas. A primeira rota a corresponder a uma solicitação será invocada.

## Encaminhamento de Método

Por padrão, os padrões de rota são correspondidos contra todos os métodos de solicitação. Você pode responder a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'Recebi uma solicitação GET.';
});

Flight::route('POST /', function () {
  echo 'Recebi uma solicitação POST.';
});
```

Você também pode mapear vários métodos para um único retorno usando um delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi uma solicitação GET ou POST.';
});
```

Além disso, você pode pegar o objeto Router que possui alguns métodos auxiliares para você usar:

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
Flight::route('/usuário/[0-9]+', function () {
  // Isso corresponderá a /usuário/1234
});
```

Embora este método esteja disponível, é recomendado usar parâmetros nomeados, ou
parâmetros nomeados com expressões regulares, pois são mais legíveis e fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados adiante para
sua função de retorno.

```php
Flight::route('/@nome/@id', function (string $nome, string $id) {
  echo "olá, $nome ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados usando
o delimitador `:`:

```php
Flight::route('/@nome/@id:[0-9]{3}', function (string $nome, string $id) {
  // Isso corresponderá a /bob/123
  // Mas não corresponderá a /bob/12345
});
```

> **Nota:** A correspondência de grupos regex `()` com parâmetros nomeados não é suportada. :'\(

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para a correspondência envolvendo
segmentos em parênteses.

```php
Flight::route(
  '/blog(/@ano(/@mês(/@dia)))',
  function(?string $ano, ?string $mês, ?string $dia) {
    // Isso corresponderá às seguintes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Quaisquer parâmetros opcionais que não sejam correspondidos serão passados como `NULL`.

## Caracteres Coringa

A correspondência é feita apenas em segmentos individuais de URL. Se você deseja corresponder vários
segmentos, pode usar o caractere coringa `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para encaminhar todas as solicitações para um único retorno, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

## Passando Adiante

Você pode passar a execução para a próxima rota correspondente retornando `true` de
sua função de retorno.

```php
Flight::route('/usuário/@nome', function (string $nome) {
  // Verifique alguma condição
  if ($nome !== "João") {
    // Continue para a próxima rota
    return true;
  }
});

Flight::route('/usuário/*', function () {
  // Isso será chamado
});
```

## Aliasing de Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente posteriormente em seu código (como um modelo, por exemplo).

```php
Flight::route('/usuários/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_usuário');

// mais tarde no código em algum lugar
Flight::getUrl('visualização_usuário', [ 'id' => 5 ]); // retornará '/usuários/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que os usuários foram movidos para `/admin/usuarios/@id` em vez disso.
Com o alias em vigor, você não precisa alterar em nenhum lugar que faça referência ao alias, porque o alias agora retornará `/admin/usuarios/5` como no
exemplo acima.

O alias de rota ainda funciona em grupos também:

```php
Flight::group('/usuários', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_usuário');
});


// mais tarde no código em algum lugar
Flight::getUrl('visualização_usuário', [ 'id' => 5 ]); // retornará '/usuários/5'
```

## Informações de Rota

Se você deseja inspecionar as informações de rota correspondente, pode solicitar que o objeto de rota seja passado para sua função de retorno passando `true` como o terceiro parâmetro no
método de rota. O objeto de rota sempre será o último parâmetro passado para sua
função de retorno.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP correspondidos
  $route->methods;

  // Array de parâmetros nomeados
  $route->params;

  // Expressão regular correspondente
  $route->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $route->splat;

  // Mostra o caminho da URL.... se realmente precisar
  $route->pattern;

  // Mostra qual middleware está atribuído a este
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
  Flight::route('/usuários', function () {
	// Corresponde a /api/v1/usuários
  });

  Flight::route('/postagens', function () {
	// Corresponde a /api/v1/postagens
  });
});
```

Você pode até mesmo aninhar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto do objeto abaixo
	Flight::route('GET /usuários', function () {
	  // Corresponde a GET /api/v1/usuários
	});

	Flight::post('/postagens', function () {
	  // Corresponde a POST /api/v1/postagens
	});

	Flight::put('/postagens/1', function () {
	  // Corresponde a PUT /api/v1/postagens
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtém variáveis, não define uma rota! Veja o contexto do objeto abaixo
	Flight::route('GET /usuários', function () {
	  // Corresponde a GET /api/v2/usuários
	});
  });
});
```

### Agrupando com Contexto de Objeto

Você ainda pode usar agrupamento de rotas com o objeto `Engine` da seguinte maneira:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // use a variável $router
  $router->get('/usuários', function () {
	// Corresponde a GET /api/v1/usuários
  });

  $router->post('/postagens', function () {
	// Corresponde a POST /api/v1/postagens
  });
});
```

## Transmissão

Agora você pode transmitir respostas para o cliente usando o método `streamWithHeaders()`. 
Isso é útil para enviar arquivos grandes, processos em execução prolongada ou gerar respostas grandes. 
Transmitir uma rota é tratado de maneira um pouco diferente de uma rota regular.

> **Nota:** A transmissão de respostas só está disponível se você tiver [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) configurado como falso.

```php
Flight::route('/stream-usuários', function() {

	// como você obtém seus dados, apenas como exemplo...
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

// Assim você definirá os cabeçalhos antes de começar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// código de status opcional, padrão 200
	'status' => 200
]);
```  