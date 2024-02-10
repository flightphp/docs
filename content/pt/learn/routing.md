# Roteamento

> **Observação:** Quer entender mais sobre roteamento? Confira a página [why frameworks](/learn/why-frameworks) para uma explicação mais detalhada.

O roteamento básico no Flight é feito combinando um padrão de URL com uma função de retorno ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'Olá mundo!';
});
```

A função de retorno pode ser qualquer objeto que seja invocável. Portanto, você pode usar uma função regular:

```php
function hello(){
    echo 'Olá mundo!';
}

Flight::route('/', 'hello');
```

Ou um método de classe:

```php
class Greeting {
    public static function hello() {
        echo 'Olá mundo!';
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

As rotas são correspondidas na ordem em que são definidas. A primeira rota a corresponder a uma solicitação será invocada.

## Roteamento de Método

Por padrão, os padrões de rota são correspondidos a todos os métodos de solicitação. Você pode responder a métodos específicos colocando um identificador antes da URL.

```php
Flight::route('GET /', function () {
  echo 'Recebi uma solicitação GET.';
});

Flight::route('POST /', function () {
  echo 'Recebi uma solicitação POST.';
});
```

Você também pode mapear vários métodos para um único retorno usando o delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recebi uma solicitação GET ou POST.';
});
```

Além disso, você pode obter o objeto Router que tem alguns métodos auxiliares para você usar:

```php

$router = Flight::router();

// mapeia todos os métodos
$router->map('/', function() {
	echo 'Olá mundo!';
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
  // Isso corresponderá a /user/1234
});
```

Embora esse método esteja disponível, é recomendado usar parâmetros nomeados, ou
parâmetros nomeados com expressões regulares, pois são mais legíveis e fáceis de manter.

## Parâmetros Nomeados

Você pode especificar parâmetros nomeados em suas rotas que serão passados para
a sua função de retorno.

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

> **Observação:** Combinar grupos regex `()` com parâmetros nomeados não é suportado. :'\(

## Parâmetros Opcionais

Você pode especificar parâmetros nomeados que são opcionais para combinação envolvendo
segmentos entre parênteses.

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

Quaisquer parâmetros opcionais que não corresponderem serão passados como `NULL`.

## Curetagem

A correspondência é feita apenas em segmentos individuais de URL. Se você deseja corresponder a vários
segmentos, pode usar o curinga `*`.

```php
Flight::route('/blog/*', function () {
  // Isso corresponderá a /blog/2000/02/01
});
```

Para rotear todas as solicitações para um único retorno, você pode fazer:

```php
Flight::route('*', function () {
  // Faça algo
});
```

## Passando

Você pode passar a execução para a próxima rota correspondente retornando `true` de
sua função de retorno.

```php
Flight::route('/usuário/@nome', function (string $nome) {
  // Verifique alguma condição
  if ($nome !== "Bob") {
    // Continuar para a próxima rota
    return true;
  }
});

Flight::route('/usuário/*', function () {
  // Isso será chamado
});
```

## Alias de Rota

Você pode atribuir um alias a uma rota, para que a URL possa ser gerada dinamicamente mais tarde em seu código (como um modelo, por exemplo).

```php
Flight::route('/usuários/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_do_usuario');

// mais tarde no código em algum lugar
Flight::getUrl('visualização_do_usuario', [ 'id' => 5 ]); // retornará '/usuários/5'
```

Isso é especialmente útil se sua URL mudar. No exemplo acima, digamos que os usuários foram movidos para `/admin/usuarios/@id` em vez disso.
Com o uso de aliasing, você não precisa alterar onde refere o alias, porque o alias agora retornará `/admin/usuarios/5` como no
exemplo acima.

O alias de rota ainda funciona em grupos também:

```php
Flight::group('/usuários', function() {
    Flight::route('/@id', function($id) { echo 'usuário:'.$id; }, false, 'visualização_do_usuario');
});


// mais tarde no código em algum lugar
Flight::getUrl('visualização_do_usuario', [ 'id' => 5 ]); // retornará '/usuários/5'
```

## Informações da Rota

Se você deseja inspecionar as informações da rota correspondente, pode solicitar o objeto de rota
para ser passado para sua função de retorno passando `true` como o terceiro parâmetro no
método de rota. O objeto de rota será sempre o último parâmetro passado para sua
função de retorno.

```php
Flight::route('/', function(\flight\net\Route $rota) {
  // Array de métodos HTTP correspondidos
  $rota->métodos;

  // Array de parâmetros nomeados
  $rota->params;

  // Expressão regular correspondente
  $rota->regex;

  // Contém o conteúdo de qualquer '*' usado no padrão de URL
  $rota->splat;

  // Mostra o caminho da URL... se você realmente precisar
  $rota->modelo;

  // Mostra qual middleware está atribuído a isso
  $rota->middleware;

  // Mostra o alias atribuído a esta rota
  $rota->alias;
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
	  // Corresponde a POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Corresponde a PUT /api/v1/posts
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

### Agrupamento com Contexto de Objeto

Você ainda pode usar o agrupamento de rotas com o objeto `Engine` da seguinte maneira:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // use a variável de $router
  $router->get('/usuários', function () {
	// Corresponde a GET /api/v1/usuários
  });

  $router->post('/posts', function () {
	// Corresponde a POST /api/v1/posts
  });
});
```