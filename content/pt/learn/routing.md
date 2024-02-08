# Roteamento

> **Nota:** Quer entender mais sobre roteamento? Confira a página [por que frameworks](/learn/why-frameworks) para uma explicação mais detalhada.

O roteamento básico no Flight é feito combinando um padrão de URL com uma função de retorno ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

A função de retorno pode ser qualquer objeto que seja chamável. Assim, você pode usar uma função regular:

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
        $this->name = 'Fulano de Tal';
    }

    public function hello() {
        echo "Olá, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

As rotas são combinadas na ordem em que são definidas. A primeira rota a corresponder a uma solicitação será invocada.

## Roteamento por Método

Por padrão, os padrões de rota são combinados com todos os métodos de solicitação. Você pode responder a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'Recebi uma solicitação GET.';
});

Flight::route('POST /', function () {
  echo 'Recebi uma solicitação POST.';
});
```

Você também pode mapear vários métodos para um único retorno, usando um delimitador `|`:

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

Embora este método esteja disponível, é recomendado usar parâmetros nomeados, ou parâmetros nomeados com expressões regulares, pois são mais legíveis e fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para sua função de retorno.

```php
Flight::route('/@nome/@id', function (string $nome, string $id) {
  echo "olá, $nome ($id)!";
});
```

Você também pode incluir expressões regulares com seus parâmetros nomeados, usando o delimitador `:`:

```php
Flight::route('/@nome/@id:[0-9]{3}', function (string $nome, string $id) {
  // Isso corresponderá a /bob/123
  // Mas não corresponderá a /bob/12345
});
```

> **Nota:** Não é suportada a correspondência de grupos regex `()` com parâmetros nomeados. :'\(

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para combinar, envolvendo
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

Quaisquer parâmetros opcionais que não correspondem serão passados como `NULL`.

## Curetagem

A combinação é feita apenas em segmentos individuais de URL. Se você quiser corresponder a vários
segmentos, pode usar o curinga `*`.

```php
Flight::route('/blog/*', function () {
  // Isso correspondará a /blog/2000/02/01
});
```

Para rotear todas as solicitações para um único retorno, você pode fazer:

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
  if ($nome !== "Fulano") {
    // Continue para a próxima rota
    return true;
  }
});

Flight::route('/usuário/*', function () {
  // Isso será chamado
});
```

## Apelidamento de Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como um modelo, por exemplo).

```php
Flight::route('/usuários/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_usuário');

// mais tarde no código
Flight::getUrl('visualização_usuário', [ 'id' => 5 ]); // retornará '/usuários/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que usuários foram movidos para `/admin/usuarios/@id` em vez disso.
Com o apelido, você não precisa alterar em nenhum lugar que faça referência ao alias, porque o alias agora retornará `/admin/usuarios/5` como no
exemplo acima.

O apelido de rota também funciona em grupos:

```php
Flight::group('/usuários', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_usuário');
});


// mais tarde no código
Flight::getUrl('visualização_usuário', [ 'id' => 5 ]); // retornará '/usuários/5'
```

## Informações de Rota

Se você deseja inspecionar informações de rota correspondente, você pode solicitar o objeto de rota
ser passado para sua função de retorno passando `true` como terceiro parâmetro no
método de rota. O objeto de rota sempre será o último parâmetro passado para sua
função de retorno.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP correspondem
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

  // Mostra o apelido atribuído a esta rota
  $route->alias;
}, true);
```

## Agrupamento de Rotas

Pode haver momentos em que você deseja agrupar rotas relacionadas juntas (como `/api/v1`).
Você pode fazer isso usando o método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/usuários', function () {
	// Corresponde a /api/v1/usuarios
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
	Flight::route('GET /usuários', function () {
	  // Corresponde a GET /api/v1/usuários
	});

	Flight::post('/posts', function () {
	  // Combina com POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Combina com PUT /api/v1/posts/1
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

Você ainda pode usar o agrupamento de rotas com o objeto `Engine` da seguinte maneira:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // use a variável $router
  $router->get('/usuários', function () {
	// Corresponde a GET /api/v1/usuários
  });

  $router->post('/posts', function () {
	// Corresponde a POST /api/v1/posts
  });
});
```  